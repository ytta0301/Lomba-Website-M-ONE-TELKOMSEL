<?php
$page_title = 'Panel Guru';

require_once '../config/db.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isLoggedIn()) {
    redirect('login.php');
}

requireDatabaseConnection();

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
if (!in_array($user_role, ['guru', 'admin', 'kepsek'], true)) {
    redirect('dashboard.php');
}

$can_manage_materi = in_array($user_role, ['guru', 'admin'], true);
if (!$can_manage_materi && $_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(403);
    exit('Akses ditolak. Kepala Sekolah hanya dapat melihat panel ini.');
}

$csrf_token = $_SESSION['csrf_token'] ?? '';
if ($csrf_token === '') {
    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token;
}

$materi_message = '';
$materi_message_type = '';
$items_per_page = 5;
$student_page = max(1, filter_input(INPUT_GET, 'student_page', FILTER_VALIDATE_INT) ?: 1);
$materi_page = max(1, filter_input(INPUT_GET, 'materi_page', FILTER_VALIDATE_INT) ?: 1);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_materi'])) {
    $posted_student_page = filter_input(INPUT_POST, 'student_page', FILTER_VALIDATE_INT);
    $posted_materi_page = filter_input(INPUT_POST, 'materi_page', FILTER_VALIDATE_INT);
    $student_page = max(1, $posted_student_page ?: 1);
    $materi_page = max(1, $posted_materi_page ?: 1);
    $posted_token = $_POST['csrf_token'] ?? '';
    $materi_id = filter_input(INPUT_POST, 'materi_id', FILTER_VALIDATE_INT);

    if (!is_string($posted_token) || !hash_equals($csrf_token, $posted_token)) {
        $materi_message = 'Permintaan tidak valid atau sesi sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        $materi_message_type = 'error';
    } elseif (!$materi_id || $materi_id < 1) {
        $materi_message = 'Materi yang akan dihapus tidak valid.';
        $materi_message_type = 'error';
    } else {
        try {
            $conn->begin_transaction();
            foreach (['kuis', 'progress', 'nilai_kuis'] as $table) {
                $delete_stmt = $conn->prepare("DELETE FROM $table WHERE materi_id = ?");
                if (!$delete_stmt) {
                    throw new RuntimeException($conn->error);
                }
                $delete_stmt->bind_param('i', $materi_id);
                $delete_stmt->execute();
                $delete_stmt->close();
            }

            $delete_stmt = $conn->prepare('DELETE FROM materi WHERE id = ?');
            if (!$delete_stmt) {
                throw new RuntimeException($conn->error);
            }
            $delete_stmt->bind_param('i', $materi_id);
            $delete_stmt->execute();
            $deleted = $delete_stmt->affected_rows > 0;
            $delete_stmt->close();
            $conn->commit();

            $materi_message = $deleted ? 'Materi dan data terkait berhasil dihapus.' : 'Materi tidak ditemukan.';
            $materi_message_type = $deleted ? 'success' : 'error';
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            $conn->rollback();
            error_log('Gagal menghapus materi dari dashboard guru: ' . $exception->getMessage());
            $materi_message = 'Gagal menghapus materi. Periksa log server untuk detail kesalahan.';
            $materi_message_type = 'error';
        }
    }
}

require_once '../includes/header.php';

// --- Hitung Statistik Global ---
$total_siswa = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='siswa'")->fetch_assoc()['c'];
$student_total_pages = max(1, (int) ceil($total_siswa / $items_per_page));
$student_page = min($student_page, $student_total_pages);
$student_offset = ($student_page - 1) * $items_per_page;
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
    LIMIT $items_per_page OFFSET $student_offset
");

