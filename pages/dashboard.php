<?php
$page_title = 'Dashboard';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

requireDatabaseConnection();

$current_user_id = (int) $_SESSION['user_id'];
$role_stmt = $conn->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
$role_stmt->bind_param('i', $current_user_id);
$role_stmt->execute();
$current_user = $role_stmt->get_result()->fetch_assoc();
$role_stmt->close();

if (!$current_user) {
    $_SESSION = [];
    session_destroy();
    redirect('login.php');
}

$current_role = strtolower((string) $current_user['role']);
$_SESSION['user_role'] = $current_role;
$is_teacher_preview = in_array($current_role, ['guru', 'admin'], true);

if ($current_role !== 'siswa' && !$is_teacher_preview) {
    redirect('login.php');
}

$uid = $current_user_id;
if ($is_teacher_preview) {
    $student_id = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT);
    if (!$student_id || $student_id < 1) {
        redirect('guru.php#daftar-siswa');
    }

    $student_stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role = 'siswa' LIMIT 1");
    $student_stmt->bind_param('i', $student_id);
    $student_stmt->execute();
    $user = $student_stmt->get_result()->fetch_assoc();
    $student_stmt->close();

    if (!$user) {
        redirect('guru.php#daftar-siswa');
    }
    $uid = (int) $user['id'];
} else {
    $user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
}

require_once '../includes/header.php';

$dashboard_errors = [];
$total_materi = null;
$materi_result = $conn->query("SELECT COUNT(*) as c FROM materi");
if ($materi_result === false) {
    error_log('Dashboard materi count failed: ' . $conn->error);
    $dashboard_errors[] = 'Statistik materi tidak dapat dimuat.';
} else {
    $materi_row = $materi_result->fetch_assoc();
    if ($materi_row && isset($materi_row['c'])) {
        $total_materi = (int) $materi_row['c'];
    } else {
        error_log('Dashboard materi count returned no result.');
        $dashboard_errors[] = 'Statistik materi tidak dapat dimuat.';
    }
}

$selesai = null;
$progress_result = $conn->query("SELECT COUNT(*) as c FROM progress WHERE user_id=$uid AND selesai=1");
if ($progress_result === false) {
    error_log('Dashboard progress count failed: ' . $conn->error);
    $dashboard_errors[] = 'Progress belajar tidak dapat dimuat.';
} else {
    $progress_row = $progress_result->fetch_assoc();
    if ($progress_row && isset($progress_row['c'])) {
        $selesai = (int) $progress_row['c'];
    } else {
        error_log('Dashboard progress count returned no result.');
        $dashboard_errors[] = 'Progress belajar tidak dapat dimuat.';
    }
}

$persentase = $total_materi !== null && $selesai !== null
    ? ($total_materi > 0 ? round(($selesai / $total_materi) * 100) : 0)
    : null;
$badges = $conn->query("SELECT b.* FROM badges b JOIN user_badges ub ON b.id = ub.badge_id WHERE ub.user_id = $uid");
if ($badges === false) {
    error_log('Dashboard badges query failed: ' . $conn->error);
    $dashboard_errors[] = 'Koleksi badge tidak dapat dimuat.';
}

$rank_query = $conn->query("SELECT COUNT(*)+1 as rank FROM users WHERE role='siswa' AND poin > {$user['poin']}");
$rank = null;
if ($rank_query === false) {
    error_log('Dashboard rank query failed: ' . $conn->error);
    $dashboard_errors[] = 'Peringkat tidak dapat dimuat.';
} else {
    $rank_row = $rank_query->fetch_assoc();
    if ($rank_row && isset($rank_row['rank'])) {
        $rank = (int) $rank_row['rank'];
    } else {
        error_log('Dashboard rank query returned no result.');
        $dashboard_errors[] = 'Peringkat tidak dapat dimuat.';
    }
}
?>

