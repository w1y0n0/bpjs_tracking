-- Database Schema untuk BPJS Tracking
-- Created for Employee Progress Management System

CREATE DATABASE IF NOT EXISTS bpjs_tracking;
USE bpjs_tracking;

-- Tabel untuk data pegawai
CREATE TABLE pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nik VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk data anggota keluarga
CREATE TABLE anggota_keluarga (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    nik VARCHAR(20) UNIQUE NOT NULL,
    hubungan VARCHAR(50) DEFAULT 'Anggota Keluarga',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
);

-- Tabel untuk master tahapan progress
CREATE TABLE tahapan_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_tahapan VARCHAR(100) NOT NULL,
    urutan INT NOT NULL,
    deskripsi TEXT
);

-- Tabel untuk tracking progress setiap pegawai
CREATE TABLE progress_pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    tahapan_id INT NOT NULL,
    status ENUM('pending', 'berhasil', 'gagal') DEFAULT 'pending',
    alasan_gagal TEXT NULL,
    tanggal_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE,
    FOREIGN KEY (tahapan_id) REFERENCES tahapan_progress(id) ON DELETE CASCADE,
    UNIQUE KEY unique_pegawai_tahapan (pegawai_id, tahapan_id)
);

-- Tabel untuk user admin (untuk login)
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data master tahapan progress
INSERT INTO tahapan_progress (nama_tahapan, urutan, deskripsi) VALUES
('Berkas Masuk', 1, 'Tahap penerimaan berkas dari pegawai'),
('Verifikasi Berkas', 2, 'Tahap verifikasi kelengkapan dan kebenaran berkas'),
('Persetujuan', 3, 'Tahap persetujuan dari pihak berwenang'),
('Bagian Keuangan', 4, 'Tahap pemrosesan di bagian keuangan'),
('DPP untuk BPJS', 5, 'Tahap pengajuan DPP untuk BPJS'),
('BPJS berhasil diaktifkan', 6, 'Tahap akhir aktivasi BPJS');

-- Insert sample admin user (password: admin123)
INSERT INTO admin_users (username, password, nama) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator');

-- Insert sample data pegawai
INSERT INTO pegawai (nama, nik) VALUES
('Kirana', '1234567890123456');

-- Insert sample data anggota keluarga
INSERT INTO anggota_keluarga (pegawai_id, nama, nik) VALUES
(1, 'Deviani', '1111111111111111'),
(1, 'Arimbi', '2222222222222222'),
(1, 'Andana', '3333333333333333');

-- Insert sample progress data untuk pegawai Kirana
INSERT INTO progress_pegawai (pegawai_id, tahapan_id, status) VALUES
(1, 1, 'berhasil'),
(1, 2, 'berhasil'),
(1, 3, 'pending'),
(1, 4, 'pending'),
(1, 5, 'pending'),
(1, 6, 'pending');

