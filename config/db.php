<?php
// config/db.php - Koneksi Database KodingKids

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kodingkids');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;text-align:center;padding:50px;">
        <h2>❌ Koneksi Database Gagal</h2>
        <p>Pastikan XAMPP MySQL sudah berjalan dan database <strong>' . htmlspecialchars(DB_NAME, ENT_QUOTES, 'UTF-8') . '</strong> sudah dibuat.</p>
        <p>Error: ' . $conn->connect_error . '</p>
    </div>');
}

$conn->set_charset("utf8mb4");

// Helper: cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Helper: redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper: sanitize input
function clean($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data))));
}

// Helper: tambah poin user
function tambahPoin($user_id, $poin) {
    global $conn;
    $stmt = $conn->prepare("UPDATE users SET poin = poin + ? WHERE id = ?");
    $stmt->bind_param("ii", $poin, $user_id);
    $stmt->execute();

    // Cek badge baru
    $user = $conn->query("SELECT poin FROM users WHERE id = $user_id")->fetch_assoc();
    $badges = $conn->query("SELECT * FROM badges WHERE syarat_poin <= {$user['poin']}");
    while ($badge = $badges->fetch_assoc()) {
        $conn->query("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES ($user_id, {$badge['id']})");
    }
}