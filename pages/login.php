<?php
$page_title = 'Login';
require_once '../includes/header.php';
if (isLoggedIn()) {
    $current_role = strtolower((string) ($_SESSION['user_role'] ?? ''));
    redirect(in_array($current_role, ['guru', 'admin'], true) ? 'guru.php' : '../index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    // Cek username atau email di database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    // Password akun yang dibuat lewat MySQL mungkin masih berupa teks biasa.
    $passwordValid = $user && password_verify($password, $user['password']);
    $legacyPlainPassword = $user
        && !$passwordValid
        && hash_equals((string) $user['password'], (string) $password);

    if ($passwordValid || $legacyPlainPassword) {
        if ($legacyPlainPassword) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updatePassword->bind_param("si", $newHash, $user['id']);
            $updatePassword->execute();
        }

        // Set session
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nama'] = $user['nama'];
        $_SESSION['user_role'] = $user['role'];
        
        tambahPoin($user['id'], 0); // Panggil untuk cek/update badge (jika ada poin nyangkut)
        
        // Guru dan admin langsung masuk ke panel guru.
        $role = strtolower((string) $user['role']);
        redirect(in_array($role, ['guru', 'admin'], true) ? 'guru.php' : 'dashboard.php');
    } else {
        $error = 'Username/email atau password salah!';
    }
}
?>

<div class="min-h-screen flex items-center justify-center px-4 py-16 bg-gradient-to-br from-purple-50 via-white to-blue-50">
    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="text-7xl animate-bounce-slow mb-4">⭐</div>
            <h1 class="text-3xl font-black text-gray-800">Selamat Datang!</h1>
            <p class="text-gray-500 font-semibold mt-2">Masuk ke akun KodingKids-mu 🚀</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8 border-2 border-purple-100">
            <?php if ($error): ?>
            <div class="bg-red-50 border-2 border-red-200 text-red-700 px-4 py-3 rounded-2xl font-bold mb-6 flex items-center gap-2">
                ❌ <?= $error ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-5">
                    <label class="block font-black text-gray-700 mb-2 text-sm">👤 Username atau Email</label>
                    <input type="text" name="username" class="form-input" placeholder="Masukkan username atau email..." required>
                </div>
                <div class="mb-6">
                    <label class="block font-black text-gray-700 mb-2 text-sm">🔒 Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Masukkan password..." required>
                </div>
                <button type="submit" class="btn-primary w-full text-center py-4 text-base">
                    🚀 Masuk Sekarang!
                </button>
            </form>

            <div class="relative my-6">
                <div class="border-t-2 border-gray-100"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="bg-white px-4 text-gray-400 font-bold text-sm">atau</span>
                </div>
            </div>

            <div class="text-center">
                <p class="text-gray-500 font-semibold">Belum punya akun?</p>
                <a href="register.php" class="btn-accent inline-block mt-3 w-full text-center py-3">
                    📝 Daftar Gratis Sekarang!
                </a>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="../index.php" class="text-gray-400 font-semibold hover:text-primary text-sm">← Kembali ke Beranda</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>