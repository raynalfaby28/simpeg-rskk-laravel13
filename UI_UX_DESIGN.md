# 🎨 MODUL UI/UX SIMPEG RSKK — PEDOMAN DESAIN

> **Status:** Pedoman desain resmi seluruh halaman SIMPEG RSKK (dari Login sampai Profil Pegawai).
> Berlaku sebagai standar visual sebelum/menyesuaikan implementasi. Diputuskan untuk **didokumentasikan dulu** (belum diterapkan ke kode).

---

## Sistem Informasi Kepegawaian RSUD Kesehatan Kerja

### Konsep utama
**Modern Hospital Enterprise + Government Administration + Clean Professional**

> Bukan desain yang terlalu ramai, bukan juga dashboard template yang kelihatan biasa.

---

## 1. Identitas Visual

Karakter desain: Profesional, Resmi, Modern, Bersih, Terpercaya, Elegan, Mudah digunakan, tidak terlalu banyak dekorasi.

Referensi visual — pertahankan karakter halaman login RSKK (tetapi halaman internal dibuat jauh lebih modern):

```text
        RSKK
   Sistem Informasi
     Kepegawaian

     ┌─────────────┐
     │   Username  │
     └─────────────┘
     ┌─────────────┐
     │   Password  │
     └─────────────┘

        [ LOGIN ]
```

---

## 2. Color System

Warna utama mengambil inspirasi dari identitas RSKK.

**Primary — Biru profesional:** Button utama, link, sidebar active, icon utama, heading tertentu, grafik.

**Secondary — Cyan/Teal lembut:** Accent, icon, informasi, highlight.

**Background:**

```text
Background utama → #F6F8FB
Card             → #FFFFFF
Border           → #E5EAF0
Text utama       → #172033
Text sekunder    → #6B7280
```

**Status:** 🟢 Success · 🟡 Warning/Pending · 🔴 Danger/Rejected · 🔵 Information · ⚪ Draft

> Catatan: warna tidak boleh mencolok. Semua terlihat seperti aplikasi rumah sakit profesional.

---

## 3. Typography

Font utama: **Inter** atau **Poppins**.

```text
Dashboard   32px / Bold
Data Pegawai 24px / Semi Bold
Section     18px / Semi Bold
Label       14px / Medium
Isi data    14–15px / Regular
Caption     12–13px / Regular
```

Jangan menggunakan terlalu banyak jenis font.

---

## 4. Layout Utama

Semua halaman setelah login:

```text
┌──────────────────────────────────────────────────────────────┐
│ TOPBAR                                                       │
│ Logo / SIMPEG                    🔔   User Profile           │
├───────────────┬──────────────────────────────────────────────┤
│               │                                              │
│ SIDEBAR       │              CONTENT AREA                    │
│               │                                              │
│ Dashboard     │                                              │
│ Pegawai       │                                              │
│ Profil        │                                              │
│ Dokumen       │                                              │
│ Pendidikan    │                                              │
│ Kepegawaian   │                                              │
│ Kinerja       │                                              │
│ Keluarga      │                                              │
│               │                                              │
│ Approval      │                                              │
│ Master Data   │                                              │
│ Laporan       │                                              │
│               │                                              │
└───────────────┴──────────────────────────────────────────────┘
```

---

## 5. Sidebar

Clean dan tidak terlalu lebar.

**Header sidebar:**

```text
┌──────────────────────┐
│ [ LOGO RSKK ]        │
│ SIMPEG RSKK          │
│ Sistem Kepegawaian   │
└──────────────────────┘
```

Logo RSKK nanti tinggal dimasukkan sebagai asset — desain tidak bergantung pada logo sekarang.

**Menu:**

```text
🏠 Dashboard

KEPEGAWAIAN
👥 Data Pegawai
🟢 Pegawai Aktif
⚪ Pegawai Nonaktif

PROFIL
👤 Data Pribadi
📁 Dokumen
🎓 Pendidikan & Diklat
💼 Kepegawaian
🏆 Kinerja & Penghargaan
👨👩👧 Keluarga

ADMINISTRASI
✅ Approval
⚙️ Master Data
📊 Laporan
📝 Audit Log
```

Menu menyesuaikan otomatis berdasarkan role.

---

## 6. Sidebar Super Admin

