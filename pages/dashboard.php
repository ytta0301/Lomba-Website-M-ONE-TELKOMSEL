<?php
$page_title = 'Dashboard';
require_once '../includes/header.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'siswa') {
    redirect('login.php');
}

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

$total_materi = $conn->query("SELECT COUNT(*) as c FROM materi")->fetch_assoc()['c'];
$selesai = $conn->query("SELECT COUNT(*) as c FROM progress WHERE user_id=$uid AND selesai=1")->fetch_assoc()['c'];
$persentase = $total_materi > 0 ? round(($selesai / $total_materi) * 100) : 0;

$badges = $conn->query("SELECT b.* FROM badges b JOIN user_badges ub ON b.id = ub.badge_id WHERE ub.user_id = $uid");

$rank_query = $conn->query("SELECT COUNT(*)+1 as rank FROM users WHERE role='siswa' AND poin > {$user['poin']}");
$rank = $rank_query->fetch_assoc()['rank'];
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
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <div class="lg:col-span-2 space-y-8">

            <div class="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-100">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-gray-800 flex items-center gap-2"><span>📈</span> Progress Belajarmu</h2>
                        <p class="text-gray-500 font-semibold mt-1">Kamu sudah menyelesaikan <?= $selesai ?> dari <?= $total_materi ?> materi koding.</p>
                    </div>
                    <div class="bg-blue-50 text-blue-600 px-4 py-2 rounded-xl font-black border border-blue-100">
                        Peringkat #<?= $rank ?> 🏆
                    </div>
                </div>
                <div class="mb-2 flex justify-between text-sm font-black text-gray-600">
                    <span>Mulai</span>
                    <span><?= $persentase ?>% Tuntas!</span>
                </div>
                <div class="h-6 bg-gray-100 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-green-400 to-green-500 rounded-full transition-all duration-1000 flex items-center justify-end px-2" style="width: <?= $persentase ?>%">
                        <?php if($persentase > 10): ?><span class="text-white text-xs">🚀</span><?php endif; ?>
                    </div>
                </div>
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
                    <a href="profile.php" class="bg-white text-gray-700 font-black text-center py-3.5 rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors flex justify-center items-center gap-2"><span>⚙️</span> Edit Profil & Foto</a>
                    <a href="toko.php" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white font-black text-center py-3.5 rounded-xl hover:shadow-lg transition-all flex justify-center items-center gap-2"><span>🏪</span> Toko Poin</a>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="font-black text-gray-800 flex items-center gap-2"><span>🏅</span> Koleksi Badge</h3>
                    <span class="bg-yellow-100 text-yellow-700 font-bold text-xs px-2 py-1 rounded-md"><?= $badges->num_rows ?> Didapat</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <?php if ($badges->num_rows == 0): ?>
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