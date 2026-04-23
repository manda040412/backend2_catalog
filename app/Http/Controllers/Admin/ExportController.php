<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Cross;
use App\Models\MatchCar;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * ExportController – Super Admin only
 * GET /api/export/{type}
 *
 * Output: .xlsx rapi (header bold navy, alternating rows, auto-width, freeze row 1)
 * Tanpa library external — murni PHP ZipArchive + SpreadsheetML Open XML.
 * Binary ditulis ke temp file lalu di-download agar tidak ada korupsi response.
 */
class ExportController extends Controller
{
    public function export(Request $request, string $type)
    {
        return match ($type) {
            'users'      => $this->exportUsers(),
            'products'   => $this->exportProducts(),
            'categories' => $this->exportCategories(),
            'crosses'    => $this->exportCrosses(),
            'match_cars' => $this->exportMatchCars(),
            default      => response()->json(['message' => 'Tipe export tidak dikenal.'], 400),
        };
    }

    // ── Data collectors ───────────────────────────────────────────

    private function exportUsers()
    {
        $users  = User::with('role')->get();
        $header = ['ID', 'Nama', 'Email', 'Role', 'Perusahaan', 'Telepon', 'Status', 'Dibuat'];
        $rows   = $users->map(fn($u) => [
            (string)($u->id_user ?? ''),
            (string)($u->name ?? ''),
            (string)($u->email ?? ''),
            (string)($u->role?->id_role_code ?? ''),
            (string)($u->company ?? ''),
            (string)($u->phone ?? ''),
            $u->is_approved ? 'Aktif' : 'Pending',
            (string)($u->created_at?->format('Y-m-d H:i:s') ?? ''),
        ])->toArray();

        return $this->xlsxDownload('users_' . now()->format('Ymd_His'), 'Users', $header, $rows);
    }

    private function exportProducts()
    {
        $products = Product::with('category')->get();
        $header   = ['ID Produk', 'Nama Produk', 'Brand', 'Item Code', 'Kategori', 'Print Description', 'Description', 'Dibuat'];
        $rows     = $products->map(fn($p) => [
            (string)($p->id_produk ?? ''),
            (string)($p->nama_produk ?? ''),
            (string)($p->brand_produk ?? ''),
            (string)($p->item_code ?? ''),
            (string)($p->category?->category_name ?? ''),
            (string)($p->print_description ?? ''),
            (string)($p->description ?? ''),
            (string)($p->created_at?->format('Y-m-d H:i:s') ?? ''),
        ])->toArray();

        return $this->xlsxDownload('products_' . now()->format('Ymd_His'), 'Products', $header, $rows);
    }

    private function exportCategories()
    {
        $cats   = Category::all();
        $header = ['ID Kategori', 'Nama Kategori', 'Deskripsi', 'Dibuat'];
        $rows   = $cats->map(fn($c) => [
            (string)($c->id_category ?? ''),
            (string)($c->category_name ?? ''),
            (string)($c->description ?? ''),
            (string)($c->created_at?->format('Y-m-d H:i:s') ?? ''),
        ])->toArray();

        return $this->xlsxDownload('categories_' . now()->format('Ymd_His'), 'Categories', $header, $rows);
    }

    private function exportCrosses()
    {
        $crosses = Cross::all();
        $header  = ['ID', 'ID Produk', 'Cross Brand', 'Cross Item Code', 'Cross Nama Produk', 'OEM Number', 'Dibuat'];
        $rows    = $crosses->map(fn($c) => [
            (string)($c->id_cross ?? ''),
            (string)($c->product_id ?? ''),
            (string)($c->cross_brand ?? ''),
            (string)($c->cross_item_code ?? ''),
            (string)($c->cross_nama_produk ?? ''),
            (string)($c->oem_number ?? ''),
            (string)($c->created_at?->format('Y-m-d H:i:s') ?? ''),
        ])->toArray();

        return $this->xlsxDownload('crosses_' . now()->format('Ymd_His'), 'Crosses', $header, $rows);
    }

    private function exportMatchCars()
    {
        $cars   = MatchCar::all();
        $header = ['ID', 'ID Produk', 'Item Code', 'Car Maker', 'Car Model', 'Year', 'Engine Desc', 'Chassis Code', 'Car Body', 'Dibuat'];
        $rows   = $cars->map(fn($c) => [
            (string)($c->id_match ?? ''),
            (string)($c->product_id ?? ''),
            (string)($c->item_code ?? ''),
            (string)($c->car_maker ?? ''),
            (string)($c->car_model ?? ''),
            (string)($c->year ?? ''),
            (string)($c->engine_desc ?? ''),
            (string)($c->chassis_code ?? ''),
            (string)($c->car_body ?? ''),
            (string)($c->created_at?->format('Y-m-d H:i:s') ?? ''),
        ])->toArray();

        return $this->xlsxDownload('match_cars_' . now()->format('Ymd_His'), 'Match Cars', $header, $rows);
    }

    // ── XLSX builder ──────────────────────────────────────────────

    /**
     * Build XLSX, tulis ke temp file, kirim sebagai download.
     * Menggunakan response()->download() agar binary tidak korup.
     */
    private function xlsxDownload(string $baseName, string $sheetName, array $header, array $rows)
    {
        // 1. Kumpulkan shared strings
        $strings = [];
        $add = function (string $s) use (&$strings) {
            if (!array_key_exists($s, $strings)) {
                $strings[$s] = count($strings);
            }
        };
        foreach ($header as $h) $add($h);
        foreach ($rows as $row) foreach ($row as $v) $add((string)$v);

        // 2. Build XML parts
        $sharedXml    = $this->buildSharedStrings($strings);
        $worksheetXml = $this->buildWorksheet($header, $rows, $strings);

        // 3. Tulis ke temp file dengan ZipArchive
        $tmpPath = tempnam(sys_get_temp_dir(), 'xlsx_') . '.xlsx';

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Gagal membuat file export.'], 500);
        }

