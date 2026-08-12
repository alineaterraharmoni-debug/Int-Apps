# Opty Tracker — CRM Internal Alinea Terra Harmoni

Laravel 12 + Livewire 3 + Tailwind (CDN) + DomPDF. Alur deploy sama kayak Cukupin: push ke GitHub lewat web UI, Railway yang build & jalanin composer install.

## Fitur
- Kanban pipeline: MQL → SQL → Develop → Closing WON / LOST, drag & drop antar stage
- Detail per opty: Judul, Nama Customer, Lini Produk, Estimasi TCV, GP (% & nominal otomatis), Rating (High/Med/Low), Ekspektasi Closing
- Assignment: Sales (yang create/pegang opty), Presales/Tim Produk, dan Tim Engineer (multi-pilih, diisi kalau sudah Close WIN)
- Halaman **Report** dengan filter (tanggal, kategori, stage, rating) — nampilin total number keseluruhan & per kategori, plus grafik bar & pie
- **Export PDF** dari report yang lagi difilter, lengkap dengan grafik (digenerate via QuickChart.io) dan tabel detail

## Setup di Railway (tanpa local dev, sama kayak Cukupin)

1. Push seluruh isi folder ini ke repo GitHub baru (lewat web UI: upload semua file, atau drag & drop kalau size-nya masuk).
2. Di Railway, buat project baru dari repo ini. Tambahin plugin **MySQL** (sama kayak Cukupin).
3. Set Environment Variables di Railway:
   - `APP_KEY` → generate dulu (lihat catatan di bawah), atau biarin kosong lalu jalanin `php artisan key:generate` via Railway shell/one-off command sekali di awal.
   - `APP_URL` → domain Railway/custom domain lo, pakai `https://`
   - Variable `MYSQLHOST`, `MYSQLPORT`, `MYSQLDATABASE`, `MYSQLUSER`, `MYSQLPASSWORD` biasanya udah otomatis ke-inject Railway dari plugin MySQL — `.env` di project ini udah disetel buat baca dari situ.
4. `nixpacks.toml` udah gw siapin biar Railway/Railpack install extension PHP yang dibutuhin (`pdo_mysql`, `mbstring`, `bcmath`, `gd`, `zip`, `dom`) — ini yang kemarin bikin masalah di Cukupin, jadi udah gw preventif dari awal.
5. Start command otomatis jalanin migrasi (`php artisan migrate --force`) tiap deploy, jadi gak perlu migrate manual tiap kali ada perubahan schema.
6. Setelah deploy pertama sukses, jalanin seeder tim sekali aja (via Railway shell/one-off command):
   ```
   php artisan db:seed --class=Database\\Seeders\\TeamMemberSeeder --force
   ```
   Ini yang ngisi daftar Sales/Presales/Engineer (Teddy, Ari, Hanif, Risky). Kalo mau nambah/ubah nama tim, tinggal edit `database/seeders/TeamMemberSeeder.php` terus seed ulang, atau nanti gw bisa bikinin halaman kelola tim kalau perlu.

## Catatan teknis
- Grafik di halaman Report (live, di browser) pakai Chart.js.
- Grafik di PDF export pakai gambar statis dari QuickChart.io (karena DomPDF gak bisa render JS/canvas) — jadi server Railway butuh akses internet keluar buat fetch gambar itu pas generate PDF (`config/dompdf` udah gw set `enable_remote = true` di `AppServiceProvider`).
- Kalau nanti mau nambah field atau ubah kategori/stage, tinggal edit constant di `app/Models/Opportunity.php` (`CATEGORIES`, `STAGES`, `RATINGS`) — otomatis kepake di form, board, filter, dan PDF.
- GP nominal **gak disimpen manual** — otomatis dihitung dari TCV x GP% (accessor `gp_nominal` di model), jadi selalu konsisten.

## Kalau mau update kode nanti
Sesuai kebiasaan Cukupin: zip cuma file yang berubah/nambah aja, jangan seluruh project, biar gampang di-apply lewat GitHub web UI.
