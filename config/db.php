<?php
// config/db.php
$conn = new mysqli('localhost', 'root', '', 'kodingkids');
if ($conn->connect_error) die('Koneksi gagal: ' . $conn->connect_error);
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