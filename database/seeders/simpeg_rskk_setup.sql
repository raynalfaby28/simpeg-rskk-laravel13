-- =========================================================
-- SIMPEG RSKK — Setup Database dari Nol
-- Cara pakai: HeidiSQL > pilih database simpeg_rskk > tab Query
-- > paste semua isi file ini > klik Execute (F9) atau tombol play
-- =========================================================

CREATE DATABASE IF NOT EXISTS simpeg_rskk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE simpeg_rskk;

SET FOREIGN_KEY_CHECKS = 0;

-- ============ MASTER DATA ============

CREATE TABLE IF NOT EXISTS work_units (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  code VARCHAR(255) NULL,
  parent_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (parent_id) REFERENCES work_units(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS positions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  type ENUM('struktural','fungsional','pelaksana') NOT NULL DEFAULT 'pelaksana',
  eselon VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ranks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  golongan VARCHAR(255) NOT NULL,
  pangkat VARCHAR(255) NULL,
  urutan TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_categories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS education_levels (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  urutan TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employment_statuses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

-- ============ USERS & AUTH ============

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nip VARCHAR(255) NOT NULL UNIQUE,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('super_admin','admin','user') NOT NULL DEFAULT 'user',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at TIMESTAMP NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
  nip VARCHAR(255) PRIMARY KEY,
  token VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sessions (
  id VARCHAR(255) PRIMARY KEY,
  user_id BIGINT UNSIGNED NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload LONGTEXT NOT NULL,
  last_activity INT NOT NULL,
  INDEX (user_id),
  INDEX (last_activity)
) ENGINE=InnoDB;

-- ============ EMPLOYEES ============

CREATE TABLE IF NOT EXISTS employees (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,

  nip VARCHAR(255) NOT NULL UNIQUE,
  nip_lama VARCHAR(255) NULL,
  nik VARCHAR(255) NULL UNIQUE,
  no_kk VARCHAR(255) NULL,
  nama_lengkap VARCHAR(255) NOT NULL,
  gelar_depan VARCHAR(255) NULL,
  gelar_belakang VARCHAR(255) NULL,
  nama_panggilan VARCHAR(255) NULL,
  tempat_lahir VARCHAR(255) NULL,
  tanggal_lahir DATE NULL,
  jenis_kelamin ENUM('L','P') NULL,
  agama ENUM('Islam','Kristen','Katolik','Hindu','Buddha','Khonghucu','Lainnya') NULL,
  status_perkawinan ENUM('Belum Kawin','Kawin','Cerai Hidup','Cerai Mati') NULL,
  golongan_darah VARCHAR(5) NULL,
  foto_path VARCHAR(255) NULL,

  no_npwp VARCHAR(255) NULL,
  no_bpjs VARCHAR(255) NULL,
  no_karpeg VARCHAR(255) NULL,
  no_karis_karsu VARCHAR(255) NULL,
  no_rekening VARCHAR(255) NULL,
  bank VARCHAR(255) NULL,
  no_taspen VARCHAR(255) NULL,
  bapertarum ENUM('Sudah Diambil','Belum Diambil','Tidak Ada') NULL,

  employee_category_id BIGINT UNSIGNED NULL,
  employment_status_id BIGINT UNSIGNED NULL,
  status_pegawai ENUM('PNS','PPPK','Honorer','Kontrak','Lainnya') NULL,
  jenis_asn ENUM('PNS','PPPK') NULL,
  status_calon VARCHAR(255) NULL,
  jenis_pns VARCHAR(255) NULL,
  kedudukan_pegawai VARCHAR(255) NULL,
  mekanisme_mutasi VARCHAR(255) NULL,
  kepemilikan_kpe TINYINT(1) NOT NULL DEFAULT 0,

  pendidikan_awal_id BIGINT UNSIGNED NULL,
  tahun_pendidikan_awal YEAR NULL,
  pendidikan_akhir_id BIGINT UNSIGNED NULL,
  tahun_pendidikan_akhir YEAR NULL,
  izin_pemakaian_gelar TINYINT(1) NOT NULL DEFAULT 0,

  jenis_jabatan ENUM('struktural','fungsional','pelaksana') NULL,
  eselon VARCHAR(255) NULL,
  tmt_eselon DATE NULL,
  current_position_id BIGINT UNSIGNED NULL,
  tmt_jabatan DATE NULL,
  tugas_tambahan_1 VARCHAR(255) NULL,
  tmt_tugas_tambahan_1 DATE NULL,
  tugas_tambahan_2 VARCHAR(255) NULL,
  tmt_tugas_tambahan_2 DATE NULL,
  work_unit_id BIGINT UNSIGNED NULL,
  tmt_skpd DATE NULL,
  instansi_dipekerjakan VARCHAR(255) NULL,

  golongan_awal_id BIGINT UNSIGNED NULL,
  tmt_golongan_awal DATE NULL,
  golongan_akhir_id BIGINT UNSIGNED NULL,
  tmt_golongan_akhir DATE NULL,
  masa_kerja_tahun TINYINT UNSIGNED NOT NULL DEFAULT 0,
  masa_kerja_bulan TINYINT UNSIGNED NOT NULL DEFAULT 0,
  gaji_pokok DECIMAL(12,2) NULL,
  tmt_gaji_berkala_terbaru DATE NULL,

  alamat_rumah TEXT NULL,
  rt_rumah VARCHAR(5) NULL,
  rw_rumah VARCHAR(5) NULL,
  kelurahan_rumah VARCHAR(255) NULL,
  kecamatan_rumah VARCHAR(255) NULL,
  kabkota_rumah VARCHAR(255) NULL,
  provinsi_rumah VARCHAR(255) NULL,
  kodepos_rumah VARCHAR(10) NULL,

  alamat_domisili_ktp TEXT NULL,
  rt_domisili VARCHAR(5) NULL,
  rw_domisili VARCHAR(5) NULL,
  kelurahan_domisili VARCHAR(255) NULL,
  kecamatan_domisili VARCHAR(255) NULL,
  kabkota_domisili VARCHAR(255) NULL,
  provinsi_domisili VARCHAR(255) NULL,
  kodepos_domisili VARCHAR(10) NULL,

  telp VARCHAR(255) NULL,
  hp VARCHAR(255) NULL,
  email_pribadi VARCHAR(255) NULL,
  email_resmi VARCHAR(255) NULL,

  data_updated_at TIMESTAMP NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,

  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (employee_category_id) REFERENCES employee_categories(id) ON DELETE SET NULL,
  FOREIGN KEY (employment_status_id) REFERENCES employment_statuses(id) ON DELETE SET NULL,
  FOREIGN KEY (pendidikan_awal_id) REFERENCES education_levels(id) ON DELETE SET NULL,
  FOREIGN KEY (pendidikan_akhir_id) REFERENCES education_levels(id) ON DELETE SET NULL,
  FOREIGN KEY (current_position_id) REFERENCES positions(id) ON DELETE SET NULL,
  FOREIGN KEY (work_unit_id) REFERENCES work_units(id) ON DELETE SET NULL,
  FOREIGN KEY (golongan_awal_id) REFERENCES ranks(id) ON DELETE SET NULL,
  FOREIGN KEY (golongan_akhir_id) REFERENCES ranks(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============ RIWAYAT ============

CREATE TABLE IF NOT EXISTS employee_educations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  education_level_id BIGINT UNSIGNED NOT NULL,
  institution VARCHAR(255) NOT NULL,
  faculty VARCHAR(255) NULL,
  major VARCHAR(255) NULL,
  no_ijazah VARCHAR(255) NULL,
  tahun_masuk YEAR NULL,
  tahun_lulus YEAR NULL,
  status VARCHAR(255) NULL,
  file_ijazah_path VARCHAR(255) NULL,
  file_transkrip_path VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (education_level_id) REFERENCES education_levels(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_position_histories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  position_id BIGINT UNSIGNED NOT NULL,
  work_unit_id BIGINT UNSIGNED NULL,
  no_sk VARCHAR(255) NULL,
  tanggal_sk DATE NULL,
  tmt DATE NULL,
  tanggal_selesai DATE NULL,
  alasan_perubahan TEXT NULL,
  file_sk_path VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (position_id) REFERENCES positions(id),
  FOREIGN KEY (work_unit_id) REFERENCES work_units(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_rank_histories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  rank_id BIGINT UNSIGNED NOT NULL,
  no_sk VARCHAR(255) NULL,
  tanggal_sk DATE NULL,
  tmt DATE NULL,
  masa_kerja_tahun TINYINT UNSIGNED NOT NULL DEFAULT 0,
  masa_kerja_bulan TINYINT UNSIGNED NOT NULL DEFAULT 0,
  file_sk_path VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (rank_id) REFERENCES ranks(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_salary_histories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  gaji_pokok DECIMAL(12,2) NOT NULL,
  no_sk VARCHAR(255) NULL,
  tanggal_sk DATE NULL,
  tmt DATE NULL,
  masa_kerja_tahun TINYINT UNSIGNED NOT NULL DEFAULT 0,
  rank_id BIGINT UNSIGNED NULL,
  file_sk_path VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (rank_id) REFERENCES ranks(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_mutations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  jenis_mutasi VARCHAR(255) NOT NULL,
  unit_asal_id BIGINT UNSIGNED NULL,
  unit_tujuan_id BIGINT UNSIGNED NULL,
  jabatan_lama VARCHAR(255) NULL,
  jabatan_baru VARCHAR(255) NULL,
  tanggal_mutasi DATE NULL,
  no_sk VARCHAR(255) NULL,
  alasan TEXT NULL,
  keterangan TEXT NULL,
  file_sk_path VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_asal_id) REFERENCES work_units(id) ON DELETE SET NULL,
  FOREIGN KEY (unit_tujuan_id) REFERENCES work_units(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_trainings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  nama_pelatihan VARCHAR(255) NOT NULL,
  penyelenggara VARCHAR(255) NULL,
  jenis_pelatihan VARCHAR(255) NULL,
  tempat VARCHAR(255) NULL,
  tanggal_mulai DATE NULL,
  tanggal_selesai DATE NULL,
  no_sertifikat VARCHAR(255) NULL,
  masa_berlaku DATE NULL,
  file_sertifikat_path VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_awards (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  nama_penghargaan VARCHAR(255) NOT NULL,
  jenis_penghargaan VARCHAR(255) NULL,
  pemberi_penghargaan VARCHAR(255) NULL,
  tingkat VARCHAR(255) NULL,
  tahun YEAR NULL,
  no_penghargaan VARCHAR(255) NULL,
  file_path VARCHAR(255) NULL,
  keterangan TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_disciplines (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  jenis_pelanggaran VARCHAR(255) NOT NULL,
  tanggal DATE NULL,
  tingkat_pelanggaran VARCHAR(255) NULL,
  sanksi VARCHAR(255) NULL,
  no_keputusan VARCHAR(255) NULL,
  keterangan TEXT NULL,
  file_path VARCHAR(255) NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS employee_families (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  type ENUM('pasangan','anak','orang_tua') NOT NULL,
  nama VARCHAR(255) NOT NULL,
  nik VARCHAR(255) NULL,
  tempat_lahir VARCHAR(255) NULL,
  tanggal_lahir DATE NULL,
  jenis_kelamin ENUM('L','P') NULL,
  pekerjaan VARCHAR(255) NULL,
  status VARCHAR(255) NULL,
  status_tanggungan TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS documents (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  jenis_dokumen VARCHAR(255) NOT NULL,
  no_dokumen VARCHAR(255) NULL,
  tanggal DATE NULL,
  file_path VARCHAR(255) NOT NULL,
  status_verifikasi ENUM('belum_diverifikasi','terverifikasi','ditolak') NOT NULL DEFAULT 'belum_diverifikasi',
  keterangan TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============ WORKFLOW: PENGAJUAN & APPROVAL ============

CREATE TABLE IF NOT EXISTS change_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_id BIGINT UNSIGNED NOT NULL,
  module_type VARCHAR(255) NOT NULL,
  old_data JSON NULL,
  new_data JSON NOT NULL,
  status ENUM('pending','reviewed','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL,
  approved_by BIGINT UNSIGNED NULL,
  approved_at TIMESTAMP NULL,
  rejection_reason TEXT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- SEED DATA — akun Super Admin pertama + master data dasar
-- =========================================================

-- NIP     : 000000000000000001
-- Password: superadmin123
INSERT INTO users (nip, name, email, password, role, is_active, created_at, updated_at)
VALUES (
  '000000000000000001',
  'Super Admin RSKK',
  'superadmin@rskk.go.id',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'super_admin',
  1,
  NOW(),
  NOW()
);

INSERT INTO work_units (name, code, created_at, updated_at) VALUES
  ('Subbagian Tata Usaha', 'TU', NOW(), NOW()),
  ('Bidang Pelayanan Medis', 'YANMED', NOW(), NOW()),
  ('Bidang Keperawatan', 'KEP', NOW(), NOW());

INSERT INTO employee_categories (name, created_at, updated_at) VALUES
  ('PNS', NOW(), NOW()),
  ('PPPK', NOW(), NOW()),
  ('Kontrak', NOW(), NOW());

INSERT INTO employment_statuses (name, created_at, updated_at) VALUES
  ('Aktif', NOW(), NOW()),
  ('Cuti', NOW(), NOW()),
  ('Nonaktif', NOW(), NOW()),
  ('Pensiun', NOW(), NOW());

INSERT INTO education_levels (name, urutan, created_at, updated_at) VALUES
  ('SMA/SMK', 3, NOW(), NOW()),
  ('D3', 4, NOW(), NOW()),
  ('S1', 6, NOW(), NOW()),
  ('S2', 7, NOW(), NOW());

-- SELESAI. Cek hasilnya:
-- SELECT * FROM users;

