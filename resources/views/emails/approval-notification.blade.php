<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Status Akun TRAD Catalog</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background-color: #eef1f5;
      color: #333;
    }
    .wrapper {
      max-width: 560px;
      margin: 40px auto;
      padding: 0 16px 40px;
    }

    /* ── Header ── */
    .header {
      background: linear-gradient(135deg, #0F2066 0%, #1a3a9c 100%);
      border-radius: 16px 16px 0 0;
      padding: 32px 40px 28px;
      text-align: center;
    }
    .header-logo {
      font-size: 28px;
      font-weight: 800;
      color: #fff;
      letter-spacing: 1px;
    }
    .header-tagline {
      font-size: 12px;
      color: rgba(255,255,255,0.6);
      margin-top: 4px;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    /* ── Body ── */
    .body {
      background: #ffffff;
      padding: 40px 40px 32px;
    }
    .greeting {
      font-size: 20px;
      font-weight: 700;
      color: #0F2066;
      margin-bottom: 12px;
    }
    .intro {
      font-size: 15px;
      color: #555;
      line-height: 1.6;
      margin-bottom: 28px;
    }

    /* ── Status Box ── */
    .status-box {
      border-radius: 14px;
      padding: 28px 24px;
      text-align: center;
      margin-bottom: 28px;
    }
    .status-box.approved {
      background: #f0fdf4;
      border: 2px solid #86efac;
    }
    .status-box.rejected {
      background: #fff8f0;
      border: 2px solid #fdba74;
    }
    .status-icon {
      font-size: 48px;
      margin-bottom: 12px;
    }
    .status-title {
      font-size: 22px;
      font-weight: 800;
      margin-bottom: 8px;
    }
    .status-box.approved .status-title { color: #15803d; }
    .status-box.rejected .status-title { color: #c2410c; }
    .status-desc {
      font-size: 14px;
      line-height: 1.6;
      color: #666;
    }

    /* ── Notes Box ── */
    .notes-box {
      background: #f8f9fc;
      border-left: 4px solid #0F2066;
      border-radius: 8px;
      padding: 16px 18px;
      font-size: 14px;
      color: #555;
      line-height: 1.6;
      margin-bottom: 28px;
    }
    .notes-box strong {
      display: block;
      color: #0F2066;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      margin-bottom: 6px;
    }

    /* ── CTA Button ── */
    .cta-wrap {
      text-align: center;
      margin-bottom: 28px;
    }
    .cta-btn {
      display: inline-block;
      background: #0F2066;
      color: #ffffff !important;
      text-decoration: none;
      font-size: 15px;
      font-weight: 700;
      padding: 14px 36px;
      border-radius: 12px;
      letter-spacing: 0.3px;
    }

    /* ── Divider ── */
    .divider {
      border: none;
      border-top: 1px solid #eee;
      margin: 24px 0;
    }

    .help-text {
      font-size: 13px;
      color: #888;
      line-height: 1.6;
    }
    .help-text a {
      color: #0F2066;
      font-weight: 600;
      text-decoration: none;
    }

    /* ── Footer ── */
    .footer {
      background: #0F2066;
      border-radius: 0 0 16px 16px;
      padding: 24px 40px;
      text-align: center;
    }
    .footer-name {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 4px;
    }
    .footer-address {
      font-size: 12px;
      color: rgba(255,255,255,0.5);
      line-height: 1.6;
    }
    .footer-note {
      margin-top: 16px;
      font-size: 11px;
      color: rgba(255,255,255,0.35);
    }
  </style>
</head>
<body>
  <div class="wrapper">

    <!-- Header -->
    <div class="header">
      <div class="header-logo">TRAD<span style="color:#f97316;">.</span>Catalog</div>
      <div class="header-tagline">PT. Timur Raya Anugerah Damai</div>
    </div>

    <!-- Body -->
    <div class="body">
      <p class="greeting">Halo, {{ $userName }}! 👋</p>
      <p class="intro">
        Kami ingin memberitahukan status terbaru mengenai permohonan pendaftaran akun Anda di <strong>TRAD Catalog</strong>.
      </p>

      <!-- Status Box -->
      @if($status === 'approved')
      <div class="status-box approved">
        <div class="status-icon">🎉</div>
        <div class="status-title">Akun Anda Telah Disetujui!</div>
        <p class="status-desc">
          Selamat! Akun TRAD Catalog Anda telah diverifikasi dan disetujui oleh admin kami.
          Kini Anda bisa mengakses seluruh fitur katalog produk kami.
        </p>
      </div>
      @else
      <div class="status-box rejected">
        <div class="status-icon">📋</div>
        <div class="status-title">Permohonan Memerlukan Tindak Lanjut</div>
        <p class="status-desc">
          Permohonan pendaftaran akun Anda saat ini tidak dapat kami proses.
          Silakan hubungi tim kami untuk informasi lebih lanjut.
        </p>
      </div>
      @endif

      <!-- Notes (if any) -->
      @if($notes)
      <div class="notes-box">
        <strong>Catatan dari Admin</strong>
        {{ $notes }}
      </div>
      @endif

      <!-- CTA -->
      @if($status === 'approved')
      <div class="cta-wrap">
        <a href="{{ config('app.url') }}" class="cta-btn">Masuk ke TRAD Catalog →</a>
      </div>
      @endif

      <hr class="divider"/>

      <p class="help-text">
        Butuh bantuan? Hubungi kami di
        <a href="mailto:manda@tranugerah.com">manda@tranugerah.com</a>
        atau kunjungi website kami.
      </p>
    </div>

    <!-- Footer -->
    <div class="footer">
      <div class="footer-name">PT. Timur Raya Anugerah Damai</div>
      <div class="footer-address">
        Jalan Danau Sunter Barat Blok A4 No.3<br/>
        Jakarta Utara, Indonesia
      </div>
      <div class="footer-note">
        Email ini dikirim otomatis, mohon tidak membalas langsung ke email ini.
      </div>
    </div>

  </div>
</body>
</html>