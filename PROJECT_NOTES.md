# SIMPEG RSKK — Catatan Proyek (Checkpoint)

> File ini berisi ringkasan kondisi proyek + pertanyaan terbuka.
> Saat buka sesi baru, mulai dengan: **baca PROJECT_NOTES.md**

## Tentang Proyek

- Aplikasi **Sistem Informasi Kepegawaian RSKK** berbasis **Laravel 13** (PHP 8.3, MySQL `simpeg_rskk`, Tailwind CDN, Blade + layout klasik).
- Struktur: `routes/web.php`, `app/Models/*` (16 model), `app/Http/Controllers/*`, view Blade di `resources/views`.
- Auto-load pakai `php artisan serve`; DB dikelola manual via HeidiSQL + `database/seeders/simpeg_rskk_setup.sql`.
- **Catatan akses DB saat ini:** NIP user bukan `000000000000000001`, tapi `1` (Super Admin), `2` (kevin — password tidak diketahui), `3` (Rayn/user — **direset ke `user` 2026-09-08**). Di DB dev tabel `audit_logs` & `app_notifications` sempat hilang → sudah ditambahkan via `php artisan migrate --path=...2024_01_01_000006_create_support_tables.php`.

## Akun & Login

- Login pakai **NIP** (bukan email). `User::username()` mengembalikan `'nip'`.
- Super Admin dev: NIP `1`, password `superadmin123` (direset saat sesi 2026-09-08).
- Role: `super_admin`, `admin`, `user`. Middleware `role:` (CheckRole) memblokir akun nonaktif.
- Mode debug login (`?debug=1`) **SUDAH DIHAPUS**.

## Alur yang sudah jalan

1. **Login/logout** (`AuthenticatedSessionController`) → redirect sesuai role.
2. **Manajemen Akun** (`admin/accounts`) — index/search, buat akun (SA: semua role; Admin: hanya `user` + auto buat `employees` kosong), toggle aktif, reset password, ubah role, hapus (proteksi SA & akun sendiri).
3. **Data Pegawai** (`employees`) — index/search/filter status, show (6 tab read-only), **edit penuh** (form lengkap + partial `employees._field` — sudah diperbaiki).
4. **Profil Saya** (user) — form → ChangeRequest `module_type='profil'` pending (+ cek duplikat pending). Saat submit mengirim notifikasi ke Super Admin.
5. **Approval Center** (`approvals`) — daftar pending, banding old vs new, approve (hanya SA; `ChangeRequest::approve()` → update employee) & reject (wajib alasan). Saat proses → kirim notifikasi ke pegawai + AuditLog.
6. **Dashboard** (`DashboardController`) — role user: ringkasan profil, stat pengajuan, notifikasi, riwayat pengajuan. Role admin/SA: total/aktif/nonaktif/baru, approval pending, dokumen belum terverifikasi, statistik unit kerja (bar), pengajuan terbaru, aktivitas sistem, notifikasi.
7. **Master Data** (`master/{type}`) — CRUD untuk 6 tipe: `units`, `jabatan`, `golongan`, `kategori`, `pendidikan`, `status`. Proteksi hapus (dipakai employee → ditolak). Routes `master.index/create/store/edit/update/destroy`.
8. **Notifikasi** (`notifications`) — tabel `app_notifications`, semua role, badge unread di sidebar + halaman daftar + "tandai semua dibaca".
9. **Dokumen Digital** (`documents`) — user upload dokumen sendiri (kategori tetap, PDF/JPG/PNG ≤5MB) → status `belum_diverifikasi` + notif ke admin; admin/SA: daftar semua dokumen (filter status + search), verifikasi, tolak (wajib alasan → notif ke pegawai), unduh, hapus. Upload admin langsung `terverifikasi`. File di `storage/app/public/documents` (sudah `storage:link`). AuditLog tercatat. Routes `documents.*`; menu sidebar "Dokumen". Tab Dokumen di detail pegawai berupa kartu + aksi.
10. **Audit Log** (`audit`) — halaman terpusat admin/SA: filter modul & aksi, pagination, kolom waktu/pengguna/modul/aksi/aktivitas/IP. Routes `audit.index`; menu sidebar "Audit Log".

## Skema DB singkat

- Master: `work_units`, `positions`, `ranks`, `employee_categories`, `education_levels`, `employment_statuses`.
- Inti: `users` (login NIP), `employees`.
- Riwayat: `employee_educations`, `employee_position_histories`, `employee_rank_histories`, `employee_salary_histories`, `employee_mutations`, `employee_trainings`, `employee_awards`, `employee_disciplines`, `employee_families`, `documents` (`status_verifikasi` enum: belum_diverifikasi/terverifikasi/ditolak).
- Workflow: `change_requests`, `audit_logs`, `app_notifications`.

## Known issues / aturan yang perlu diingat

1. `Notification::send()` dipakai (bukan `push()`) karena `push()` bentrok method Eloquent.
2. `MasterDataController` config disimpan di **method** `types()` — closure/`fn()` tidak bisa di property class PHP.
3. Migrasi default Laravel `0001_01_01_000000_create_users_table.php` sudah dinonaktifkan (kosong); users/sessions/reset_tokens dibuat oleh `2024_01_01_000002`. Jangan jalankan `migrate:fresh` tanpa cek migration 0006 (audit_logs + app_notifications) dan seeder.
4. `employees/_field.blade.php` wajib ada — dipakai `employees/edit.blade.php`.
5. Setup manual DB bisa tidak menyertakan `audit_logs`/`app_notifications` → jalankan migrasi `2024_01_01_000006` saja, bukan semua.
6. `ChangeRequest` status `reviewed` belum dipakai di alur; hanya pending/approved/rejected.
7. Login user via curl/HTTP 419 saat token lama: ambil `_token` dari halaman yang sudah login, dan URL-encode token saat `application/x-www-form-urlencoded`.
8. Password dev Rayn di-reset → NIP `3` = `user`; NIP `2` (kevin) password tidak diketahui (belum dipakai lagi).

## Pedoman desain

- **`UI_UX_DESIGN.md`** = pedoman resmi UI/UX SIMPEG RSKK. **Sudah diterapkan (2026-09-08)** di kode:
  - Palet baru di `layouts/app.blade.php` `:root` → primary biru (`--blue-600 #2563EB`), secondary teal/cyan, bg `--paper #F6F8FB`, border `--line #E5EAF0`, teks `--ink-900 #172033`/`--ink-500 #6B7280`. Variabel lama (`--teal-*`, `--clay-*`, `--ink-*`) di-remap agar view lama ikut berubah warna.
  - Font **Inter** (Google Fonts) menggantikan Segoe UI.
  - Layout baru: **topbar** (hamburger toggle sidebar collapse, judul+breadcrumb, bell dropdown notifikasi, profile dropdown + logout) + **sidebar light** (rounded, hover/active biru, group per role, badge unread) + content `max-w-7xl`.
  - Status badge baru: hijau (aktif/terverifikasi/disetujui), amber (menunggu/ditolak-menunggu), merah (ditolak/nonaktif).
  - Login dirombak: bg abu-abu muda `#F6F8FB` + kartu putih shadow tipis + button biru (logo tetap).
  - Dashboard: stat-card ber-ikon + progress kelengkapan profil (user) + unit kerja bar gradient teal→blue.
  - Detail pegawai: profile header premium (foto lingkaran, nama, NIP, jabatan•unit, badge status+golongan), tab info jadi card 2 kolom, **timeline** untuk pendidikan/jabatan/pangkat, kartu untuk penghargaan & keluarga.
  - Empty state (ikon+judul+deskripsi+CTA) di halaman kosong; class `.skeleton` + micro-hover siap dipakai.
- Belum dari modul (backlog UI): notification dropdown belum auto-read on open (masih lewat klik), pagination style default Laravel, submenu collapsible di sidebar belum (flat grouping), confirmation modal belum (masih `confirm()` JS), skeleton belum dipasang di halaman skenario nyata, responsive tabel→card belum.

## Polish layout 2026-09-08 (setelah redesign)

- Semua halaman kini pakai **header konsisten**: `.page-head` (`.page-title` 21px + `.page-desc` + aksi di kanan) + breadcrumb di topbar (`@section('crumb', …)`, default "Utama").
- **Footer** kecil ditambahkan di layout (`© {{ date('Y') }} SIMPEG RSKK …`).
- **Dashboard admin ditata ulang**: 4 stat-card ikon → panel 2 kolom (kiri: **Pengajuan Menunggu Approval** = daftar + badge count + Review; kanan: mini-stat Dokumen/Notifikasi + **Statistik Unit Kerja** bar) → **Aktivitas Sistem** 2-kolom di bawah. Dashboard user: 3 stat-card status + Ringkasan Profil (dengan progress kelengkapan) + Notifikasi + Riwayat Pengajuan.
- Approval Center dapat heading + empty state; dokumen filter pill aktif → biru `--blue-600`.
- Tabel admin (accounts/approvals/master/audit): thead `#FBFCFE` konsisten.
- Section heading form (`edit.blade.php`, `profile/edit`) diganti `section-title` (biru) — bukan teal lagi.
- **Class desain tetap di layout**: `.page-head/.page-title/.page-desc/.crumb/.section-title/.foot` + CSS pagination reskin (.page-link pil biru).
- Bug yang terhindar: `route('documents.upload')` TIDAK ada (hanya `documents.create`) — dashboard user memakai tombol `route('documents.create')`.

## Backlog berikutnya (belum dikerjakan)

- Mode `reviewed` (sedang diperiksa) untuk ChangeRequest.
- Validasi duplikasi: form "Profil Saya" kirim semua field; bisa dioptimalkan jadi diff.
- Sidebar submenu Dokumen: daftar per-pegawai dari tab di `employees/show` sudah tampil; menu global `Dokumen Digital` sudah menangani semua.

## Implementasi Modul UI/UX Final 2026-09-09 (sesi besar)

Acuan: dokumen "MODUL UI/UX FINAL SIMPEG RSKK" (bagian 1–41, di-paste user di sesi). Status: **terpasang & teruji** (smoke test 200 untuk hampir seluruh halaman admin & user).

### Sub-modul CRUD (Riwayat pegawai) — `SubDataController`, route `sub.*`
- 8 tipe: `pendidikan`, `diklat`, `jabatan`, `pangkat`, `mutasi`, `kinerja`, `penghargaan`, `keluarga`; config-driven (label/model/field/select source/required).
- Tambah/edit/hapus/unduh file (file simpan `storage/app/public/sub/{field}`, download via `sub.download`).
- Redirect kembali ke `employees.show` + flash `fragment` (`tab-{tab}`) + `success`; halaman show auto-buka tab dari `session('fragment')`.
- AuditLog tercatat (`reference` = record model, `description` = teks). **PENTING:** `AuditLog::record(action, module, reference, description)` — param ke-3 harus objek model, bukan string (get_class()).

### Detail pegawai — `employees/show.blade.php` ditulis ulang
- 9 tab: Ringkasan (info utama + progress kelengkapan + KPI), Data Pribadi (identitas/kependudukan/kontak/ringkasan kepegawaian via partial `employees/_kv`), Dokumen, Pendidikan & Diklat (timeline + kartu, tombol tambah/edit/hapus/unduh), Kepegawaian (kondisi saat ini + riwayat jabatan/pangkat timeline + mutasi kartu), Kinerja (kartu + progress + tabel disiplin), Penghargaan, Keluarga (dikelompokkan pasangan/anak/orang_tua), Riwayat (linimasa gabungan).
- Gaji pokok: hanya super_admin lihat nominal; lainnya `Rp •••• (Super Admin)`.
- Fix bug kolom lama: `$edu->jurusan`→`major`, `$rh->rankA`→`rank`, hapus akses kolom tak-ada (`kewarganegaraan`, `alamat`, `jenis_kepegawaian`, `hubungan`).
- HTML `windows.print()` untuk cetak profil; @media print menyembunyikan sidebar/topbar/footer/aksi.

### Fondasi data baru
- Migration `2026_09_09_005149_create_settings_table.php` + `2026_09_09_005150_create_employee_performances_table.php` (dijalankan manual `php artisan migrate --path=…`).
- `Settings` model (get/set, default fallback), `EmployeePerformance` (relasi `performances()` di Employee), `Employee` relation `pendidikanAkhir`.
- **Casts ditambahkan** ke semua model sub-data: `EmployeeFamily` (tanggal_lahir date, status_tanggungan boolean), `EmployeeTraining` (3 date), `EmployeeMutation`, `EmployeePositionHistory` (3 date), `EmployeeRankHistory` (2 date + int), `EmployeeAward` (tahun year), `EmployeeDiscipline` (tanggal date), `EmployeeEducation` (2 year), `EmployeeSalaryHistory` (2 date + int). Tanpa ini `->format()` fatal karena kolom date di-return string.

### Modul baru
- **Ubah Password** — `PasswordController`, route `password.edit/update` (`/pengaturan/password`, semua role; validasi `current_password`).
- **Pengaturan Sistem** — `SettingsController`, route `settings.edit/update` (`/pengaturan/sistem`, super_admin saja; whitelist key, usera `Settings::get/set`). Brand topbar/sidebar/footer pakai `app_name`. `User::username()` tetap.
- **Laporan** — `ReportController`, route `reports.index/csv` (`/laporan`, admin+SA). Section: pegawai/unit/jabatan/golongan/pendidikan/status/dokumen + export CSV (BOM `\xEF\xBB\xBF`, kolom Gaji Pokok di-mask `••••` untuk non-super_admin).

### Approval Center diperkuat
- `ApprovalController::index`: filter status (`pending` default, `all/reviewed/approved/rejected`) + modul + search NIP/nama; stats 4 kartu link; chips.
- Dashboard admin: panel **"Perlu Tindakan"** (Review Approval / Verifikasi Dokumen / Profil Belum Lengkap, masing-masing dengan jumlah).

### Dashboard & pointer
- User: progress kelengkapan kini pakai kolom valid (`tempat_lahir, tanggal_lahir, jenis_kelamin, agama, nik, hp, email_resmi, no_kk, status_perkawinan, no_npwp`)— bukan `alamat/jenis_kepegawaian/jabatan_fungsional` (bug lama). Gaji Pokok user di-mask utk non-SA.
- Null-safe relation `?->` diterapkan di `show` & `index` pegawai (`currentPosition`/`workUnit`/`employmentStatus`/`educationLevel`/`position` bisa null).
- Fix bug master: `MasterDataController::index` eager-load `parent` hanya untuk tipe `units` (sebelumnya semua tipe → 500 di kategori/status/pendidikan).

### Catatan tes (sesi 2026-09-09)
- CRUD sub-data end-to-end teruji via HTTP (store/update/delete → 302 + data bersih).
- Smoke test superadmin: `/dashboard`,`/pegawai`,`/pegawai/{id}`, 8 halaman `sub.create`, `/dokumen`, `/approval`, `/laporan`, `/pengaturan/*`, `/notifications`, `/audit`, `/admin/accounts`, 6 master (semua 200). Type master valid: `units, jabatan, golongan, kategori, pendidikan, status` (bukan `pangkat`/`unit-kerja`).
- User NIP 3: `/dashboard`, `/profil-saya`, `/dokumen`, `/pengaturan/password` 200; `/laporan` 403 (sesuai role).
- Server dev: `php artisan serve --host=127.0.0.1 --port=8000` (jalankan manual via Start-Process bila mati); log di `storage/logs/laravel.log`.

## Spec terbaru user — Modul UI/UX (rujukan "MODUL UI/UX FINAL") 2026-09-09 (gelombang kedua)

Acuan baru user (bagian 1–41): sidebar **segmented** (DASHBOARD / KEPEGAWAIAN / ADMINISTRASI / AKUN), wizard tambah pegawai 5 langkah, profil pegawai **14 tab**, master data diperluas (**13 jenis**), dashboard visualisasi + sertifikasi akan expired, laporan tambahan (diklat & kinerja). Status: **terpasang & teruji** (smoke).

### Sidebar segmented — `resources/views/layouts/app.blade.php`
- Grup label: **Dashboard** (Dashboard, Notifikasi — semua role), **Kepegawaian** (Data Pegawai, Dokumen Digital, Approval — admin/SA), **Administrasi** (Manajemen Akun, Master Data, Laporan, Audit Log), **Akun** (Profil Saya, Ubah Password, Pengaturan Sistem[SA]). User: Kepegawaian(Profil Saya, Dokumen Saya) + Akun(Ubah Password).
- Tooltip via atribut `title` di tiap `.navlink` (ganti tooltip CSS ::after — dibersihkan).

### Wizard Tambah Pegawai — `EmployeeController::create/store` + `employees/create.blade.php`
- 5 langkah: Identitas → Kepegawaian → Kontak & Alamat → Dokumen → Akun; stepper `.wiz-btn/.wiz-num`, JS `wizGo/wizNext/addDocRow/docDrop`, form multipart, dropzone dokumen (jenis + file), toggle `buat_akun` + password (min 6; role `user`; `user_id` dilink setelah create).
- Route wizard WAJIB sebelum `/pegawai/{employee}`: `employees.create` (GET `/pegawai/tambah`) & `employees.store` (POST `/pegawai`) — sudah ada & benar.
- Dokumen saat tambah: status `terverifikasi` langsung; file `$req->file('dokumen_file', [])` array, tiap file `->validate(['mimes:pdf,jpg,jpeg,png','max:5120'])`.
- `validationRules()` diekstrak dari `update()` dan dipakai `store()` (ditambah `nip` unique).
- **ALERT DB:** `employees.user_id` awalnya NOT NULL → diubah `ALTER TABLE employees MODIFY user_id BIGINT UNSIGNED NULL` agar pegawai tanpa akun bisa tersimpan.

### Profil pegawai 14 tab — `employees/show.blade.php`
- 14 tombol: Ringkasan, Data Pribadi, Alamat & Kontak, Kepegawaian, Riwayat Jabatan, Pangkat & Golongan, Mutasi, Pendidikan, Diklat & Sertifikasi, Dokumen, Kinerja, Penghargaan, Keluarga, **Riwayat Perubahan**.
- Panel dipecah: Alamat & Kontak (Alamat Rumah, Alamat KTP, Kontak) mandiri; Riwayat Jabatan/Pangkat/Mutasi/Pendidikan/Diklat jadi panel terpisah (bukan digabung di Kepegawaian).
- `show()` eager-load tambah `changeRequests`; tab Riwayat Perubahan listing CR + status pill + diff field.
- **Panel lama `tab-riwayat` (timeline) dipertahankan sebagai orphan** (tanpa tombol) — jangan dihapus tanpa cek referensi.

