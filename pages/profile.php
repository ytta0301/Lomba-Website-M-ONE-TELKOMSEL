<?php
$page_title = 'Edit Profil';
require_once '../includes/header.php';

if (!isLoggedIn()) redirect('login.php');

$uid = $_SESSION['user_id'];
$error = '';
$success = '';

$avatars = ['🐱','🐶','🐸','🦊','🐧','🦄','🐻','🐼','🐨','🦁','🐯','🐙','🤖','👻','👨‍🏫','👩‍🏫'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_nama = clean($_POST['nama']);
    $new_avatar = isset($_POST['avatar']) ? $_POST['avatar'] : '';

    // UPLOAD FOTO -> LANGSUNG SIMPAN KE DATABASE SEBAGAI BASE64
    if (isset($_FILES['foto_custom']) && $_FILES['foto_custom']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['foto_custom']['name'], PATHINFO_EXTENSION));
        $size = $_FILES['foto_custom']['size'];

        if (!in_array($ext, $allowed)) {
            $error = 'Format foto harus JPG, PNG, atau GIF!';
        } elseif ($size > 2 * 1024 * 1024) {
            $error = 'Ukuran foto maksimal 2MB!';
        } else {
            // Baca file foto dan konversi jadi base64
            $foto_data = file_get_contents($_FILES['foto_custom']['tmp_name']);
            $mime_type = mime_content_type($_FILES['foto_custom']['tmp_name']);
            $base64 = base64_encode($foto_data);

            // Simpan sebagai data URI (langsung bisa ditampilkan di browser)
            $new_avatar = "data:{$mime_type};base64,{$base64}";
        }
    }

    if (!$error) {
        if (strlen($new_nama) < 3) {
            $error = 'Nama minimal 3 karakter!';
        } elseif (empty($new_avatar)) {
            $error = 'Pilih avatar emoji atau upload foto!';
        } else {
            $stmt = $conn->prepare("UPDATE users SET nama = ?, avatar = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_nama, $new_avatar, $uid);

            if ($stmt->execute()) {
                $_SESSION['user_nama'] = $new_nama;
                $success = 'Profil kamu berhasil diperbarui! 🎉';
            } else {
                $error = 'Gagal menyimpan, coba lagi!';
            }
        }
    }
}

// Ambil data terbaru
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-gray-800">Pengaturan Profil</h1>
            <p class="text-gray-500 font-semibold mt-2">Ganti nama, pilih avatar, atau upload fotomu sendiri!</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-700 px-4 py-3 rounded-xl font-bold mb-6 border border-red-200">❌ <?= $error ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-xl font-bold mb-6 border border-green-200">✅ <?= $success ?></div>
            <?php endif; ?>

            <!-- Preview Avatar Saat Ini -->
            <div class="flex flex-col items-center justify-center mb-8 bg-purple-50 py-6 rounded-2xl border-2 border-dashed border-purple-200">
                <span class="text-gray-500 font-bold mb-3 text-sm uppercase tracking-widest">Penampilanmu Saat Ini</span>
                <div class="text-7xl bg-white rounded-full shadow-md flex items-center justify-center w-36 h-36 overflow-hidden">
                    <?= renderAvatar($user['avatar'], '100%', 'contain') ?>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-6">
                    <label class="block font-black text-gray-700 mb-2">👤 Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" class="form-input text-lg bg-gray-50" required>
                </div>

                <!-- Upload Foto -->
                <div class="mb-6 p-5 border-2 border-blue-100 bg-blue-50/30 rounded-2xl">
                    <label class="block font-black text-blue-800 mb-2">📸 Upload Foto Custom</label>
                    <input type="file" name="foto_custom" accept="image/png, image/jpeg, image/gif" class="w-full bg-white border border-blue-200 rounded-xl px-4 py-2 font-semibold text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-blue-100 file:text-blue-700 hover:file:bg-blue-200 cursor-pointer">
                    <p class="text-xs text-blue-500 font-bold mt-2">Maksimal 2MB. Kosongkan jika ingin pakai emoji.</p>
                </div>

                <!-- Pilih Emoji -->
                <div class="mb-8">
                    <label class="block font-black text-center text-sm uppercase tracking-wider text-gray-400 mb-4">Atau Pilih Avatar Emoji</label>
                    <div class="grid grid-cols-4 sm:grid-cols-8 gap-3 bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <?php foreach ($avatars as $av): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="avatar" value="<?= $av ?>" class="hidden peer" <?= ($user['avatar'] === $av) ? 'checked' : '' ?>>
                            <div class="text-3xl text-center p-2 rounded-xl border-4 border-transparent peer-checked:border-primary peer-checked:bg-purple-50 hover:bg-gray-200 transition-all peer-checked:scale-110">
                                <?= $av ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 items-center justify-between pt-6 border-t border-gray-100">
                    <a href="<?= ($_SESSION['user_role'] === 'admin') ? 'guru.php' : 'dashboard.php' ?>" class="text-gray-400 font-bold hover:text-gray-800 order-2 sm:order-1">← Kembali</a>
                    <button type="submit" class="btn-primary px-8 py-3.5 text-lg w-full sm:w-auto order-1 sm:order-2 shadow-lg hover:scale-105 transition-transform flex items-center justify-center gap-2">
                        💾 Simpan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>