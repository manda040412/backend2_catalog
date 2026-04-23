<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Kode Verifikasi TRAD Catalog</title>
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
    .header-logo span {
      color: #f97316;
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
      margin-bottom: 32px;
    }

    /* ── OTP Box ── */
    .otp-label {
      font-size: 11px;
      font-weight: 700;
      color: #999;
      text-transform: uppercase;
      letter-spacing: 2px;
      text-align: center;
      margin-bottom: 12px;
    }
    .otp-box {
      background: #f0f4ff;
      border: 2px dashed #b8c5f0;
      border-radius: 14px;
      padding: 28px 20px;
      text-align: center;
      margin-bottom: 28px;
    }
    .otp-digits {
      display: inline-flex;
      gap: 10px;
      justify-content: center;
      flex-wrap: wrap;
    }
    .otp-digit {
      display: inline-block;
      width: 48px;
      height: 56px;
      line-height: 56px;
      background: #ffffff;
      border: 2px solid #0F2066;
      border-radius: 10px;
      font-size: 26px;
      font-weight: 800;
      color: #0F2066;
      text-align: center;
      box-shadow: 0 2px 8px rgba(15,32,102,0.10);
    }
    .otp-copy {
      margin-top: 14px;
      font-size: 13px;
      color: #888;
    }
    .otp-copy strong {
      font-family: monospace;
      font-size: 15px;
      color: #0F2066;
      background: #e8ecfa;
      padding: 2px 8px;
      border-radius: 6px;
    }

    /* ── Info boxes ── */
    .info-row {
      display: flex;
      gap: 12px;
      margin-bottom: 24px;
    }
    .info-box {
      flex: 1;
      background: #f8f9fc;
      border-radius: 12px;
      padding: 16px;
      text-align: center;
    }
    .info-box .icon { font-size: 22px; margin-bottom: 6px; }
    .info-box .title { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
    .info-box .value { font-size: 14px; font-weight: 700; color: #333; }

    /* ── Warning ── */
    .warning {
      background: #fff8ec;
      border-left: 4px solid #f97316;
      border-radius: 8px;
      padding: 14px 16px;
      font-size: 13px;
      color: #7a4a10;
      line-height: 1.6;
      margin-bottom: 28px;
    }
    .warning strong { color: #c45a00; }

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
    <div class="header-logo">
      <img src="https://domainkamu.com/images/logo.png" alt="TRA Logo" style="height:60px; margin-bottom:10px;">
    </div>
    <div class="header-tagline">PT. Timur Raya Anugerah Damai</div>

    <!-- Body -->
    <div class="body">
      <p class="greeting">Halo, <?php echo e($userName); ?>! 👋</p>
      <p class="intro">
        Terima kasih telah mendaftar di <strong>TRAD Catalog</strong>. 
        Gunakan kode verifikasi di bawah ini untuk menyelesaikan proses registrasi akun Anda.
      </p>

      <!-- OTP -->
      <p class="otp-label">Kode Verifikasi Anda</p>
      <div class="otp-box">
        <div class="otp-digits">
          <?php $__currentLoopData = str_split($code); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $digit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span class="otp-digit"><?php echo e($digit); ?></span>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <p class="otp-copy">atau ketik langsung: <strong><?php echo e($code); ?></strong></p>
      </div>

      <!-- Info boxes -->
      <div class="info-row">
        <div class="info-box">
          <div class="icon">⏱️</div>
          <div class="title">Berlaku Selama</div>
          <div class="value">10 Menit</div>
        </div>
        <div class="info-box">
          <div class="icon">🔢</div>
          <div class="title">Panjang Kode</div>
          <div class="value">6 Digit</div>
        </div>
        <div class="info-box">
          <div class="icon">📧</div>
          <div class="title">Dikirim Ke</div>
          <div class="value" style="font-size:12px; word-break:break-all;"><?php echo e($userName); ?></div>
        </div>
      </div>

      <!-- Warning -->
      <div class="warning">
        <strong>⚠️ Jangan bagikan kode ini kepada siapapun.</strong><br/>
        Tim TRAD tidak akan pernah meminta kode verifikasi Anda melalui telepon, WhatsApp, atau saluran lain. 
        Jika Anda tidak merasa mendaftar, abaikan email ini.
      </div>

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
</html><?php /**PATH D:\Manda\VSCode\Catalog\catalog-backend\resources\views/emails/two-factor.blade.php ENDPATH**/ ?>