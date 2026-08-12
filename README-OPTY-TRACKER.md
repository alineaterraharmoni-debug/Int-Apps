# Opty Tracker — Superapp Internal Alinea Terra Harmoni (Fase 1)

Laravel 12 + Livewire 3 + Tailwind (CDN) + DomPDF + PWA. Alur deploy sama kayak Cukupin: push ke GitHub lewat web UI, Railway yang build & jalanin composer install.

## Yang baru di Fase 1 (dari CRM standalone jadi shell superapp)

- **Home shell**: layar utama nampilin grid 4 modul (CRM, Project & Tiket, Dokumen, Report Bisnis) — 2 modul terakhir masih "segera hadir" nunggu fase berikutnya
- **Navigasi mobile sesuai spek**: bottom nav isi modul utama (Home/CRM/Report), begitu masuk modul CRM baru muncul submenu tab di atas (Board / Report / Customer Insight) — bukan numpuk semua menu di bawah
- **Customer master data**: semua opty sekarang terhubung ke tabel `customers` (bukan teks bebas lagi), lengkap sama PIC, industri, kontak
- **Customer Insight**: halaman analisa customer — total opty, total TCV, total WON, transaksi terakhir, per rentang tanggal, plus tombol tandai **★ Fokus Customer** buat nge-highlight target strategi marketing
- **Business Review periodik**: report sekarang punya toggle **Bulanan / Kuartalan / Tahunan** dan otomatis bandingin ke periode sebelumnya (growth % di tiap angka) — bukan cuma filter tanggal manual
- **PWA**: udah bisa "Add to Home Screen" dari browser HP (manifest + service worker udah include). Icon masih placeholder simpel, ganti nanti kalau udah ada logo final di ukuran 192x192 & 512x512 (`public/icons/`)

## Fitur inti (tetap dari fase sebelumnya)
- Kanban pipeline: MQL → SQL → Develop → Closing WON / LOST, drag & drop (desktop) / tap-to-edit (mobile)
- Detail opty: Judul, Customer (sekarang dari master data), Lini Produk, Estimasi TCV, GP (% & nominal otomatis), Rating, Ekspektasi Closing
- Assignment: Sales, Presales/Tim Produk, Tim Engineer (multi-select, diisi kalau Close WIN)
- Export PDF business review lengkap grafik (via QuickChart.io)

## Setup di Railway (tanpa local dev, sama kayak Cukupin)

1. Push seluruh isi folder ini ke repo GitHub (baru atau replace repo Opty Tracker yang lama).
2. Railway project + plugin **MySQL** (kalau lanjut dari repo lama, plugin-nya udah ada, tinggal deploy ulang).
3. Environment Variables sama kayak sebelumnya (`APP_KEY`, `APP_URL`, kredensial MySQL otomatis dari plugin Railway).
4. `nixpacks.toml` udah nyiapin extension PHP yang dibutuhin.
5. Start command otomatis jalanin `php artisan migrate --force` tiap deploy — migration baru (`customers` table + `customer_id` di opportunities) bakal otomatis ke-apply.
6. Kalau ini pertama kali deploy dari awal, jalanin seeder tim sekali:
   ```
   php artisan db:seed --class=Database\\Seeders\\TeamMemberSeeder --force
   ```

## Catatan teknis
- `customer_name` (string) di tabel opportunities dipertahankan sebagai cache tampilan, tapi sumber kebenarannya sekarang `customer_id` → tabel `customers`. Otomatis disinkron tiap simpan opty.
- Logic report (Monthly/Quarterly/Yearly + growth %) dipusatkan di `app/Services/OpportunityReportService.php` — dipakai bareng sama halaman Report *dan* PDF export, jadi angka di keduanya selalu konsisten.
- Business review pakai `created_at` buat filter periode, sementara "Total Closing WON" & Customer Insight pakai `closed_at` (tanggal opty ditandai WON) — biar analisa "kapan customer transaksi" akurat.
- Kalau nanti mau nambah field atau ubah kategori/stage, tinggal edit constant di `app/Models/Opportunity.php`.

## Fase berikutnya
Fase 2 (Modul Dokumen otomatis), Fase 3 (Project Ops — lisensi/maintenance), Fase 4 (Tiketing) — gas kapan pun lo siap.

## Kalau mau update kode nanti
Sesuai kebiasaan Cukupin: zip cuma file yang berubah/nambah aja, jangan seluruh project, biar gampang di-apply lewat GitHub web UI.

