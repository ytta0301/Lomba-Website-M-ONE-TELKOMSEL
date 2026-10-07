<?php
$page_title = 'Daftar Akun';
require_once '../includes/header.php';
if (isLoggedIn()) redirect('../index.php');

$error = '';
$success = '';
// Pilihan Avatar yang seru untuk anak-anak
$avatars = ['🐱','🐶','🐸','🦊','🐧','🦄','🐻','🐼','🐨','🦁','🐯','🐙'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama     = clean($_POST['nama']);
    $username = clean($_POST['username']);
    $email    = clean($_POST['email']);
    $password = $_POST['password'];
    $konfirm  = $_POST['konfirm'];
    $avatar   = in_array($_POST['avatar'], $avatars) ? $_POST['avatar'] : '🐱';

    // Validasi Form
    if (strlen($nama) < 3)               $error = 'Nama minimal 3 karakter!';
    elseif (strlen($username) < 4)       $error = 'Username minimal 4 karakter!';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Format email tidak valid!';
    elseif (strlen($password) < 6)       $error = 'Password minimal 6 karakter!';
    elseif ($password !== $konfirm)      $error = 'Password dan konfirmasi tidak sama!';
    else {
        // Cek apakah username / email sudah terdaftar
        $cek = $conn->query("SELECT id FROM users WHERE username='$username' OR email='$email'")->num_rows;
        if ($cek > 0) {
            $error = 'Username atau email sudah digunakan!';
        } else {
            // Hash password untuk keamanan
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (nama, username, email, password, avatar) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $nama, $username, $email, $hash, $avatar);
            
            if ($stmt->execute()) {
                $new_id = $conn->insert_id;
                // Kasih badge pertama ("Pemula Bintang" - ID 1 di tabel badges)
                $conn->query("INSERT INTO user_badges (user_id, badge_id) VALUES ($new_id, 1)");
                $success = 'Akun berhasil dibuat! Silakan login.';
            } else {
                $error = 'Terjadi kesalahan, coba lagi!';
            }
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center px-4 py-16 bg-gradient-to-br from-red-50 via-white to-yellow-50">
    <div class="w-full max-w-lg">

        <div class="text-center mb-8">
            <div class="text-7xl animate-bounce-slow mb-4">🎉</div>
            <h1 class="text-3xl font-black text-gray-800">Daftar KodingKids!</h1>
            <p class="text-gray-500 font-semibold mt-2">Buat akun gratis dan mulai petualanganmu! 🚀</p>
        </div>

        <div class="bg-white rounded-3xl shadow-xl p-8 border-2 border-red-100">
            <?php if ($error): ?>
            <div class="bg-red-50 border-2 border-red-200 text-red-700 px-4 py-3 rounded-2xl font-bold mb-5 flex items-center gap-2">
                ❌ <?= $error ?>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="bg-green-50 border-2 border-green-200 text-green-700 px-4 py-3 rounded-2xl font-bold mb-5 flex items-center gap-2">
                ✅ <?= $success ?> <a href="login.php" class="underline ml-2">Login →</a>
            </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Pilih Avatar -->
                <div class="mb-6">
                    <label class="block font-black text-gray-700 mb-3 text-sm">🎭 Pilih Avatarmu!</label>
                    <div class="grid grid-cols-6 gap-2">
                        <?php foreach ($avatars as $i => $av): ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="avatar" value="<?= $av ?>" class="hidden peer" <?= $i==0?'checked':'' ?>>
                            <div class="text-3xl text-center p-2 rounded-xl border-3 border-transparent peer-checked:border-primary peer-checked:bg-purple-50 hover:bg-gray-50 transition-all">
                                <?= $av ?>
                            </div>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block font-black text-gray-700 mb-2 text-sm">👤 Nama Lengkap</label>
                        <input type="text" name="nama" class="form-input" placeholder="Nama kamu..." required value="<?= isset($_POST['nama'])?htmlspecialchars($_POST['nama']):'' ?>">
                    </div>
                    <div>
                        <label class="block font-black text-gray-700 mb-2 text-sm">🏷️ Username</label>
                        <input type="text" name="username" class="form-input" placeholder="Username unikmu..." required value="<?= isset($_POST['username'])?htmlspecialchars($_POST['username']):'' ?>">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block font-black text-gray-700 mb-2 text-sm">📧 Email</label>
                    <input type="email" name="email" class="form-input" placeholder="email@kamu.com" required value="<?= isset($_POST['email'])?htmlspecialchars($_POST['email']):'' ?>">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block font-black text-gray-700 mb-2 text-sm">🔒 Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Min. 6 karakter" required>
                    </div>
                    <div>
                        <label class="block font-black text-gray-700 mb-2 text-sm">🔒 Konfirmasi</label>
                        <input type="password" name="konfirm" class="form-input" placeholder="Ulangi password" required>
                    </div>
                </div>

                <button type="submit" class="btn-accent w-full text-center py-4 text-base">
                    🎉 Buat Akun Sekarang!
                </button>
            </form>

            <div class="text-center mt-6">
                <p class="text-gray-500 font-semibold">Sudah punya akun?</p>
                <a href="login.php" class="btn-outline inline-block mt-3 w-full text-center py-3">🔐 Masuk di Sini</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>