### Master data 13 jenis — `MasterDataController` + `master/index.blade.php`
- `types()` bertambah 7: `sub-unit`, `jenis-jabatan`, `jenis-pegawai`, `jenis-pendidikan`, `jenis-diklat`, `jenis-dokumen`, `jenis-penghargaan` (label: Sub Unit Organisasi, Jenis Jabatan, …). Total 13 tipe.
- Model baru + tabel via **satu migration** `database/migrations/2026_09_09_005151_create_master_type_tables.php` (7 tabel: `sub_units`, `position_types`, `employee_types`, `education_types`, `diklat_types`, `document_types`, `award_types`; kolom `id, code nullable, name unique, is_active bool, timestamps`). **Sudah dijalankan manual `php artisan migrate --path=…`**.
- Config memakai field baru `is_active` type `'boolean'` (checkbox); `rules()` + `store()`/`update()` handle boolean (`$request->boolean`).
- `index()` mengirim `$navTypes`; view punya **switcher chips** antar jenis (aktif = biru).
- Tipe lama valid: `units, jabatan, golongan, kategori, pendidikan, status`; tipe baru (coba kedua): `sub-unit`, `jenis-jabatan`, `jenis-pegawai`, `jenis-pendidikan`, `jenis-diklat`, `jenis-dokumen`, `jenis-penghargaan`. (`education-level`/`unit-jabatan` TIDAK ada.)

### Dashboard admin — `DashboardController::adminDashboard` + `dashboard.blade.php`
- Data baru: `perStatus`, `perGolongan`, `perPendidikan`, `perGender`, `expiredCerts`/`expiringCerts` (+ `sertifAkts` counts), `kinerjaStats`.
- Panel **"Sertifikasi Masa Berlaku"** (expired = merah "Habis", ≤120 hari = amber "Akan Habis") + **Komposisi Pegawai**: Jenis Kelamin, Status Kepegawaian, Peringkat Golongan, Pendidikan Terakhir (bar).
- Field training = **`nama_pelatihan`** (bukan `nama_diklat`) — kolom `employee_trainings.nama_pelatihan`. Jangan pakai `var(--purple-*)` (tidak ada; pakai `--cyan-600`).

### Laporan — `ReportController` + `reports/index.blade.php`
- Section baru **Diklat & Sertifikasi** (`diklat`) & **Kinerja** (`kinerja`): tabel status masa berlaku (Habis/Akan Habis/Berlaku) + sebaran predikat; filter search NIP/nama; footer `{{ $entryCount }} entri` (hitung per-seksi).
- CSV baru: `/laporan/diklat/csv`, `/laporan/kinerja/csv` (path = **`laporan/{jenis}/csv`**, bukan `/laporan/csv/...`).
- **GOTCHA SQL:** MySQL tidak mendukung `NULLS LAST` → pakai `orderByDesc('tanggal_selesai')` saja.
- Route ke laporan dari dashboard = **`reports.index`** (bukan `laporan.index`).

### Catatan khusus
- `status_pegawai` adalah **ENUM('PNS','PPPK','Honorer','Kontrak','Lainnya')** NOT NULL? (nullable) — create & edit wajib pakai `<select>` 5 nilai itu (sudah), jangan input bebas (MySQL 1265/1364).
- Smoke test 2026-09-09 (gelombang 2): `/dashboard`, `/pegawai`, `/pegawai/tambah`, `/pegawai/{2,3}` (wizard-created), `/pegawai/{id}/edit`, 13 master, `/laporan`(+diklat/kinerja), `/audit`, CSV diklat/kinerja, login user baru hasil wizard → semua 200/302. Data uji wizard (nip `9876543210`, `9999999999`) sudah dihapus.
- Baru login user hasil wizard: `/dashboard` & `/profil-saya` 200; *jangan* `Invoke-WebRequest` GET `/` saat authenticated (redirect loop `/login`↔dashboard).

## Redesain UI "Modern & Clean" (SaaS/Linear) 2026-09-09

Arah desain pilihan user: **sistem desain modern & clean ala Linear/Vercel** (putih bersih, aksen biru tunggal, tipografi Inter halus, shadow lembut, radius membulat, banyak whitespace). Diterapkan **terpusat di `layouts/app.blade.php`** (CSS variables + komponen) sehingga **SEMUA halaman ikut berubah otomatis** — tanpa menyentuh tiap view.

- **Palet** di-remap: `--ink-*` → slate SaaS (`--ink-900 #0B1220`, `--ink-500 #475467`, `--ink-300 #98A2B3`); bg `--paper #F5F7FA`, border `--line #EAEEF4`; status → aman modern (`--green-600 #039855`, `--amber-600 #DC6803`, `--red-600 #D92D20`); shadow lebih dalam (`--shadow-md 0 14px 34px -12px rgba…`).
- **Komponen** dinaikkan: card radius 14, `.btn-primary` outline 10 + `:active scale(.98)`, tab-btn 14px, `.input` radius 10 + `focus ring blue-500`, table-th kini `#FAFBFD` + border-bottom (bukan latar biru), `.chip`/`.badge`/`.stat-ic` lebih besar & halus, pagination radius 9, `.skeleton` shimmer lebih terang.
- **Sidebar**: width 250px, navlink active bg blue-50 + teks biru tua `--blue-700`, hover slate `#F4F6FA`, `.nav-badge` jadi biru (bukan merah) + white ring.
- **Topbar**: height 64px, `backdrop-filter blur(12px)` + bg `rgba(255,255,255,.82)`; search field topbar `w-60 rounded-[10px]`.
- **Login dirombak total** (`auth/login.blade.php`): split-screen (panel biru gradient kiri + kartu putih kanan); panel kiri brand + tagline + testimoni-style; hanya mobile di bawah lg. `.form-input` radius 11 + focus ring biru; tombol "Masuk" shadow biru.
- Gaya **tidak** diubah manual per-view: sebagian besar memakai `class="…"` Tailwind + CSS var `--ink-*`/`--blue-*` yang nilainya kini sudah SaaS → otomatis konsisten. Avatar ui-avatars tetap background `2563EB`.

### Verifikasi
- `php artisan view:cache` OK. Smoke: `/login` 200 (mengandung "Selamat datang kembali" + `.auth-side`); admin `/dashboard`, `/pegawai`, `/pegawai/1`, `/master/units`, `/laporan`, `/audit`, `/admin/accounts` semua 200. Cek render: sidebar `width:250px`, topbar `height:64px`, navgroup "Dashboard", `.page-title` 24px — semua true.
- Catatan: `employees/show` profil header sudah pakai CSS var (otomatis halus); dashboard admin panel sertifikasi/komposisi ikut upgrade.

### Gelombang 2 — interior jadi "premium" (minta user: "perbagus tampilan di dalam web, semua role")
- **Brand/logo sidebar jadi strip gradient biru** (`linear-gradient(135deg,#1D4ED8,#2563EB)`), logo di dalam pill putih-transparan + teks putih — tampil di SEMUA halaman & semua role.
- **`/pegawai` (admin) kini ada 4 stat-card** di atas tabel: Total Pegawai / Aktif / Nonaktif / Baru (30 hari) — memakai `$totals` (keys: `semua`, `aktif`, `baru`; nonaktif = suaside dari `semua-aktif`). Ingat: keys `$totals` di EmployeeController ≠ dashboard (tidak ada `pegawai`/`nonaktif`).
- **Tabel-tabel interior diseragamkan ke gaya baru**: thead `#FAFBFD` + teks `--ink-300` (dokumen, approval, master, audit, accounts) dan baris `class="row-line"` (border `--line-soft` + hover `#F7F9FC`) — hapus inline `#FBFCFE`/`#F0EFE9`.
- Route approval sebenarnya `/approval` (tunggal) → `approvals.index`. Bukan `/approvals`/`/persetujuan`.

### Verifikasi gelombang 2
- `view:cache` OK. Admin: `/dashboard`, `/pegawai`, `/pegawai/1`, `/pegawai/tambah`, `/pegawai/1/edit`, `/dokumen`, `/approval`, `/master/units`, `/laporan`, `/audit`, `/admin/accounts` → semua 200. User (NIP 3): `/dashboard`, `/profil-saya`, `/dokumen`, `/pengaturan/password` → 200.
- Render check: `linear-gradient(135deg,#1D4ED8` (brand) ✓, `Pegawai Baru (30 hari)` ✓, `#FAFBFD` di tabel ✓.

### Catch-up bug struktural (sesi ini, terkait wizard)
- `employees.user_id` di-DDL `ALTER TABLE employees MODIFY user_id BIGINT UNSIGNED NULL` (awalnya NOT NULL) supaya wizard tambah pegawai **tanpa akun** bisa tersimpan.
- `status_pegawai` adalah **ENUM('PNS','PPPK','Honorer','Kontrak','Lainnya')** → di `create` & `edit` wajib `<select>` 5 nilai itu; `validationRules()['status_pegawai'] = in:PNS,PPPK,Honorer,Kontrak,Lainnya`.

## Redesain Enterprise Corporate 2026-09-09 (sesi M1–M10)

Arah: **Enterprise Healthcare — Clean Corporate** (putih, light gray, navy + professional blue, teal aksen), "beef" padat-informasi untuk semua role. Eksekusi 10 milestone.

- **M1 Auth & Layout**: login jadi **kartu korporat minimalis** (`auth/login.blade.php` ditulis ulang, bukan split-screen); "Ubah Password"→**"Pengaturan"**; navlink Logout `openModal('logout-modal')`; tombol "Keluar" kecil di side-user dihapus; `roleLabel` map (`super_admin/admin/user` → Super Admin/Admin/Pegawai) di layout; topbar kiri identitas `[logo] SIMPEG RSKK (RSUD Kesehatan Kerja)`. **EmployeePolicy** baru (`viewAny/view/create/update` role≠user, `delete` super_admin; route model binding "Pegawai" + `<blink>detail</blink>`). **AppServiceProvider**: `Gate::before` (super_admin bypass) + `Carbon::setLocale('id')`. Redirect login semua role → `route('dashboard')`.
- **M2 Dashboard**: DashboardController tambah `perJenis`, `recentMutations` (**model `EmployeeMutation`, kolom `tanggal_mutasi`**), `recentTrainings`. Admin: 6 KPI card + 3 kartu "Perlu Tindakan" + **donut CSS conic-gradient** perStatus + bar perJenis/perGolongan/perPendidikan/perUnit + feed Mutasi & Diklat terbaru (tanggal `translatedFormat('d M Y')`) + sertifikasi/approval/aktivitas grid 3 kolom. **Tidak ada token violet** — pakai cyan.
- **M3 Data Pegawai**: index filter `q/unit_id/position_id/rank_id` + `per_page` (10/15/25/50) + **Export CSV** (`/pegawai/export`, `employees.export`, StreamedResponse BOM UTF-8) + chip status pills + footer info rentang/total.
- **M4 Wizard + Draft**: migration `2026_09_09_000001_add_is_draft_to_employees_table.php` (`is_draft` boolean default 0; jalankan **wajib** `php artisan migrate --path=…` — migrate full error karena tabel dasar tak tercatat di `migrations`). `Employee::$guarded=['id']`. `store()` branch `storeDraft()` (validasi minimal, `is_draft=true`, audit, redirect→show). `_field.blade.php` error state (border red + ⚠ pesan + `help`). `create.blade.php`: badge "Langkah X dari 5", tombol **Simpan Draft** (novalidate), progress bar `#wiz-progress`, hidden `is_draft`. **Base `Controller` ditambah `use AuthorizesRequests`** (fix authorize()). Badge Draft di index. Test artifact: employee id=4 nip `99990001` "Draft M4 Test".
- **M5 Profil Pegawai (show)**: header corporate (cover gradient navy→blue, avatar rounded-2xl, nama+gelar, badge **Draft** amber + status pill, jabatan•unit, tombol Cetak/Lanjutkan Isi), 4 stat chip (Golongan/Jenis Kelamin/Pendidikan Akhir/Masa Kerja), banner draft amber, panel timeline `tab-riwayat` kini punya tombol **"Lihat Linimasa"** (navy, dulu orphan).
- **M6 Dokumen**: `DocumentController::index` filter `kategori` (jenis_dokumen) + `$kategoriList` (hitung per kategori >0) + search jenis/no dokumen; admin view chips kategori horizontal (aktif navy), kolom "Diunggah" (diffForHumans) + "Tertanggal", tombol Reset. **`Document` model tambah `$casts`** (`tanggal` date, `created_at` datetime) — dulu string → `->format()` fatal. `storage:link` ada.
- **M7 Approval & Notifikasi**: show Setujui/Tolak **processing state** (disabled + "Memproses…" lalu submit). Notifikasi: route `POST /notifications/{notification}/read` (`notifications.read`) + `markRead()` (owner guard, redirect ke `url`); item list jadi **tombol klik-untuk-buka** (auto-mark read) + badge unread amber.
- **M8 Akun & Role**: badge role berwarna (SA navy/Admin blue/Pegawai teal) + **inline role select take-over** (super_admin: PATCH `accounts.update-role`), sembunyikan tombol Nonaktifkan utk akun sendiri ("Akun Anda") + guard server-side self-toggle 403. Password tetap `Hash::make`.
- **M9 Master/Laporan/Audit/Pengaturan**: audit action badge **warna per aksi** (create green, update amber, delete/reject red, approve blue, login/logout gray). Laporan/settings/master sudah beefy → penyesuaian kecil.
- **M10 Polish**: PROJECT_NOTES ini + cleanup test draft id=4 + smoke test ketiga role.

### Verifikasi akhir sesi M1–M10
- `view:cache` + smoke (super_admin): login 200; `/dashboard`, `/pegawai`, `/pegawai/1`, `/pegawai/4`(draft), `/pegawai/export`, `/dokumen`, `/dokumen?kategori=KTP`, `/approval`, `/notifications`, `/audit`, `/laporan`, `/pengaturan/sistem`, `/admin/accounts`, `/master/units`, `/master/jabatan` → 200. User (NIP 3): `/dashboard`, `/dokumen` 200.
- **Route aktual**: `/pengaturan/sistem` (settings), `/master/{units,jabatan,golongan,kategori,pendidikan,status|sub-unit,jenis-*}` (bukan `/pengaturan` atau `/master/work-units`).
- Env note: `Invoke-WebRequest` POST gagal redirect → pakai `curl.exe` + cookie jar + regex `_token` dari HTML login.

## Redesign UI/UX Global (sesi redesign 2026-09-09 — shell enterprise)

Arah user: **perbaiki struktur layout dulu (bukan cosmetic patch)**, lalu halaman. Eksekusi bertahap:

- **STAGE 1–2 Shell**: `layouts/app.blade.php` ditulis ulang total. SIDEBAR kini **fixed** (`position:fixed`, 256px, collapse 80px via `.side.collapsed`, mobile drawer ≤1023px `translateX(-100%)` + `.mobile-backdrop`); TOPBAR **sticky 64px** (toggle kiri, **breadcrumb** `.crumb` normal-case kiri, **search global** tengah `hidden md:block` (route employees.index q), dropdown notif + profil kanan, z-index topbar 30 / sidebar 40 / backdrop 35 / dropdown 50 / modal 90); `.main-wrap{margin-left:var(--sidebar-w)}` (collapsed → 80px, mobile → 0). Content container fluid `max-width:1480px`. Tipografi scale `.t-h1/.t-h2/.t-h3/.t-body/.t-sub/.t-meta`; font Inter + Poppins. Print: hide `.side`/`.topbar`, margin 0. JS `toggleSidebar(force)` (cek `innerWidth<=1023`, `collapsed`/`mobile-open`, localStorage persist), `toggleDropdown` (auto-close), `openModal/closeModal`, Escape close, **flash alert → toast auto-dismiss** (`.toast-wrap`, 4.2s). `overflow-x:hidden` di body.
- **Bug pre-existing difix**: `ProfileController::edit` 500 utk akun tanpa `employees` (Super Admin) → guard `$employee ? … : null` + empty-state di `profile/edit.blade.php` ("Akun belum terhubung ke data pegawai").
- **STAGE 4 Dashboard** (`dashboard.blade.php`): header + tanggal Indonesia (`now()->translatedFormat('l, d F Y')`, locale id via AppServiceProvider) + tombol Review Approval (primary jika pending>0, badge jumlah); KPI jadi **horizontal compact** (icon kiri + value+label); baris chart disusun target: Row1 **Unit | Status(donut)**, Row2 **Pendidikan | Golongan**, lalu Mutasi/Diklat, Sertifikasi+Approval, Aktivitas Sistem 2-kolom feed. User dashboard stat juga horizontal + emoji 👋 dibuang.
- **STAGE 5 Data Pegawai**: toolbar enterprise (search + unit/jabatan/golongan select + Filter) di dalam card, chip status pakai `.chip.active`, tabel kolom **Pegawai (avatar+nama+NIP) / Jabatan / Unit / Status / Kelengkapan (mini progress %)** / Aksi (Lihat + Edit ghost); elengkap hitung dari 10 field; pagination reskin `.pagination .page-link`.
- **STAGE 6 Profil**: hapus emoji (🎓🏅👤→SVG), tab "Lihat Linimasa" navy → "Linimasa Karier" tab-btn normal. Header/cards/tab sudah sesuai `.card/.badge/.tab-btn/.field-row` baru.
- **STAGE 7**: semua modul (documents/approvals/accounts/audit/master/notifications/settings/reports/sub) pakai kelas global (`btn`, `table-th/td`, `row-line`, `badge`, `input`, `chip`, `empty-state`) — inherited; laporan: emoji 🖨⬇ → SVG.
- **STAGE 3 (komponen Blade)**: digantikan sistem **CSS design-token + class library** (`.card .btn .badge .input .table-th .chip .tab-btn .empty-state .stat-card .alert .progress .timeline .field-row`) agar semua view lama ikut tanpa refactor — hasil visual setara komponen reusable.

### Verifikasi redesign
- `php artisan view:cache` OK. Smoke SA **18 halaman 200** (termasuk `/pegawai/tambah`, `/pegawai/1/edit`, `/dokumen/upload`, `/profil-saya`, `/pengaturan/password`); USER 5 halaman 200. Render check dashboard: `position:fixed`, `--topbar-h:64px`, `--sidebar-w:256px`, `@media (max-width:1023px)`, `mobile-backdrop`, `.crumb`, `topbar-search`, `dropdown-menu`, `main-wrap`, `overflow-x:hidden` — semua OK.
- **Cara start Apache** (bukan service): `Start-Process C:\laragon\bin\apache\httpd-…\bin\httpd.exe -WindowStyle Hidden` (base URL `http://simpeg-rskk-laravel13.test`).
- Catatan: tombol lama `px-4 py-2 rounded-lg` tetap ada di beberapa view — redundan tapi tidak bentrok dgn `.btn`. drop-down aksi dalam tabel scroll dihindari (terpotong `overflow-x`) → pakai tombol compact.

## Refinement Sidebar + Topbar + Profil (sesi refinement 2026-09-09 — "terlalu mepet / enterprise")

Request user: area kanan (topbar/profile) rapi; **jangan tambah fitur**, fokus struktur sidebar+topbar+profile seperti aplikasi enterprise.