```text
Dashboard

Data Pegawai
Manajemen User

Profil
Dokumen
Pendidikan
Kepegawaian
Kinerja
Keluarga

Approval

Master Data
├─ Unit Kerja
├─ Jabatan
├─ Pangkat
├─ Golongan
└─ Kategori Pegawai

Laporan

Audit Log

Pengaturan
```

---

## 7. Sidebar Admin

```text
Dashboard

Data Pegawai
├─ Pegawai Aktif
└─ Pegawai Nonaktif

Profil
Dokumen
Pendidikan
Kepegawaian
Kinerja
Keluarga

Approval

Master Data

Laporan
```

---

## 8. Sidebar User

```text
Dashboard

Profil Saya
├─ Data Pribadi
├─ Dokumen
├─ Pendidikan & Diklat
├─ Kepegawaian
├─ Kinerja & Penghargaan
└─ Keluarga

Pengajuan Saya

Notifikasi

Pengaturan Akun
```

---

## 9. Topbar

```text
┌──────────────────────────────────────────────────────────┐
│ ☰   Dashboard                              🔔   👤 Rayn ▼ │
└──────────────────────────────────────────────────────────┘
```

Menu profil:

```text
┌──────────────────────┐
│ 👤 R.M. Rayn         │
│ Super Admin          │
├──────────────────────┤
│ Profil Saya          │
│ Pengaturan           │
│ Ubah Password        │
│                      │
│ 🚪 Keluar            │
└──────────────────────┘
```

---

## 10. Dashboard

Halaman paling informatif tetapi tetap clean.

**Header:** "Selamat Datang, Super Admin 👋 — Kelola dan pantau informasi kepegawaian RSUD Kesehatan Kerja."

**Statistic cards** (tanpa gradient berlebihan):

```text
┌───────────────┐ ┌───────────────┐ ┌───────────────┐ ┌───────────────┐
│ 👥            │ │ 🟢            │ │ 🟡            │ │ 📄            │
│ Total Pegawai │ │ Pegawai Aktif │ │ Approval      │ │ Dokumen       │
│ 356           │ │ 340           │ │ 12            │ │ 1,284         │
└───────────────┘ └───────────────┘ └───────────────┘ └───────────────┘
```

---

## 11. Dashboard Chart

Statistik Pegawai — grafik sederhana:
- Pegawai per unit
- Pegawai per golongan
- Pegawai per jabatan
- Pendidikan
- Status pegawai

---

## 12. Approval Card di Dashboard

Kartu "Pengajuan Menunggu Approval" + link "Lihat Semua →"; tiap baris: nama pengaju, jenis perubahan, badge 🟡 Pending.

---

## 13. Halaman Data Pegawai

Header + tombol "+ Tambah Pegawai", search (Cari NIP, nama, jabatan...), filter: Unit Kerja / Jabatan / Golongan / Status.

---

## 14. Tabel Pegawai

Kolom: Foto · NIP · Nama · Jabatan · Unit · Status (🟢 Aktif) · Aksi (`⋮`).
Aksi: Lihat, Edit, Dokumen, Riwayat, Nonaktifkan.

---

## 15. Profile Header Pegawai (premium)

```text
┌──────────────────────────────┐
│ [ FOTO ]  NAMA LENGKAP       │
│           NIP                │
│           Jabatan • Unit     │
│           🟢 AKTIF           │
│           [Edit Data][Pengajuan] │
└──────────────────────────────┘
```

Foto menggunakan lingkaran / rounded-square profesional.

---

## 16. Data Pribadi

Jangan memanjang ke bawah — pakai **card two-column**:

```text
┌──────────────────────────┬────────────────────┐
│ NIP                      │ NIP Lama           │
│ 198xxxxxxxx              │ 198xxxxxxxx        │
├──────────────────────────┼────────────────────┤
│ Nama Lengkap             │ Jenis Kelamin      │
├──────────────────────────┼────────────────────┤
│ Tempat Lahir             │ Tanggal Lahir      │
└──────────────────────────┴────────────────────┘
```

Card lain: Informasi Pribadi · Alamat & Kontak · Identitas Kependudukan · Informasi Kepegawaian.

---

## 17. Mode Edit

Form modern (label di atas input) + tombol [Batal][Simpan].
Ketika disimpan: **"Perubahan akan diajukan untuk approval Super Admin."**

---

## 18. Approval UI

Tampilan **Data Sebelum vs Data Sesudah** (perubahan diberi highlight ringan), 📎 dokumen pendukung [Lihat Dokumen], identitas pengaju, tanggal pengajuan, tombol [Tolak][✓ Setujui].

