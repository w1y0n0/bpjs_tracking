<?php
// Fungsi untuk load file .env ke environment
function loadEnv($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // skip komentar
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        // Set ke environment
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// Load .env dari root project
loadEnv(__DIR__ . '/../.env');

// Database Configuration
define('DB_HOST', getenv('DB_HOST')); // ambil dari file .env
define('DB_USERNAME', getenv('DB_USERNAME')); // ambil dari file .env
define('DB_PASSWORD', getenv('DB_PASSWORD')); // ambil dari file .env
define('DB_NAME', getenv('DB_NAME')); // ambil dari file .env

// Application Configuration
define('APP_NAME', 'BPJS Tracking');
define('APP_VERSION', '1.0.0');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
