# BPJS Tracking

Sistem manajemen progress BPJS untuk pegawai dan administrator yang dibuat dengan HTML, CSS, JavaScript, PHP, dan MySQL.

## Fitur Utama

### Portal Admin

- Login sistem dengan autentikasi
- Dashboard dengan data pegawai dan anggota keluarga
- Filter status progress (Semua, Diproses, Selesai, Gagal)
- Edit progress dengan 6 tahapan:
  1. Berkas Masuk
  2. Verifikasi Berkas
  3. Persetujuan
  4. Bagian Keuangan
  5. DPP untuk BPJS
  6. BPJS berhasil diaktifkan
- Input alasan untuk status gagal
- Progress tracking visual

### Portal Pegawai

- Pencarian berdasarkan NIK (16 digit)
- Tampilan progress timeline
- Detail status setiap tahapan
- Ringkasan progress dengan persentase
- Interface responsif

## Teknologi yang Digunakan

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 8.1
- **Database**: MySQL 8.0
- **Icons**: Font Awesome 6
- **Responsive Design**: Bootstrap Grid System

## Struktur Proyek

```
bpjs_tracking/
├── admin/                  # Portal admin
│   ├── index.php          # Dashboard admin
│   ├── login.php          # Halaman login
│   ├── logout.php         # Logout handler
│   └── edit.php           # Edit progress
├── pegawai/               # Portal pegawai
│   ├── index.php          # Pencarian NIK
│   └── lihat_progress.php # Tampilan progress
├── includes/              # File konfigurasi
│   ├── config.php         # Konfigurasi database
│   ├── db_connect.php     # Koneksi database
│   ├── functions.php      # Fungsi utility
│   ├── header.php         # Header template
│   └── footer.php         # Footer template
├── assets/                # Asset statis
│   ├── css/
│   │   └── style.css      # CSS custom
│   └── js/
│       └── script.js      # JavaScript custom
├── sql/                   # Database schema
│   └── database_schema.sql
├── index.php              # Halaman utama
├── setup_database.php     # Setup database
└── README.md              # Dokumentasi
```

## Instalasi dan Setup

### Persyaratan Sistem

- PHP 8.1 atau lebih baru
- MySQL 8.0 atau lebih baru
- Web server (Apache/Nginx) atau PHP built-in server

### Langkah Instalasi

1. **Clone atau download proyek**

   ```bash
   # Jika menggunakan Git
   git clone [repository-url]
   cd bpjs_tracking
   ```

2. **Setup Database**

   - Pastikan MySQL server berjalan
   - Buka browser dan akses: `http://localhost/bpjs_tracking/setup_database.php`
   - Atau jalankan SQL script secara manual:

   ```bash
   mysql -u root -p < sql/database_schema.sql
   ```

3. **Konfigurasi Database**

   - Edit file `includes/config.php` jika diperlukan
   - Sesuaikan kredensial database:

   ```php
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', '');
   define('DB_NAME', 'bpjs_tracking');
   ```

4. **Jalankan Aplikasi**

   **Menggunakan PHP Built-in Server:**

   ```bash
   cd bpjs_tracking
   php -S localhost:8000
   ```

   **Menggunakan Apache/Nginx:**

   - Copy folder ke document root (htdocs/www)
   - Akses melalui browser: `http://localhost/bpjs_tracking`

## Penggunaan

### Login Admin

- URL: `/admin/login.php`
- Username: `admin`
- Password: `admin123`

### Portal Pegawai

- URL: `/pegawai/index.php`
- Masukkan NIK 16 digit untuk mencari data
- NIK demo: `1234567890123456` (Pegawai: Kirana)

### Data Demo

Sistem sudah dilengkapi dengan data demo:

- **Pegawai**: Kirana (NIK: 1234567890123456)
- **Anggota Keluarga**:
  - Deviani (NIK: 1111111111111111)
  - Arimbi (NIK: 2222222222222222)
  - Andana (NIK: 3333333333333333)

## Fitur Keamanan

- **SQL Injection Protection**: Menggunakan prepared statements
- **XSS Protection**: Input sanitization
- **CSRF Protection**: Token validation
- **Session Security**: Secure session handling
- **Password Hashing**: Untuk production environment

## Customization

### Menambah Pegawai Baru

```sql
INSERT INTO pegawai (nama, nik) VALUES ('Nama Pegawai', 'NIK16DIGIT');
```

### Menambah Anggota Keluarga

```sql
INSERT INTO anggota_keluarga (pegawai_id, nama, nik)
VALUES (1, 'Nama Anggota', 'NIK16DIGIT');
```

### Mengubah Tahapan Progress

Edit tabel `tahapan_progress` untuk menambah/mengubah tahapan.

## Troubleshooting

### Database Connection Error

- Pastikan MySQL server berjalan
- Periksa kredensial di `includes/config.php`
- Pastikan database sudah dibuat

### Permission Error

- Pastikan folder memiliki permission yang tepat
- Untuk Linux/Mac: `chmod 755 bpjs_tracking`

### PHP Error

- Pastikan PHP version 8.1+
- Enable extension: mysqli, pdo_mysql
- Periksa error log PHP

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Lisensi

Proyek ini dibuat untuk keperluan demonstrasi dan pembelajaran.

## Kontak

Untuk pertanyaan atau dukungan, silakan hubungi developer.

---

**Catatan**: Sistem ini sudah diuji dan siap untuk digunakan. Untuk production environment, pastikan untuk mengubah password default dan mengaktifkan HTTPS.