// --- Ambil Aktivitas Kuis Terbaru ---
$aktivitas = $conn->query("
    SELECT n.*, u.nama, u.avatar, m.judul 
    FROM nilai_kuis n
    JOIN users u ON n.user_id = u.id
    JOIN materi m ON n.materi_id = m.id
    ORDER BY n.created_at DESC LIMIT 5
");

$total_materi = (int) $conn->query('SELECT COUNT(*) AS c FROM materi')->fetch_assoc()['c'];
$materi_total_pages = max(1, (int) ceil($total_materi / $items_per_page));
$materi_page = min($materi_page, $materi_total_pages);
$materi_offset = ($materi_page - 1) * $items_per_page;
$materi_list = $conn->query("
    SELECT m.id, m.judul, m.level, m.icon, m.warna, m.urutan,
        (SELECT COUNT(*) FROM kuis WHERE materi_id = m.id) AS total_kuis
    FROM materi m
    ORDER BY m.urutan ASC, m.id DESC
    LIMIT $items_per_page OFFSET $materi_offset
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
        <div class="flex flex-wrap items-center gap-3">
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'kepsek'): ?>
                <a href="kelola_guru.php" class="bg-gradient-to-r from-slate-800 to-indigo-900 text-white font-black py-3 px-6 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2 border border-indigo-400/30">
                    <span>👑</span> Panel Kepsek (Kelola Guru)
                </a>
            <?php endif; ?>
            <?php if ($can_manage_materi): ?>
                <a href="kelola_materi.php" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-black py-3 px-6 rounded-xl hover:shadow-lg transition-all flex items-center justify-center gap-2">
                    <span>📝</span> Tambah Materi & Kuis
                </a>
            <?php endif; ?>
        </div>
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
            <div id="daftar-siswa" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
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
                                <th class="p-4 border-b border-gray-100 text-right">Dashboard</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php if ($siswa_list->num_rows == 0): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-gray-400 font-semibold">Belum ada siswa yang mendaftar.</td>
                            </tr>
                            <?php else: while ($s = $siswa_list->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="text-2xl w-10 h-10 bg-white border border-gray-100 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0">
                                            <?= renderAvatar($s['avatar'], '2rem') ?>
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
                                <td class="p-4 text-right">
                                    <a href="dashboard.php?student_id=<?= (int) $s['id'] ?>" class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 hover:bg-blue-100 transition-colors">
                                        👀 Lihat
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($student_total_pages > 1): ?>
                <nav class="flex flex-wrap items-center justify-center gap-2 border-t border-gray-100 p-4" aria-label="Halaman daftar murid">
                    <?php if ($student_page > 1): ?>
                    <a href="?student_page=<?= $student_page - 1 ?>&materi_page=<?= $materi_page ?>#daftar-siswa" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">← Sebelumnya</a>
                    <?php endif; ?>
                    <?php
                    $student_page_start = max(1, $student_page - 2);
                    $student_page_end = min($student_total_pages, $student_page + 2);
                    for ($page = $student_page_start; $page <= $student_page_end; $page++):
                    ?>
                    <a href="?student_page=<?= $page ?>&materi_page=<?= $materi_page ?>#daftar-siswa" class="rounded-lg px-3 py-2 text-sm font-black <?= $page === $student_page ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>" <?= $page === $student_page ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                    <?php endfor; ?>
                    <?php if ($student_page < $student_total_pages): ?>
                    <a href="?student_page=<?= $student_page + 1 ?>&materi_page=<?= $materi_page ?>#daftar-siswa" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">Berikutnya →</a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
            </div>
            <section id="materi-tayang" class="mt-8 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-black text-gray-800 text-lg flex items-center gap-2"><span aria-hidden="true">📚</span> Materi yang Tayang</h2>
                    <div class="flex items-center gap-3">
                        <span class="text-xs bg-purple-100 text-primary font-bold px-3 py-1 rounded-full"><?= $total_materi ?> Materi</span>
                        <?php if ($can_manage_materi): ?>
                            <a href="kelola_materi.php" class="text-xs font-black text-indigo-700 hover:text-purple-700">➕ Tambah Materi</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($materi_message !== ''): ?>
                <div role="status" class="mx-5 mt-5 px-4 py-3 rounded-xl font-bold <?= $materi_message_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
                    <?= htmlspecialchars($materi_message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                </div>
                <?php endif; ?>
                <div class="divide-y divide-gray-100">
                    <?php if ($materi_list->num_rows === 0): ?>
                    <p class="p-6 text-center text-gray-500 font-semibold">Belum ada materi.</p>
                    <?php else: while ($materi = $materi_list->fetch_assoc()): ?>
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0" style="background: <?= htmlspecialchars($materi['warna'], ENT_QUOTES, 'UTF-8') ?>22">
                                <?= htmlspecialchars($materi['icon'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-black text-gray-800 text-base"><?= htmlspecialchars($materi['judul'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
                                <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-gray-500 mt-1">
                                    <span class="text-primary">Urutan #<?= (int) $materi['urutan'] ?></span>
                                    <span aria-hidden="true">•</span>
                                    <span><?= htmlspecialchars($materi['level'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                    <span aria-hidden="true">•</span>
                                    <span class="text-yellow-700">🧩 <?= (int) $materi['total_kuis'] ?> soal</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="materi_detail.php?id=<?= (int) $materi['id'] ?>" target="_blank" rel="noopener" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">👁️ Lihat</a>
                            <?php if ($can_manage_materi): ?>
                                <form method="POST" onsubmit="return confirm('Hapus materi ini beserta seluruh kuis dan progress siswa terkait?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="materi_id" value="<?= (int) $materi['id'] ?>">
                                    <input type="hidden" name="student_page" value="<?= $student_page ?>">
                                    <input type="hidden" name="materi_page" value="<?= $materi_page ?>">
                                    <button type="submit" name="hapus_materi" value="1" class="bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">🗑️ Hapus</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endwhile; endif; ?>
                </div>
                <?php if ($materi_total_pages > 1): ?>
                <nav class="flex flex-wrap items-center justify-center gap-2 border-t border-gray-100 p-4" aria-label="Halaman daftar materi">
                    <?php if ($materi_page > 1): ?>
                    <a href="?student_page=<?= $student_page ?>&materi_page=<?= $materi_page - 1 ?>#materi-tayang" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">← Sebelumnya</a>
                    <?php endif; ?>
                    <?php
                    $materi_page_start = max(1, $materi_page - 2);
                    $materi_page_end = min($materi_total_pages, $materi_page + 2);
                    for ($page = $materi_page_start; $page <= $materi_page_end; $page++):
                    ?>
                    <a href="?student_page=<?= $student_page ?>&materi_page=<?= $page ?>#materi-tayang" class="rounded-lg px-3 py-2 text-sm font-black <?= $page === $materi_page ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>" <?= $page === $materi_page ? 'aria-current="page"' : '' ?>><?= $page ?></a>
                    <?php endfor; ?>
                    <?php if ($materi_page < $materi_total_pages): ?>
                    <a href="?student_page=<?= $student_page ?>&materi_page=<?= $materi_page + 1 ?>#materi-tayang" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-bold text-gray-700 hover:bg-gray-200">Berikutnya →</a>
                    <?php endif; ?>
                </nav>
                <?php endif; ?>
            </section>
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
                        <div class="text-2xl flex-shrink-0"><?= renderAvatar($a['avatar'], '2rem') ?></div>
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