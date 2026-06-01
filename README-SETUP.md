# UAS-API — Backend Laravel untuk Pajak Pintar UMKM

## Setup Lokal

### 1. Install Dependencies
```bash
cd UAS-API
composer install
```

### 2. Setup Database MySQL
Buat database MySQL bernama `uas_pajak_pintar`:
```sql
CREATE DATABASE uas_pajak_pintar;
```

### 3. Konfigurasi .env
Edit file `.env` dan sesuaikan:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uas_pajak_pintar
DB_USERNAME=root
DB_PASSWORD=your-password

SUPABASE_URL=https://qszkssxbnpbfewagksyz.supabase.co
SUPABASE_SERVICE_KEY=your-supabase-service-role-key
```

**Untuk mendapatkan SUPABASE_SERVICE_KEY:**
1. Buka Supabase Dashboard → Project Settings → API
2. Copy "service_role" key (bukan "anon" key)

### 4. Jalankan Migration & Seeder
```bash
php artisan migrate
php artisan db:seed
```

### 5. Jalankan Server
```bash
php artisan serve
```
Server berjalan di `http://localhost:8000`

---

## API Endpoints

### Artikel (User)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/articles` | Ambil semua artikel published |
| GET | `/api/articles/{id}` | Ambil detail artikel |

### Artikel (Admin)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/admin/articles` | Ambil semua artikel (termasuk draft) |
| POST | `/api/admin/articles` | Buat artikel baru |
| PUT | `/api/admin/articles/{id}` | Update artikel |
| DELETE | `/api/admin/articles/{id}` | Hapus artikel |

### Dokumen
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/documents` | Ambil dokumen (opsional: `?user_id=xxx&group_by_user=1`) |
| POST | `/api/documents` | Upload dokumen |
| DELETE | `/api/documents/{id}` | Hapus dokumen |
| GET | `/api/documents/{id}/download` | Download dokumen |

### User (Admin)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/api/admin/users` | Ambil semua user dari Supabase |
| POST | `/api/admin/users/role` | Ubah role user |

---

## Deploy ke Hosting Gratis

### Railway.app
1. Buat akun di [railway.app](https://railway.app)
2. Klik "New Project" → "Deploy from GitHub"
3. Push project Laravel ke GitHub
4. Railway akan auto-detect Laravel
5. Tambahkan MySQL database di Railway
6. Set environment variables (DB_*, SUPABASE_*)

### Render.com
1. Buat akun di [render.com](https://render.com)
2. Klik "New" → "Web Service"
3. Connect GitHub repo
4. Pilih "PHP" sebagai environment
5. Build command: `composer install --no-dev`
6. Start command: `php artisan serve --host=0.0.0.0 --port=$PORT`
7. Tambahkan MySQL database
8. Set environment variables

---

## Struktur Project
```
UAS-API/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── API/
│   │           ├── ArticleController.php
│   │           ├── DocumentController.php
│   │           └── UserController.php
│   └── Models/
│       ├── Article.php
│       └── Document.php
├── config/
│   └── services.php (Supabase config)
├── database/
│   ├── migrations/
│   │   ├── create_articles_table.php
│   │   └── create_documents_table.php
│   └── seeders/
│       └── ArticleSeeder.php
└── routes/
    └── api.php
```
