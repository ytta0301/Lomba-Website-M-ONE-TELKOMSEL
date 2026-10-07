<?php
$page_title = 'Panel Guru';

require_once '../config/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isLoggedIn()) {
    redirect('login.php');
}

$role_stmt = $conn->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
$role_stmt->bind_param('i', $_SESSION['user_id']);
$role_stmt->execute();
$role_result = $role_stmt->get_result();
$current_user = $role_result->fetch_assoc();

if (!$current_user) {
    $_SESSION = [];
    session_destroy();
    redirect('login.php');
}

$user_role = strtolower((string) $current_user['role']);
$_SESSION['user_role'] = $user_role;
if (!in_array($user_role, ['guru', 'admin'], true)) {
    redirect('dashboard.php');
}

require_once '../includes/header.php';

// --- Hitung Statistik Global ---
$total_siswa = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='siswa'")->fetch_assoc()['c'];
$total_kuis_dikerjakan = $conn->query("SELECT COUNT(*) as c FROM nilai_kuis")->fetch_assoc()['c'];

$avg_global_query = $conn->query("SELECT AVG((skor/total_soal)*100) as a FROM nilai_kuis")->fetch_assoc();
$avg_global = $avg_global_query['a'] ? round($avg_global_query['a']) : 0;

// --- Ambil Data Semua Siswa ---
$siswa_list = $conn->query("
    SELECT u.id, u.nama, u.username, u.poin, u.avatar, u.created_at,
        (SELECT COUNT(*) FROM progress WHERE user_id=u.id AND selesai=1) as materi_selesai,
        (SELECT COUNT(*) FROM nilai_kuis WHERE user_id=u.id) as total_kuis,
        (SELECT AVG((skor/total_soal)*100) FROM nilai_kuis WHERE user_id=u.id) as avg_skor
    FROM users u 
    WHERE u.role='siswa' 
    ORDER BY u.poin DESC
");

// --- Ambil Aktivitas Kuis Terbaru ---
$aktivitas = $conn->query("
    SELECT n.*, u.nama, u.avatar, m.judul 
    FROM nilai_kuis n
    JOIN users u ON n.user_id = u.id
    JOIN materi m ON n.materi_id = m.id
    ORDER BY n.created_at DESC LIMIT 5
");
?>

<!-- HEADER GURU -->
<div class="bg-gradient-to-r from-gray-800 to-gray-900 py-12 px-4 text-white shadow-inner">
    <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-6">
        <div class="flex items-center gap-6">
            <div class="text-6xl bg-white/10 p-4 rounded-3xl border border-white/20">👨‍🏫</div>
            <div>
                <p class="text-gray-300 font-semibold mb-1 text-lg">Panel Kontrol</p>
                <h1 class="text-3xl md:text-4xl font-black mb-2">Dashboard Guru</h1>
                <p class="text-gray-400 font-medium">Pantau aktivitas dan perkembangan belajar semua siswa.</p>
            </div>
        </div>
        <a href="dashboard.php" class="btn-primary bg-white text-gray-800 hover:bg-gray-100">📊 Buka Dashboard Siswa</a>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-10">

    <!-- STATISTIK KESELURUHAN -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-3xl">👥</div>
            <div>
                <div class="text-3xl font-black text-gray-800"><?= $total_siswa ?></div>
                <div class="text-sm font-bold text-gray-400 uppercase">Total Siswa</div>
            </div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-green-50 text-green-600 flex items-center justify-center text-3xl">📝</div>
            <div>
                <div class="text-3xl font-black text-gray-800"><?= $total_kuis_dikerjakan ?></div>
                <div class="text-sm font-bold text-gray-400 uppercase">Kuis Dikerjakan</div>
            </div>
        </div>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-yellow-50 text-yellow-600 flex items-center justify-center text-3xl">📊</div>
            <div>
                <div class="text-3xl font-black text-gray-800"><?= $avg_global ?>%</div>
                <div class="text-sm font-bold text-gray-400 uppercase">Rata-rata Kelas</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- KOLOM KIRI (Tabel Siswa - Lebar 2/3) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h2 class="text-xl font-black text-gray-800 flex items-center gap-2">
                        <span>📋</span> Daftar Murid
                    </h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-400 text-xs uppercase tracking-wider font-bold">
                                <th class="p-4 border-b border-gray-100">Siswa</th>
                                <th class="p-4 border-b border-gray-100 text-center">Materi</th>
                                <th class="p-4 border-b border-gray-100 text-center">Nilai Rata²</th>
                                <th class="p-4 border-b border-gray-100 text-right">Poin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if ($siswa_list->num_rows == 0): ?>
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-400 font-semibold">Belum ada siswa yang mendaftar.</td>
                            </tr>
                            <?php else: while ($s = $siswa_list->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="text-2xl w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                                            <?= $s['avatar'] ?>
                                        </div>
                                        <div>
                                            <div class="font-black text-gray-800 text-sm"><?= htmlspecialchars($s['nama']) ?></div>
                                            <div class="text-xs text-gray-400 font-bold">@<?= htmlspecialchars($s['username']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="text-sm font-bold text-gray-700"><?= $s['materi_selesai'] ?> <span class="text-gray-400 font-normal">selesai</span></div>
                                </td>
                                <td class="p-4 text-center">
                                    <?php 
                                    $skor = $s['avg_skor'] ? round($s['avg_skor']) : 0;
                                    $warna = $skor >= 80 ? 'text-green-600 bg-green-50 border-green-200' : ($skor >= 50 ? 'text-yellow-600 bg-yellow-50 border-yellow-200' : ($skor > 0 ? 'text-red-600 bg-red-50 border-red-200' : 'text-gray-400 bg-gray-50 border-gray-200'));
                                    ?>
                                    <span class="px-2 py-1 rounded-md text-xs font-bold border <?= $warna ?>">
                                        <?= $skor > 0 ? $skor.'%' : '-' ?>
                                    </span>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="font-black text-yellow-500 text-base">⭐ <?= number_format($s['poin']) ?></div>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN (Aktivitas Terbaru - Lebar 1/3) -->
        <div>
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-black text-gray-800 mb-5 flex items-center gap-2">
                    <span>⚡</span> Aktivitas Terbaru
                </h2>
                
                <div class="space-y-4">
                    <?php if ($aktivitas->num_rows == 0): ?>
                        <p class="text-sm text-gray-400 text-center py-4 font-semibold">Belum ada aktivitas kuis.</p>
                    <?php else: while ($a = $aktivitas->fetch_assoc()): 
                        $persen = round(($a['skor']/$a['total_soal'])*100);
                        $bg = $persen >= 80 ? 'bg-green-50 border-green-100' : ($persen >= 50 ? 'bg-yellow-50 border-yellow-100' : 'bg-red-50 border-red-100');
                    ?>
                    <div class="flex gap-3 p-3 rounded-2xl border <?= $bg ?>">
                        <div class="text-2xl flex-shrink-0"><?= $a['avatar'] ?></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-black text-gray-800 truncate"><?= htmlspecialchars($a['nama']) ?></div>
                            <div class="text-xs text-gray-600 truncate mt-0.5">Mengerjakan: <?= htmlspecialchars($a['judul']) ?></div>
                            <div class="text-[10px] text-gray-400 font-bold mt-1"><?= date('d M Y, H:i', strtotime($a['created_at'])) ?></div>
                        </div>
                        <div class="font-black text-sm <?= $persen >= 80 ? 'text-green-600' : ($persen >= 50 ? 'text-yellow-600' : 'text-red-600') ?>">
                            <?= $persen ?>%
                        </div>
                    </div>
                    <?php endwhile; endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>