<?php
$page_title = 'Leaderboard';
require_once '../includes/header.php';

// Ambil data user, poin, jumlah materi selesai, dan jumlah kuis
// Hanya ambil user dengan role 'siswa', urutkan berdasarkan poin tertinggi
$users = $conn->query("SELECT u.nama, u.username, u.poin, u.avatar,
    (SELECT COUNT(*) FROM progress WHERE user_id=u.id AND selesai=1) as materi_selesai,
    (SELECT COUNT(*) FROM nilai_kuis WHERE user_id=u.id) as total_kuis
    FROM users u 
    WHERE u.role='siswa'
    ORDER BY u.poin DESC 
    LIMIT 50");

$rank_icons = ['🥇','🥈','🥉'];
$current_user_rank = null;

// Jika user (siswa) sedang login, cari tahu dia ada di peringkat berapa
if (isLoggedIn() && $_SESSION['user_role'] === 'siswa') {
    $uid = $_SESSION['user_id'];
    $r = $conn->query("SELECT COUNT(*)+1 as r FROM users WHERE poin > (SELECT poin FROM users WHERE id=$uid) AND role='siswa'")->fetch_assoc();
    $current_user_rank = $r['r'];
}
?>

<!-- PAGE HEADER -->
<div class="bg-gradient-to-br from-blue-50 via-white to-red-50 py-16 px-4 text-gray-800 text-center relative overflow-hidden shadow-inner">
    <!-- Hiasan Background -->
    <div class="absolute -left-10 top-10 text-9xl opacity-10 rotate-12">🏅</div>
    <div class="absolute -right-10 bottom-0 text-9xl opacity-10 -rotate-12">⭐</div>
    <div class="absolute top-0 right-1/4 text-6xl opacity-20 animate-bounce-slow">✨</div>

    <div class="relative z-10">
        <div class="text-7xl mb-4 animate-pop drop-shadow-lg">🏆</div>
        <h1 class="text-5xl font-black mb-3 tracking-tight"><span class="text-blue-700">Leader</span><span class="text-red-600">board</span></h1>
        <p class="font-bold text-lg max-w-lg mx-auto">
            <span class="text-blue-700">Siapa programmer cilik paling rajin minggu ini?</span>
            <span class="text-red-600">Kumpulkan poin dan jadilah nomor satu! 🚀</span>
        </p>
        
        <?php if ($current_user_rank): ?>
        <div class="inline-flex items-center gap-2 mt-6 bg-white text-orange-600 px-6 py-2 rounded-full font-black text-lg shadow-lg animate-fadeInUp">
            <span>Peringkatmu saat ini:</span>
            <span class="text-2xl">#<?= $current_user_rank ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-12">
    
    <?php
    $all_users = $users->fetch_all(MYSQLI_ASSOC);
    $top3 = array_slice($all_users, 0, 3);
    // Urutan podium visual: Juara 2 di kiri, Juara 1 di tengah, Juara 3 di kanan
    $podium_order = count($top3) >= 3 ? [1, 0, 2] : array_keys($top3); 
    ?>
    
    <!-- TOP 3 PODIUM -->
    <?php if (count($top3) >= 2): ?>
    <div class="flex justify-center items-end gap-2 sm:gap-6 mb-16 mt-8">
        <?php foreach ($podium_order as $idx):
            if (!isset($top3[$idx])) continue;
            $u = $top3[$idx];
            
            // Konfigurasi visual berdasarkan rank (1, 2, 3)
            $rank_num = $idx + 1;
            
            if ($rank_num == 1) {
                $height = 'h-40 sm:h-48';
                $color = 'bg-gradient-to-t from-yellow-500 to-yellow-300';
                $border = 'border-yellow-400';
                $text_color = 'text-yellow-600';
                $shadow = 'shadow-[0_0_30px_rgba(250,204,21,0.5)] z-10';
                $avatar_size = 'text-6xl sm:text-7xl';
            } elseif ($rank_num == 2) {
                $height = 'h-32 sm:h-36';
                $color = 'bg-gradient-to-t from-gray-400 to-gray-200';
                $border = 'border-gray-300';
                $text_color = 'text-gray-600';
                $shadow = 'shadow-lg';
                $avatar_size = 'text-5xl sm:text-6xl';
            } else { // Rank 3
                $height = 'h-24 sm:h-28';
                $color = 'bg-gradient-to-t from-orange-500 to-orange-300';
                $border = 'border-orange-400';
                $text_color = 'text-orange-700';
                $shadow = 'shadow-md';
                $avatar_size = 'text-4xl sm:text-5xl';
            }
        ?>
        <div class="flex flex-col items-center relative <?= $rank_num == 1 ? 'transform -translate-y-4' : '' ?>">
            <!-- Confetti untuk juara 1 -->
            <?php if ($rank_num == 1): ?>
                <div class="absolute -top-8 text-2xl animate-bounce-slow">✨</div>
            <?php endif; ?>

            <div class="<?= $avatar_size ?> mb-2 drop-shadow-md animate-float" style="animation-delay: <?= $idx*0.2 ?>s"><?= $u['avatar'] ?></div>
            <div class="text-3xl sm:text-4xl absolute -top-4 sm:-top-6 right-0 drop-shadow-sm z-20"><?= $rank_icons[$idx] ?></div>
            
            <!-- Nama & Poin di atas balok -->
            <div class="bg-white px-3 py-1.5 rounded-xl shadow-sm border border-gray-100 mb-3 text-center min-w-[80px] sm:min-w-[100px] relative z-20">
                <div class="font-black text-gray-800 text-xs sm:text-sm truncate max-w-[80px] sm:max-w-[100px]"><?= htmlspecialchars(explode(' ',$u['nama'])[0]) ?></div>
                <div class="font-black <?= $text_color ?> text-xs">⭐<?= number_format($u['poin']) ?></div>
            </div>
            
            <!-- Balok Podium -->
            <div class="<?= $height ?> <?= $color ?> border-t-4 <?= $border ?> w-20 sm:w-28 rounded-t-2xl flex items-end justify-center pb-4 font-black text-white/80 text-3xl sm:text-5xl <?= $shadow ?>">
                <?= $rank_num ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- LIST PERINGKAT LENGKAP -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex font-bold text-xs text-gray-400 uppercase tracking-wider">
            <div class="w-16 text-center">Rank</div>
            <div class="flex-1 pl-4">Siswa</div>
            <div class="w-32 hidden md:block text-center">Statistik</div>
            <div class="w-24 text-right">Poin</div>
        </div>

        <div class="divide-y divide-gray-50">
            <?php foreach ($all_users as $i => $u):
                $rank = $i + 1;
                $is_me = isLoggedIn() && isset($_SESSION['user_nama']) && $u['username'] === $_SESSION['user_nama'];
                
                // Styling khusus untuk Top 3 di list dan user yang sedang login
                $bg_row = $is_me ? 'bg-primary/5 border-l-4 border-primary' : 'bg-white border-l-4 border-transparent';
                $rank_display = "<span class='text-gray-500'>#$rank</span>";
                
                if ($rank == 1) {
                    $bg_row = 'bg-yellow-50/50 border-l-4 border-yellow-400';
                    $rank_display = "<span class='text-3xl drop-shadow-sm'>🥇</span>";
                } elseif ($rank == 2) {
                    $bg_row = 'bg-gray-50/50 border-l-4 border-gray-400';
                    $rank_display = "<span class='text-3xl drop-shadow-sm'>🥈</span>";
                } elseif ($rank == 3) {
                    $bg_row = 'bg-orange-50/30 border-l-4 border-orange-400';
                    $rank_display = "<span class='text-3xl drop-shadow-sm'>🥉</span>";
                }
            ?>
            <div class="flex items-center px-4 md:px-6 py-4 <?= $bg_row ?> transition-colors hover:bg-gray-50 group">
                
                <!-- Rank -->
                <div class="w-12 md:w-16 text-center font-black text-lg flex-shrink-0">
                    <?= $rank_display ?>
                </div>
                
                <!-- Avatar & Nama -->
                <div class="flex-1 flex items-center gap-4 pl-2 md:pl-4 min-w-0">
                    <div class="text-3xl w-12 h-12 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                        <?= $u['avatar'] ?>
                    </div>
                    <div class="min-w-0">
                        <div class="font-black text-gray-800 text-base md:text-lg truncate flex items-center gap-2">
                            <?= htmlspecialchars($u['nama']) ?>
                            <?php if ($is_me): ?>
                                <span class="bg-primary text-white text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-full">Kamu</span>
                            <?php endif; ?>
                        </div>
                        <div class="text-xs text-gray-400 font-bold">@<?= htmlspecialchars($u['username']) ?></div>
                    </div>
                </div>
                
                <!-- Statistik (Hanya Desktop) -->
                <div class="w-32 hidden md:flex flex-col items-center justify-center gap-1 text-xs text-gray-500 font-bold border-l border-r border-gray-100 px-4">
                    <div class="flex items-center gap-1"><span class="text-blue-500">📚</span> <?= $u['materi_selesai'] ?> materi</div>
                    <div class="flex items-center gap-1"><span class="text-red-500">🧩</span> <?= $u['total_kuis'] ?> kuis</div>
                </div>
                
                <!-- Poin Total -->
                <div class="w-24 text-right flex-shrink-0 pl-4">
                    <div class="font-black text-yellow-500 text-xl md:text-2xl drop-shadow-sm flex items-center justify-end gap-1">
                        <?= number_format($u['poin']) ?> <span class="text-sm">⭐</span>
                    </div>
                </div>

            </div>
            <?php endforeach; ?>

            <!-- State Kosong (Jika tidak ada siswa sama sekali) -->
            <?php if (count($all_users) == 0): ?>
            <div class="text-center py-20">
                <div class="text-6xl mb-4 grayscale opacity-40">🌟</div>
                <h3 class="font-black text-2xl text-gray-700 mb-2">Belum ada siswa</h3>
                <p class="text-gray-500 font-semibold mb-6">Jadilah pendaftar pertama dan raih peringkat #1!</p>
                <a href="register.php" class="btn-primary inline-flex items-center gap-2">
                    Daftar Sekarang 🚀
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- CTA Daftar untuk pengunjung yang belum login -->
    <?php if (!isLoggedIn() && count($all_users) > 0): ?>
    <div class="mt-12 bg-gradient-to-r from-primary to-blue-500 rounded-3xl p-8 md:p-10 text-white text-center shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -top-10 text-9xl opacity-10">🎮</div>
        
        <div class="relative z-10">
            <h3 class="text-3xl font-black mb-3">Tantang Dirimu! 💪</h3>
            <p class="text-blue-100 mb-6 font-semibold text-lg max-w-lg mx-auto">
                Daftar sekarang, kumpulkan bintang dari setiap pelajaran, dan pastikan namamu ada di papan peringkat!
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="register.php" class="bg-white text-primary font-black px-8 py-3.5 rounded-full hover:scale-105 transition-transform shadow-md">
                    Daftar Gratis Sekarang!
                </a>
                <a href="login.php" class="border-2 border-white text-white font-black px-8 py-3.5 rounded-full hover:bg-white hover:text-primary transition-colors">
                    Sudah punya akun
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>