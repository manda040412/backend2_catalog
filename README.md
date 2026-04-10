# 🚀 Timur Raya Auto Parts Catalog — Backend Laravel

## ⚙️ Requirements
- PHP >= 8.1
- Composer
- MySQL 8.0+
- Node.js (untuk frontend)

---

## 📦 Instalasi

### 1. Clone & Install Dependencies
```bash
cd catalog-backend
composer install
```

### 2. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Edit `.env` — Sesuaikan koneksi database & mail
```env
DB_DATABASE=catalog_db
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=noreply@timurraya.com
```

### 4. Buat Database MySQL
```sql
CREATE DATABASE catalog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Jalankan Migrasi + Seeder
```bash
php artisan migrate --seed
```
> Ini akan otomatis mengisi semua data 555 (389 produk, 3826 match cars, 388 OEM numbers)

### 6. Jalankan Server
```bash
php artisan serve
# Server berjalan di http://127.0.0.1:8000
```

---

## 👤 Default Login

| Role        | Email                        | Password       |
|-------------|------------------------------|----------------|
| Super Admin | superadmin@timurraya.com     | SuperAdmin@123 |
| Admin       | admin@timurraya.com          | Admin@12345    |

> ⚠️ Ganti password setelah pertama login!

---

## 📡 API Endpoints

### Auth
| Method | Endpoint                      | Keterangan                    |
|--------|-------------------------------|-------------------------------|
| POST   | `/api/auth/register`          | Daftar akun baru              |
| POST   | `/api/auth/login`             | Login                         |
| POST   | `/api/auth/two-factor/send`   | Kirim ulang kode 2FA          |
| POST   | `/api/auth/two-factor/verify` | Verifikasi kode 2FA           |
| POST   | `/api/auth/logout`            | Logout                        |
| GET    | `/api/auth/me`                | Info user login               |

### Search (butuh login + 2FA + approved)
| Method | Endpoint                        | Keterangan                             |
|--------|----------------------------------|----------------------------------------|
| GET    | `/api/search/product?mode=item_code&q=SA-1762L` | Search by item code  |
| GET    | `/api/search/product?mode=oem&q=BBM2-34-380A`   | Search by OEM number |
| GET    | `/api/search/application?car_brand=MAZDA&car_type=BIANTE&year_from=2015` | Search by kendaraan |
| GET    | `/api/search/dropdown/brands`   | Dropdown merek mobil                   |
| GET    | `/api/search/dropdown/types`    | Dropdown model mobil                   |

### Produk
| Method | Endpoint             | Akses      |
|--------|----------------------|------------|
| GET    | `/api/products`      | Login      |
| GET    | `/api/products/{id}` | Login      |
| POST   | `/api/products`      | ADM / SADM |
| PUT    | `/api/products/{id}` | ADM / SADM |
| DELETE | `/api/products/{id}` | ADM / SADM |

### Admin
| Method  | Endpoint                      | Akses      |
|---------|-------------------------------|------------|
| GET     | `/api/admin/approvals`        | ADM / SADM |
| PATCH   | `/api/admin/approvals/{id}`   | ADM / SADM |
| GET     | `/api/admin/users`            | SADM only  |
| POST    | `/api/admin/users`            | SADM only  |
| PUT     | `/api/admin/users/{id}`       | SADM only  |
| DELETE  | `/api/admin/users/{id}`       | SADM only  |
| GET     | `/api/admin/activity-logs`    | SADM only  |

---

## 🗄️ Struktur Database

```
roles ──────────────< users
                         │
                         ├──< approvals
                         └──< activity_logs

categories ─────────< products
                         │
                         ├──< crosses (OEM numbers)
                         └──< match_cars (kesesuaian kendaraan)
```

### Primary Key Convention
| Tabel       | Format PK          | Contoh           |
|-------------|-------------------|------------------|
| roles       | `ROLE-XXX`        | `ROLE-001`       |
| categories  | `CAT-XXX`         | `CAT-001`        |
| products    | `PROD-XXXXXX`     | `PROD-SA1762L`   |
| match_cars  | `MATCH-XXXXXXXX`  | `MATCH-00000001` |

---

## 🔐 Alur Autentikasi

```
Register → Kode 2FA dikirim via email
         → Verifikasi 2FA
         → Status: Pending Approval
         → Admin approve
         → Bisa akses search & katalog
```

## 🛡️ Hak Akses User

| Role         | Data Internal | Approve User | Kelola Produk | User Management |
|--------------|:---:|:---:|:---:|:---:|
| Super Admin  | ✅  | ✅  | ✅  | ✅  |
| Admin        | ✅  | ✅  | ✅  | ❌  |
| Internal     | ✅  | ❌  | ❌  | ❌  |
| External     | ❌  | ❌  | ❌  | ❌  |

---

## 📊 Data Produk 555
- **389 produk** unik brand 555
- **3.826 match cars** — kesesuaian kendaraan
- **388 OEM numbers** — cross reference
- Data source: `Jikiu_555_Spare_Parts.csv` + `PART_DATABASE_FOR_VALIDATION.xlsx`

---

## 🔗 Frontend
Frontend Vue.js berjalan di `http://localhost:5173`
Pastikan `SANCTUM_STATEFUL_DOMAINS` dan `FRONTEND_URL` di `.env` sudah sesuai.
