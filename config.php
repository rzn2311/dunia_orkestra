<?php
declare(strict_types=1);

// ==========================================
// KONFIGURASI PHP
// ==========================================

ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/'
    ]);

    session_start();
}

// ==========================================
// KONEKSI MYSQL LARAGON
// ==========================================

$host = '127.0.0.1';
$port = 3306;
$dbname = 'dunia_orkestra';
$dbuser = 'root';
$dbpass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log('Koneksi MySQL gagal: ' . $e->getMessage());
    http_response_code(500);
    exit('Database belum terhubung. Periksa konfigurasi MySQL Laragon.');
}

// ==========================================
// DATA PRODUK
// ==========================================

$products = [
    1 => [
        'name' => 'Yamaha F310',
        'category' => 'Acoustic',
        'price' => 2070000,
        'image' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=800&q=80',
        'description' => 'Gitar akustik untuk pemula dengan suara natural.'
    ],
    2 => [
        'name' => 'Yamaha F370',
        'category' => 'Acoustic',
        'price' => 2576000,
        'image' => 'https://images.unsplash.com/photo-1550291652-6ea9114a47b1?auto=format&fit=crop&w=800&q=80',
        'description' => 'Desain klasik dengan karakter suara hangat.'
    ],
    3 => [
        'name' => 'Yamaha APX600',
        'category' => 'Electro Acoustic',
        'price' => 3960000,
        'image' => 'https://images.unsplash.com/photo-1525201548942-d8732f6617a0?auto=format&fit=crop&w=800&q=80',
        'description' => 'Gitar elektro akustik untuk panggung.'
    ],
    4 => [
        'name' => 'Yamaha FG800',
        'category' => 'Acoustic',
        'price' => 3800000,
        'image' => 'https://images.unsplash.com/photo-1516924962500-2b4b3b99ea02?auto=format&fit=crop&w=800&q=80',
        'description' => 'Gitar akustik dengan suara penuh dan jernih.'
    ],
    5 => [
        'name' => 'Yamaha Pacifica 112V',
        'category' => 'Electric',
        'price' => 4200000,
        'image' => 'https://images.unsplash.com/photo-1516924962500-2b4b3b99ea02?auto=format&fit=crop&w=800&q=80',
        'description' => 'Gitar elektrik serbaguna.'
    ],
    6 => [
        'name' => 'Yamaha Revstar',
        'category' => 'Electric',
        'price' => 7850000,
        'image' => 'https://images.unsplash.com/photo-1525201548942-d8732f6617a0?auto=format&fit=crop&w=800&q=80',
        'description' => 'Gitar elektrik premium.'
    ],
    7 => [
        'name' => 'Yamaha CPX600',
        'category' => 'Electro Acoustic',
        'price' => 4550000,
        'image' => 'https://images.unsplash.com/photo-1550291652-6ea9114a47b1?auto=format&fit=crop&w=800&q=80',
        'description' => 'Gitar elektro akustik dengan suara kaya.'
    ],
    8 => [
        'name' => 'Yamaha C40',
        'category' => 'Acoustic',
        'price' => 1650000,
        'image' => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=800&q=80',
        'description' => 'Gitar klasik untuk belajar.'
    ]
];

// ==========================================
// FUNGSI AUTENTIKASI
// ==========================================

function loggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function requireLogin(): int
{
    if (!loggedIn()) {
        jsonResponse([
            'success' => false,
            'message' => 'Silakan login terlebih dahulu.'
        ], 401);
    }

    return (int) $_SESSION['user_id'];
}

// ==========================================
// RESPONSE JSON
// ==========================================

function jsonResponse(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}