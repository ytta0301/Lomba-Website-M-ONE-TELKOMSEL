<?php
$page_title = 'Toko Poin';
require_once '../includes/header.php';

if (!isLoggedIn()) redirect('login.php');

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
$pesan = '';
$pesan_type = '';

// === AKSI BELI ===
if (isset($_POST['beli'])) {
    $item_id = (int)$_POST['item_id'];
    $item = $conn->query("SELECT * FROM toko_items WHERE id=$item_id")->fetch_assoc();

    if (!$item) {
        $pesan = 'Item tidak ditemukan!';
        $pesan_type = 'error';
    } elseif (userOwnsItem($uid, $item_id)) {
        $pesan = 'Kamu sudah punya item ini!';
        $pesan_type = 'error';
    } elseif ($user['poin'] < $item['harga']) {
        $pesan = 'Poin kamu tidak cukup! Butuh ' . $item['harga'] . ' poin, kamu punya ' . $user['poin'] . ' poin.';
        $pesan_type = 'error';
    } else {
        // Kurangi poin
        $conn->query("UPDATE users SET poin = poin - {$item['harga']} WHERE id = $uid");
        // Tambah ke inventory
        $conn->query("INSERT INTO user_items (user_id, item_id) VALUES ($uid, $item_id)");
        $pesan = "Berhasil membeli \"{$item['nama']}\"! 🎉";
        $pesan_type = 'success';
        // Refresh data user
        $user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
    }
}