<div class="bg-gradient-to-r from-primary to-blue-500 py-12 px-4 text-white shadow-inner relative overflow-hidden">
    <div class="absolute -right-10 -top-10 text-9xl opacity-10 rotate-12">🚀</div>
    <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center gap-8 relative z-10">

        <!-- ⭐ AVATAR PAKAI renderAvatar() -->
        <div class="text-6xl bg-white p-2 rounded-3xl border-4 border-white/40 shadow-lg flex items-center justify-center w-32 h-32 overflow-hidden">
            <?= renderAvatar($user['avatar'], '100%', 'contain') ?>
        </div>

        <div class="text-center md:text-left flex-1">
            <h1 class="text-3xl md:text-5xl font-black mb-2 tracking-tight">Halo, <?= htmlspecialchars(explode(' ', $user['nama'])[0]) ?>!</h1>
            <p class="text-blue-100 font-semibold text-lg">Selamat datang di markas rahasiamu. Siap untuk petualangan koding hari ini?</p>
        </div>

        <div class="bg-white p-4 rounded-3xl border-2 border-white text-center min-w-[160px] shadow-xl transform hover:scale-105 transition-transform">
            <div class="text-sm font-black text-indigo-700 uppercase tracking-wider mb-1">Total Poin</div>
            <div class="text-4xl font-black text-gray-900 drop-shadow-sm"><span class="text-yellow-500">⭐</span> <?= number_format($user['poin']) ?></div>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-10">
    <?php if ($dashboard_errors): ?>
    <div role="alert" class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800">
        Sebagian data dashboard gagal dimuat. <?= htmlspecialchars(implode(' ', array_unique($dashboard_errors)), ENT_QUOTES, 'UTF-8') ?> Silakan coba lagi nanti.
    </div>
    <?php endif; ?>

    <?php if ($is_teacher_preview): ?>
    <div class="mb-8 flex flex-col gap-3 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-blue-900 sm:flex-row sm:items-center sm:justify-between">
        <p class="font-bold">👀 Mode lihat guru: dashboard <?= htmlspecialchars($user['nama']) ?>. Data ini hanya untuk dilihat.</p>
        <a href="guru.php#daftar-siswa" class="font-black underline hover:text-blue-700">← Kembali ke daftar siswa</a>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 space-y-8">

            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 flex items-center gap-2"><span>📈</span> Progress Belajarmu</h2>
                        <p class="text-gray-500 font-semibold mt-1">
                            <?php if ($selesai !== null && $total_materi !== null): ?>
                                Kamu sudah menyelesaikan <?= $selesai ?> dari <?= $total_materi ?> materi koding.
                            <?php else: ?>
                                Progress belajar belum tersedia saat ini.
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl font-black border border-blue-100">
                        Peringkat #<?= $rank !== null ? $rank : '—' ?> 🏆
                    </div>
                </div>
                <div class="mb-2 flex justify-between text-sm font-black text-gray-600">
                    <span>Mulai</span>
                    <span><?= $persentase !== null ? $persentase . '% Tuntas!' : 'Tidak tersedia' ?></span>
                </div>
                <?php if ($persentase !== null): ?>
                    <div class="h-6 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                        <div class="h-full bg-gradient-to-r from-green-400 to-green-500 rounded-full transition-all duration-1000 flex items-center justify-end px-2" style="width: <?= $persentase ?>%">
                            <?php if ($persentase > 10): ?><span class="text-white text-xs">🚀</span><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-3xl p-6 md:p-8 border border-purple-100 flex flex-col md:flex-row items-center gap-6">
                <div class="text-6xl drop-shadow-md animate-float">💻</div>
                <div class="flex-1 text-center md:text-left">
                    <h3 class="text-xl font-black text-gray-800 mb-2">Ayo Lanjutkan Petualanganmu!</h3>
                    <p class="text-gray-600 font-semibold mb-4 text-sm">Masih banyak lencana dan poin yang menunggumu. Jangan menyerah!</p>
                    <a href="materi.php" class="btn-accent inline-block px-8 py-3 shadow-lg">Mulai Belajar Sekarang →</a>
                </div>
            </div>
        </div>

        <div class="space-y-8">

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-black text-gray-800 mb-4 flex items-center gap-2"><span>⚡</span> Menu Cepat</h3>
                <div class="flex flex-col gap-3">
                    <a href="materi.php" class="btn-primary text-center py-3.5 flex justify-center items-center gap-2"><span>📚</span> Lihat Materi</a>
                    <a href="leaderboard.php" class="bg-gray-100 text-gray-700 font-black text-center py-3.5 rounded-xl hover:bg-gray-200 transition-colors flex justify-center items-center gap-2"><span>🏆</span> Papan Peringkat</a>
                    <?php if (!$is_teacher_preview): ?>
                    <a href="profile.php" class="bg-white text-gray-700 font-black text-center py-3.5 rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors flex justify-center items-center gap-2"><span>⚙️</span> Edit Profil & Foto</a>
                    <a href="toko.php" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white font-black text-center py-3.5 rounded-xl hover:shadow-lg transition-all flex justify-center items-center gap-2"><span>🏪</span> Toko Poin</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-black text-gray-800 flex items-center gap-2"><span>🏅</span> Koleksi Badge</h3>
                    <span class="bg-yellow-100 text-yellow-700 font-bold text-xs px-2 py-1 rounded-md"><?= $badges !== false ? $badges->num_rows : '—' ?> Didapat</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <?php if ($badges === false): ?>
                        <div class="col-span-3 text-center text-gray-400 font-semibold py-4 text-sm">Koleksi badge tidak dapat dimuat saat ini.</div>
                    <?php elseif ($badges->num_rows == 0): ?>
                        <div class="col-span-3 text-center text-gray-400 font-semibold py-4 text-sm">Belum ada badge.<br>Ayo selesaikan kuis!</div>
                    <?php else: while($b = $badges->fetch_assoc()): ?>
                        <div class="bg-gray-50 rounded-2xl p-3 text-center border border-gray-100 group hover:border-yellow-300 hover:bg-yellow-50 transition-colors cursor-help" title="<?= htmlspecialchars($b['deskripsi']) ?>">
                            <div class="text-3xl mb-1 group-hover:scale-110 transition-transform"><?= $b['icon'] ?></div>
                            <div class="text-[10px] font-black text-gray-600 uppercase tracking-wide truncate"><?= htmlspecialchars($b['nama']) ?></div>
                        </div>
                    <?php endwhile; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>