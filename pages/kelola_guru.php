<?php
$page_title = 'Kelola Guru — Panel Kepsek';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$role_stmt = $conn->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
if (!$role_stmt) {
    throw new RuntimeException('Gagal menyiapkan pemeriksaan role: ' . $conn->error);
}
$role_stmt->bind_param('i', $_SESSION['user_id']);
if (!$role_stmt->execute()) {
    throw new RuntimeException('Gagal memeriksa role pengguna: ' . $role_stmt->error);
}
$current_user = $role_stmt->get_result()->fetch_assoc();
$role_stmt->close();

if (!$current_user) {
    $_SESSION = [];
    session_destroy();
    redirect('login.php');
}

$user_role = strtolower((string) $current_user['role']);
$_SESSION['user_role'] = $user_role;
if ($user_role !== 'kepsek') {
    redirect(in_array($user_role, ['guru', 'admin'], true) ? 'guru.php' : 'dashboard.php');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$pesan = '';
$pesan_type = '';
$form_nama = '';
$form_username = '';
$form_email = '';
$form_avatar = '👨‍🏫';

if (isset($_SESSION['kelola_guru_flash'])) {
    $flash = $_SESSION['kelola_guru_flash'];
    unset($_SESSION['kelola_guru_flash']);
    $pesan = $flash['message'];
    $pesan_type = $flash['type'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!is_string($posted_token) || !hash_equals($csrf_token, $posted_token)) {
        $pesan = 'Permintaan tidak valid atau sesi sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        $pesan_type = 'error';
    } elseif (isset($_POST['tambah_guru'])) {
        $form_nama = is_string($_POST['nama'] ?? null) ? trim($_POST['nama']) : '';
        $form_username = is_string($_POST['username'] ?? null) ? strtolower(trim($_POST['username'])) : '';
        $form_email = is_string($_POST['email'] ?? null) ? strtolower(trim($_POST['email'])) : '';
        $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        $form_avatar = is_string($_POST['avatar'] ?? null) ? $_POST['avatar'] : '👨‍🏫';
        $avatars = ['👨‍🏫', '👩‍🏫', '🧑‍💻'];

        if (mb_strlen($form_nama) < 3 || mb_strlen($form_nama) > 100) {
            $pesan = 'Nama guru harus terdiri dari 3 sampai 100 karakter.';
            $pesan_type = 'error';
        } elseif (!preg_match('/^[a-z0-9_.-]{4,50}$/', $form_username)) {
            $pesan = 'Username harus terdiri dari 4 sampai 50 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.';
            $pesan_type = 'error';
        } elseif (!filter_var($form_email, FILTER_VALIDATE_EMAIL)) {
            $pesan = 'Alamat email tidak valid.';
            $pesan_type = 'error';
        } elseif (strlen($password) < 6) {
            $pesan = 'Password guru minimal 6 karakter.';
            $pesan_type = 'error';
        } elseif (!in_array($form_avatar, $avatars, true)) {
            $pesan = 'Pilihan avatar tidak valid.';
            $pesan_type = 'error';
        } else {
            try {
                $check_stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
                if (!$check_stmt) {
                    throw new RuntimeException($conn->error);
                }
                $check_stmt->bind_param('ss', $form_username, $form_email);
                $check_stmt->execute();
                $exists = $check_stmt->get_result()->num_rows > 0;
                $check_stmt->close();

                if ($exists) {
                    $pesan = 'Username atau email sudah terdaftar di sistem.';
                    $pesan_type = 'error';
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $teacher_role = 'guru';
                    $insert_stmt = $conn->prepare(
                        'INSERT INTO users (nama, username, email, password, role, avatar) VALUES (?, ?, ?, ?, ?, ?)'
                    );
                    if (!$insert_stmt) {
                        throw new RuntimeException($conn->error);
                    }
                    $insert_stmt->bind_param(
                        'ssssss',
                        $form_nama,
                        $form_username,
                        $form_email,
                        $password_hash,
                        $teacher_role,
                        $form_avatar
                    );
                    $insert_stmt->execute();
                    $insert_stmt->close();

                    $_SESSION['kelola_guru_flash'] = [
                        'message' => "Akun guru untuk \"$form_nama\" berhasil dibuat.",
                        'type' => 'success',
                    ];
                    redirect('kelola_guru.php');
                }
            } catch (mysqli_sql_exception | RuntimeException $exception) {
                error_log('Gagal membuat akun guru: ' . $exception->getMessage());
                $pesan = $exception instanceof mysqli_sql_exception && $exception->getCode() === 1062
                    ? 'Username atau email sudah terdaftar di sistem.'
                    : 'Gagal membuat akun guru. Periksa log server untuk detail kesalahan.';
                $pesan_type = 'error';
            }
        }
    } elseif (isset($_POST['hapus_guru'])) {
        $hapus_id = filter_input(INPUT_POST, 'guru_id', FILTER_VALIDATE_INT);
        if (!$hapus_id || $hapus_id < 1) {
            $pesan = 'Akun guru yang dipilih tidak valid.';
            $pesan_type = 'error';
        } elseif ($hapus_id === (int) $_SESSION['user_id']) {
            $pesan = 'Anda tidak dapat menghapus akun sendiri.';
            $pesan_type = 'error';
        } else {
            try {
                $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role IN ('guru', 'admin')");
                if (!$delete_stmt) {
                    throw new RuntimeException($conn->error);
                }
                $delete_stmt->bind_param('i', $hapus_id);
                $delete_stmt->execute();
                $deleted = $delete_stmt->affected_rows > 0;
                $delete_stmt->close();

                $_SESSION['kelola_guru_flash'] = [
                    'message' => $deleted ? 'Akun guru berhasil dihapus.' : 'Akun guru tidak ditemukan.',
                    'type' => $deleted ? 'success' : 'error',
                ];
                redirect('kelola_guru.php');
            } catch (mysqli_sql_exception | RuntimeException $exception) {
                error_log('Gagal menghapus akun guru: ' . $exception->getMessage());
                $pesan = 'Gagal menghapus akun guru. Periksa log server untuk detail kesalahan.';
                $pesan_type = 'error';
            }
        }
    }
}

$daftar_guru = $conn->query(
    "SELECT id, nama, username, email, role, created_at
     FROM users
     WHERE role IN ('guru', 'admin', 'kepsek')
     ORDER BY CASE WHEN role = 'kepsek' THEN 0 ELSE 1 END, nama ASC"
);
if (!$daftar_guru) {
    throw new RuntimeException('Gagal mengambil daftar guru: ' . $conn->error);
}
$total_guru = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role IN ('guru', 'admin')");
if (!$total_guru) {
    throw new RuntimeException('Gagal menghitung akun guru: ' . $conn->error);
}
$total_guru = (int) $total_guru->fetch_assoc()['total'];

require_once '../includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-6xl mx-auto">
        <section class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-8 text-white mb-8 shadow-xl">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <span class="bg-indigo-500/30 text-indigo-300 border border-indigo-400/30 text-xs font-black px-3.5 py-1.5 rounded-full uppercase tracking-wider">
                        👑 Otoritas Kepala Sekolah
                    </span>
                    <h1 class="text-3xl md:text-4xl font-black mt-3">Manajemen Akun Guru 👨‍🏫</h1>
                    <p class="text-indigo-200/80 font-semibold mt-1">Daftarkan dan kelola akun tenaga pendidik.</p>
                </div>
                <div class="bg-white/10 px-6 py-4 rounded-2xl border border-white/10 text-center min-w-[140px]">
                    <div class="text-3xl font-black text-indigo-300"><?= number_format($total_guru) ?></div>
                    <div class="text-xs font-bold text-gray-300 uppercase tracking-wider mt-1">Total Guru</div>
                </div>
            </div>
        </section>

        <?php if ($pesan): ?>
            <div class="mb-6 px-6 py-4 rounded-2xl font-bold flex items-center gap-3
                <?= $pesan_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>"
                role="status">
                <span><?= $pesan_type === 'success' ? '✅' : '❌' ?></span>
                <span><?= htmlspecialchars($pesan, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <section class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 lg:sticky lg:top-24">
                    <h2 class="font-black text-gray-800 text-lg mb-2">➕ Daftarkan Guru Baru</h2>
                    <p class="text-xs text-gray-400 font-semibold mb-6">Buat akun resmi untuk guru pengajar.</p>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                        <div>
                            <label for="nama" class="block font-black text-gray-700 text-xs mb-1">Nama Lengkap Guru</label>
                            <input id="nama" type="text" name="nama" class="form-input text-sm" minlength="3" maxlength="100"
                                   value="<?= htmlspecialchars($form_nama, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div>
                            <label for="username" class="block font-black text-gray-700 text-xs mb-1">Username Login</label>
                            <input id="username" type="text" name="username" class="form-input text-sm" minlength="4" maxlength="50"
                                   pattern="[A-Za-z0-9_.-]+" value="<?= htmlspecialchars($form_username, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div>
                            <label for="email" class="block font-black text-gray-700 text-xs mb-1">Alamat Email Guru</label>
                            <input id="email" type="email" name="email" class="form-input text-sm" maxlength="100"
                                   value="<?= htmlspecialchars($form_email, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div>
                            <label for="password" class="block font-black text-gray-700 text-xs mb-1">Password Sementara</label>
                            <input id="password" type="password" name="password" class="form-input text-sm" minlength="6" required>
                            <span class="text-[10px] text-gray-400 font-bold">Minimal 6 karakter.</span>
                        </div>
                        <div>
                            <label for="avatar" class="block font-black text-gray-700 text-xs mb-1">Avatar Guru</label>
                            <select id="avatar" name="avatar" class="form-input text-sm font-bold">
                                <?php foreach (['👨‍🏫' => 'Guru Laki-laki', '👩‍🏫' => 'Guru Perempuan', '🧑‍💻' => 'Pembina Koding'] as $avatar => $label): ?>
                                    <option value="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" <?= $form_avatar === $avatar ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($avatar . ' ' . $label, ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="tambah_guru" value="1"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3.5 rounded-xl shadow-md transition-all text-sm">
                            💾 Daftarkan Akun Guru
                        </button>
                    </form>
                </div>
            </section>

            <section class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 bg-gray-50 border-b border-gray-100">
                        <h2 class="font-black text-gray-800">👥 Tenaga Pendidik Terdaftar</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <?php if ($daftar_guru->num_rows === 0): ?>
                            <p class="p-6 text-gray-500 font-semibold">Belum ada akun guru terdaftar.</p>
                        <?php else: ?>
                            <?php while ($guru = $daftar_guru->fetch_assoc()): ?>
                                <?php $is_kepsek = $guru['role'] === 'kepsek'; ?>
                                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-gray-50">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-black text-gray-800"><?= htmlspecialchars($guru['nama'], ENT_QUOTES, 'UTF-8') ?></h3>
                                            <span class="text-[10px] font-black uppercase px-2 py-1 rounded-full <?= $is_kepsek ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' ?>">
                                                <?= $is_kepsek ? 'Kepala Sekolah' : 'Guru' ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 font-semibold">
                                            @<?= htmlspecialchars($guru['username'], ENT_QUOTES, 'UTF-8') ?>
                                            · <?= htmlspecialchars($guru['email'], ENT_QUOTES, 'UTF-8') ?>
                                        </p>
                                    </div>
                                    <?php if (!$is_kepsek): ?>
                                        <form method="POST" onsubmit="return confirm('Hapus akun guru ini? Tindakan ini tidak dapat dibatalkan.');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="guru_id" value="<?= (int) $guru['id'] ?>">
                                            <button type="submit" name="hapus_guru" value="1"
                                                    class="text-sm font-black text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl">
                                                🗑️ Hapus
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>