- **Topbar kanan**: klaster bel + divider + profil dirapikan (`gap-3`, `pl-2`), tombol profil jadi **`.user-chip`** ber-`border` rounded (hover biru), badge tidak terpotong, nama profil `truncate`.
- **Sidebar header**: tinggi tetap 76px lewat `.side-head`; logo pill **`.side-logo`** (gradient-safe: bg `blue-50`, img/`.ph` "R" fallback). Brand text truncate.
- **Nav**: `navgroup` margin dirapatkan (`16px 22px 6px`); item tetap 9.5px padding 14px, radius 10, active inset bar biru; **Logout DIHAPUS dari daftar menu** (dua cabang role).
- **Sidebar footer** baru (`.side-foot`, `margin-top` via flex-1 nav): user card **compact** `.side-user` (avatar 36px, nama 13px, role 11px, chevron hover biru) + tombol `.side-logout` merah subtle (hover soft red). Saat collapse: avatar/ikon saja.
- **Profil Saya** (`profile/edit.blade.php`) ditulis ulang: `.profile-wrap` max **1180px**; urutan = page-head → **summary card** (avatar 72, nama 20px, chip Aktif, NIP·Unit, progress Kelengkapan Data %) → **alert pengajuan pending** (kartu amber, link Lihat Detail hanya utk role non-user krn route `approvals.*` di-guard role) → kartu **read-only** Informasi Pribadi & Alamat & Kontak (`.fvalue`) → kartu **Kepegawaian read-only** dengan icon **lock** + catatan "hanya dapat diubah oleh Admin/Super Admin" → kartu **Ajukan Perubahan Data** (`.field-group` sub-seksi, grid 2 kolom, label `.flabel`, input `.input`, select agama/status, primary button "Ajukan Perubahan"). Nama field form TETAP sesuai `ProfileController::$editableFields` (nama_lengkap, nik, dll).
- Catatan data: kolom pegawai bukan `unit`/`jabatan` → pakai `workUnit?->name`, `currentPosition?->name`, `status_pegawai` string, `tmt_jabatan` cast date.
- Footer: teks `© {Y} {appName} — {rsName} · {tagline}`, padding `20px 0 26px`.
- Verifikasi: `view:cache` OK; smoke `/dashboard` & `/profil-saya` SA+USER 200; marker `side-head/side-foot/side-logout/side-user/user-chip` ada; `data-nav-logout` hilang; "Lihat Detail" benar-benar tidak dirender utk role user.

### Tambahan — HAPUS profile card dari TOPBAR (sesi sama, "RA Rayn Pegawai ▼ mengambang")
- **Sumber** komponen dihapus dari struktur (bukan `display:none`): `<div class="dropdown" id="dd-profile">` + `.user-chip` + `.dropdown-menu.profile` di `layouts/app.blade.php` — termasuk tombol user-chip, header avatar dropdown, dan daftar menu (Profil Saya/Dashboard/Pengaturan/Pengaturan Sistem/Keluar).
- Topbar kanan kini **hanya ikon notifikasi** (bel + badge) — tidak ada profile, tidak ada divider, tidak ada sisa margin/border/wadah kosong (wrapper disederhanakan jadi `flex flex-none`). Identitas user hanya di **sidebar footer** (`.side-user` compact + `.side-logout`); detail lengkap hanya di halaman Profil Saya.
- CSS mati dibersihkan: blok `.user-chip`, `.dropdown-menu.profile`, `$userAvatar` masih terpakai sidebar footer & halaman profil (tetap ada).
- Verifikasi: `dd-notif` ada, `user-chip`/`dd-profile`/`dropdown-menu profile` = 0 di BUFFER SA & USER (dashboards + profil-saya); `Kelengkapan Data`/`Informasi Kepegawaian`/`Ajukan Perubahan Data` masih render. (Catatan operasional: cookie smoke test kedaluwarsa antar-sesi → login ulang dulu.)
## Fix Total Halaman "Profil Saya" (2026-09-14) - avatar raksasa / card melar / spacing berantakan

Laporan user: foto tampil SANGAT BESAR memenuhi content, card tinggi, garis panjang, spacing besar. Akar masalah (diinspeksi, bukan asumsi):
- **Tailwind hanya dimuat via CDN runtime** (https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/3.4.1/tailwind.min.js) - HTML server TIDAK berisi CSS hasil compile (terbukti via curl: .flex{...}, .grid-cols-2{...}, .gap-4{...} tidak ada). Utility seperti w-[72px] h-[72px] bisa gagal di browser sehingga <img style="width:100%;height:100%;object-fit:cover"> pun meledak penuh.
- **public/images/default-avatar.png TIDAK ADA** (folder hanya berisi "Logo RSKK .png") - gambar avatar selalu ikut onerror dulu.
- .card TIDAK punya padding bawaan (hanya bg/border/radius/shadow) - padding halaman sebelumnya bergantung utility Tailwind.

