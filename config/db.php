<?php
// config/db.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Otomatis baca database Railway / fallback lokal
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'kodingkids';
$port = (int)(getenv('MYSQLPORT') ?: 3306);

// Cegah PHP meledak jika database belum tersambung
mysqli_report(MYSQLI_REPORT_OFF);
$real_conn = @new mysqli($host, $user, $pass, $db, $port);

class SafeResult {
    public $num_rows = 0;
    public function fetch_assoc() { return null; }
    public function fetch_all($mode = null) { return []; }
}

class SafeStmt {
    public function bind_param(...$args) { return true; }
    public function execute() { return true; }
    public function get_result() { return new SafeResult(); }
    public function close() { return true; }
}

class SafeDB {
    public $connect_error = null;
    public $insert_id = 0;
    public function query($sql) { return new SafeResult(); }
    public function prepare($sql) { return new SafeStmt(); }
    public function set_charset($charset) { return true; }
}

if ($real_conn->connect_error) {
    $db_connected = false;
    $conn = new SafeDB();
} else {
    $db_connected = true;
    $conn = $real_conn;
    $conn->set_charset("utf8mb4");
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function clean($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($data)));
}

function tambahPoin($user_id, $poin) {
    global $conn, $db_connected;
    if ($db_connected) {
        $conn->query("UPDATE users SET poin = poin + $poin WHERE id = $user_id");
    }
}

// Render avatar (Base64 atau emoji)
function renderAvatar($avatar, $size = '1em') {
    if (strpos($avatar, 'data:image') === 0) {
        return "<img src='{$avatar}' style='width: {$size}; height: {$size}; object-fit: cover; border-radius: 50%; display: inline-block; vertical-align: middle;'>";
    }
    return $avatar;
}
?>
