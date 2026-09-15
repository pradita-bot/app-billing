-- Tambah kolom baru di tabel psb
ALTER TABLE psb 
ADD COLUMN IF NOT EXISTS paket_id INT NULL AFTER catatan,
ADD COLUMN IF NOT EXISTS latitude DECIMAL(10,7) NULL AFTER paket_id,
ADD COLUMN IF NOT EXISTS longitude DECIMAL(10,7) NULL AFTER latitude,
ADD COLUMN IF NOT EXISTS foto_rumah VARCHAR(255) NULL AFTER longitude,
ADD FOREIGN KEY (paket_id) REFERENCES paket(id) ON DELETE SET NULL;

-- Tambah kolom di tabel pengaturan untuk syarat & ketentuan
ALTER TABLE pengaturan
ADD COLUMN IF NOT EXISTS syarat_ketentuan TEXT NULL AFTER api_key_wa,
ADD COLUMN IF NOT EXISTS deskripsi_psb TEXT NULL AFTER syarat_ketentuan;