        $zip->addFromString('[Content_Types].xml',        $this->xmlContentTypes());
        $zip->addFromString('_rels/.rels',                $this->xmlRootRels());
        $zip->addFromString('xl/workbook.xml',            $this->xmlWorkbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xmlWorkbookRels());
        $zip->addFromString('xl/sharedStrings.xml',       $sharedXml);
        $zip->addFromString('xl/styles.xml',              $this->xmlStyles());
        $zip->addFromString('xl/worksheets/sheet1.xml',   $worksheetXml);
        $zip->addFromString('docProps/app.xml',           $this->xmlAppProps());
        $zip->close();

        $filename = $baseName . '.xlsx';
        $mime     = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        // 4. response()->download() membaca file dan mengirim sebagai binary yang benar
        return response()->download($tmpPath, $filename, [
            'Content-Type'              => $mime,
            'Content-Disposition'       => "attachment; filename=\"{$filename}\"",
            'Access-Control-Allow-Origin' => '*',
        ])->deleteFileAfterSend(true); // hapus temp file setelah kirim
    }

    // ── XML builders ──────────────────────────────────────────────

    private function buildSharedStrings(array $strings): string
    {
        $count = count($strings);
        $xml   = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
        $xml  .= "<sst xmlns=\"http://schemas.openxmlformats.org/spreadsheetml/2006/main\" count=\"{$count}\" uniqueCount=\"{$count}\">";
        foreach (array_keys($strings) as $s) {
            $xml .= '<si><t xml:space="preserve">' . htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></si>';
        }
        return $xml . '</sst>';
    }

    private function buildWorksheet(array $header, array $rows, array $strings): string
    {
        $colCount = count($header);

        // Hitung lebar kolom otomatis
        $widths = [];
        foreach ($header as $i => $h) {
            $widths[$i] = min(max(mb_strlen($h) + 4, 12), 60);
        }
        foreach ($rows as $row) {
            foreach ($row as $i => $v) {
                if (isset($widths[$i])) {
                    $widths[$i] = min(max($widths[$i], mb_strlen((string)$v) + 2), 60);
                }
            }
        }

        $xml  = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        // Freeze row 1
        $xml .= '<sheetViews><sheetView tabSelected="1" workbookViewId="0">';
        $xml .= '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>';
        $xml .= '</sheetView></sheetViews>';

        // Column widths
        $xml .= '<cols>';
        foreach ($widths as $i => $w) {
            $c = $i + 1;
            $xml .= "<col min=\"{$c}\" max=\"{$c}\" width=\"{$w}\" bestFit=\"1\" customWidth=\"1\"/>";
        }
        $xml .= '</cols>';

        $xml .= '<sheetData>';

        // Header row — style 1
        $xml .= '<row r="1" ht="18" customHeight="1">';
        foreach ($header as $ci => $h) {
            $col = $this->colName($ci + 1);
            $si  = $strings[$h];
            $xml .= "<c r=\"{$col}1\" t=\"s\" s=\"1\"><v>{$si}</v></c>";
        }
        $xml .= '</row>';

        // Data rows — alternating style 2/3
        foreach ($rows as $ri => $row) {
            $rn    = $ri + 2;
            $style = ($ri % 2 === 0) ? '2' : '3';
            $xml  .= "<row r=\"{$rn}\">";
            foreach ($row as $ci => $val) {
                $col = $this->colName($ci + 1);
                $si  = $strings[(string)$val];
                $xml .= "<c r=\"{$col}{$rn}\" t=\"s\" s=\"{$style}\"><v>{$si}</v></c>";
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData>';

        // Auto filter
        $lastCol = $this->colName($colCount);
        $xml    .= "<autoFilter ref=\"A1:{$lastCol}1\"/>";

        return $xml . '</worksheet>';
    }

    private function xmlStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font><sz val="10"/><name val="Calibri"/><family val="2"/></font>
    <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Calibri"/><family val="2"/></font>
    <font><sz val="10"/><name val="Calibri"/><family val="2"/></font>
  </fonts>
  <fills count="5">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0F2066"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF0F2F7"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="4">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0"><alignment vertical="center" horizontal="left"/></xf>
    <xf numFmtId="0" fontId="2" fillId="3" borderId="0" xfId="0"><alignment vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="4" borderId="0" xfId="0"><alignment vertical="center"/></xf>
  </cellXfs>
</styleSheet>';
    }

    private function xmlWorkbook(string $sheetName): string
    {
        $safe = htmlspecialchars($sheetName, ENT_XML1, 'UTF-8');
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <bookViews><workbookView xWindow="0" yWindow="0" windowWidth="16384" windowHeight="8192"/></bookViews>
  <sheets><sheet name="' . $safe . '" sheetId="1" r:id="rId1"/></sheets>
</workbook>';
    }

    private function xmlWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"     Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"        Target="styles.xml"/>
</Relationships>';
    }

    private function xmlRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"     Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>';
    }

    private function xmlContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml"  ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml"          ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/sharedStrings.xml"     ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
  <Override PartName="/xl/styles.xml"            ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/docProps/app.xml"         ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>';
    }

    private function xmlAppProps(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
  <Application>TRA Catalog System</Application>
</Properties>';
    }

    /** Convert 1-based column index ke huruf Excel: 1→A, 26→Z, 27→AA */
    private function colName(int $n): string
    {
        $name = '';
        while ($n > 0) {
            $n--;
            $name = chr(65 + ($n % 26)) . $name;
            $n    = intdiv($n, 26);
        }
        return $name;
    }
}