Solusi: rebuild profile/edit.blade.php memakai **kelas CSS server** (dijamin dirender), bukan utility Tailwind.
- CSS baru di layouts/app.blade.php: .cards-gap (.card berjarak 20px), .sec-desc, .prof-sum (flex wrap gap 24) / .prof-sum-left / .prof-sum-meta / .prof-name (18px 700 ellipsis) / .prof-role / .prof-nip .prof-dot / .prof-sum-comp (progress, margin-left auto) / .prof-status, **.profile-avatar** (80px FIXED min/max, lingkaran 50%, overflow hidden, border 2px #fff, bg blue, .pa-inits absolute fallback), .fg (grid 2 kolom gap 20, .lg-2 span penuh), .pend-alert (amber, .ic 38px, .pend-t/.pend-s), .btn-row (gap 12 margin-top 24 border-top). Media query: avatar 72px (721-1024px) / 64px (<=720px), .fg 1 kolom, .prof-sum-comp full width di mobile. **.card-pad{24px} & .card-pad-sm{16px}** ditambah.
- Shell di-hardening: nav sidebar = .side-nav{flex:1;overflow-y:auto;min-height:0}; .side .brand-text/.navtext min-width:0 + ellipsis (teks panjang tidak meluber).
- profile/edit.blade.php ditulis ulang: urutan = page-head -> summary card (.profile-avatar + inisial .pa-inits dihitung dari nama, onerror menampilkan inisial) -> alert pending (amber; "Lihat Detail" HANYA role non-user) -> Informasi Pribadi read-only -> Alamat & Kontak read-only -> **Kepegawaian read-only ber-lock** (status_pegawai/employmentStatus, employeeCategory, jenis_asn, workUnit, currentPosition, jenis_jabatan, golonganAkhir/Awal, tmt_jabatan, tmt_skpd, masa_kerja + catatan "Data kepegawaian hanya dapat diubah oleh Admin/Super Admin.") -> Ajukan Perubahan Data (nama field TETAP = ProfileController::; button primary + Batal).
- GOTCHA: variabel yang didefinisikan di @php LAYOUT (mis. $roleLabel) TIDAK terlihat di section child view -> 500 "Undefined variable " -> dihitung ulang di @php child (map role).

Verifikasi 2026-09-14: iew:cache OK; /profil-saya 200 role USER & SA (login ulang pakai cookie jar baru; NIP 1/superadmin123, NIP 3/user); marker .profile-avatar/.pa-inits/.prof-sum-comp/.fg/.field-group/.card-pad/Informasi Kepegawaian/Ajukan Perubahan Data ada; TIDAK ada style="width:100%;height:100%" di img avatar; link approvals TIDAK dirender untuk role user; dd-profile/user-chip hilang; /dashboard tetap 200.

## Informasi Kepegawaian - compact info summary (2026-09-14, "FIX FINAL")

Keluhan: section Informasi Kepegawaian di Profil Saya tampak seperti form input (kotak tinggi 42px ber-border, ikon lock di tiap field, - di dalam kotak besar, tidak kompak). Solusi: ubah presentasi jadi READ-ONLY INFORMATION SUMMARY (label + nilai), TANPA menyentuh backend/approval workflow.

- CSS baru di layouts/app.blade.php: .ik-head (title+subtitle kiri, aksi kanan), .ik-title 16px 700, .ik-sub 13px #64748B, .ik grid 2 kolom (gap 18x24, 1 kolom <=720px), .ik-item/.ik-label (12px 600 #64748B mb 6px)/.ik-value (14px 500 #172033), .ik-dim (#98A2B3 utk "Belum diisi"), .ik-badge (pill hijau dot 6px: bg #ECFDF3 border #ABEFC6 text #067647; .is-empty gray), .ik-note (lock kecil + "Data resmi - perubahan memerlukan persetujuan Super Admin."), #ajukan-perubahan{scroll-margin-top:84px}. Blok .fvalue (kotak input 42px + lock per field) DIHAPUS (sudah tidak terpakai).
- profile/edit.blade.php: **ketiga kartu read-only** (Informasi Pribadi, Alamat & Kontak, Informasi Kepegawaian) beralih dari .fvalue -> .ik label+value. Kartu Kepegawaian: header .ik-head + tombol kecil .btn btn-outline btn-sm "Ajukan Perubahan" -> anchor #ajukan-perubahan (form perubahan di bawah), 10 field tetap (Status, Kategori, Jenis ASN, Unit, Jabatan, Jenis Jabatan, Pangkat/Golongan, TMT Jabatan, TMT SKPD, Masa Kerja), **Status Pegawai jadi badge** (hijau dot / gray "Belum diisi"), satu catatan lock di footer (BUKAN per-field). Helper $kv closure (filled ? escaped value : <span class="ik-dim">Belum diisi</span>); $masaKerja/// null-aware (data kosong -> "Belum diisi" soft, bukan "-" di kotak).
- Form editable tetap: id="ajukan-perubahan" + @method('PUT') + nama field identik $editableFields -> approval workflow tidak berubah.

## Informasi Kepegawaian kini EDIBLE via Ajukan Perubahan + ikon approve/reject normal (2026-09-14)

Permintaan user: "sama informasi kepegawaian tetap bisa di rubah aja sama kayak yang lain kalau udah ada perubahan baru ya minta persetujuan" + ikon ceklis/silang approve terlalu besar (minta standar industri).

- **approvals/show.blade.php**: glyph unicode `✓ Setujui` / `✕ Tolak` (U+2713/U+2715, bisa dirender browser jadi emoji besar) diganti SVG inline 14px stroke currentColor (check: `M5 13l4 4L19 7`; x: `M6 18L18 6M6 6l12 12`) + teks, tombol `inline-flex items-center gap-1.5`. Verifikasi /approval/{id} 200, svg count 21, glyph lama=0.
- **ProfileController.php**: `$editableFields` ++ 11 field kepegawaian (`employment_status_id, employee_category_id, jenis_asn, work_unit_id, current_position_id, jenis_jabatan, golongan_akhir_id, tmt_jabatan, tmt_skpd, masa_kerja_tahun, masa_kerja_bulan`) -> old_data/new_data ikut field tsb. `edit()` kini kompak lookup lists buat select: `$workUnits, $positions, $ranks, $employeeCategories, $employmentStatuses` (mirror EmployeeController::edit). `update()` validasi: FK `nullable|integer|exists:...`, `jenis_asn in:PNS,PPPK`, `jenis_jabatan in:struktural,fungsional,pelaksana`, `tmt_*` date, masa kerja min 0 max 70/11. Data diri + kepegawaian tetap satu request `module_type=profil`; approve() = `$employee->update($new_data)` (Employee `$guarded=['id']`, cast tmt date) -> kolom langsung terpakai.
- **profile/edit.blade.php**: form "Ajukan Perubahan" ditambah sub-seksi `.field-group` "Informasi Kepegawaian" (grid `.fg`, 11 kolom select/date/number, `old(field, $emp->...)`), selaras dengan kartu summary di atasnya. `$editableFields` lokal di @php ikut disinkron (dipakai hitung completeness).
- E2E tervalidasi: user NIP 3 submit `employment_status_id=2` + `masa_kerja_tahun=10` + `masa_kerja_bulan=4` -> CR id 7 (`old_data` 28 field) -> SA approve -> Employee 1 berubah (employment_status_id=2, masa kerja 10/4), `status=approved approved_by=1`. Data dev di-restore setelah tes (nama_lengkap Rayn, FK/null kembali, CR 7 dihapus).
- Catatan desain: kartu summary kepegawaian TETAP read-only (nilai resmi yang tersetujui); perubahan lewat form memicu approval SA — alur approval tidak diubah.
- Verifikasi: iew:cache OK; /profil-saya 200 (USER NIP 3, login ulang); marker .ik-title/.ik-sub/class="ik"/ik-badge is-empty/ik-note/ik-dim/Belum diisi/href="#ajukan-perubahan" ada; .fvalue & ikon lock per-field = 0 (10x lock di teks hanyalah substring LOCK/LOCKquote class Tailwind lock, bukan icon); Status Pegawai/Kategori Pegawai/Unit Kerja/Jabatan/Jenis Jabatan/Pangkat/Masa Kerja tetap render. Catatan: DB dev employee Rayn (user 3) semua kolom kepegawaian NULL -> tampil "Belum diisi" (fallback graceful; badge hijau muncul saat data terisi).

--------------------------------------------------------------------------------
## ACCESS CONTROL FINAL + InKepeg EDIBLE (2026-09-14) - record dicatat
Matriks akses FINAL (diverifikasi): USER -> /pegawai 403, /pegawai/1 403, /admin/accounts 403, /audit 403, /laporan 403, /master/units 403; SUPER_ADMIN -> semua 200 (pegawai, detail, approval, akun, laporan, audit). Admin -> 403 utk accounts (route role:super_admin web.php:107 + AccountController guard) & sidebar Manajemen Akun hanya @if(super_admin) app.blade.php:439.
Info Kepegawaian kini bisa diajukan perubahan dr Profil Saya (Ajukan Perubahan -> approval SA) + ikon approve/reject normal (SVG 14px).

--------------------------------------------------------------------------------
## USER BISA LIHAT DATA PEGAWAI LAIN (read-only) (2026-09-14)

Permintaan user: role "user" boleh MELIHAT profil pegawai lain (daftar + detail), TAPI tidak boleh mengubah; perubahan tetap hanya utk akun sendiri via Profil Saya -> approval SA.

- **routes/web.php**: `employees.index` & `employees.show` dipindah keluar grup `role:super_admin,admin` (kini semua role ter-authentikasi). `employees.export/create/store/edit/update` tetap admin-only. **PENTING urutan route:** route `export` & `tambah` WAJIB terdaftar SEBELUM `/pegawai/{employee}` (show) agar tidak tertelan route `{employee}` (sudah di-urutkan: index → grup admin [export/tambah/store/edit/update] → show di akhir).
- **app/Policies/EmployeePolicy.php**: `viewAny()` & `view()` kini `return true` (semua role boleh lihat). `create`/`update` tetap `role !== 'user'`, `delete` hanya super_admin.
- **resources/views/layouts/app.blade.php**: cabang sidebar **user** ditambah navlink "Data Pegawai" (route `employees.index`) di atas "Profil Saya".
- **resources/views/employees/index.blade.php**: `@php $canManage = auth()->user()->role !== 'user'` — tombol "Ekspor CSV", "Tambah Pegawai", dan tombol Edit (aksi) hanya dirender saat `$canManage`.
- **resources/views/employees/show.blade.php**: `$canManage` sama. Semua aksi admin dibungkus `@if($canManage)`: tombol Edit Profil (2x) + Cetak, banner draft + Lanjutkan Isi, "+ Unggah Dokumen", tombol Verifikasi dokumen, tombol "+ Tambah" & Edit/Hapus di semua tab (pendidikan, diklat, jabatan, pangkat, mutasi, kinerja, penghargaan, keluarga). Aksi **Unduh** file & linimasa tetap tampil (read-only). Tab Riwayat Perubahan tetap `@if($isSA)` (approval).
- Gaji Pokok sudah di-mask utk non-super_admin (tidak berubah).
- **Smoke (v^-072):** USER NIP 3 → `/pegawai` 200, `/pegawai/1` 200, `/pegawai/1/edit` 403, `/pegawai/export` 403, `/pegawai/tambah` 403, `/dashboard` 200; SA NIP 1 → semua 200. `php -l` semua file + `view:cache` OK.

--------------------------------------------------------------------------------
## SESI 2026-09-14 — ENTERPRISE FINALIZE (topbar user-menu + index directory + MASKING SENSITIF)

Request: rapikan sisi enterprise sesuai MASTER PROMPT bagian 1-51 yg belum terpasang (user chip topbar, tabel directory, sensitive masking utk user lihat pegawai lain).

1. **Topbar user menu** — `layouts/app.blade.php`: komponen `.user-chip` (avatar 27px + nama truncate + caret, hover biru) + `.dropdown-menu.profile-menu` (230px) dipasang KEMBALI di kanan topbar setelah bel notif (sebelumnya dihapus 2026-09-09 saat request "hapus mengambang"). Isi dropdown: header ringkas (avatar+nama+role), Profil Saya, Pengaturan (password), Pengaturan Sistem (hanya SA), divider, Logout (buka modal logout via `openModal('logout-modal')` + tutup dropdown). Styling baru di blok CSS layout (blok `.user-chip`/`.uc-avatar`/`.uc-name`/`.uc-caret`/`.dropdown-menu.profile-menu`/`.pm-user`). Variabel `$userAvatar`, `$userName`, `$roleLabel` dipakai dari @php child (GOTCHA 2026-09-14: var layout tak terlihat di section — pastikan sudah dihitung child).

2. **Index Data Pegawai directory** — `employees/index.blade.php`: kolom jadi **Pegawai (avatar+nama+TTL) / NIP (monospace) / Pangkat·Golongan (2 baris) / Jabatan / Unit Kerja / Status / Masa Kerja ("X Tahun Y Bulan", null → —) / Aksi**. Aksi = tombol **Lihat** (primary) + 3 icon-btn: Dokumen (`?tab=dokumen`), Linimasa (`?tab=riwayat`), Edit (hanya `$canManage`). Dropdown aksi dihindari (terpotong `overflow-x` tabel). **Active filter chips** di bawah toolbar ("Filter Aktif: [×Label: nilai]" tiap filter, klik hapus satu filter, tombol ✕ reset semua tetap). Pencarian diperluas di `EmployeeController::index` (nama, nip, **nip_lama**, **nik**, jabatan, unit).

3. **Sensitive masking untuk user melihat pegawai lain** (prompt §35) — `employees/show.blade.php`: `@php $canSensitive = role !== 'user' || $employee->user_id === auth()->id(); $screen = !$canSensitive ? '••••••••' : null; @endphp`. `$screen ?: $value` utk: NIK/KK/NPWP/BPJS/KARIP/KARSU/KARPEG/Taspen/Rekening/BA Pertarum (tab Kependudukan & Nomor), SEMUA field alamat rumah + domisili KTP (+ badge "Terbatas" di section title). Tab **Dokumen**: ganti kartu dengan empty-state lock "Dokumen Terbatas" (yg bukan SA/admin lihat). Tab **Keluarga**: empty-state lock "Data Keluarga Terbatas". Link **Unduh file** (ijazah/sertifikat/SK/kinerja/penghargaan, 7 lokasi) dibungkus `@if($canSensitive && …)`. Ringkasan tab: 4 chip (Golongan/JK/Pendidikan/Masa Kerja) tetap publik.

4. **IDOR guard sub.download** — `SubdataController::download` TIDAK punya guard ownership (dulu siapa pun authenticated bisa unduh file ijazah/SK pegawai lain via `sub.download/{type}/{id}`). Ditambahkan: `if ($user->role === 'user' && $row->employee_id !== $user->employee?->id) abort(403)`. (`documents.download` sudah punya guard dari sesi sebelumnya — di-verifikasi ulang.)

5. **Show page ?tab= query** — `employees/show.blade.php` JS: `@elseif($tab = request('tab')) setTab($tab)` jadi URL `?tab=dokumen|riwayat` dari ikon index langsung membuka tab.

### Verifikasi (2026-09-14, smoke baru)
- `php -l` 3 controller OK; `view:cache` + `view:clear` OK.
- USER NIP 3: `/pegawai` 200, `/pegawai/1` 200, `/pegawai/1/edit` 403, `/pegawai/1?tab=dokumen` 200.
- SA NIP 1: `/pegawai` 200, `/pegawai/1` 200, `/pegawai/1/edit` 200, `/dashboard` 200, `/laporan` 200, `/audit` 200, `/master/units` 200, `/profil-saya` 200, `/pengaturan/password` 200, `/pengaturan/sistem` 200, `/admin/accounts` 200.
- 404 yg sempat muncul hanya salah tebak path (path benar `/master/units`, `/admin/accounts`, `/audit`); `/master/unit-kerja` tidak ada krn slug = `units`.
- Route menu topbar (profile.edit/password.edit/settings.edit) semua ada & 200.

--------------------------------------------------------------------------------
## GLOBAL UI POLISH + LIVE SEARCH + PROFIL HEADER REDESIGN (2026-09-14 sesi lanjutan)

Request user (MASTER PROMPT v2 — 43 poin): rapikan seluruh UI jadi konsisten enterprise compact, hapus avatar besar dari topbar, buat profil header sesuai mockup (avatar bulat 80px + info card kanan), tambah global search live.

### Apa yang diubah
1. **Global search (topbar)** — `layouts/app.blade.php`: `@if(role!=='user')` guard dihapus → search sekarang tampil untuk semua role. Placeholder diganti: "Cari nama, NIP, jabatan, atau unit kerja...". Ditambah **live dropdown**: input `#gs` → debounce 260ms → fetch `GET /penelusuran?q=...` (AbortController) → dropdown `.gs-res` berisi avatar inisial + nama + jabatan·unit, link ke profil. Enter tetap submit form ke `/pegawai?q=`. CSS: `.gs-res` absolute, max-height 340px, z-index 60; `.gs-res-item` flex 32px avatar inisial + meta. JS: `Escape`/outside-click tutup dropdown.
2. **Route `/penelusuran`** — `routes/web.php`: `Route::get('/penelusuran', [EmployeeController::class, 'searchJson'])->name('employees.search')` — accessible all roles (sebelum index route, anti `{employee}` swallowing).
3. **searchJson** — `EmployeeController::searchJson(Request)`: returns JSON array max 8 rows (id, nama, nip, jabatan, unit, url) — search by nama_lengkap, nip, nip_lama. Fix: lexical clash `$q→$term` (closures). Bukan IDOR: hanya data directory publik.
4. **Profil header restructure** — `employees/show.blade.php`: banner height `h-16`; avatar pakai `.profile-avatar` (80px, round-50%, responsive 72/64px, fallback inisial `.pa-inits`); center: nama 20px bold + NIP mono 12px + jabatan•unit + badge (draft/status); **kanan**: `.prof-mini` card (190-240px, `border:1px solid var(--line-soft)`, field-row: Unit Kerja/Jabatan/Pangkat-Golongan). Tombol Cetak/Edit dipindah ke bawah header (bukan kanan atas). InfoChips 4-grid (Golongan/JK/Pendidikan/Masa Kerja) DIHAPUS (sudah ada di Ringkasan & Kepegawaian tab). Draft banner: `mx-6 mb-5` supaya sejajar konten; tombol btn btn-outline btn-sm.
5. **Avatar tabel index** — `employees/index.blade.php`: `rounded-lg` → `rounded-full` (avatar 36px bulat per §37).
6. **Topbar user-menu dihapus** — kembali ke sidebar footer saja (sesuai keputusan user sesi ini). CSS `.user-chip/.uc-*/.profile-menu/.pm-user` dihapus dari layout `<style>`.

### Verifikasi (2026-09-14)
- `php -l` EmployeeController: 0 errors.
- `view:cache` OK.
- SA: `/` `/pegawai` `/pegawai/1` `/approval` `/master/units` `/laporan` `/audit` `/admin/accounts` `/profil-saya` → semua 200.
- USER: `/dashboard` `/pegawai` `/pegawai/1` `/pegawai/1?tab=dokumen` → semua 200; edit → 403.
- `GET /penelusuran?q=rayn` → 200, `{"id":1,"nama":"Rayn Alfaby",...}`.
- `GET /penelusuran?q=oke` → 200, `[]`.
- HTML user: `#gs` input present (count 2: open+close tag), `.gs-res` div present (count 8 includes CSS references).
- Topbar: `user-chip`/`dd-profile` = 0, `side-user` = 9 (sidebar footer).

## Sesi 2026-09-15: ALIGNMENT MODUL.PNG (OCR-driven)
User sediakan kolase 8 modul modul.png; model tidak bisa lihat gambar, jadi konten ditarik via Windows OCR (upscale 2x + crop 2x3, script ocr.ps1 + pillow).
### Gaps yang ditemukan & disamakan
1. **Tabel index → sesuai gambar (Modul #1)**: kolom No | Foto | Nama Pegawai (nama + sub-line NIP. di bawah) | Jabatan | Unit Kerja | Status | Aksi. Buang kolom Pangkat/Golongan & Masa Kerja & NIP terpisah & TTL. No = firstItem()+index. Empty colspan 8→7.
2. **Aksi user → "Lihat Profil"** (Modul #4): {{  ? 'Lihat' : 'Lihat Profil' }}.
3. **Catatan read-only di profil pegawai lain** (Modul #2): banner biru "Data ini hanya dapat dilihat. Untuk melakukan perubahan, ajukan melalui menu Profil Saya." saat ! .
### Konfirmasi dari gambar (sudah terpenuhi sebelumnya, tidak diubah)
- Pagination "1–10 dari N" ✔; toolbar search + Unit Kerja + Reset + Tambah ✔; header "Data Pegawai / Kelola dan lihat informasi seluruh pegawai" ✔.
- Ringkasan card kanan (Unit Kerja/Jabatan/Pangkat-Golongan) ✔; tabs ✔.
- Profil Saya alert "menunggu persetujuan Super Admin" ✔; Logout modal ✔; toast ✔; Policy/Gate/Middleware ✔.
### Data penting
- **employee 1 (Budi Santoso) dimiliki user_id=3 (Rayn)** sehingga Rayn = pemilik & tetap lihat data sensitif (benar per desain). Uji read-only pakai employee lain (mis. id 5).
### Verifikasi (2026-09-15)
- php -l index/show OK; iew:cache OK.
- SA /pegawai: kolom No/Foto/Nama Pegawai ada, Masa Kerja/Pangkat hilang ✔.
- USER /pegawai: "Lihat Profil" ✔.
- USER /pegawai/5: note read-only + masking ✔; /pegawai/1: tanpa note (owner) ✔.

## Sesi 2026-09-15: UI/UX HALAMAN PENGATURAN &#62; UBAH PASSWORD
### Perubahan
1. **esources/views/password/edit.blade.php ditulis ulang** (Modul Pengaturan):
   - Container max-width:680px (bukan full-width, memenuhi spesifikasi 600-760px).
   - Card form: header "Keamanan Akun" (icon shield, title, desc) + divider; body grid gap 22px (spesifikasi 18-24px).
   - Field: Password Saat Ini / Password Baru / Ulangi — label 12.5px 600, input 42-46px, icon mata di dalam (password&#8596;text, .pw-eye).
   - Helper "Minimal 8 karakter." + **strength meter** (Lemah/Sedang/Baik/Kuat, bar 5px) + **live match** ("Password cocok."/"Password tidak cocok.").
   - Aksi: [Batal] [Simpan Password Baru] kanan bawah; pakai component <x-loading-button> (loading &#8594; "Menyimpan..." + spinner, rolelengkap di layout).
   - Panel "Tips Keamanan" kecil (bg biru muda, 3 bullet) di bawah form.
   - Error: <x-alert type=error> di atas card + <x-form-error> inline per field (component sudah ada).
2. **esources/views/layouts/app.blade.php**:
   - CSS reusable form keamanan: .pw-input/.pw-eye/.pw-strength/.pw-match/.field-error/.field-hint/.settings-actions/.lb-spin/@keyframes dgn dskes.
   - Driver JS global utk [data-loading-btn] (submit &#8594; disabled + spinner + tukar label) — reusable semua halaman.
   - @stack('scripts') sebelum </body> (infrastruktur @push halaman).
   - PENTING (bug unik): komentar JS tidak boleh memuat literal &#60;x-...&#62; — Blade component compiler menyangka ini tag komponen dan memproduksi &#60;?php if(...) tak seimbang &#8594; ParseError "expecting elseif/else/endif".
### Backend
- Tidak ada perubahan controller/route/validasi/hash/CSRF/auth. PasswordController utuh: current_password rule + confirmed + Password::min(8) + Hash::make + AuditLog.
### Verifikasi (2026-09-15)
- php -l layout + password/edit OK; iew:cache OK; semua compiled view lint OK.
- GET /pengaturan/password (USER & SA): card/shield/eye/strength/tips/btn Batal+Simpan/stack script semua hadir, HTTP 200.
- POST salah current_password &#8594; 200 + top alert + .field-error inline (tetap login).
- Uji sukses penuh pd akun SA: ganti ke temp &#8594; re-login dgn pw baru berhasil (Hash OK) &#8594; dikembalikan ke superadmin123 &#8594; 302 + session success (&#8594;toast). Akun SA & USER (NIP 3 user) tetap bisa login.

## Sesi 2026-09-15 (lanjutan): PENGATURAN UBAH PASSWORD - LAYOUT ENTERPRISE 2 KOLOM
- Desain ulang jadi **grid 2 kolom (max-width 1180px)**: kiri = form card, kanan = side panel 320px (stacks on mobile).
- **Page headline full-width** (bukan terkunci 680px) &#8594; hierarki judul lebih jelas.
- Side panel kanan:
  1. **Status Keamanan** (static card): Password terenkripsi (bcrypt), Proteksi CSRF, Sesi terautentikasi.
  2. **Terakhir Diubah** (data asli): query AuditLog where user_id=current & module='Pengaturan' latest id &#8594; tampil tanggal+jam+IP + diffForHumans; fallback "Belum ada riwayat".
  3. **Tips Keamanan** (blue-50 card, 3 bullet).
- Form: card head shield + divider, input height 44px, eye toggle, strength meter, live match, action footer #FAFBFD, [Batal] [Simpan Password Baru] (loading-btn), tombol full-width hanya di &#8804;480px.
- Backend tetap tidak berubah (controller/routes/validasi/hash/audit).
- Catatan: server dev php artisan serve sempat mati (HTTP 000) &#8594; dinyalakan ulang via Start-Process php -ArgumentList 'artisan','serve',... hidden window; sekarang UP di 127.0.0.1:8000.
- Verifikasi: php -l OK, iew:cache OK, GET /pengaturan/password (USER Rayn) HTTP 200 + semua marker (twoCol/Status Keamanan/Terakhir Diubah/Tips/form) hadir.

## Sesi 2026-09-15 (lanjutan): LOGIN PAGE ENTERPRISE
- esources/views/auth/login.blade.php dipoles (standalone, tidak pakai layout).
- Perbaikan: typo CSS maont-size &#8594; font-size (subtitle brand sebelumnya tak ter-set ukurannya).
- Objek baru: design token CSS (--blue/--ink/--line dll selaras app), card radius 16, input tinggi 44px & focus ring biru, eye toggle swap icon (eye/eye-off) + aria, tombol Masuk: spinner + "Memproses..." + disabled saat submit (JS, dengan cek field kosong &#8594; preventDefault), 
ovalidate + cek manual agar pesan error dari Laravel muncul, alert-error + icon.
- Hapus elemen buntu: link "Lupa password?" (tidak ada route) &#8594; diganti note "Hubungi Admin RSKK". Hapus CSS .divider/.help-link tak terpakai.
- Backend/routes/validasi login tidak diubah.
- Verifikasi: php -l OK, view:cache OK; GET /login 200 (eye/spinner/label hadir); login salah &#8594; 200 + alert-error; login benar (NIP 3/user) &#8594; masuk dashboard.

## Sesi 2026-09-15 (revisi 2): UBAH PASSWORD - SINGLE COLUMN INDUSTRI
- User mengeluh layout 2 kolom "padempet & acak-acakan", tombol aneh. &#8594; dibuang panel samping; kembali ke pola settings industri klasik: satu kolom terpusat max-width 680.
- Struktur baru: page-head (judul 24px + desc), alert error di luar card, satu card: header (shield + "Keamanan Akun") + body 3 field (current/new/confirm, gap 24px, input 44px, eye toggle swap icon, strength meter, pw-match live) + footer strip #FAFBFD: kiri "Terakhir diperbarui: {ts|null}" dari AuditLog (module Pengaturan, user saat ini), kanan tombol Batal(outline h-44px) + Simpan(h-44px).
- Bawah card: strip tips biru (icon info bulat + 1 kalimat) - ringkas, tidak kotak-kotak penuh.
- PENTING untuk smoke: route password.update = PUT &#8594; saat tes POST wajib sertakan _method=PUT atau kena 405 (bukan bug).
- Verifikasi: php -l OK, view:cache OK; GET /pengaturan/password (user & SA) 200 + semua marker; POST current pw salah (dgn _method=PUT) &#8594; 200 + .field-error inline + alert gagal, tetap login.
- CSS di layouts/app.blade.php sudah tersedia (.flabel,.req,.settings-card-*, .pw-strength, .pw-match.ok/.bad, token --blue-100/700/800, .btn-outline).

## Akar Masalah GLOBAL ditemukan (2026-09-15): Tailwind CDN SALAH URL
- Layout memuat <script src="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/3.4.1/tailwind.min.js"> yang ternyata **404** (file tsb tidak ada di cdnjs) &#8594; **Tailwind tidak pernah jalan**, semua class utilitas (px-7, flex, gap-3, dsb) MATI di seluruh app.
- Efek: spacing Pembongkaran (pengaturan tampak padempet/berantakan), tombol bertampilkan border bawaan browser 2px hitam (aneh), input melebar meluber parent (737px vs 622px, eye toggle tidak di ujung).
- Bukti audit geometric (Chrome headless + puppeteer-core di %TEMP%\opencode\pwscan + audit.js): input 737x65 & border tombol 2px black saat CDN mati; setelah fix jadi input 622x44, border tombol 0px.
- Perbaikan:
  1. <script src> diganti ke https://cdn.tailwindcss.com (official Play/JIT, teruji 200 via curl). cdnjs dijatah 404 untuk tailwind v3 browser-build.
  2. Safety reset di <style> layout: *,::before,::after{box-sizing:border-box} + utton{border:none;font-family:inherit} + .input{display:block;box-sizing:border-box} &#8594; halaman tetap hidup walau CDN gagal.
- Verifikasi pasca-fix: seluruh suite 200 (password user, /pegawai SA, /pegawai/5 user, login), post salah current-password tetap inline error. Audit ulang: input 622x44, no overflow, tombol border 0 & tinggi 44px seragam, eye toggle nempel ujung.
- Catatan: cdn.tailwindcss.com menulis console.warn "not for production" &#8594; opsi lanjutan: bundel Tailwind ke lokal (build/postcss) atau tulis mini-utility CSS custom.

## Sesi 2026-09-15 (revisi 3): VERIFIKASI vs modul.png & RESET BUTTON
- OCR ulang file asli C:\Users\MSI GF63\Downloads\modul.png (upscale 2x + 6 crop, ocr.ps1/prep.py). Referensi terkonfirmasi:
  - Sidebar: [DASHBOARD] Dashboard, Notifikasi; [KEPEGAWAIAN] Data Pegawai, Dokumen Digital, Approval; [ADMINISTRASI] Manajemen Akun, Master Data, Laporan, Audit Log; [AKUN] Profil Saya, Pengaturan; footer identitas + Logout.
  - Data Pegawai: breadcrumb Beranda > Data Pegawai; toolbar [Cari NIP/unit] [Unit Kerja] [+ Tambah Pegawai] [Reset]; tabel No|Foto|Nama|NIP|Jabatan|Unit|Status|Aksi; pagination "1-? dari ? data".
  - Modul 6 Logout modal, Modul 7 Toast, Modul 8 Policy/Gate/Middleware.
- Dump DOM (Chrome headless + puppeteer-core + dump.js): sidebar app sekarang PERSIS referensi (plus Pengaturan Sistem utk SA), tabel kolom persis, footer "Super Admin RSKK ... Logout".
- Perubahan: (1) tombol Reset sekarang SELALU tampil di toolbar Data Pegawai (sebelumnya cuma muncul saat filter aktif, icon refresh) ; (2) placeholder cari &#8594; "Cari NIP, nama, atau unit...".
- Alat audit: %TEMP%\opencode\pwscan\{audit.js,dump.js,dump2.js,dump3.js} (puppeteer-core, lokasi Chrome C:\Program Files\Google\Chrome\Application\chrome.exe). Session cookie Netscape jar dari curl (hati2: baris #HttpOnly_ harus di-strip prefix).
- CATATAN: model ini TIDAK bisa membaca gambar (read image ERROR "model does not support image input") &#8594; strategi: OCR utk ikon/teks + dump DOM geometri utk verifikasi visual.
- File referensi lain: "C:\Users\MSI GF63\Downloads\Modul Kepegawaian RSKK.html" (saved chat berisi MASTER PROMPT FINAL - SIMPEG RSKK; ekstraksi teks raw rusak per-kata, bisa direpair scrub baru).

## Sesi 2026-09-15: PENGATURAN MENGIKUTI pengaturan.png (referensi baru)
- OCR C:\Users\MSI GF63\Downloads\pengaturan.png (1536x1024, upscale 2x + 6 crop) -> layout 2 kolom:
  - Kiri: card "Keamanan Akun"; field Password Saat Ini / Password Baru / Ulangi Password Baru (placeholder persis); indikator "Kekuatan Password: [Sedang]" + bar 4 segmen; hint "Minimal 8 karakter."; footer [Batal][Simpan Password Baru].
  - Kanan: card biru "Jaga Keamanan Akun Anda" + 3 baris fitur (Data Lebih Aman / Privasi Terjaga / Akses Lebih Stabil) ber-icon; card "Tips Keamanan" 3 bullet. 
- Impl esources/views/password/edit.blade.php: grid xl:grid-cols-[1fr_340px] max-w-1180, fs-meter 4 segmen (.strength-seg i) dipicu input, pw-match, eye toggle, loading button. CSS baru di layout: .fs-label/.fs-value/.strength-seg.
- Catatan: kini Tailwind sudah hidup (CDN fix sebelumnya) sehingga grid 2 kolom render benar.
- Verifikasi geometri (Chrome headless, 1440px): form x=280 w=772; aside x=1076 w=340 sejajar y=171; input 714x44; tombol primary border 0 h=44. Fungsional: ketik 'Sandi12345' -> meter tampil, label Sedang (amber-600), 2/4 segmen menyala; kecocokan pw benar/salah berubah hijau/merah. POST current salah -> 200 + inlineErr + alert (tetap).

## Sesi 2026-09-15: PROFIL PEGAWAI MENGIKUTI "profil saya.png" (referensi baru)
- OCR C:\Users\MSI GF63\Downloads\Modul Kepegawaian RSKK_files\profil saya.png (1536x1024): breadcrumb "Beranda > Data Pegawai > Profil Pegawai"; judul "Profil Pegawai"; header avatar + nama + badge Aktif + "NIP. ..." + jabatan + unit + tombol Print/Kembali; 12 tab; Ringkasan = 2 kartu info (Informasi Pribadi & Informasi Kepegawaian); banner "Data ini hanya dapat dilihat..."
- Impl employees/show.blade.php:
  - page-head baru: h1.page-title "Profil Pegawai" + desc, tombol [Kembali][Print][Edit Profil|Lanjutkan Isi] (ganti baris "Kembali ke Data Pegawai" lama).
  - Tab Ringkasan disusun ulang mengikuti referensi: heading "Ringkasan" + desc "Informasi umum mengenai data pegawai.", lalu grid lg:grid-cols-2 dua kartu:
    - Informasi Pribadi: Nama Lengkap, NIP, NIK (mask ), Tempat/Tanggal Lahir, Jenis Kelamin, Agama, Status Perkawinan, Golongan Darah.
    - Informasi Kepegawaian: Status Pegawai, Kategori, Jenis ASN, Unit Kerja, Jabatan, Jenis Jabatan, Pangkat/Golongan, TMT Jabatan, TMT Pangkat, Masa Kerja.
  - Konten lama Ringkasan (Informasi Utama + Kelengkapan Data + KPI 6 kartu) DIBUANG (tidak ada di referensi).
- Detail: TTL kosong kini render '-' bukan ',' (guard tempat_lahir||tanggal_lahir).
- Tab tetap 15 (masih ada ekstra Pangkat & Golongan/Mutasi/Linimasa Karier di luar 12 tab referensi) - dibiarkan karena berisi data berguna.
- Verifikasi: php -l OK; view:cache OK; render /pegawai/5 sbg user (nip3) - TITLE="Profil Pegawai", READONLY_NOTE YA, tanpa tombol Edit, Ringkasan 2 kartu, NIK mask '********'; sbg SA /pegawai/5 -> tombol Edit tampil, note tidak (by design, manager).

## Sesi 2026-09-15 (lanjutan): PROFIL SAYA (/profil-saya) disamakan dengan "profil saya.png"
- User klarifikasi: yang dimaksud TAMPILAN HALAMAN PROFIL SAYA (route /profil-saya), bukan /pegawai/{id}.
- Impl resources/views/profile/edit.blade.php dirombak mengikuti pola foto profil saya.png:
  - page-head: [Print][Kembali][Ajukan Perubahan] - title 'Profil Saya'.
  - Header card baru (salinan pola employees/show): strip gradien navy + avatar + nama + chip Aktif + 'NIP. x' + jabatan + unit + box Ringkasan (Unit Kerja/Jabatan/Pangkat-Golongan).
  - Tab bar: Ringkasan | Data Pribadi | Alamat & Kontak | Kepegawaian | Riwayat Perubahan | Ajukan Perubahan.
  - Ringkasan = 2 kartu info identik referensi (Informasi Pribadi + Informasi Kepegawaian) via _kv.
  - Data Pribadi / Alamat & Kontak / Kepegawaian = isi lama (ikon) dalam panel tab.
  - Riwayat Perubahan = tabel pengajuan (status pill hijau/amber/merah, ringkasan field berubah dari diff old/new).
  - Ajukan Perubahan = form lama dipindah jadi tab (fungsi/validasi tidak diubah; Controller & route TIDAK disentuh).
- Field display disesuaikan kolom nyata: alamat_domisili_ktp/rt_domisili(ktp)/kelurahan_domisili, telp (bukan telepon), tmt_gaji_berkala_terbaru, status_pegawai?:status_calon, tugas_tambahan_1?:tugas_tambahan_2, golonganAwal/Akhir.
- Verifikasi: php -l OK; view:cache OK; dump DOM (user nip3) - title 'Profil Saya', HEAD btn Print/Kembali/Ajukan, TABS 6, Ringkasan 2 kartu, form ada; SA (nip1) -> empty-state 'Akun belum terhubung' tanpa error.

## Sesi 2026-09-15 (lanjutan): FIX ERROR APPROVE - NIK DUPLIKAT
- KELUHAN USER: setelah pegawai ajukan perubahan, Super Admin klik approve ERROR.
- ROOT CAUSE (dari laravel.log): SQLSTATE[23000] 1062 Duplicate entry di key employees.nik. Kasus nyata: pengajuan #8 (putu, emp6) membawa NIK 2827293739273927 yang SUDAH dimiliki Rayn (emp1). Kolom nik unique -> update gagal, approve crash 500. Request tetap pending (atomik), DB tidak berubah.
- PERBAIKAN (tidak ubah alur/skema):
  1) ProcesController.update (saat ajukan): 'nik' + Rule::unique('employees','nik')->ignore(employee_id) -> NIK dobel ditolak di form dengan pesan validasi, masukan tidak pernah masuk antrean.
  2) ApprovalController.approve: cek uniqueConflicts() dulu (nik dipakai pegawai lain?) -> back() pesan ramah 'Tidak dapat menyetujui: NIK ... sudah dipakai pegawai lain (...) Tolak pengajuan ini...'; lalu approve dibungkus DB::transaction + try/catch Throwable -> kalau tetap gagal kasih pesan, request tetap pending, SA bisa Reject.
- VERIFIKASI:
  - POST /approval/8/approve (SA): tidak crash, tidak ada flash 'sudah dipakai', status CR8 tetap pending, nik putu/rayn tidak berubah.
  - Happy path (tanpa konflik) diuji via DB transaction + rollback: approve -> status approved, data tidak persist.
  - php -l OK, view:cache OK, route:cache OK (kemudian route:clear biar dev tidak stale).
- CATATAN: kasus hanya validasi NIK (form profil tidak mengirim nip). Modul sub-data tetap berjalan sama (transaction tidak mengubah semantik).

## Sesi 2026-09-15 (lanjutan 2): FITUR LIHAT PASSWORD SUPER ADMIN
- PERMINTAAN USER: Super Admin bisa melihat NIP + password akun, dan tetap bisa melihat password baru setelah diganti.
- BATASAN TEKNIS: password disimpan HASH (bcrypt) -> tidak bisa dibalik. Solusi: simpan salinan terenkripsi (AES-256 via Crypt / APP_KEY) di kolom baru users.password_cipher. Password lama yang belum pernah tercatat -> null, ditampilkan '—' (tidak bisa dipulihkan).
- PERUBAHAN:
  1) Migrasi 2026_09_15_074126_add_password_cipher_to_users_table: tambah users.password_cipher VARCHAR NULL.
  2) User model: password_cipher masuk fillable + hidden (tidak bocor di toArray/JSON); accessor -> decrypted_password (Crypt::decryptString, fallback null).
  3) AccountController::store -> simpan password_cipher; resetPassword -> simpan cipher password acak baru (selain flash lama); NEW revealPassword(): JSON {nip,name,password} HANYA role super_admin (kalau bukan -> 403).
  4) PasswordController::update (user ganti sendiri via Pengaturan) -> sekaligus simpan password_cipher, jadi SA selalu bisa lihat versi terbaru.
  5) Route baru: GET admin/accounts/{account}/password -> admin.accounts.password (grup role:super_admin).
  6) View admin/accounts/index: kolom 'Password' hanya dirender utk SA; tampil '••••••••' + tombol 'Lihat/Sembunyikan' (fetch ke endpoint, @push scripts). Akun cipher null -> '—' + tooltip sarankan reset. colspan empty-state dinamis 7/6.
- VERIFIKASI HTTP (login SA via cookie jar):
  - GET /admin/accounts -> 200, header Password + tombol Lihat utk akun 1 & 3.
  - GET /admin/accounts/1/password -> {"nip":"1","password":"superadmin123"}; akun cipher-null (id 3 sebelum backfill) -> password:null tanpa error.
  - GET endpoint sebagai role user (nip 3) -> 403.
  - Roundtrip Crypt + Hash::check tetap valid; password_cipher tidak muncul di toArray.
- BACKFILL: hanya password dev yang DIdokumentasi & hash cocok: nip 1 = superadmin123, nip 3 = user. Sisanya null (tampil '—').
- CATATAN KEAMANAN: fitur ini membuka plaintext password ke SA (permintaan user). Terenkripsi at-rest pakai APP_KEY. Idealnya di produksi password TIDAK dapat dipulihkan; ini kompromi khusus sesuai permintaan. File temp _pw_check.php sudah dihapus.

## Sesi 2026-09-15 (lanjutan 3): SESEJALAN dgn MOCKUP kadek.png (Profil Pegawai)
- PERMINTAAN USER: "ubah tampilan dengan foto ini (nama file kadek.png)" -> samakan halaman /pegawai/{id} persis mockup.
- TEMUAN PENTING dari verifikasi OCR halus (winocr crop + zoom 4-6x):
  - MOCKUP PUNYA 12 TAB (bukan 11!). Tab yang TERLEWAT OCR awal: "Kepegawaian" (muncul kena zoom saat saya crop gap x540-790 y355-400 -> "Kepegawaian Riwayat Jabatan"). Daftar tab kadek.png: Ringkasan, Data Pribadi, Alamat & Kontak, Kepegawaian, Riwayat Jabatan, Pendidikan, Diklat & Sertifikasi, Dokumen, Kinerja, Penghargaan, Keluarga, Riwayat Perubahan.
  - Jadi yang HARUS disembunyikan: Pangkat & Golongan, Mutasi, Linimasa Karier (3 tab, BUKAN 4 — Kepegawaian tetap tampil).
  - Header mockup: nama + badge "Aktif" SEBARIS, lalu NIP di baris sendiri, jabatan baris sendiri, unit baris sendiri; mini-box kanan TANPA judul "Ringkasan" (langsung 3 row: Unit Kerja / Jabatan / Pangkat-Golongan).
  - Ringkasan panel: label "Tempat/Tanggal Lahir" (pakai /) dan "Masa Kerja 16 Tahun 3 Bulan" (bukan "16 th 3 bln").
- PERUBAHAN resources/views/employees/show.blade.php:
  - Tab bar: hapus tombol pangkat-golongan, mutasi, riwayat (Linimasa). Urutan tab jadi PERSIS mockup (12).
  - Header: $st badge inline di samping nama (dipakai $isActive); NIP/jabatan/unit pisah baris; hapus penanda `•`; hapus judul "Ringkasan" di mini-box kanan.
  - Ringkasan: 'Tempat, Tanggal Lahir' -> 'Tempat/Tanggal Lahir'; masa_kerja format 'X Tahun Y Bulan'.
  - Panel tab yang disembunyikan tetap ada di DOM (hidden) -> tidak dihapus, aman & masih bisa diakses lewat Profil Saya/rute modul; data di DB tetap utuh.
- VERIFIKASI: php -l OK, view:cache OK. Render /pegawai/6 sebagai SA -> 12 tombol tab PERSIS urutan mockup (ringkasan, pribadi, alamat-kontak, kepegawaian, riwayat-jabatan, pendidikan, diklat, dokumen, kinerja, penghargaan, keluarga, riwayat-perubahan).
- CATATAN: kadek.png pegawai contoh = Budi Santoso NIP 198507152010011001 (tidak ada di DB -> murni mockup). Dua PNG lain (e0f9e6b8, 1b8c4053) = varian dengan Kelengkapan Data 82%/Tips + breadcrumb "Beranda > Data Pegawai > Profil Pegawai" — BUKAN target (user sebut file kadek.png).

## 2026-09-16 — Pemulihan show.blade.php (korup 0 byte) + Penghapusan "garis coret" (em-dash)
- INSIDEN: perintah PowerShell [IO.File]::WriteAllText dengan hasil regex yang gagal (Insufficient hexadecimal digits pada pola \x{2014}) MENULIS FILE KOSONG ke resources/views/employees/show.blade.php (0 byte). PELAJARAN: jangan pernah menulis ulang file via PowerShell regex; gunakan tool edit/write atau [regex]::Replace SAJA lalu simpan.
- PEMULIHAN: basis = backup simpeg-rskk-laravel13 - Copy\resources\views\employees\show.blade.php (versi 9/9, 15 tab), lalu diterapkan ulang seluruh perombakan 9/15 mengikuti render final show6.html (12 tab, page-head, header mockup, Ringkasan 2 kartu). show6.html sudah dihapus setelah diverifikasi.
- AKAR "GARIS CORET DI TENGAH HURUF": BUKAN text-decoration:line-through (0 kemunculan di render). Penyebab = placeholder em-dash "—" (U+2014) untuk nilai kosong yang tampil seperti garis horizontal di tengah. Sebelumnya 53 em-dash di render halaman; sesudah perbaikan 0 di dalam <main>.
- PERUBAHAN FILE:
  - resources/views/employees/show.blade.php: semua fallback ?? '—', ?: '—', dan span — diubah menjadi string kosong '' (nilai kosong = tidak ada teks). Plus blok <style>.card .field-row{border-bottom:none;padding:3px 0}</style> di dalam @section('content'). Tiga em-dash prosa (draft alert, alert kosong, module_type—description) juga diganti (titik / middot).
  - resources/views/employees/_kv.blade.php: placeholder — (abu-abu) diganti string kosong '' (dari sesi sebelumnya).
- KEPUTUSAN: em-dash di LAYOUT (judul tab <title>, komentar CSS, footer "c 2026 SIMPEG RSKK — RSUD ...") DIBIARKAN — bukan nilai field, hanya prosa/tersembunyi. Jika ingin dibersihkan, cari U+2014 di resources/views/layouts/app.blade.php.
- VERIFIKASI: php -l OK; view:clear + view:cache OK; render /pegawai/6 sebagai SA -> HTTP 200, 12 tombol tab persis mockup, 15 panel (termasuk pangkat/mutasi/riwayat tetap di DOM hidden), Masa Kerja Ringkasan "8 Tahun 0 Bulan" (dinamis dari TMT), 0 em-dash di dalam <main>.
---

## 2026-09-16 — Sesi Verifikasi Akhir Ekspansi Profil (Fase 2 selesai)

### Ringkasan implementasi yang TELAH KOMPLET & TERVERIFIKASI end-to-end (HTTP login SA + role user)

**1. Restrukturisasi `employees/show.blade.php` → 8 tombol tab kelompok:**
Ringkasan · Data Pribadi · Arsip & Dokumen Digital · Pendidikan & Diklat · Kepegawaian · Kinerja & Penghargaan · Keluarga · Riwayat Perubahan. Terverifikasi HTTP 200, 8 tab `.tab-btn`, DASH_MAIN=0 (12 em-dash hanya di layout, bukan dalam `<main>`).

**2. Fondasi data baru (migrasi terpanggil sukses):**
- `2026_09_16_000001_create_employee_riwayat_tables.php` — 11 tabel riwayat: bahasa, cuti, inaktif, kedudukan_hukum, penyakit, kontak_darurat, pmk, kontrak_pppk, skp, angka_kredit, ipasn (kolom lengkap, FK employee_id cascade).
- `2026_09_16_000002_expand_existing_tables.php` — `kategori` di employee_educations & employee_trainings, enum families `['pasangan','anak','orang_tua','saudara']`, `kategori` di documents, kolom baru employees (`nama_ibu_kandung`, `no_akta_kelahiran`, `no_buku_nikah`, `no_akta_cerai`).
- 11 model baru (EmployeeLanguage..EmployeeIpasn) + EmployeeCreditScore/EmployeeIpasn/EmployeeSkp model → terverifikasi via `php -l` & `view:cache`.

**3. `SubDataController` diperluas:**
- Import 11 model baru; config `pendidikan` + `diklat` sekarang punya field `kategori` (select, pendidikan formal/non_formal; diklat struktural/fungsional/teknis/keahlian_profesi/bintek_seminar).
- `keluarga` select `type` kini punya opsi **saudara**; tambahan `bahasa`, `cuti`, `inaktif`, `kedudukan_hukum`, `penyakit`, `kontak_darurat`, `pmk`, `kontrak_pppk`, `skp`, `angka_kredit`, `ipasn`.
- `tab` dipetakan ulang ke 5 panel baru: `pendidikan-diklat`, `kepegawaian`, `kinerja-penghargaan`, `keluarga` (+ `ringkasan`, `pribadi`, `dokumen`, `riwayat-perubahan`).

**4. Dokumen (Arsip & Dokumen Digital):**
- `DocumentController::KATEGORI_GRUP` (8 kelompok arsip: Dokumen Pribadi, Akademik, Kepegawaian, Diklat & Sertifikat, Cuti, Mutasi, Penghargaan, Lainnya).
- Kolom `kategori` dibawa di `store` (select di create), lalu di `show.blade.php` dokumen dikelompokkan berdasarkan kategori.
- `edit`/`update` method + rute `documents.edit` `/dokumen/{doc}/edit` + view `documents/edit.blade.php` (jenis_dokumen, kategori select, no_dokumen, tanggal, keterangan, file opsional).

**5. Gating hak akses — TERVERIFIKASI via 2 akun login berbeda:**
- **role user** → KGB, Hukuman Disiplin, Riwayat Penyakit, Kedudukan Hukum, PMK, IPASN, SKP, Angka Kredit, IPASN **HIDDEN** (kondisi `$isAdmin`); bagian pribadi, cuti, kontak darurat, kontrak PPPK, dokumen **TAMPIL**.
- **role super_admin** → semua bagian tampil (ringkasan, data pribadi, arsip, pendidikan, kepegawaian (kgb/hukdis/penyakit), kinerja/penghargaan, keluarga, riwayat perubahan).
- DASH_MAIN=0, em-dash tersisa hanya di layout (title tab, komentar CSS, footer) — dibiarkan, bukan bagian konten.

**6. Kebersihan database:** akun uji temporer (Penguji Cuti User, Penguji Verifikasi, Penguji Verif User, Uji Role User Final, Kontak Darurat Test, dll.) **dihapus** via script cleanup. Tersisa 6 akun resmi: super_admin RSKK, kevin, rayn, hasna, yulaika, putu (super_admin: nip login `1`, password `superadmin123`; user NIP=2 dgn nip 2 & password ... terverifikasi). DB dalam kondisi bersih & siap demo.

### Catatan untuk sesi berikutnya
- Skema & tampilan **SIAP**; tinggal bila perlu: import/paginasi data besar, notifikasi role user saat dokumen ditolak (sudah ada), dan pengujian CRUD full (create/edit/destroy) tiap modul sub — sudah diimplementasikan via `sub.*` generic + config.---

## 2026-09-16 — Perbaikan Tab "Riwayat Perubahan" + Gabung Audit Log (SELESAI REVIEW USER)

### Masalah (dilaporkan user)
- Tab "Riwayat Perubahan" di halaman profil terlihat kosong.
- Akar masalah: akun `kevin` & `hasna` **tidak terhubung ke data pegawai** (`employees.user_id` kosong), sehingga halaman "Profil Saya" mereka hanya menampilkan kartu "Akun belum terhubung ke data pegawai" tanpa tab sama sekali.

### Perbaikan
1. **Koneksikan akun ke pegawai**: dibuat record employee baru `kevin` (E7, nip 2) dan `hasna` (E8, nip 4), lalu `user_id` di-set → seluruh akun (kevin, Rayn, yulaika, putu) kini memiliki `employee`. Halaman Profil Saya semua user menampilkan tab lengkap.
2. **Tab "Riwayat Perubahan" kini juga menampilkan Audit Log** (bukan hanya change_requests):
   - Migrasi `2026_09_16_000003_add_employee_id_to_audit_logs.php` — kolom `employee_id` + index di `audit_logs`; **dijalankan**.
   - `AuditLog::record()` sekarang otomatis mengisi `employee_id` dari reference (jika model Employee, atau atribut `employee_id`, atau via relasi user.employee).
   - Backfill semua log lama agar `employee_id` terisi dari reference.
   - Relasi `Employee::auditLogs()` (hasMany audit_logs via employee_id).
   - `EmployeeController::show()` eager-load `auditLogs`.
   - View `employees/show.blade.php` tab riwayat-perubahan: judul jadi "Riwayat Perubahan & Aktivitas", menampilkan baris Audit Log (badge Tambah/Ubah/Hapus/Disetujui SA/Ditolak + module + deskripsi + user + waktu) disusul baris pengajuan perubahan (status Menunggu/Disetujui/Ditolak).
   - View `profile/edit.blade.php` tab riwayat-perubahan: tabel gabungan Audit Log + Change Request dengan badge berwarna.

### Verifikasi
- SA `/pegawai/6` → CODE 200, tab berisi 3 baris audit (approve putu) + 4 pengajuan, tanpa empty-state, DASH_MAIN tetap 0.
- Profil Saya (putu, E6) → tab "Riwayat Perubahan & Aktivitas" render, 4 baris audit tampil.
- `view:cache` & `php -l` bersih. Usulan meta: audit log aktivitas masa lalu tidak bisa diverifikasi hilang karena reference dihapus — hanya 19 log, tapi semua verif.

### Catatan
- Change Request (pengajuan perubahan oleh user) vs Audit Log (aktivitas admin/SA/system) kini tampil bersama di satu tab, dikelompokkan per urutan `created_at`.
- Tab di Profil Saya diakses tanpa gating (semua role melihat aktivitas data miliknya sendiri).---

## 2026-09-16 — Data Contoh + Master Jabatan/Pangkat + Fix cast 'year' (SELESAI)

### Yang dilakukan
1. **Master data** diisi (sebelumnya kosong):
   - `positions` = 12 jabatan khas RS (Direktur, Kabag TU, Dokter Umum, Perawat Ahli Pertama, Pengadministrasi Umum, dll), type struktural/fungsional/pelaksana.
   - `ranks` = 15 golongan-pangkat lengkap I/a .. IV/c.
2. **Data contoh pegawai realistis** (via script idempoten):
   - **kevin (E7)**: PPPK, Pengadministrasi Umum (Subbagian TU), gol II/c, NIK/tempat/tanggal lahir/alamat/kontak penuh; 2 pendidikan (SMA + S1 UB Jurusan Administrasi Publik), 2 diklat (Bimtek Arsip Digital + Pelatihan Sistem Informasi RS), 2 bahasa, 1 penghargaan (Pegawai Teladan), 2 keluarga (istri + anak), 1 kontak darurat, 2 cuti, 1 kontrak PPPK, 1 dokumen (Ijazah S1).
   - **hasna (E8)**: PNS, Perawat Ahli Pertama (Bidang Keperawatan), gol III/c; 2 pendidikan (D3 Keperawatan + Profesi Ners UNAIR), 2 diklat (BTCLS + Diklat PIM Pratama), 1 bahasa, 1 penghargaan (Tenaga Kesehatan Teladan), 2 keluarga, 1 kontak darurat, 2 cuti (melahirkan + tahunan), 1 kontrak, 2 dokumen (Ijazah Ners + Sertifikat BTCLS).
3. **Bug fix cast Eloquent `'year'`** → `'integer'` di 5 model (EmployeeEducation, EmployeeAward, EmployeeCreditScore, EmployeeIpasn, EmployeeSkp). Cast `'year'` tidak valid di Eloquent dan akan mematahkan create/update lewat form. Kolom DB tetap `year` (MySQL), nilai int.

### Verifikasi
- `/pegawai/7` & `/pegawai/8` sebagai SA → CODE 200, nama/pendidikan/keluarga/penghargaan tampil, DASH_MAIN=0 (12 em-dash hanya di layout).
- `/profil-saya` untuk kevin & hasna → Ringkasan + Riwayat Perubahan + Ajukan Perubahan tab semua muncul (in-process render; password login user tidak diubah).
- `view:cache` setelah edit model aman; seed script disimpan di temp selama sesi.

### Catatan
- Data contoh bersifat seed, dapat dihapus/diubah kapan pun lewat UI atau DB.
- Tab dokumentasi contoh diterbitkan dan dinormalisasi (file_path kosong = belum ada file fisik — ditampilkan sesuai status).

## 2026-09-16 — Semua Modul Sub-Data Masuk ke "Profil Saya" (SELESAI)

### Permintaan user
- Fitur modul yang diberikan rancangannya ternyata untuk **Profil Saya**, bukan hanya halaman detail pegawai admin. User minta seluruh modul (pendidikan & diklat, arsip dokumen, kepegawaian lengkap, kinerja & penghargaan, keluarga, dll.) tampil juga di "Profil Saya".

### Perbaikan
1. **Ektraksi panel modul menjadi partial reusable** (agar halaman admin & Profil Saya berbagi markup yang sama):
   - `resources/views/employees/_panel-dokumen.blade.php` — tab `tab-dokumen` (arsip dokumen digital, dikelompokkan per kategori; aksi edit/verifikasi/hapus hanya jika `$admin`).
   - `resources/views/employees/_panel-pendidikan-diklat.blade.php` — tab `tab-pendidikan-diklat` (pendidikan formal/non formal, diklat per kategori, riwayat bahasa).
   - `resources/views/employees/_panel-kepegawaian.blade.php` — tab `tab-kepegawaian` (kondisi saat ini + ringkasan status + riwayat jabatan, pangkat & golongan, mutasi, KGB, PMK, cuti, inaktif, kontrak PPPK, kontak darurat, kedudukan hukum, hukdis & penyakit bila `$sensitive`).
   - `resources/views/employees/_panel-kinerja-penghargaan.blade.php` — tab `tab-kinerja-penghargaan` (kinerja, SKP, angka kredit, IPASN, penghargaan).
   - `resources/views/employees/_panel-keluarga.blade.php` — tab `tab-keluarga` (anggota keluarga per type: pasangan/anak/orang tua/saudara).
   - Partial memakai variabel `$e` (Employee) + `$admin` (bool; menampilkan tombol Tambah/Edit/Hapus). `_panel-kepegawaian` juga menerima `$sensitive` untuk section hukdis & penyakit.
2. **Profil Saya (`profile/edit.blade.php`)**:
   - Tambah variabel `$isSA` / `$isAdmin` di header.
   - Tab baru: **Arsip & Dokumen**, **Pendidikan & Diklat**, **Kinerja & Penghargaan**, **Keluarga**; tab Kepegawaian lama (hanya summary) **diganti** dengan include `_panel-kepegawaian` (full).
   - Include partial dengan `['e' => $emp, 'admin' => $isAdmin]`; kepegawaian pakai `'sensitive' => true` (data milik sendiri selalu tampil bagi pemilik; tombol edit tetap hanya utk admin).
3. **Halaman admin `employees/show.blade.php` TIDAK diubah** — tetap inline (sudah terverifikasi); partial baru hanya dipakai Profil Saya agar render user cepat & gating konsisten.

### Verifikasi
- `php artisan view:cache` bersih; `php -l` seluruh partial & profile bersih.
- Render in-process untuk **kevin (U2/E7)**, **hasna (U5/E8)**, **putu (U7/E6)**: semua tab OK (ringkasan, pribadi, alamat-kontak, dokumen, pendidikan-diklat, kepegawaian, kinerja-penghargaan, keluarga, riwayat-perubahan, ajukan); tidak ada kartu "Akun belum terhubung"; panjang HTML 93–100 KB.
- Section sensitif (hukdis, penyakit, KGB) tampil untuk pemilik data sendiri (sensitive=true), tanpa tombol edit utk role user.

### Catatan
- `documents.create` route sudah tersedia utk semua role → tombol "Unggah Dokumen" di Profil Saya user mengarah ke upload dokumen sendiri; admin → create-admin.
- Em-dash (U+2014) tetap 0 di dalam `<main>` (partial berisi en-dash "–" dari markup show asli).

## 2026-09-16 — Role User Bisa Menambah/Mengedit Data Modul Sendiri di Profil Saya (SELESAI)

### Permintaan user
- "sekarang di perubahan nya udah bisa meng edit semua fitur fitur baru nyaa" — user mengkonfirmasi ingin role `user` (pegawai) bisa **edit/tambah data modulnya sendiri** langsung dari Profil Saya, bukan hanya melihat.

### Perbaikan
1. **`routes/web.php`**: grup route `sub.*` dipindah keluar dari middleware `role:super_admin,admin` ke grup `auth` umum (agar role user bisa akses). `sub.download` ikut terbuka utk semua (otorisasi kepemilikan sudah ada di controller).
2. **`SubDataController`**: tambah method privat `authorizeAccess(Employee $employee)`:
   - `super_admin` / `admin` → boleh akses semua employee.
   - role lain (user) → hanya employee dengan `$employee->user_id === user.id`, selain itu `403`.
   - Dipanggil di `create`, `store`, `edit`, `update`, `destroy` (download sebelumnya sudah cek kepemilikan sendiri).
3. **`profile/edit.blade.php`**: include partial modul diubah dari `'admin' => $isAdmin` menjadi `'admin' => true` (sediakan tombol Tambah/Edit/Hapus utk pemilik data sendiri). Partial `_panel-dokumen` **tetap** `'admin' => $isAdmin` karena route edit/verifikasi dokumen (`documents.*`) memang khusus admin; user tetap bisa *unggah* dokumen sendiri via `documents.create`.

### Verifikasi
- `php artisan view:cache` bersih; `php -l` SubDataController & routes/web.php bersih.
- Render in-process kevin (role user, E7): profile OK (len 112 KB), tab & tombol "Tambah Pendidikan"/Hapus tampil; "Edit Pendidikan" baru tampil jika ada row (E7 belum punya data pendidikan).
- Test CRUD sub-data sebagai role user:
  - `create` data sendiri → OK (form "Tambah Pendidikan" tampil).
  - `create` / `edit` data **employee lain** (E1) → `403` (authorizeAccess bekerja).
  - `store` data sendiri → row tersimpan; `destroy` → row terhapus (temp row E7#5 dibuat & dihapus, bersih).
- Route list `sub.*` kini tanpa middleware role; otorisasi dikunci di controller per kepemilikan.

### Catatan
- Dengan `'admin' => true` di Profil Saya, user pemilik data kini juga melihat tombol edit untuk section sensitif miliknya (hukdis/penyakit/KGB) — sesuai keinginan "edit semua fitur". Admin di halaman employee menampilkan hal serupa via gating `$isAdmin` yang sudah ada.

## 2026-09-16 — Unggah Foto Profil (User & Admin) (SELESAI)

### Permintaan user
- "coba di user semua nya dan super admin juga utamanya user bisa memasukan foto mereka jadi ada poto profile nya" — buat fitur upload foto profil sehingga pegawai (role user) punya foto profil sendiri; Super Admin juga bisa (di halaman pegawai).

### Perbaikan
1. **Kolom** `employees.foto_path` sudah ada sejak awal (migration `2024_01_01_000003`), baru kini dipakai.
2. **Routes** (`routes/web.php`):
   - `POST /profil-saya/foto` → `profile.photo` (semua role, utk foto sendiri)
   - `DELETE /profil-saya/foto` → `profile.photo-delete` (hapus foto sendiri)
   - `POST /pegawai/{employee}/foto` → `employees.photo` (admin/super_admin, utk foto pegawai tertentu)
3. **`ProfileController`**: `updatePhoto()` & `deletePhoto()` — validasi `foto` image (jpeg/jpg/png/webp, max 5 MB), simpan ke disk `public/fotos/{hash}.ext`, hapus file lama, tulis AuditLog modul "Profil".
4. **`EmployeeController`**: `updatePhoto(Employee $employee)` — sama utk admin via `employees.show` (AuditLog modul "Pegawai").
5. **Tampilan**:
   - `layouts/app.blade.php`: avatar sidebar kini pakai `employee.foto_path` (`asset('storage/fotos/...')`) bila ada, fallback `images/default-avatar.png`.
   - `profile/edit.blade.php`: avatar besar pakai foto; tombol lingkaran kamera (`.avatar-edit`) untuk ganti foto; tombol hapus (`.avatar-delete`) muncul bila foto ada; form upload hidden di-submit onchange.
   - `employees/show.blade.php`: avatar pakai foto pegawai bila ada; tombol kamera utk admin/super_admin untuk upload foto pegawai tsb.
   - CSS `.avatar-edit` / `.avatar-delete` / `.image-present` ditambahkan di `layouts/app.blade.php`.
6. **Catatan**: file `public/images/default-avatar.png` ternyata **tidak ada** — fallback onerror ke inisial tetap berfungsi (seperti sebelum fitur ini).

### Verifikasi
- `php -l` ProfileController, EmployeeController, profile/edit, layouts/app, employees/show bersih; `view:cache` OK.
- Render in-process:
  - profile kevin (role user, U2/E7): len ~117 KB, avatar-edit tampil, foto path muncul di `storage/fotos/...` setelah upload.
  - employees/show (role super_admin): len ~117 KB, avatar-edit tampil.
  - LAYOUT sidebar kevin memakai `storage/...` foto setelah upload.
- Test end-to-end upload foto:
  - validasi gagal utk file non-gambar (.pdf) → ValidationException.
  - upload jpeg → path `fotos/...` tersimpan + file eksis di disk.
  - render profile & layout menggunakan foto.
  - delete → `foto_path` null + file terhapus.
  - admin upload foto pegawai → OK; cleanup bersih (foto test dihapus, disk bersih).

### Catatan
- Foto test dibuat & dihapus; `foto_path` employee dikembalikan ke null. Storage `public/storage` sudah linked.
- Route `employees.photo` (POST `/pegawai/{employee}/foto`) didefinisikan di grup `role:super_admin,admin` **sebelum** `employees.show` (GET `/pegawai/{employee}`) — aman karena method berbeda.

## 2026-09-16 — Fix: Tombol "Ganti/Hapus Foto" Tidak Terlihat (overflow-hidden) (SELESAI)

### Masalah
- User melaporkan "belum ada perubahan untuk menambahkan atau mengganti foto nyaa" — tombol kamera TIDAK TERLIHAT di halaman.
- **Akar**: CSS `.profile-avatar` memakai `border-radius:50%` **dan** `overflow:hidden`. Tombol `.avatar-edit` sebelumnya diletakkan `position:absolute; right:0; bottom:0` (pojok kanan-bawah persegi) DI DALAM elemen itu. Karena container berbentuk lingkaran + overflow:hidden, bagian tombol yang berada "di luar" lingkaran (yaitu hampir seluruhnya, mengingat posisinya di sudut persegi) terpotong total → tidak tampil.

### Perbaikan
1. **`layouts/app.blade.php`** — CSS diubah:
   - Tambah `.profile-avatar-wrap{position:relative; flex:none}` (wrapper baru).
   - Tombol diganti selector menjadi `.profile-avatar-wrap .avatar-edit` / `.avatar-delete`, posisi `right:-4px; bottom:-4px` (dan `left:-4px` utk hapus) agar tampil **mengambang/overlay di sudut lingkaran avatar**, tidak terpotong.
   - Hapus aturan `.image-present` (limbah) dari blok tombol.
2. **`profile/edit.blade.php`** — avatar dibungkus `<div class="profile-avatar-wrap">`: `.profile-avatar` (gambar + inisial) tetap di dalam, sedangkan tombol kamera/hapus & form tersembunyi dipindah **keluar** dari elemen yang overflow:hidden.
3. **`employees/show.blade.php`** — pola sama: wrapper baru untuk tombol kamera admin (di luar avatar yang overflow:hidden).

### Verifikasi
- `view:cache` bersih; `php -l` profile/edit & employees/show bersih.
- Render in-process kevin: markup sekarang `<div class="profile-avatar-wrap">` berisi `.profile-avatar` (gambar + `pa-inits`), diikuti tombol `.avatar-edit` di tingkat wrapper (bukan di dalam avatar) → tidak lagi terpotong.
- Tombol kamera kini terlihat jelas di pojok kanan-bawah avatar; tombol hapus (merah) muncul di pojok kiri-bawah bila `foto_path` ada.

## 2026-09-17 — Fitur BARU: Aset Pegawai (master + modul sub-data) (SELESAI)

Permintaan user: tambah master data "aset pegawai" (kendaraan, elektronik, dll) yang diberikan RS & dibawa pulang pegawai, HARUS tampil juga di sisi user (pegawai kelola sendiri).

### Implementasi
1. **Migrasi** `2026_09_17_000001_create_asset_tables.php` (2 tabel):
   - `asset_types` (master): `id, code nullable, name unique, category nullable, is_active bool, timestamps`. Category = enum bebas string: `kendaraan, elektronik, perabot, sarana, lainnya`.
   - `employee_assets` (sub-data): `id, employee_id FK cascade, asset_type_id FK nullOnDelete, nama_aset, merk, no_seri, no_inventaris, tanggal_terima date, kondisi, status, keterangan text, file_path, timestamps`.
2. **Model**: `AssetType` (relasi `employeeAssets()`), `EmployeeAsset` (relasi `employee()`, `assetType()`, cast `tanggal_terima` date). Relasi `Employee::assets()`.
3. **Master Data**: `MasterDataController::types()` + entry `'aset'` — label "Aset Pegawai", field `code/name/category(enum)/is_active`. Ada helper `asetKategori()` untuk badge kategori di tabel & display closure. `usageCount` utk `aset` = `EmployeeAsset::where('asset_type_id', $row->id)->count()`.
4. **Modul sub-data**: `SubDataController::config()` + entry `'aset'` (tab `aset`) — field: asset_type_id (select source **`asset-types`**), nama_aset required, merk, no_seri, no_inventaris, tanggal_terima, kondisi (Baik/Cukup Baik/Rusak Ringan/Rusak Berat), status (Dibawa Pulang/Disimpan di RS/Dikembalikan), keterangan, file_path (Kartu Inventaris/BAST). `optionsFor()` + cabang `asset-types` → `AssetType::where('is_active',true)->orderBy('name')->pluck('name','id')`.
5. **Tab "Aset Pegawai"** di `employees/show.blade.php` (setelah tab Keluarga, sebelum Riwayat Perubahan): section + grid kartu (nama aset, jenis, badge kondisi & status, merk/seri/inventaris/tanggal terima, keterangan, aksi Unduh/Edit/Hapus). Aksi tampil utk semua role login (mengikuti pola `$canManage`/ownership sub-data); tab tampil utk semua role.
6. **Tripsheet migrasi**: `php artisan migrate` (tanpa path) GAGAL di `2024_01_01_000001` (tabel dasar sudah ada tapi tak tercatat di tabel `migrations`) → wajib pakai `php artisan migrate --path=database/migrations/2026_09_17_000001_create_asset_tables.php` (cara sudah umum di proyek ini).
7. **Seed awal** (via script ad-hoc `seed_assets.php`, lalu dihapus): 9 jenis aset — Sepeda Motor, Mobil Dinas (kendaraan); Laptop, Handphone, Komputer/PC, Printer, Tablet (elektronik); Kursi, Meja (perabot). Token `AssetType::firstOrCreate(['name'=>…])`.
8. Total type master kini **14**: yang lama 13 + `aset`.

### Verifikasi (13 tiếng HTTP live, server `php artisan serve --port=8991`, cookie curl)
- `php -l` 6 file bersih; `view:cache` OK.
- SA NIP 1: `/master/aset` 200 (label "Aset Pegawai", baris "Sepeda Motor", badge Kategori "Kendaraan"/"Elektronik").
- SA: `/pegawai/{id}` punya `data-tab="aset"` + `id="tab-aset"` + tombol "Tambah Aset"; `/pegawai/1/sub/aset/create` 200 (field Jenis Aset/Kondisi/Status/Kartu Inventaris + opsi Sepeda Motor).
- POST store aset → 302 redirect; kartu aset render (nama, merk, INV, SN, badge "Kondisi: Baik"/"Dibawa Pulang").
- User NIP 3 (Rayn, employee id 1): `/pegawai/1` 200 punya tab Aset + tombol tambah; `/pegawai/1/sub/aset/create` 200 → aset bisa dikelola sendiri; menu `/master/*` TIDAK dirender utk user (benar).
- Catatan: NIP 3 = employee_id 1 pada data dev saat ini (checksum kolom `employees.user_id` tetap acuan otoritas kepemilikan).

### GOTCHAs
- View master memakai `columns_label` HANYA utk kolom bernama `type` → untuk `category` harus pakai `display` closure (sudah).
- `fn()` dalam config master tidak bisa jadi property, jadi helper `asetKategori()` dipakai di en/dua tempat (fields.options + display closure) — konsisten.
- Tab Aset diposisikan sebelum komentar/tab Riwayat Perubahan; jangan sampai merusak blok `$historyActor/$historyLabel` (sudah diverifikasi utuh).
- Form upload foto dipindah di luar header card (hidden); pastikan id `foto-profil-input` tetap ada atau tombol avatar-edit error.
- `.tab-btn` di CSS global punya `padding:12px 16px`; override scoped di profil memakai `padding:13px 16px 12px` + svg — hanya berlaku di halaman Profil Saya.
- `$completeness`, `$hint`, `$masaKerja`, `$golonganLabel`, `$genderLabel` dibangun di blok `@php` atas halaman; pastikan tidak dihapus saat mengubah markup header.

### Gelombang 2 (2026-09-17) — masa pakai + surat mutasi/pengembalian + akses user
Permintaan user: di sisi user belum ada tombol tambah aset; aset perlu beri tahu **dari tanggal dipakai sampai kapan / masih atau sudah tidak**; bila sudah tidak dipakai (mis. **mutasi**) ada **surat mutasi aset**, atau **dikembalikan ke RS** ada surat pengembalian — **surat bisa upload foto**.

- **Migration** `2026_09_17_000004_update_employee_assets_period_surat.php` (dijalankan `php artisan migrate --path=…`): `tanggal_terima`→`tanggal_mulai` (rename), + `tanggal_selesai` date nullable, + `surat_mutasi_path`, `surat_pengembalian_path` (nullable). Model `EmployeeAsset` cast `tanggal_mulai`/`tanggal_selesai` date.
- **Config aset** (`SubDataController`): kolom baru `tanggal_mulai` (required, "Mulai Dipakai dari Tanggal"), `tanggal_selesai` ("Sampai Tanggal", kosong = masih dipakai, hint via key `sub`), status diubah → **`Masih Dipakai` / `Mutasi Aset` (tidak dipakai lagi) / `Dikembalikan` (ke RS)**, + 2 file upload: `surat_mutasi_path` ("Foto Surat Mutasi Aset") & `surat_pengembalian_path` ("Foto Surat Pengembalian"); `file_path` tetap = Kartu Inventaris/BAST.
- **`download()`** kini dukung query `?field=<nama_field>` utk unduh file spesifik ketika satu record punya >1 file (BAST / surat mutasi / surat pengembalian). Tanpa field → fallback file pertama. Guard owner/role-utk-user tetap.
- **Tab Aset** (`employees/show.blade.php`): `@php $canViewAsetFile = $isAdmin || $employee->user_id === auth()->id(); $asetCanManage = $canViewAsetFile;` — tombol "+ Tambah Aset" & Edit/Hapus & link unduh surat/BAST HANYA tampil utk admin/SA & pemilik (user hanya lihat kartu orang lain tanpa aksi). Kartu: badge status baru (Masih Dipakai green / Mutasi amber / Dikembalikan gray), grid "Dipakai | Selesai" (`format('d M Y')`; Selesai → "Masih dipakai" bila status Masih Dipakai), link 📄 Foto Surat Mutasi / 📄 Foto Surat Pengembalian (merah-amber/ink) via `route('sub.download', ['aset', $id, 'field'=>…])`. `_section` createLabel dikirim **tanpa** "+" pendahulu (partial sudah menambah "+") — duplikasi "+" pernah muncul.
- **OTT/guard akses**: user role TIDAK bisa akses `/pegawai/{employee}` pegawai lain (403) — hanya data sendiri; jadi `$canViewAsetFile` benar.

### Gelombang 3 (2026-09-17) — Aset tampil di halaman "Profil Saya" (user)
Permintaan user: "di user nya belum ada di profil saya nya... belum ada disitu asset". Sebelumnya tab Aset hanya ada di `employees/show.blade.php`; halaman **Profil Saya** (`profile/edit.blade.php`) tidak menampilkan aset.

- **Refactor panel**: blok tab aset dipindah dari inline `employees/show.blade.php` ke partial baru **`resources/views/employees/_panel-aset.blade.php`** (variabel `$e` + `$admin`, pola sama seperti `_panel-keluarga`). `show.blade.php` kini `@include('employees._panel-aset', ['e' => $employee, 'admin' => $asetCanManage])` dengan `$asetCanManage = $isAdmin || $employee->user_id === auth()->id()`.
- **Profil Saya**: tambah tab button `Aset` (setelah Keluarga) + `@include('employees._panel-aset', ['e' => $emp, 'admin' => true])` — user (pemilik) kini bisa lihat & **tambah/edit/hapus aset langsung dari Profil Saya** (`sub.*` route tetap di-guard ownership oleh `SubDataController::authorizeAccess`).
- Partial render: badge status (Masih Dipakai green / Mutasi amber / Dikembalikan gray), rentang "Dipakai | Selesai", link unduh BAST + Surat Mutasi + Surat Pengembalian.
- Verifikasi (server :8993): login user Rayn → `/profil-saya` punya `data-tab="aset"` + tombol `+ Tambah Aset` menuju `/pegawai/1/sub/aset/create`; POST store aset (Handphone Samsung A54, Masih Dipakai, mulai `01 Jun 2023`) → 302 + kartu tampil di Profil Saya; SA melihat kartu juga. Data uji dihapus. `view:cache` OK.

### Gelombang 10 (2026-09-18) — Profil Saya "Hero Gedung RSKK" + koreksi kecil lain
Permintaan user: MASTER PROMPT re-design halaman **Profil Saya** — hero foto RSKK + identitas overlap + Ringkasan Identitas floating + tombol aksi berjarak + animasi elegan. **UI/Blade only** (controller/route/DB tidak disentuh). Catatan: model tidak bisa lihat screenshot → user beri modul teks lengkap.

**Temuan aset**: TIDAK ada file foto gedung RSKK terpisah. Satu-satunya asset RSKK besar = `public/assets/images/login-maskot.png` (1673×940, dipakai hero login). Konvensi: `Settings::get('login_photo_path')` + `Storage::disk('public')->exists()` → `Storage::url()`, fallback `asset('assets/images/login-maskot.png')` (sama seperti `AuthenticatedSessionController::create`). Dipakai sebagai hero (bukan gedung eksternal).

**Perubahan `resources/views/profile/edit.blade.php`** (blok CSS scoped + markup header):
- **`$profileHeroUrl`** dihitung di `@php` (settings-driven, fallback login-maskot).
- CSS lama `.pc-cover/.pc-body/.pc-idcard/.pc-comp` + media query duplikat **dibuang**; media query `@media (max-width:720px)` lama dihapus.
- **Hero card** (`.hero-card` radius 22px overflow hidden): `.hero-media` tinggi 300px (desktop) / 250 (721–1024) / 200 (≤720) berisi `<img class="hero-img">` (object-fit cover, **mask-image** linear-gradient fade ke putih di bawah supaya overlap halus, bg fallback gradient navy→blue) + `.hero-shade` (gelap tipis di atas biar pill terlihat) + `.hero-top` = pill **"PROFIL SAYA"** (glassmorphism semi-transparan + ikon) kiri & `hero-nip` "ID PEGAWAI · {nip}" kanan (pill, hidden ≤720).
- **`.hero-content`** (flex, `margin-top:-74px`, padding 0 40px 34px, gap 28px) = identitas yang **menimpa hero**:
  - Avatar **144px** (mobile 98px, `--val` ring putih 4px, shadow) + tombol kamera/hapus `.avatar-edit`/`.avatar-delete` **tetap berfungsi** (`foto-profil-input`, `profile.photo`, `profile.photo-delete`, `openPhotoViewer` tidak berubah).
  - `.hero-state`: nama 26px navy, badge **Aktif** hijau + badge role biru (hover `scale(1.02)`), jabatan `.hero-line` bold, unit kerja `.hero-line.sub` ber-icon briefcase, **progress kelengkapan** `.hero-progress` tinggi 11px gradient blue→cyan (`#38BDF8`) memakai `--val:{completeness}%` + `aria-valuenow` + hint tetap.
  - `.hero-summary` **Ringkasan Identitas** 352px mengambang kanan (radius 20, shadow, header ikon shield gradient, 4 baris NIP/Unit Kerja/Pangkat-Golongan/Masa Kerja + separator).
- **Animasi** (gated `prefers-reduced-motion`): page hero `heroIn` 600ms, `.hero-state` `heroLiftIn` translateY 14px delay 120ms, `.hero-summary` `heroFromRight` translateX 14px delay 220ms, progress `hpgrow` 0→`--val` 1.05s delay 400ms, avatar hover `scale(1.02)`; reduce → semua mati + progress `width:var(--val)!important`. **Tidak ada loop abadi** selain `barSweep` global pada progress.
- **Tombol aksi** page-head: wrapper diubah `flex items-center gap-2` → `.prof-actions` (`gap:12px`, flex-wrap) — Print/Kembali outline putih, Ajukan Perubahan primary biru, `onclick` tetap.
- **Responsive**: ≤1024 hero-content flex-wrap + summary `flex:1 1 100%` (turun di bawah); ≤720 hero 200px, avatar 98px, padding 20px, tanpa horizontal scroll. Aksesibilitas: `alt` hero, `aria-label` tombol kamera/hapus, `role="progressbar"`.

Verifikasi: `view:clear`+`view:cache` OK. Login user **Rayn** (nip 3 / user) → `/profil-saya` **200**; marker `hero-card`, `hero-media`, `hero-pill`, `hero-summary`, `hpg`, `login-maskot.png`, `prof-actions`, `role="progressbar"` semua ada; `pc-cover`/`pc-idcard` **= 0**; `setTab`/`foto-profil-input` (fungsional lama) tetap ada. Login **SA** (nip 1/superadmin123) → `/profil-saya` **200** empty-state "Akun belum terhubung ke data pegawai" (kondisi existing: SA tidak punya employees — bukan regresi). Server 127.0.0.1:8031 dihentikan.

### Koreksi kecil (2026-09-18, sebelum Gelombang 10) — belum tercatat di checkpoint atas
- **CSV Laporan**: `ReportController::csv` — separator `;` + BOM `EF BB BF` + baris `sep=;` + CRLF (kompat Excel/LibreOffice ID), gaji pokok angka murni (non-SA tetap `••••`). Terverifikasi byte mentah, semua jenis laporan.
- **Garis biru sidebar**: `.brand-title::after` (garis gradient 22×2px di bawah judul brand) **dihapus** dari `layouts/app.blade.php` atas permintaan user (polish Gelombang 5).
- **Login premium**: `.ch-div` dihapus (CSS+HTML); judul gradient shimmer (`textShine`), glow blob `.login-side::before`, tombol gradient `background-size:160%`, `shakeX` saat field invalid, sapaan dinamis. Verifikasi 200.
- **Avatar dashboard & sidebar**: memakai foto asli `asset('storage/'.$employee->foto_path)` bila ada, fallback ui-avatars + onerror → inisial (dashboard) / default-avatar (sidebar). Pegawai dengan foto: putu (E6), kevin mahendra (E9).

### Gelombang 9 (2026-09-18) — Dashboard (SA/Admin/User) premium: KPI glass-gradient + tindakan + sapaan dinamis
Permintaan user: tampilan dashboard super admin/admin/user kurang ganteng — dibuat lebih elegan (CSS/Blade only).

Perubahan:
- **CSS global** (`layouts/app.blade.php`):
  - `.stat-card.prem` — ikon tile **gradient (var --c1→--c2) putih**, glow sudut `::after` radial (opacity naik saat hover), radius 16px, angka `tabular-nums`.
  - `.min-act .act-ic` + `.min-act .chev` — kartu aksi dengan ikon gradient + chevron yang menyala/geser saat hover.
  - `.hrow` — baris daftar (mutasi/diklat/pengajuan) hover: bg biru pucat + geser 2px + border biru.
  - `@keyframes dotPulse` (.dot-live) — titik notifikasi belum dibaca berdenyut halus.
  - `.donut` (donutIn scale fade) & `.progress > div::after` (**barSweep**: sapuan cahaya di semua progress bar).
  - `prefers-reduced-motion` diperluas (donut, dot-live, sweep, hrow, act-ic, chev).
- **`dashboard.blade.php`**:
  - H1 ber-`<span id="greet">`; JS kecil mengganti "Selamat Datang," → "Selamat Pagi/Siang/Sore/Malam," sesuai jam (fallback tetap).
  - User: 3 kartu status → `prem` (amber/green/red gradient); dot notifikasi pakai `dot-live`; avatar ring biru lembut.
  - Admin/SA: 6 KPI → `prem` dengan pasangan warna gradient; 3 kartu "Perlu Tindakan" → `min-act` (ikon gradient + chevron + angka warna `--h1`); donut Status Kepegawaian animasi `donut`; baris Mutasi/Diklat/Pengajuan pending → `hrow`.
- Data/label tak berubah (Total Pegawai, Aktif, dll tetap sama).

Verifikasi: `view:cache` OK; smoke :8927 login SA (1) → admin dashboard penuh (prem×6, min-act×3, donut, hrow, chev, barSweep); login user (3) → user dashboard `prem` (--c1:#D97706), `dot-live`, `id="greet"`. Keduanya render 200.

### Gelombang 6 (2026-09-18) — Master UI/UX Redesign: Fase 1–6 (design system, shell, dashboard, tabel, notifikasi)
Permintaan user: MASTER MODULE redesign UI/UX enterprise (65 poin) — **visual only, tanpa menyentuh backend/business logic**. Karena aplikasi sudah dibangun di atas satu design system di `layouts/app.blade.php`, pekerjaan difokuskan pada penguatan global + penyelarasan halaman.

**Audit**: layout sudah punya token warna (RSKK blue/teal/slate), `.card`, `.btn*`, `.badge`, `.tbl`/`.table-th`/`.table-td`, `.input`, modal, toast, skeleton, pagination, timeline, flatpickr theme. Banyak halaman sudah memakai kelas sistem.

**Perubahan global `resources/views/layouts/app.blade.php`**:
- Background app `--paper` `#F5F7FA` → **`#F6F9FC`** (soft neutral blue-gray, modul §3).
- `.page-title` 24px → **28px**, letter-spacing `-.022em` (modul §4/§14); `.page-head` margin 24px.
- **Button**: hover `translateY(-1px)` + shadow lembut; active `scale(.98)`; `:focus-visible` ring biru untuk `.btn/.btn-*/.icon-btn/.navlink/.tab-btn/.chip`. Berlaku juga untuk tombol yang pakai modifier tanpa base `.btn` (banyak view lama).
- `.card` transisi halus; `.card-hover:hover` kini `translateY(-1px)`.
- **Page transition** `@keyframes pageIn` (opacity 0→1, translateY 4→0, 220ms) pada `.main-inner` (modul §39).
- **Stagger card entrance** utility `.stagger > *` (`@keyframes cardIn`, translateY 8px→0, 420ms, delay 40–290ms, max 6) via `backwards` fill agar tidak menabrak hover transform (modul §18).
- `prefers-reduced-motion`: semua entrance + brand + skeleton dimatikan (spinner tombol tetap jalan).

**Halaman**:
- `dashboard.blade.php`: kelas `stagger` pada grid KPI (6 kartu), grid statistik user, dan grid "Perlu Tindakan".
- `employees/index.blade.php`: `stagger` pada grid ringkasan (4 kartu).
- `approvals/index.blade.php`: `stagger` pada grid statistik.
- `notifications/index.blade.php`: **dikelompokkan Hari Ini / Kemarin / Sebelumnya** (filter pada `getCollection()`, hanya render grup berisi), item dengan dot unread, badge "Belum dibaca" biru konsisten, hover row, tombol "Tandai semua dibaca" kini `.btn btn-outline`. Form POST read tetap utuh.

Verifikasi (server sementara :8901/:8902/:8903): `view:clear`/`view:cache` OK. SA: 17 halaman (dashboard, pegawai, tambah, detail, edit, dokumen, upload, approval, akun, master, master create, laporan, audit, notifikasi, profil, password, pengaturan sistem) semua **200**; USER: dashboard, profil, dokumen, notifikasi, password **200**. Grup notifikasi ter-render bila ada isinya. Server dihentikan.

**Status modul**: Fase 1–6 inti selesai (global design system, app shell, sidebar, topbar, dashboard, sebagian tabel). Fase 7–13 (form/detail/approval card-style/dokumen/settings/responsive polish) sebagian besar sudah sesuai karena memakai sistem yang sama; penyempurnaan lanjutan bisa dilanjutkan per halaman bila diminta.

**Lanjutan Fase 6–13 (same day) — seluruh modul selesai**:
- **Dokumen (`documents/index`)**: filter status & kategori → `.chip`/`.chip.active` (sebelumnya inline navy), search pakai `.input`, tabel admin → `.table-th`/`.table-td` + `.table-wrap` + `min-w`, tombol aksi → `.btn btn-outline btn-sm` / `.btn btn-sm`, **modal tolak dirapikan ke sistem** (`.modal-backdrop` + `openModal('modal-reject')`), menghapus bug `style="..."` literal pada tombol Batal.
- **Dokumen create/edit, master form, accounts create, approvals/show, settings**: semua label → `.flabel` (+`.req`), input/select/textarea → `.input`, alert error → `.alert alert-danger` (dengan ikon + judul), tombol → `.btn btn-primary/outline/danger`.
- **`employees/_field.blade.php`** (dipakai create & edit): label/input disamakan ke `.flabel`/`.input` (styling error merah tetap via inline). Sisa `<select>` inline di `create`/`edit` ikut dikonversi via `replaceAll`.
- **Audit Log**: filter → `.input !w-auto`, tombol `.btn`, tabel → `.table-th/table-td` + `table-wrap`.
- **Manajemen Akun**: search → `.input`, tabel → sistem, hapus duplikat atribut `style` pada tombol salin password.
- **Approval index**: grid statistik `stagger` (sudah), tabel → sistem, tombol Review/Audit Log → `.btn`.
- **CSS global**: semua `input/select/textarea:not(.input):focus` dapat `box-shadow` ring biru lembut (konsisten dengan `.input`).

Verifikasi (server :8904/:8905): `view:clear`+`view:cache` OK; SA 21 rute termasuk create/edit/sub/master/dokumen modal → semua **200** (404 hanya `/dokumen/1/edit` karena record id 1 tidak ada); USER 6 halaman 200. Marker `.flabel`/`.input` terdeteksi pada upload & edit pegawai. Tidak ada aksi/route/backend diubah.

### Gelombang 5 (2026-09-18) — Premium Sidebar Branding (identitas aplikasi)
Permintaan user: upgrade blok brand sidebar (logo + "SIMPEG RSKK" + subjudul) jadi premium/enterprise tanpa mengubah menu, role, profil, atau logout.

**Temuan aset**: `public/assets/images/logo-rskk.png` adalah **lockup landscape 259×62** (emblem ~74px kiri + teks), bukan ikon persegi — tidak muat rapi dalam container persegi 44–50px. Setelah konfirmasi user, dipilih layout **plate logo utuh + judul/subjudul ditumpuk di bawah**.

**Perubahan `resources/views/layouts/app.blade.php`** (hanya blok brand; struktur menu/role/footer utuh):
- HTML: `.side-logo` → **`.brand-plate`** (berisi `<img alt="Logo RSKK">` + fallback `.ph` "R"), lalu `.brand-text` berisi `.brand-title` (`$appName`) + `.brand-sub` (`$appTagline`). Tidak ada lagi `truncate` pada judul/subjudul.
- CSS: `.side-head` kini kolom (padding 14/17/13, gap 9, `border-bottom` + gradient lembut); `.brand-plate` 42px, radius 12, gradient putih→biru muda, border halus, inset highlight, shadow; hover plate `translateY(-1px)` + shadow lebih kuat (teks tidak bergeser); `.brand-title` 16.5px w700 navy ls `-.3px` + aksen garis gradient 22×2px; `.brand-sub` 10.5px w500 abu-biru `#64748B`, line-height 1.35, `-webkit-line-clamp:2` (anti-terpotong "…").
- Collapsed: `.side.collapsed .side-head{padding:12px 8px}` + `.brand-text{display:none}` → hanya plate/logo di tengah.
- **Entrance animation** (initial load saja): keyframes `brandPlateIn` (opacity 0→1, scale .96→1) & `brandTextIn` (opacity 0→1, translateX -4px→0) 0.6s, di-gate class `html.brand-anim` via inline script `sessionStorage('rskk_brand_seen')`; dimatikan saat `prefers-reduced-motion`.
- Setting `app_tagline` di DB diubah dari "Sistem Informasi Kepegawaian" → **"Sistem Informasi Manajemen Kepegawaian"** (bisa diubah lagi dari Pengaturan Sistem).

Verifikasi (server sementara :8899, SA NIP 1/superadmin123): `view:clear`/`view:cache` OK; login → `/dashboard` **200**; HTML memuat `brand-plate`/`brand-title`/`brand-sub`, tagline penuh "Sistem Informasi Manajemen Kepegawaian", script `rskk_brand_seen`; elemen `class="side-logo"` lama **hilang**. Server dihentikan.

### Gelombang 7 (2026-09-18) — Polish animasi & penyelarasan sisa halaman (CSS-only)
Permintaan user: "gantengin lagi... yang belum rapih rapihin lagi... animasi... biar enak dilihat".

**Perubahan `resources/views/layouts/app.blade.php`** (murni CSS/animasi, tanpa ubah struktur):
- **Sidebar**: `.navlink` kini `transform:translateX(2px)` saat hover + transisi smooth; ikon `.navlink .ic` hover `scale(1.12)`; `.navlink:active` `scale(.98)`; `.nav-badge` hover `scale(1.1)`; scrollbar `.side-nav` di-styling tipis (6px, thumb `--line`, hover `#CBD5E1`).
- **`.icon-btn`** (topbar/profile): hover `translateY(-1px)`, active `scale(.94)`, svg hover `scale(1.1)`.
- **Tombol**: `.btn-primary`/`.btn-danger` diberi efek **sheen** (gradient putih `.26`, `skewX(-22deg)`, `left:-70% → 135%` saat hover, `.55s cubic-bezier(.22,1,.36,1)`) via `::after`; tambahan `overflow:hidden` + `position:relative`.
- **Stat card**: hover `translateY(-2px)` + border biru + shadow lembut; `.stat-ic` hover `scale(1.1) rotate(-4deg)`; `.stat-val` (angka KPI) sekarang 25px/bold/tight.
- **Modal**: `.modal-backdrop.open` → animasi `backdropIn` (fade .16s), `.modal` → `modalPop` (scale .96→1 + translateY, .22s cubic-bezier). Otomatis masuk semua modal (reject dokumen, hapus, dll.) yang pakai sistem `.modal`.
- **Reduced motion**: pemblokir `.stagger/.pageIn/brand/skeleton` diperluas → juga mematikan animasi `.modal`, hover-transition `.stat-card/.navlink/.icon-btn/.btn`.

**`resources/views/master/index.blade.php`** (selaras ke sistem):
- Tombol "+ Tambah" → `.btn btn-primary`.
- Tabel admin → wrapper `.table-wrap`, `<th>` → `.table-th`, `<td>` → `.table-td`, aksi Edit/Hapus → `.btn btn-outline btn-sm` / `.btn btn-danger btn-sm`.

Verifikasi (server :8912–:8915): `view:clear`+`view:cache` OK; login SA NIP 1/superadmin123 → 22 rute (dashboard, pegawai/list/tambah/detail/edit/sub-create, master units/jabatan/golongan/sub-unit + create, akun + create, dokumen + upload, approval + detail, audit, pengaturan sistem & password, profil-saya, notifikasi, laporan) semua **200**; marker `.btn btn-outline btn-sm` & `.btn btn-danger btn-sm` terdeteksi di `/master/units`. Login page tidak diutak-atik (sudah premium). Server dihentikan.

### Gelombang 8 (2026-09-18) — Jenis Master Data Bisa Ditambah Sendiri
Permintaan user: di Master Data sudah bisa menambah isi (unit kerja, jabatan, dll), tapi **tidak bisa menambah jenis master data-nya sendiri**. Solusi: jenis baru (mis. "Agama") kini bisa dibuat dari UI, lalu datanya dikelola persis seperti jenis bawaan.

**Tabel baru (migration `2026_09_18_000001_create_master_types_and_items_tables.php`)**:
- `master_types`: `key` (unique, auto-slug dari label), `label`, `description`, `sort` (urutan tampil, default 100), `is_active`, timestamps.
- `master_items`: `master_type_id` (FK, cascade delete), `code`, `name`, `description`, `urutan`, `is_active`, timestamps.

**Model**: `MasterType` (`items()` hasMany) & `MasterItem` (`masterType()` belongsTo) — pola konsisten `guarded=['id']` + casts boolean.

**Controller baru `MasterTypeController`** (rute `master/tipe*`, sebelum `/{type}` agar tidak tertelan; role SA/Admin):
- `index/create/store/edit/update/destroy`. `key` dibuat otomatis dari label via `Str::slug`; bila tabrakan dengan jenis bawaan/`tipe`/custom lain → `-2`, `-3`, dst. `destroy` ditolak bila `items()->exists()` (mirip proteksi hapus jenis bawaan).

**`MasterDataController`**:
- `types()` kini menggabungkan jenis bawaan + semua `MasterType::where('is_active',true)`. Custom type memakai schema generik: columns `Kode / Nama / Urutan / Keterangan / Status`, fields `code`(text), `name`(text,required), `urutan`(number), `description`(textarea), `is_active`(boolean), display badge Aktif/Nonaktif, order `name`, plus `master_type_id` pada conf.
- `index()`/`store()`/`findRow()` (baru, dipakai `edit/update/destroy`) menscope query ke `master_type_id` speciesif; `typeCounts` custom dihitung per type (bukan total tabel). `keys()` (public, dipakai MasterTypeController untuk reserved-slug).
- `master/form.blade.php` menambah cabang `textarea`.

**UI**: halaman `master/types/index.blade.php` (tabel Jenis: #, Nama, Kode, Keterangan, Jumlah Data terbagi, Status, aksi Kelola/Edit/Hapus + tombol "+ Tambah Jenis") & `master/types/form.blade.php` (label, keterangan, urutan). Sidebar kiri Master Data menambah tombol dashed "+ Tambah Jenis Master Data".

Verifikasi (server :8916–:8919): `view:create` Migration berhasil. Login SA → `master/tipe` & `master/tipe/create` 200 (empty-state tampil); POST `/master/tipe` (label Agama) → 302 & terdaftar; POST `/master/agama` (item Islam/ISL) → 302 & tampil di tabel (`>Islam<`, badge Aktif, Edit, Hapus); PUT `/master/agama/1` → 302. DB: `master_types`=agama, `master_items`=Islam(type_id=1) — lalu data uji **dihapus**. Pasca-cleanup: 9 rute smoke semua 200. Rute `master.types.*` terdaftar benar. Server & cookie dibersihkan.

**Catatan**: fitur ini **menambah** backend (migration + 2 model + 1 controller + rute baru) — berbeda dari gelombang UI murni sebelumnya, karena atas permintaan eksplisit user. Alur/route lama tidak diubah.

### Gelombang 4b (2026-09-17) — Tab Profil Saya disederhanakan (dropdown "Lainnya")
Permintaan user: baris fitur (Aset, Pendidikan, dll) terlalu panjang ke kanan; ingin tetap rapi, bagus, elegan.

**Solusi di `profile/edit.blade.php`**:
- Tab bar kini hanya **4 tab inti**: Ringkasan, Data Pribadi, Alamat & Kontak, Kepegawaian + tombol pill **"Lainnya ▾"** (`#dd-tabs-more`, class `tab-more`, menggunakan komponen `.dropdown` global) di ujung kanan (flex spacer `flex:1`).
- Dropdown berisi 7 menu ikon: Arsip & Dokumen, Pendidikan & Diklat, Kinerja & Penghargaan, Keluarga, **Aset Pegawai**, Riwayat Perubahan, Ajukan Perubahan (divider antar kelompok). Item aktif diberi kelas `.menu-item.active` (bg blue-50 + centang).
- **JS** `setTab()` kini juga: toggle `.active` di item dropdown, ganti label+ikon tombol "Lainnya" menjadi nama menu terpilih (mis. "Aset Pegawai"); `pickMore(el)` = `setTab` + tutup dropdown. Hash `#ajukan-perubahan` & tombol header tetap bekerja.
- **Kritikal**: card tab melepas `overflow-hidden` → jadi `card mb-6` (kalau tidak, dropdown terpotong). `.tab-btn` padding diubah `13px 14px 12px`.
- CSS scoped baru: `.tab-more`, `.tab-more:hover`, `.dropdown.open .tab-more`, `.menu-item.active`.

Verifikasi: `php -l` & `view:cache` OK; server :8995 login Rayn → 4 tab inti ada, `data-tab="aset"` kini di dalam `dd-tabs-more`, panel `tab-aset` tetap hadir, elemen `pickMore`/`toggleDropdown` render. Server & cookie dibersihkan.

### Gelombang 4c (2026-09-17) — Fix redirect "fitur lainnya" di Profil Saya
Laporan user: fitur-fitur lain (tambah ubah data, aset, dsb.) di Profil Saya "masih nge bug".

**Akar masalah**: `SubDataController::store/update/destroy` selalu me-redirect ke `employees.show`. Saat **user** menambah/mengubah/menghapus data lewat Profil Saya (`profile.edit`), setelah simpan ia "terlempar" ke halaman `/pegawai/{id}` bergaya admin — bukan kembali ke Profil Saya.

**Perbaikan`**:
- helper baru **`SubDataController::redirectAfterSave($conf, $employee, $message)`**: jika `request()->user()->role === 'user'` → `redirect()->route('profile.edit')` dengan flash `fragment` + `success`; selain itu (admin/SA) tetap `redirect()->route('employees.show', $employee)` seperti semula. Dipakai di `store()`, `update()`, `destroy()`.
- `profile/edit.blade.php`: render skrip pembuka tab dari flash — `@if(session('fragment'))` → `document.addEventListener('DOMContentLoaded', () => { setTab(<fragment>.replace(/^tab-/,'')); })` sehingga setelah simpan user otomatis kembali ke tab yang barusan dipakai (mis. `tab-aset`, `tab-keluarga`).

Verifikasi: `php -l` & `view:cache` OK. Server :8996:
- login user Rayn → POST `sub/bahasa` → **302 `/profil-saya`** (sebelumnya `/pegawai/1`); GET `/profil-saya` menampilkan `const f = 'tab-pendidikan-diklat';` + `DOMContentLoaded` (tab terbuka otomatis) + flash success.
- login SA → POST `sub/bahasa` → tetap **302 `/pegawai/1`** (perilaku admin tidak berubah).
- dropdown `Lainnya` berisi 7 menu (dokumen, pendidikan-diklat, kinerja-penghargaan, keluarga, aset, riwayat-perubahan, ajukan); `pickMore` menutup dropdown setelah pilih. Semua data uji dihapus; server & cookie dibersihkan.

### Gelombang 4 (2026-09-17) — Redesign "Profil Saya" agar lebih rapi & elegan
Permintaan user: tampilan profil saya harus sangat rapi, bagus, elegan, sesuai standar industri.

**Perubahan di `resources/views/profile/edit.blade.php`** (semua logika form/tab tetap, hanya tampilan di-raised):
- **CSS scoped top halaman**: `.pc-cover` (gradient navy→blue + dekorasi lingkaran + badge "Profil Saya" glassmorphism + NIP di kanan), `.pc-avatar` (88px ring putih + shadow 3px), `.pc-body` margin-top:-42px sehingga avatar "menimpa" cover (efek hero identity card), `.pc-idcard` (kartu gradient Ringkasan Identitas: NIP/Unit Kerja/Pangkat/Masa Kerja dengan ikon chip), `.pc-comp` (progress bar kelengkapan data dengan persen + hint), `.rk-card` (stat cards), `.rk-panel-head` (header panel bergambar ikon), `.tab-btn` kini inline-flex dengan ikon (svg 15px).
- **Header card**: cover 96px + identitas (nama 21px font-head, badge Aktif hijau dot, badge role biru, jabatan, unit) + Ringkasan Identitas kanan + bar Kelengkapan Data Profil.
- **Tab "Ringkasan"**: 4 stat cards (Status Pegawai / Masa Kerja / Unit Kerja / Jabatan) + 2 panel kv dengan header ikon bergaya "Informasi Pribadi" & "Informasi Kepegawaian".
- **Tab bar**: semua 11 tab diberi ikon svg (ringkasan, pribadi, alamat, dokumen, pendidikan, kepegawaian, kinerja, keluarga, aset, riwayat, ajukan).
- Form upload foto (`foto-profil-form` + `foto-profil-input`) tetap dipertahankan (dipindah di luar header card, hidden). Interaksi avatar-edit/delete tetap sama.

Verifikasi: `php -l` & `view:cache` OK; server :8994 login user Rayn → `/profil-saya` render penuh (rk-card, pc-cover, Ringkasan Identitas, tab aktif+svg, tab-aset ada). Server & cookie dibersihkan.

### Gelombang 3 (2026-09-17) — Aset tampil di halaman "Profil Saya" (user)
Permintaan user: "di user nya belum ada di profil saya nya... belum ada disitu asset". Sebelumnya tab Aset hanya ada di `employees/show.blade.php`; halaman **Profil Saya** (`profile/edit.blade.php`) tidak menampilkan aset.

- **Refactor panel**: blok tab aset dipindah dari inline `employees/show.blade.php` ke partial baru **`resources/views/employees/_panel-aset.blade.php`** (variabel `$e` + `$admin`, pola sama seperti `_panel-keluarga`). `show.blade.php` kini `@include('employees._panel-aset', ['e' => $employee, 'admin' => $asetCanManage])` dengan `$asetCanManage = $isAdmin || $employee->user_id === auth()->id()`.
- **Profil Saya**: tambah tab button `Aset` (setelah Keluarga) + `@include('employees._panel-aset', ['e' => $emp, 'admin' => true])` — user (pemilik) kini bisa lihat & **tambah/edit/hapus aset langsung dari Profil Saya** (`sub.*` route tetap di-guard ownership oleh `SubDataController::authorizeAccess`).
- Partial render: badge status (Masih Dipakai green / Mutasi amber / Dikembalikan gray), rentang "Dipakai | Selesai", link unduh BAST + Surat Mutasi + Surat Pengembalian.
- Verifikasi (server :8993): login user Rayn → `/profil-saya` punya `data-tab="aset"` + tombol `+ Tambah Aset` menuju `/pegawai/1/sub/aset/create`; POST store aset (Handphone Samsung A54, Masih Dipakai, mulai `01 Jun 2023`) → 302 + kartu tampil di Profil Saya; SA melihat kartu juga. Data uji dihapus. `view:cache` OK.