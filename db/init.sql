-- =====================================================
-- SKEMA DATABASE BILLING RT/RW NET (UPDATED)
-- =====================================================

-- 1. USER APLIKASI (Admin & Teknisi)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  nama VARCHAR(100) NOT NULL,
  role ENUM('admin','teknisi') NOT NULL DEFAULT 'teknisi',
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. PAKET INTERNET
CREATE TABLE paket (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_paket VARCHAR(100) NOT NULL,
  kecepatan VARCHAR(50) NOT NULL,
  harga_bulanan DECIMAL(12,2) NOT NULL DEFAULT 0,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. PELANGGAN (inti sistem: MAC, PPPoE, koordinat, desa)
CREATE TABLE pelanggan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_pelanggan VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  alamat TEXT,
  desa VARCHAR(100),
  kecamatan VARCHAR(100),
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  mac_address VARCHAR(17),
  username_pppoe VARCHAR(50),
  password_pppoe VARCHAR(50),
  paket_id INT,
  status ENUM('aktif','isolir','berhenti') NOT NULL DEFAULT 'aktif',
  tgl_pasang DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (paket_id) REFERENCES paket(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_mac (mac_address),
  INDEX idx_desa (desa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. PSB (Pemasangan Baru) - UPDATED
CREATE TABLE psb (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  alamat TEXT,
  desa VARCHAR(100),
  kecamatan VARCHAR(100),
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  catatan TEXT,
  paket_id INT NULL,
  foto_rumah VARCHAR(255) NULL,
  status ENUM('baru','survey','menunggu_bayar','dikerjakan','selesai','ditolak') NOT NULL DEFAULT 'baru',
  teknisi_id INT,
  jadwal_survey DATETIME,
  hasil_survey TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (paket_id) REFERENCES paket(id) ON DELETE SET NULL,
  FOREIGN KEY (teknisi_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TAGIHAN BULANAN
CREATE TABLE tagihan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id INT NOT NULL,
  periode CHAR(7) NOT NULL COMMENT 'format YYYY-MM',
  nominal DECIMAL(12,2) NOT NULL,
  denda DECIMAL(12,2) NOT NULL DEFAULT 0,
  total DECIMAL(12,2) NOT NULL,
  tgl_jatuh_tempo DATE NOT NULL,
  status ENUM('belum_bayar','menunggu_verifikasi','lunas') NOT NULL DEFAULT 'belum_bayar',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_periode (pelanggan_id, periode),
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE CASCADE,
  INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. PEMBAYARAN (Gateway + Upload Bukti)
CREATE TABLE pembayaran (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tagihan_id INT NOT NULL,
  metode ENUM('gateway','upload') NOT NULL,
  nama_pengirim VARCHAR(100),
  nominal DECIMAL(12,2) NOT NULL,
  tgl_bayar DATETIME,
  bukti_bayar VARCHAR(255),
  gateway_ref VARCHAR(100),
  status ENUM('menunggu_verifikasi','perlu_diperiksa','lunas','ditolak') NOT NULL DEFAULT 'menunggu_verifikasi',
  verified_by INT,
  catatan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tagihan_id) REFERENCES tagihan(id) ON DELETE CASCADE,
  FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. LOG WHATSAPP (anti spam reminder)
CREATE TABLE log_wa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id INT,
  jenis ENUM('reminder','isolir','lunas') NOT NULL,
  nomor VARCHAR(20) NOT NULL,
  pesan TEXT NOT NULL,
  status ENUM('terkirim','gagal','tertunda') NOT NULL DEFAULT 'tertunda',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. TITIK INFRASTRUKTUR (ODP, ODC, Tiang)
CREATE TABLE titik_infrastruktur (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  jenis ENUM('odp','odc','tiang','pusat') NOT NULL DEFAULT 'odp',
  desa VARCHAR(100),
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  catatan TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. GANGGUAN / TIKET
CREATE TABLE gangguan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pelanggan_id INT,
  pelapor VARCHAR(100),
  keluhan TEXT NOT NULL,
  status ENUM('baru','dikerjakan','selesai') NOT NULL DEFAULT 'baru',
  teknisi_id INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE SET NULL,
  FOREIGN KEY (teknisi_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. LOG AKTIVITAS USER
CREATE TABLE log_aktivitas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  aktivitas VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. PENGATURAN APLIKASI - UPDATED
CREATE TABLE pengaturan (
  nama_usaha VARCHAR(150) NOT NULL DEFAULT 'RT/RW Net',
  alamat TEXT,
  no_wa VARCHAR(20),
  jatuh_tempo_tanggal TINYINT NOT NULL DEFAULT 5,
  denda DECIMAL(12,2) NOT NULL DEFAULT 0,
  wa_reminder_hari VARCHAR(20) NOT NULL DEFAULT '3,1,0',
  wa_jam_kirim TIME NOT NULL DEFAULT '08:00',
  api_key_wa VARCHAR(255),
  deskripsi_psb TEXT NULL,
  syarat_ketentuan TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Data awal
INSERT INTO pengaturan (nama_usaha, deskripsi_psb, syarat_ketentuan) VALUES 
('RT/RW Net', 'Isi form di bawah untuk mendaftar layanan internet RT/RW Net', 'Dengan mendaftar, Anda setuju untuk mematuhi semua aturan yang berlaku.');

INSERT INTO paket (nama_paket, kecepatan, harga_bulanan) VALUES
('Paket Hemat', '10 Mbps', 75000),
('Paket Keluarga', '20 Mbps', 100000),
('Paket Sultan', '30 Mbps', 150000);