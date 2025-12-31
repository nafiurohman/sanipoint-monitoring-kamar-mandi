# SANIPOINT - Dokumentasi Lengkap

## 📋 Daftar Isi
1. [Gambaran Umum](#gambaran-umum)
2. [Fitur Utama](#fitur-utama)
3. [Teknologi](#teknologi)
4. [Struktur Project](#struktur-project)
5. [Instalasi](#instalasi)
6. [Konfigurasi](#konfigurasi)
7. [Routing System](#routing-system)
8. [Database Schema](#database-schema)
9. [API Endpoints](#api-endpoints)
10. [UI/UX Design](#uiux-design)
11. [Security Features](#security-features)
12. [Deployment](#deployment)
13. [Troubleshooting](#troubleshooting)

---

## 🎯 Gambaran Umum

**SANIPOINT** adalah sistem monitoring kebersihan kamar mandi minimarket berbasis IoT dengan reward poin untuk karyawan. Sistem ini menggabungkan teknologi sensor IoT, gamifikasi, dan manajemen data untuk menciptakan lingkungan yang lebih bersih dan sehat.

### Tujuan Sistem
- Monitoring real-time kebersihan kamar mandi
- Otomasi sistem reward untuk karyawan
- Peningkatan motivasi melalui gamifikasi
- Analitik dan laporan komprehensif
- Manajemen efisien operasional kebersihan

---

## 🚀 Fitur Utama

### Admin Dashboard
- **Real-time Monitoring**: Status kamar mandi, sensor IoT, aktivitas karyawan
- **Manajemen Karyawan**: CRUD karyawan dengan sistem poin
- **Manajemen Kamar Mandi**: Pengaturan lokasi dan batas pengunjung
- **Manajemen Produk**: Marketplace untuk penukaran poin
- **Sensor Management**: Konfigurasi dan monitoring sensor IoT
- **Laporan Lengkap**: Analitik performa dan distribusi poin
- **Transaksi**: Monitoring semua transaksi poin

### Karyawan Dashboard
- **E-wallet Style**: Saldo poin dengan riwayat transaksi
- **Marketplace**: Tukar poin dengan produk
- **Transfer Poin**: Kirim poin ke karyawan lain
- **Monitoring**: Lihat status kamar mandi real-time
- **Riwayat**: Track semua aktivitas dan transaksi
- **Pengaturan**: Kelola profil dan preferensi

### IoT Integration
- **Sensor MQ-135**: Monitoring kualitas udara (PPM)
- **Sensor IR**: Penghitung pengunjung otomatis
- **RFID Reader**: Tap untuk mulai/selesai pembersihan
- **Servo Motor**: Pengunci pintu otomatis
- **LED & Buzzer**: Indikator status visual dan audio

---

## 🛠️ Teknologi

### Backend
- **PHP 7.4+**: Native PHP dengan arsitektur MVC
- **MySQL 5.7+**: Database relasional
- **PDO**: Database abstraction layer
- **Session Management**: Secure session handling

### Frontend
- **TailwindCSS**: Utility-first CSS framework
- **Vanilla JavaScript**: Modern ES6+ features
- **Font Awesome 6**: Icon library
- **Inter Font**: Modern typography

### Security
- **CSRF Protection**: Token-based validation
- **XSS Prevention**: Input sanitization
- **SQL Injection**: Prepared statements
- **Password Hashing**: Secure bcrypt hashing
- **Session Security**: HTTP-only cookies

### IoT Hardware
- **Arduino/ESP32**: Microcontroller
- **MQ-135**: Air quality sensor
- **IR Sensor**: Motion detection
- **RFID RC522**: Card reader
- **Servo SG90**: Door lock mechanism
- **LED & Buzzer**: Status indicators

---

## 📁 Struktur Project

```
sanipoint/
├── assets/                 # Static assets
│   ├── css/
│   │   ├── input.css      # Tailwind input
│   │   └── style.css      # Compiled CSS
│   ├── js/
│   │   ├── main.js        # Main application logic
│   │   ├── notifications.js # Toast & confirmation system
│   │   ├── admin-crud.js  # Admin CRUD operations
│   │   ├── simple.js      # Simple form handling
│   │   └── theme.js       # Theme management
│   └── images/            # Image assets
├── config/
│   └── config.php         # Application configuration
├── controllers/           # MVC Controllers
│   ├── AdminController.php
│   ├── KaryawanController.php
│   ├── AuthController.php
│   ├── ApiController.php
│   └── SettingsController.php
├── core/                  # Core classes
│   ├── Router.php         # URL routing
│   ├── Database.php       # Database connection
│   ├── Auth.php          # Authentication
│   ├── Security.php      # Security utilities
│   └── PDFReportGenerator.php
├── models/               # Data models
│   ├── UserModel.php
│   ├── BathroomModel.php
│   ├── SensorModel.php
│   ├── ProductModel.php
│   ├── OrderModel.php
│   └── PointModel.php
├── views/                # View templates
│   ├── layouts/          # Layout templates
│   │   ├── main.php      # Main layout
│   │   ├── sidebar.php   # Navigation sidebar
│   │   └── header.php    # Page header
│   ├── auth/
│   │   └── login.php     # Login page
│   ├── admin/            # Admin views
│   ├── karyawan/         # Employee views
│   └── settings/         # Settings views
├── database/             # Database files
│   ├── schema.sql        # Database schema
│   └── migrations/       # Database migrations
├── logs/                 # Application logs
├── vendor/               # Composer dependencies
├── .htaccess            # URL rewriting
├── index.php            # Front controller
└── README.md            # Project documentation
```

---

## 🔧 Instalasi

### Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Composer (opsional)
- Node.js (untuk build CSS)

### Langkah Instalasi

1. **Clone/Download Project**
```bash
git clone https://github.com/username/sanipoint.git
cd sanipoint
```

2. **Setup Database**
```sql
-- Buat database baru
CREATE DATABASE sanipoint_db;

-- Import schema
mysql -u root -p sanipoint_db < database/schema.sql
```

3. **Konfigurasi Environment**
```php
// Edit config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sanipoint_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

4. **Setup Web Server**
```apache
# Apache Virtual Host
<VirtualHost *:80>
    DocumentRoot /path/to/sanipoint
    ServerName sanipoint.local
    
    <Directory /path/to/sanipoint>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

5. **Build CSS (Opsional)**
```bash
npm install
npm run build
```

---

## ⚙️ Konfigurasi

### Database Configuration
```php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sanipoint_db');
define('DB_USER', 'username');
define('DB_PASS', 'password');
```

### Application Settings
```php
define('APP_KEY', 'your-secret-key');
define('APP_URL', 'http://your-domain.com');
define('TIMEZONE', 'Asia/Jakarta');
```

### Security Settings
```php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Production: 0, Development: 1
```

---

## 🛣️ Routing System

### URL Structure
```
/                           # Login page
/login                      # Login form
/logout                     # Logout action

# Admin Routes
/admin/dashboard            # Admin dashboard
/admin/karyawan            # Employee management
/admin/kamar-mandi         # Bathroom management
/admin/produk              # Product management
/admin/sensor              # Sensor management
/admin/transaksi           # Transaction history
/admin/laporan             # Reports
/admin/pengaturan          # Admin settings

# Employee Routes
/karyawan/dashboard        # Employee dashboard
/karyawan/poin             # Points balance
/karyawan/marketplace      # Product marketplace
/karyawan/transfer         # Point transfer
/karyawan/riwayat          # Transaction history
/karyawan/monitoring       # Real-time monitoring
/karyawan/pengaturan       # Employee settings

# API Routes
/api/sensor-data           # IoT sensor data endpoint
/api/rfid-tap             # RFID tap endpoint
/api/realtime-status      # Real-time status
```

### Router Implementation
```php
// core/Router.php
class Router {
    private $routes = [];
    
    public function add($route, $handler) {
        $this->routes[$route] = $handler;
    }
    
    public function dispatch($uri) {
        // Dynamic path handling
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '');
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Route matching and execution
        if (isset($this->routes[$uri])) {
            $this->callHandler($this->routes[$uri]);
        } else {
            $this->notFound();
        }
    }
}
```

---

## 🗄️ Database Schema

### Core Tables

#### users
```sql
CREATE TABLE users (
    id VARCHAR(36) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    role ENUM('admin', 'employee') DEFAULT 'employee',
    employee_code VARCHAR(20) UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### bathrooms
```sql
CREATE TABLE bathrooms (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200) NOT NULL,
    max_visitors INT DEFAULT 5,
    current_visitors INT DEFAULT 0,
    status ENUM('available', 'needs_cleaning', 'being_cleaned', 'maintenance') DEFAULT 'available',
    last_cleaned TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### sensors
```sql
CREATE TABLE sensors (
    id VARCHAR(36) PRIMARY KEY,
    bathroom_id VARCHAR(36) NOT NULL,
    sensor_type ENUM('mq135', 'ir', 'rfid') NOT NULL,
    sensor_code VARCHAR(50) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bathroom_id) REFERENCES bathrooms(id)
);
```

#### sensor_logs
```sql
CREATE TABLE sensor_logs (
    id VARCHAR(36) PRIMARY KEY,
    sensor_id VARCHAR(36) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    unit VARCHAR(10),
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES sensors(id)
);
```

#### points
```sql
CREATE TABLE points (
    id VARCHAR(36) PRIMARY KEY,
    user_id VARCHAR(36) NOT NULL,
    type ENUM('earned', 'spent', 'transferred_in', 'transferred_out') NOT NULL,
    amount INT NOT NULL,
    description TEXT,
    reference_id VARCHAR(36),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

## 🔌 API Endpoints

### Sensor Data Endpoint
```http
POST /api/sensor-data
Content-Type: application/x-www-form-urlencoded

sensor_code=MQ135_001&value=350&unit=ppm
```

**Response:**
```json
{
    "success": true,
    "message": "Data logged successfully",
    "sensor_id": "uuid",
    "bathroom_id": "uuid"
}
```

### RFID Tap Endpoint
```http
POST /api/rfid-tap
Content-Type: application/x-www-form-urlencoded

rfid_code=EMP001&bathroom_id=uuid
```

**Response:**
```json
{
    "success": true,
    "message": "Cleaning started/completed",
    "action": "start_cleaning|complete_cleaning",
    "points_earned": 10
}
```

### Real-time Status
```http
GET /api/realtime-status
```

**Response:**
```json
{
    "success": true,
    "bathrooms": [
        {
            "id": "uuid",
            "name": "Kamar Mandi A",
            "status": "available",
            "current_visitors": 2,
            "max_visitors": 5,
            "last_sensor_reading": {
                "mq135": 320,
                "timestamp": "2024-01-01 12:00:00"
            }
        }
    ],
    "sensors": [
        {
            "id": "uuid",
            "type": "mq135",
            "value": 320,
            "unit": "ppm",
            "status": "normal"
        }
    ]
}
```

---

## 🎨 UI/UX Design

### Design System

#### Color Palette
```css
/* Primary Colors */
--blue-500: #3B82F6;
--purple-600: #9333EA;
--green-500: #10B981;
--red-500: #EF4444;

/* Gradients */
--gradient-primary: linear-gradient(to right, #3B82F6, #9333EA);
--gradient-success: linear-gradient(to right, #10B981, #059669);
--gradient-danger: linear-gradient(to right, #EF4444, #DC2626);
```

#### Typography
```css
/* Font Family */
font-family: 'Inter', system-ui, sans-serif;

/* Font Sizes */
--text-xs: 0.75rem;    /* 12px */
--text-sm: 0.875rem;   /* 14px */
--text-base: 1rem;     /* 16px */
--text-lg: 1.125rem;   /* 18px */
--text-xl: 1.25rem;    /* 20px */
--text-2xl: 1.5rem;    /* 24px */
--text-3xl: 1.875rem;  /* 30px */
--text-4xl: 2.25rem;   /* 36px */
```

#### Border Radius
```css
/* Rounded Corners */
--rounded-lg: 0.5rem;    /* 8px */
--rounded-xl: 0.75rem;   /* 12px */
--rounded-2xl: 1rem;     /* 16px */
--rounded-3xl: 1.5rem;   /* 24px */
```

### Component Classes

#### Cards
```css
.card-large {
    @apply bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-2xl border border-gray-200 dark:border-gray-700 transition-all duration-300 hover:shadow-3xl;
}
```

#### Buttons
```css
.btn-large {
    @apply px-8 py-4 rounded-2xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 shadow-lg;
}

.btn-primary {
    @apply bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700;
}
```

### Responsive Breakpoints
```css
/* Mobile First Approach */
sm: 640px   /* Small devices */
md: 768px   /* Medium devices */
lg: 1024px  /* Large devices */
xl: 1280px  /* Extra large devices */
2xl: 1536px /* 2X Extra large devices */
```

### Dark Mode Support
```css
/* Automatic theme detection */
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #111827;
        --text-primary: #F9FAFB;
    }
}

/* Manual theme toggle */
.dark {
    --bg-primary: #111827;
    --text-primary: #F9FAFB;
}
```

---

## 🔐 Security Features

### Authentication & Authorization
```php
// Session-based authentication
class Auth {
    public function login($username, $password) {
        $user = $this->userModel->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }
    
    public function hasRole($role) {
        return $_SESSION['role'] === $role;
    }
}
```

### CSRF Protection
```php
class Security {
    public static function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }
}
```

### Input Sanitization
```php
public static function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map([self::class, 'sanitizeInput'], $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
```

### SQL Injection Prevention
```php
// Using prepared statements
public function getUser($id) {
    $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
```

---

## 🚀 Deployment

### Production Configuration
```php
// config/config.php - Production
define('DB_HOST', 'production-host');
define('DB_NAME', 'production_db');
define('DB_USER', 'production_user');
define('DB_PASS', 'secure_password');

// Disable error display
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
```

### Web Server Configuration

#### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

#### Nginx
```nginx
server {
    listen 80;
    server_name sanipoint.example.com;
    root /var/www/sanipoint;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### SSL Configuration
```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # SSL Security
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
}
```

---

## 🔧 Troubleshooting

### Common Issues

#### 1. 500 Internal Server Error
```bash
# Check error logs
tail -f /var/log/apache2/error.log
tail -f logs/php-errors.log

# Common causes:
- Incorrect file permissions
- PHP syntax errors
- Missing dependencies
- Database connection issues
```

#### 2. CSS/JS Not Loading
```php
// Check file paths in main.php
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '');
$cssPath = $basePath . '/assets/css/style.css';
```

#### 3. Database Connection Failed
```php
// Verify database credentials
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    echo "Connection successful";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
```

#### 4. Session Issues
```php
// Check session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_start();
```

#### 5. CSRF Token Mismatch
```php
// Ensure token is included in forms
<input type="hidden" name="csrf_token" value="<?= Security::generateCSRFToken() ?>">

// Validate in controller
if (!Security::validateCSRFToken($_POST['csrf_token'] ?? '')) {
    throw new Exception('Invalid CSRF token');
}
```

### Performance Optimization

#### Database Optimization
```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_sensor_logs_recorded_at ON sensor_logs(recorded_at);
CREATE INDEX idx_points_user_id ON points(user_id);
```

#### Caching Strategy
```php
// Simple file-based caching
class Cache {
    public static function get($key) {
        $file = "cache/{$key}.cache";
        if (file_exists($file) && (time() - filemtime($file)) < 3600) {
            return unserialize(file_get_contents($file));
        }
        return null;
    }
    
    public static function set($key, $data) {
        file_put_contents("cache/{$key}.cache", serialize($data));
    }
}
```

### Monitoring & Logging

#### Error Logging
```php
// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $log = date('Y-m-d H:i:s') . " - Error: {$errstr} in {$errfile}:{$errline}\n";
    file_put_contents('logs/php-errors.log', $log, FILE_APPEND);
}
set_error_handler('customErrorHandler');
```

#### Performance Monitoring
```php
// Execution time tracking
$start_time = microtime(true);
// ... application code ...
$execution_time = microtime(true) - $start_time;
error_log("Execution time: {$execution_time} seconds");
```

---

## 📞 Support & Maintenance

### Regular Maintenance Tasks
1. **Database Backup**: Daily automated backups
2. **Log Rotation**: Weekly log cleanup
3. **Security Updates**: Monthly dependency updates
4. **Performance Review**: Monthly performance analysis
5. **User Feedback**: Continuous improvement based on feedback

### Contact Information
- **Developer**: [Your Name]
- **Email**: [your.email@domain.com]
- **GitHub**: [github.com/username/sanipoint]
- **Documentation**: [docs.sanipoint.com]

---

**© 2024 SANIPOINT - IoT Bathroom Monitoring System**