// === AKSI PASANG / LEPAS ===
if (isset($_POST['equip'])) {
    $item_id = (int)$_POST['item_id'];
    $item = $conn->query("SELECT * FROM toko_items WHERE id=$item_id")->fetch_assoc();

    if ($item && userOwnsItem($uid, $item_id)) {
        // Lepas semua item di kategori yang sama
        $conn->query("UPDATE user_items ui
                      JOIN toko_items ti ON ui.item_id = ti.id
                      SET ui.aktif = 0
                      WHERE ui.user_id = $uid AND ti.kategori = '{$item['kategori']}'");
        // Pasang item ini
        $conn->query("UPDATE user_items SET aktif = 1 WHERE user_id = $uid AND item_id = $item_id");
        $pesan = "\"{$item['nama']}\" berhasil dipasang! ✨";
        $pesan_type = 'success';
    }
}

if (isset($_POST['unequip'])) {
    $item_id = (int)$_POST['item_id'];
    $conn->query("UPDATE user_items SET aktif = 0 WHERE user_id = $uid AND item_id = $item_id");
    $pesan = "Item berhasil dilepas!";
    $pesan_type = 'success';
}

// Ambil semua item toko
$items_avatar = $conn->query("SELECT * FROM toko_items WHERE kategori='avatar' ORDER BY harga ASC");
$items_gelar = $conn->query("SELECT * FROM toko_items WHERE kategori='gelar' ORDER BY harga ASC");
$items_border = $conn->query("SELECT * FROM toko_items WHERE kategori='border' ORDER BY harga ASC");

// Ambil equipped items
$equipped = getEquippedItems($uid);

// Refresh poin
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
?>

<!-- HEADER TOKO -->
<div class="bg-gradient-to-r from-purple-600 via-pink-500 to-orange-400 py-12 px-4 text-white text-center relative overflow-hidden">
    <div class="absolute -left-10 top-5 text-9xl opacity-10 rotate-12">🛒</div>
    <div class="absolute -right-10 bottom-0 text-9xl opacity-10 -rotate-12">💎</div>
    <div class="relative z-10">
        <div class="text-6xl mb-3 animate-pop">🏪</div>
        <h1 class="text-4xl md:text-5xl font-black mb-3 drop-shadow-md">Toko Poin</h1>
        <p class="text-white/80 font-bold text-lg mb-6">Tukarkan bintangmu dengan item-item keren!</p>

        <!-- Saldo Poin -->
        <div class="inline-flex items-center gap-3 bg-white/20 backdrop-blur-sm px-8 py-3 rounded-full border border-white/30 shadow-lg">
            <span class="text-3xl">⭐</span>
            <div class="text-left">
                <div class="text-xs font-bold text-white/70 uppercase tracking-wider">Saldo Poinmu</div>
                <div class="text-3xl font-black"><?= number_format($user['poin']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-10">

    <?php if ($pesan): ?>
        <div class="mb-8 px-6 py-4 rounded-2xl font-bold text-lg flex items-center gap-3 animate-fadeInUp
            <?= $pesan_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
            <?= $pesan_type === 'success' ? '✅' : '❌' ?> <?= $pesan ?>
        </div>
    <?php endif; ?>

    <!-- Item yang sedang dipakai -->
    <?php if ($equipped['avatar'] || $equipped['gelar'] || $equipped['border']): ?>
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 mb-10">
        <h2 class="font-black text-gray-800 text-xl mb-4 flex items-center gap-2">🎒 Item yang Sedang Dipakai</h2>
        <div class="flex flex-wrap gap-4">
            <?php foreach (['avatar', 'gelar', 'border'] as $kat): ?>
                <?php if ($equipped[$kat]): ?>
                <div class="bg-purple-50 border-2 border-purple-200 rounded-2xl px-5 py-3 flex items-center gap-3">
                    <span class="text-2xl"><?= $equipped[$kat]['icon'] ?></span>
                    <div>
                        <div class="text-xs font-bold text-purple-400 uppercase"><?= $kat ?></div>
                        <div class="font-black text-gray-800"><?= htmlspecialchars($equipped[$kat]['nama']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ============ KATEGORI: AVATAR ============ -->
    <div class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-4xl">🎭</span>
            <div>
                <h2 class="text-2xl font-black text-gray-800">Avatar Eksklusif</h2>
                <p class="text-gray-500 font-semibold text-sm">Avatar spesial yang tidak bisa didapat dari registrasi biasa!</p>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php while ($item = $items_avatar->fetch_assoc()):
                $owned = userOwnsItem($uid, $item['id']);
                $is_active = $equipped['avatar'] && $equipped['avatar']['id'] == $item['id'];
                $can_buy = $user['poin'] >= $item['harga'];
            ?>
            <div class="bg-white rounded-2xl border-2 <?= $is_active ? 'border-purple-400 shadow-lg shadow-purple-100' : ($owned ? 'border-green-300' : 'border-gray-100') ?> p-5 text-center hover:shadow-md transition-all group">
                <div class="text-5xl mb-3 group-hover:scale-110 transition-transform"><?= $item['icon'] ?></div>
                <h3 class="font-black text-gray-800 text-sm"><?= htmlspecialchars($item['nama']) ?></h3>
                <p class="text-gray-400 text-xs font-semibold mt-1 mb-3"><?= htmlspecialchars($item['deskripsi']) ?></p>

                <?php if ($is_active): ?>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button name="unequip" class="w-full bg-purple-100 text-purple-700 font-black py-2 rounded-xl text-sm">✅ Sedang Dipakai</button>
                    </form>
                <?php elseif ($owned): ?>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button name="equip" class="w-full bg-green-100 text-green-700 font-black py-2 rounded-xl text-sm hover:bg-green-200 transition-colors">🎭 Pasang</button>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button name="beli" class="w-full <?= $can_buy ? 'bg-yellow-400 text-yellow-900 hover:bg-yellow-500' : 'bg-gray-100 text-gray-400 cursor-not-allowed' ?> font-black py-2 rounded-xl text-sm transition-colors" <?= !$can_buy ? 'disabled' : '' ?>>
                            ⭐ <?= $item['harga'] ?> Poin
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- ============ KATEGORI: GELAR ============ -->
    <div class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-4xl">🏷️</span>
            <div>
                <h2 class="text-2xl font-black text-gray-800">Gelar Keren</h2>
                <p class="text-gray-500 font-semibold text-sm">Gelar yang tampil di samping namamu di Leaderboard!</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php while ($item = $items_gelar->fetch_assoc()):
                $owned = userOwnsItem($uid, $item['id']);
                $is_active = $equipped['gelar'] && $equipped['gelar']['id'] == $item['id'];
                $can_buy = $user['poin'] >= $item['harga'];
            ?>
            <div class="bg-white rounded-2xl border-2 <?= $is_active ? 'border-purple-400 shadow-lg shadow-purple-100' : ($owned ? 'border-green-300' : 'border-gray-100') ?> p-5 flex items-center gap-4 hover:shadow-md transition-all">
                <div class="text-4xl flex-shrink-0"><?= $item['icon'] ?></div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-black text-gray-800"><?= htmlspecialchars($item['nama']) ?></h3>
                    <p class="text-gray-400 text-xs font-semibold mt-0.5"><?= htmlspecialchars($item['deskripsi']) ?></p>

                    <div class="mt-3">
                        <?php if ($is_active): ?>
                            <form method="POST">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <button name="unequip" class="bg-purple-100 text-purple-700 font-black py-1.5 px-4 rounded-xl text-xs">✅ Sedang Dipakai</button>
                            </form>
                        <?php elseif ($owned): ?>
                            <form method="POST">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <button name="equip" class="bg-green-100 text-green-700 font-black py-1.5 px-4 rounded-xl text-xs hover:bg-green-200 transition-colors">🏷️ Pasang Gelar</button>
                            </form>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                <button name="beli" class="<?= $can_buy ? 'bg-yellow-400 text-yellow-900 hover:bg-yellow-500' : 'bg-gray-100 text-gray-400 cursor-not-allowed' ?> font-black py-1.5 px-4 rounded-xl text-xs transition-colors" <?= !$can_buy ? 'disabled' : '' ?>>
                                    ⭐ <?= $item['harga'] ?> Poin
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- ============ KATEGORI: BORDER ============ -->
    <div class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <span class="text-4xl">🎨</span>
            <div>
                <h2 class="text-2xl font-black text-gray-800">Border Profil</h2>
                <p class="text-gray-500 font-semibold text-sm">Bingkai keren untuk avatarmu di seluruh website!</p>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            <?php while ($item = $items_border->fetch_assoc()):
                $owned = userOwnsItem($uid, $item['id']);
                $is_active = $equipped['border'] && $equipped['border']['id'] == $item['id'];
                $can_buy = $user['poin'] >= $item['harga'];
            ?>
            <div class="bg-white rounded-2xl border-2 <?= $is_active ? 'border-purple-400 shadow-lg shadow-purple-100' : ($owned ? 'border-green-300' : 'border-gray-100') ?> p-5 text-center hover:shadow-md transition-all">
                <!-- Preview border -->
                <div class="w-16 h-16 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl bg-gray-50" style="<?= $item['nilai'] ?>">
                    <?= $item['icon'] ?>
                </div>
                <h3 class="font-black text-gray-800 text-sm"><?= htmlspecialchars($item['nama']) ?></h3>
                <p class="text-gray-400 text-[10px] font-semibold mt-1 mb-3"><?= htmlspecialchars($item['deskripsi']) ?></p>

                <?php if ($is_active): ?>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button name="unequip" class="w-full bg-purple-100 text-purple-700 font-black py-2 rounded-xl text-xs">✅ Dipakai</button>
                    </form>
                <?php elseif ($owned): ?>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button name="equip" class="w-full bg-green-100 text-green-700 font-black py-2 rounded-xl text-xs hover:bg-green-200 transition-colors">🎨 Pasang</button>
                    </form>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                        <button name="beli" class="w-full <?= $can_buy ? 'bg-yellow-400 text-yellow-900 hover:bg-yellow-500' : 'bg-gray-100 text-gray-400 cursor-not-allowed' ?> font-black py-2 rounded-xl text-xs transition-colors" <?= !$can_buy ? 'disabled' : '' ?>>
                            ⭐ <?= $item['harga'] ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Kembali -->
    <div class="text-center">
        <a href="dashboard.php" class="text-gray-400 font-bold hover:text-gray-800 transition-colors">← Kembali ke Dashboard</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>