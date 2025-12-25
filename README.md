# SANIPOINT - IoT Bathroom Monitoring System

Sistem monitoring kebersihan kamar mandi minimarket berbasis IoT dengan reward poin untuk karyawan.

## 🚀 Fitur Utama

### Admin Dashboard
- **Real-time Monitoring**: Status kamar mandi, sensor IoT, aktivitas karyawan
- **Manajemen Karyawan**: CRUD karyawan dengan sistem poin
- **Manajemen Kamar Mandi**: Pengaturan lokasi dan batas pengunjung
- **Manajemen Produk**: Marketplace untuk penukaran poin
- **Laporan Lengkap**: Analitik performa dan distribusi poin

### Karyawan Dashboard
- **E-wallet Style**: Saldo poin dengan riwayat transaksi
- **Marketplace**: Tukar poin dengan produk
- **Transfer Poin**: Kirim poin ke karyawan lain
- **QR Code**: Download QR untuk klaim produk

### IoT Integration
- **Sensor MQ-135**: Monitoring kualitas udara
- **Sensor IR**: Penghitung pengunjung otomatis
- **RFID Reader**: Tap untuk mulai/selesai pembersihan
- **Servo Motor**: Pengunci pintu otomatis
- **LED & Buzzer**: Indikator status

## 🛠️ Teknologi

- **Backend**: PHP Native (MVC Pattern)
- **Database**: MySQL
- **Frontend**: TailwindCSS + Vanilla JavaScript
- **Security**: CSRF Protection, XSS Prevention, SQL Injection Protection
- **Real-time**: AJAX Polling (5 detik)
- **Routing**: Clean URLs tanpa .php

## 📋 Persyaratan Sistem

- PHP 7.4+
- MySQL 5.7+
- XAMPP/WAMP/LAMP
- Node.js (untuk build CSS)

## 🚀 Instalasi

### 1. Clone/Download Project
```bash
# Letakkan di folder htdocs XAMPP
C:\xampp\htdocs\sanipoint\
```

### 2. Setup Database
```sql
-- Import file database/schema.sql ke MySQL
-- Atau jalankan query di phpMyAdmin
```

### 3. Konfigurasi Environment
```bash
# Edit file .env sesuai konfigurasi database Anda
DB_HOST=localhost
DB_NAME=sanipoint_db
DB_USER=root
DB_PASS=
```

### 4. Build CSS (Opsional)
```bash
# Install dependencies
npm install

# Build CSS
npm run build
```

### 5. Akses Aplikasi
```
http://localhost/sanipoint
```

## 👤 Default Login

### Admin
- **Username**: `admin`
- **Password**: `password`

### Demo Karyawan
Buat karyawan baru melalui admin dashboard.

## 🏗️ Struktur Project

```
sanipoint/
├── config/           # Konfigurasi aplikasi
├── core/            # Core classes (Router, Database, Auth, Security)
├── controllers/     # Controllers (Admin, Karyawan, API)
├── models/          # Models (Database operations)
├── views/           # Views (HTML templates)
├── assets/          # CSS, JS, Images
├── database/        # Database schema
├── .env            # Environment variables
├── .htaccess       # URL rewriting
└── index.php       # Front controller
```

## 🔧 API Endpoints

### IoT Sensor Data
```http
POST /api/sensor-data
Content-Type: application/x-www-form-urlencoded

sensor_code=MQ135_001&value=350&unit=ppm
```

### RFID Tap
```http
POST /api/rfid-tap
Content-Type: application/x-www-form-urlencoded

rfid_code=EMP001&bathroom_id=1
```

### Real-time Status
```http
GET /api/realtime-status
```

## 🎯 Alur Kerja Sistem

### 1. Monitoring Otomatis
- Sensor IR menghitung pengunjung
- Jika mencapai batas → Status "Perlu Dibersihkan"
- Servo menutup pintu, LED menyala, buzzer aktif

### 2. Proses Pembersihan
- Karyawan tap RFID pertama → "Sedang Dibersihkan"
- Sistem catat waktu mulai
- Karyawan tap RFID kedua → "Selesai"
- Sistem hitung durasi, beri poin, reset counter

### 3. Reward System
- Poin otomatis setelah pembersihan
- Bisa ditukar produk di marketplace
- Bisa ditransfer ke karyawan lain
- QR code untuk klaim di kasir

## 🔒 Keamanan

- **CSRF Protection**: Token validasi setiap form
- **XSS Prevention**: Input sanitization
- **SQL Injection**: Prepared statements
- **Session Security**: HTTP-only cookies
- **Role-based Access**: Admin vs Karyawan
- **Input Validation**: Frontend + Backend

## 📱 Responsive Design

- **Mobile First**: Optimized untuk smartphone
- **Tablet Friendly**: Layout adaptif
- **Desktop**: Full dashboard experience
- **Dark Mode**: Support sistem preference

## 🚀 Deployment

### Production Setup
1. Upload ke web server
2. Setup database production
3. Update .env dengan kredensial production
4. Set proper file permissions
5. Enable HTTPS
6. Setup backup otomatis

### IoT Hardware Setup
1. Hubungkan sensor ke microcontroller
2. Konfigurasi WiFi untuk HTTP requests
3. Set endpoint API sesuai domain
4. Test koneksi sensor

## 🤝 Kontribusi

1. Fork repository
2. Buat feature branch
3. Commit changes
4. Push ke branch
5. Create Pull Request

## 📄 Lisensi

MIT License - Bebas digunakan untuk komersial dan non-komersial.

## 📞 Support

Untuk pertanyaan teknis atau bug report, silakan buat issue di repository ini.

---

**SANIPOINT** - Menjaga kebersihan dengan teknologi IoT dan gamifikasi! 🚽✨