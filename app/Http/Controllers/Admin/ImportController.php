<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Cross;
use App\Models\MatchCar;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * ImportController – Super Admin only
 * POST /api/import/{type}
 * type: users | products | categories | crosses | match_cars
 *
 * Upload multipart/form-data dengan field "file" berisi file CSV.
 * Baris pertama CSV adalah header (dilewati).
 */
class ImportController extends Controller
{
    public function import(Request $request, string $type)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $path = $request->file('file')->getRealPath();
        $rows = $this->parseCsv($path);

        if (count($rows) < 2) {
            return response()->json(['message' => 'File CSV kosong atau hanya berisi header.'], 422);
        }

        // Hapus header row
        array_shift($rows);

        return match ($type) {
            'users'      => $this->importUsers($rows),
            'products'   => $this->importProducts($rows),
            'categories' => $this->importCategories($rows),
            'crosses'    => $this->importCrosses($rows),
            'match_cars' => $this->importMatchCars($rows),
            default      => response()->json(['message' => 'Tipe import tidak dikenal.'], 400),
        };
    }

    // ─────────────────────────────────────────────────────────────
    // USERS
    // Kolom CSV: name, email, role_code, company, phone, password
    // ─────────────────────────────────────────────────────────────
    private function importUsers(array $rows)
    {
        $imported = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $lineNum = $i + 2; // +2 karena header + 0-index
            if (empty(array_filter($row))) continue;

            [$name, $email, $roleCode, $company, $phone, $password] = array_pad($row, 6, '');

            $v = Validator::make(compact('name', 'email'), [
                'name'  => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
            ]);

            if ($v->fails()) {
                $errors[] = "Baris {$lineNum}: " . implode(', ', $v->errors()->all());
                continue;
            }

            $role = Role::where('id_role_code', strtoupper(trim($roleCode)))->first();
            if (!$role) {
                $errors[] = "Baris {$lineNum}: Role '{$roleCode}' tidak ditemukan.";
                continue;
            }

            User::create([
                'name'        => trim($name),
                'email'       => strtolower(trim($email)),
                'password'    => Hash::make($password ?: 'Password123!'),
                'role_id'     => $role->id_role,
                'company'     => trim($company) ?: null,
                'phone'       => trim($phone) ?: null,
                'is_approved' => 1,
            ]);

            $imported++;
        }

        return response()->json([
            'message'  => "Import selesai. {$imported} user berhasil diimport.",
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // PRODUCTS
    // Kolom CSV: nama_produk, brand_produk, item_code, category_id, print_description, description, is_internal_only
    // ─────────────────────────────────────────────────────────────
    private function importProducts(array $rows)
    {
        $imported = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $lineNum = $i + 2;
            if (empty(array_filter($row))) continue;

            [$namaProduk, $brandProduk, $itemCode, $categoryId, $printDesc, $desc, $internalOnly] = array_pad($row, 7, '');

            $v = Validator::make([
                'nama_produk'  => $namaProduk,
                'brand_produk' => $brandProduk,
                'item_code'    => $itemCode,
                'category_id'  => $categoryId,
            ], [
                'nama_produk'  => 'required|string|max:255',
                'brand_produk' => 'required|string|max:255',
                'item_code'    => 'required|string|max:255',
                'category_id'  => 'required|exists:categories,id_category',
            ]);

            if ($v->fails()) {
                $errors[] = "Baris {$lineNum}: " . implode(', ', $v->errors()->all());
                continue;
            }

            Product::create([
                'id_produk'         => Product::generateId(),
                'nama_produk'       => trim($namaProduk),
                'brand_produk'      => trim($brandProduk),
                'item_code'         => trim($itemCode),
                'category_id'       => trim($categoryId),
                'print_description' => trim($printDesc) ?: null,
                'description'       => trim($desc) ?: null,
                'is_internal_only'  => in_array(strtolower(trim($internalOnly)), ['1', 'ya', 'yes', 'true']) ? 1 : 0,
            ]);

            $imported++;
        }

        return response()->json([
            'message'  => "Import selesai. {$imported} produk berhasil diimport.",
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // CATEGORIES
    // Kolom CSV: category_name, description
    // ─────────────────────────────────────────────────────────────
    private function importCategories(array $rows)
    {
        $imported = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $lineNum = $i + 2;
            if (empty(array_filter($row))) continue;

            [$categoryName, $description] = array_pad($row, 2, '');

            $v = Validator::make(['category_name' => $categoryName], [
                'category_name' => 'required|string|max:255',
            ]);

            if ($v->fails()) {
                $errors[] = "Baris {$lineNum}: " . implode(', ', $v->errors()->all());
                continue;
            }

            Category::create([
                'id_category'   => Category::generateId(),
                'category_name' => trim($categoryName),
                'description'   => trim($description) ?: null,
            ]);

            $imported++;
        }

        return response()->json([
            'message'  => "Import selesai. {$imported} kategori berhasil diimport.",
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // CROSSES
    // Kolom CSV: product_id, cross_brand, cross_item_code, cross_nama_produk, oem_number
    // ─────────────────────────────────────────────────────────────
    private function importCrosses(array $rows)
    {
        $imported = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $lineNum = $i + 2;
            if (empty(array_filter($row))) continue;

            [$productId, $crossBrand, $crossItemCode, $crossNamaProduk, $oemNumber] = array_pad($row, 5, '');

            $v = Validator::make([
                'product_id' => $productId,
                'oem_number' => $oemNumber,
            ], [
                'product_id' => 'required|exists:products,id_produk',
                'oem_number' => 'required|string|max:255',
            ]);

            if ($v->fails()) {
                $errors[] = "Baris {$lineNum}: " . implode(', ', $v->errors()->all());
                continue;
            }

            Cross::create([
                'product_id'        => trim($productId),
                'cross_brand'       => trim($crossBrand) ?: null,
                'cross_item_code'   => trim($crossItemCode) ?: null,
                'cross_nama_produk' => trim($crossNamaProduk) ?: null,
                'oem_number'        => trim($oemNumber),
            ]);

            $imported++;
        }

        return response()->json([
            'message'  => "Import selesai. {$imported} data cross berhasil diimport.",
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // MATCH CARS
    // Kolom CSV: product_id, item_code, car_maker, car_model, year, engine_desc, chassis_code, car_body
    // ─────────────────────────────────────────────────────────────
    private function importMatchCars(array $rows)
    {
        $imported = 0;
        $errors   = [];

        foreach ($rows as $i => $row) {
            $lineNum = $i + 2;
            if (empty(array_filter($row))) continue;

            [$productId, $itemCode, $carMaker, $carModel, $year, $engineDesc, $chassisCode, $carBody] = array_pad($row, 8, '');

            $v = Validator::make([
                'product_id' => $productId,
                'item_code'  => $itemCode,
                'car_maker'  => $carMaker,
                'car_model'  => $carModel,
            ], [
                'product_id' => 'required|exists:products,id_produk',
                'item_code'  => 'required|string|max:255',
                'car_maker'  => 'required|string|max:255',
                'car_model'  => 'required|string|max:255',
            ]);

            if ($v->fails()) {
                $errors[] = "Baris {$lineNum}: " . implode(', ', $v->errors()->all());
                continue;
            }

            // Generate id_match
            $lastId = MatchCar::orderByDesc('id_match')->value('id_match');
            $nextNum = 1;
            if ($lastId && preg_match('/MC-(\d+)$/', $lastId, $m)) {
                $nextNum = intval($m[1]) + 1;
            }
            $idMatch = 'MC-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            MatchCar::create([
                'id_match'    => $idMatch,
                'product_id'  => trim($productId),
                'item_code'   => trim($itemCode),
                'car_maker'   => trim($carMaker),
                'car_model'   => trim($carModel),
                'year'        => trim($year) ?: null,
                'engine_desc' => trim($engineDesc) ?: null,
                'chassis_code'=> trim($chassisCode) ?: null,
                'car_body'    => trim($carBody) ?: null,
            ]);

            $imported++;
        }

        return response()->json([
            'message'  => "Import selesai. {$imported} data match car berhasil diimport.",
            'imported' => $imported,
            'errors'   => $errors,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    private function parseCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            // Strip UTF-8 BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }
        return $rows;
    }
}