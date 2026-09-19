import sys, io, os
p = "C:/laragon/www/simpeg-rskk-laravel13/PROJECT_NOTES.md"
add = """

## Access Control FINAL - Manajemen Akun & profil pegawai (2026-09-14, "Adm & Akun")

Permintaan user (bulk spec "FIX ACCESS CONTROL" + penekanan terakhir: "Manajemen Akun itu mah KHUSUS Super Admin, Admin mah tidak ada berhak").

- **Keputusan role akun (HARUS diingat selamanya)**:
  - `super_admin` = satu-satunya yang boleh manajemen akun + setting sistem + player role. **``` Admin ma = HANYA data pegawai ```**.
  - `admin` = lihat/list/edit operasional data pegawai, approval lihat, Akun TIDAK (guard super_admin-only).
  - `user` = hanya Profil Saya / Dokumen Saya / Notifikasi.
- **Backend dikunci finite (bukan cuma sembunyi menu)**:
  - routes/web.php Line:107: `Route::middleware('role:super_admin')` untuk seluruh `admin.accounts.*` (sebelumnya `super_admin,admin`).
  - Sidebar layouts/app.blade.php:438 Manajemen Akun DOORN gated `@if(auth()->user()->role==='super_admin')`.
  - AccountController: `authorize('index')` guard super_admin; store guard self-promote/non-super; destroy cegah hapus self + role super_admin (Abort 403).
  - Approval routes + EmployeePolicy: `view` admin/super_admin full, user block.
"""

with open(p, "a", newline="") as f:
    f.write(add)
print("appended OK, len now:", len(open(p).read()))
