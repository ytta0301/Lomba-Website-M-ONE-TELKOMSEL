<?php
// config/db.php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$db_name = getenv('DB_NAME') ?: 'kodingkids';
$db_port = getenv('DB_PORT') ? (int)getenv('DB_PORT') : 3306;

$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;text-align:center;padding:50px;">
        <h2>❌ Koneksi Database Gagal</h2>
        <p>Pastikan database <strong>' . htmlspecialchars($db_name, ENT_QUOTES, 'UTF-8') . '</strong> di host <strong>' . htmlspecialchars($db_host, ENT_QUOTES, 'UTF-8') . '</strong> sudah berjalan.</p>
        <p>Error: ' . htmlspecialchars($conn->connect_error, ENT_QUOTES, 'UTF-8') . '</p>
    </div>');
}
$conn->set_charset("utf8mb4");

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
    global $conn;
    $conn->query("UPDATE users SET poin = poin + $poin WHERE id = $user_id");

    // Otomatis berikan badge jika poin mencukupi
    $user = $conn->query("SELECT poin FROM users WHERE id = $user_id")->fetch_assoc();
    if ($user) {
        $badges = $conn->query("SELECT * FROM badges WHERE syarat_poin <= {$user['poin']}");
        if ($badges) {
            while ($badge = $badges->fetch_assoc()) {
                $conn->query("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES ($user_id, {$badge['id']})");
            }
        }
    }
}

// Render avatar: bisa emoji ATAU foto dari database
function renderAvatar($avatar, $size = '1em', $fit = 'cover') {
    // Cek apakah avatar adalah data base64 (foto dari database)
    if (strpos($avatar, 'data:image') === 0) {
        return "<img src='{$avatar}' style='width: {$size}; height: {$size}; object-fit: {$fit}; border-radius: 50%; display: inline-block; vertical-align: middle;'>";
    }
    // Kalau bukan, berarti emoji biasa
    return $avatar;
}

function getEquippedItems($user_id) {
    global $conn;

    $stmt = $conn->prepare(
        "SELECT ti.* FROM user_items ui
         JOIN toko_items ti ON ui.item_id = ti.id
         WHERE ui.user_id = ? AND ui.aktif = 1"
    );
    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan query item yang dipakai: ' . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new RuntimeException('Gagal mengambil item yang dipakai: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $equipped = ['avatar' => null, 'gelar' => null, 'border' => null];
    while ($row = $result->fetch_assoc()) {
        if (array_key_exists($row['kategori'], $equipped)) {
            $equipped[$row['kategori']] = $row;
        }
    }

    $stmt->close();
    return $equipped;
}

function userOwnsItem($user_id, $item_id) {
    global $conn;

    $stmt = $conn->prepare(
        "SELECT id FROM user_items WHERE user_id = ? AND item_id = ? LIMIT 1"
    );
    if (!$stmt) {
        throw new RuntimeException('Gagal menyiapkan query kepemilikan item: ' . $conn->error);
    }

    $stmt->bind_param("ii", $user_id, $item_id);
    if (!$stmt->execute()) {
        throw new RuntimeException('Gagal memeriksa kepemilikan item: ' . $stmt->error);
    }

    $stmt->store_result();
    $owns_item = $stmt->num_rows > 0;
    $stmt->close();

    return $owns_item;
}
?>