---

## 19. Dokumen Digital

Document cards (bukan daftar file):

```text
┌──────────────┐
│ 📄           │
│ SK Pengangkatan│
│ SK-123/2026  │
│ 🟢 Terverifikasi │
│ [Lihat][⋮]   │
└──────────────┘
```

Grid 3 kolom.

---

## 20. Pendidikan

**Timeline** dari terbaru ke lama (tahun → titik → jenjang + instansi).

---

## 21. Riwayat Karier

**Career timeline** (tahun ───● jabatan, RSKK, TMT).

---

## 22. Pangkat & Golongan

Timeline golongan + card "Golongan Sekarang / TMT / Masa Kerja".

---

## 23. Gaji

Data sensitif: ditampilkan tertutup `Rp •••••••••` + tombol [Tampilkan]. Akses sesuai permission.

---

## 24. Keluarga

Card per anggota: Pasangan, lalu grid Anak 1/2/3.

---

## 25. Profil Completeness

Di dashboard user: progress bar persentase + checklist (Data Pribadi, Alamat, Kepegawaian, Pendidikan, Dokumen, Keluarga, Sertifikasi) + link "Lengkapi Data →".

---

## 26. Notification Center

Dropdown klik 🔔: daftar notifikasi dengan badge kategori (🟢 disetujui / 🟡 diproses / 🔴 perlu diperbaiki) + waktu relatif.

---

## 27. Status Badge

Badge kecil: 🟢 Aktif · ⚪ Nonaktif · 🟡 Pending · 🟢 Approved · 🔴 Rejected · 🔵 Diproses. Tidak terlalu besar.

---

## 28. Responsive

- Desktop: Sidebar + Content
- Tablet: sidebar collapse
- Mobile: hamburger + content full width
- Tabel di mobile menjadi card/list

---

## 29. Slot Foto & Asset

Semua foto placeholder, tinggal diganti:

```text
Logo            → /public/assets/images/logo-rskk.png
Foto profil     → /storage/pegawai/profile/
Default avatar  → /public/assets/images/default-avatar.png
Dokumen         → /storage/dokumen/
```

---

## 30. Login Screen

Jangan diubah terlalu jauh dari tampilan yang sudah ada. Background **abu-abu sangat muda**, card login putih + shadow tipis. Tidak perlu background foto rumah sakit yang ramai.

```text
                     [ LOGO RSKK ]
               Sistem Informasi Kepegawaian
          ┌──────────────────────────┐
          │ NIP / Username            │
          └──────────────────────────┘
          ┌──────────────────────────┐
          │ Password             👁  │
          └──────────────────────────┘
                 □ Ingat saya
          ┌──────────────────────────┐
          │          LOGIN           │
          └──────────────────────────┘
                 © 2026 RSKK
```

---

## 31. Komponen (Reusable)

Button · Input · Select · Datepicker · Search · Filter · Card · Stat Card · Table · Badge · Modal · Alert · Toast · Dropdown · Tabs · Timeline · File Card · Profile Header · Approval Panel · Pagination · Breadcrumb · Empty State.

---

## 32. Micro Interaction

Animasi halus: button hover, card hover, sidebar transition, dropdown fade, modal fade, tab transition, loading skeleton.

Dilarang: animasi berlebihan, background bergerak, neon, glassmorphism berlebihan, gradient di setiap card.

---

## 33. Empty State

Jangan tampilkan tabel kosong — tampilkan ikon + judul + deskripsi + [CTA] (mis. "Belum Ada Dokumen... [+ Tambah Dokumen]").

---

## 34. Loading State

Gunakan skeleton (blok `▓▓▓`), jangan halaman putih.

---

## 35. Confirmation

Tindakan penting (mis. Nonaktifkan Pegawai) wajib confirmation modal: judul + penjelasan + [Batal][Ya, Nonaktifkan].

---

## 36. Halaman Detail Pegawai — Desain Final

```text
← Data Pegawai

┌──────────────────────────────────────────┐
│ [FOTO] NAMA · NIP · Jabatan • Unit · 🟢 Aktif │
│                       [Edit][Pengajuan]  │
└──────────────────────────────────────────┘

[Ringkasan][Data Pribadi][Dokumen][Pendidikan][Kepegawaian][Kinerja][Keluarga]

┌───────────────┐  ┌────────────────────┐
│ IDENTITAS     │  │ STATUS KEPEGAWAIAN │
│ NIP / Nama    │  │ Status/Jabatan/    │
│ TTL / Gender  │  │ Golongan/Unit      │
└───────────────┘  └────────────────────┘

┌──────────────────────────────────────────┐
│ RIWAYAT KARIER (timeline)                │
│ 2026 ● Kepala Unit · 2024 ● Staff Senior │
└──────────────────────────────────────────┘
```

Desain ini menjadi **visual utama SIMPEG RSKK**.

---

## 37. Master Prompt UI (untuk pembuatan tampilan)

> **Buat desain UI/UX lengkap untuk website Sistem Informasi Kepegawaian RSUD Kesehatan Kerja (SIMPEG RSKK) menggunakan Laravel 13 Blade.**
>
> Buat desain dengan gaya **modern, elegant, clean, professional hospital enterprise dan government administration system**.
>
> Pertahankan karakter visual halaman login RSKK yang menggunakan background abu-abu sangat muda, area putih, aksen biru profesional, dan tampilan sederhana. Jangan mengubah identitas visual RSKK secara drastis.
>
> Gunakan font Inter atau Poppins, whitespace yang cukup, typography yang jelas, border tipis, rounded corners sedang, shadow lembut, icon sederhana dan konsisten.
>
> Jangan menggunakan glassmorphism berlebihan, neon, gradient berlebihan, animasi berlebihan, atau desain dashboard template yang terlalu ramai.
>
> Buat layout utama menggunakan sidebar kiri collapsible, topbar, notification center, profile dropdown, breadcrumb dan content area yang luas.
>
> Buat tiga role interface: Super Admin, Admin dan User. Sidebar dan fitur harus otomatis menyesuaikan permission masing-masing role.
>
> Buat halaman Dashboard, Data Pegawai, Detail Pegawai, Data Pribadi, Dokumen Digital, Pendidikan & Diklat, Kepegawaian, Jabatan, Pangkat & Golongan, Gaji, Mutasi, Kinerja, Penghargaan, Keluarga, Approval, Master Data, Laporan dan Audit Log.
>
> Halaman detail pegawai harus memiliki profile header dengan foto pegawai sebagai placeholder yang nantinya dapat diganti dengan foto asli, NIP, nama, jabatan, unit kerja, status aktif dan action button.
>
> Gunakan tab navigation untuk mengelompokkan informasi pegawai agar data yang sangat banyak tidak terlihat seperti tabel panjang.
>
> Gunakan card layout, two-column information layout, timeline untuk riwayat karier, document card untuk dokumen digital, statistic card untuk dashboard, table modern untuk daftar pegawai dan comparison panel untuk approval.
>
> Sistem approval harus memiliki tampilan **Data Sebelum vs Data Sesudah**, dokumen pendukung, identitas pengaju, tanggal pengajuan, status dan tombol Approve/Reject.
>
> Gunakan status badge Pending, Approved, Rejected, Active, Inactive dan Draft.
>
> Buat profile completeness indicator untuk menunjukkan persentase kelengkapan data pegawai.
>
> Semua foto, logo RSKK dan asset visual lainnya harus menggunakan placeholder/asset path sehingga nantinya mudah diganti tanpa mengubah layout.
>
> Desain harus responsive untuk desktop, tablet dan mobile.
>
> Gunakan reusable components seperti Sidebar, Navbar, Card, Table, Modal, Badge, Tabs, Timeline, FileCard, ProfileHeader, ApprovalPanel, Notification, Form dan Breadcrumb.
>
> **Prioritaskan usability, accessibility, readability dan konsistensi visual.**
>
> Hasil akhir harus terlihat seperti aplikasi SIMPEG rumah sakit profesional yang benar-benar siap digunakan, bukan sekadar template dashboard.

---

## Alur standar tampilan

**LOGIN** (tetap seperti RSKK) → **DASHBOARD** (modern & informatif) → **DATA PEGAWAI** (tabel clean + filter) → **DETAIL PEGAWAI** (profile header + tabs) → **DATA PRIBADI** (grouped cards) → **RIWAYAT** (timeline) → **DOKUMEN** (document cards) → **EDIT** (form modern) → **APPROVAL** (before/after) → **LAPORAN** (clean tables + charts).

Logo RSKK, foto pegawai, foto gedung, favicon, dan asset lain sebagai **slot yang bisa diganti kapan saja** — tidak perlu menunggu foto/logo untuk menyusun desain.