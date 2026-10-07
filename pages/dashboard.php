<?php
$page_title = 'Dashboard';
require_once '../includes/header.php';

// Pastikan user sudah login
if (!isLoggedIn()) redirect('../pages/login.php');

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

// --- Hitung Statistik Siswa ---
// 1. Total materi yang tersedia
$total_materi = $conn->query("SELECT COUNT(*) as c FROM materi")->fetch_assoc()['c'];

// 2. Jumlah materi yang sudah selesai (dari tabel progress)
$selesai_materi = $conn->query("SELECT COUNT(*) as c FROM progress WHERE user_id=$uid AND selesai=1")->fetch_assoc()['c'];

// 3. Jumlah kuis yang sudah pernah dikerjakan
$total_kuis_done = $conn->query("SELECT COUNT(*) as c FROM nilai_kuis WHERE user_id=$uid")->fetch_assoc()['c'];

// 4. Rata-rata persentase skor kuis
$avg_skor_query = $conn->query("SELECT AVG((skor/total_soal)*100) as a FROM nilai_kuis WHERE user_id=$uid")->fetch_assoc();
$avg_skor = $avg_skor_query['a'];

// 5. Persentase penyelesaian keseluruhan
$persen_progress = $total_materi > 0 ? round(($selesai_materi / $total_materi) * 100) : 0;

// --- Ambil Data Badge ---
// Daftar ID badge yang sudah dimiliki user
$badges_milik = $conn->query("SELECT b.* FROM badges b JOIN user_badges ub ON b.id=ub.badge_id WHERE ub.user_id=$uid");
$badge_ids = [];
while ($b = $badges_milik->fetch_assoc()) { 
    $badge_ids[] = $b['id']; 
}

// Daftar semua badge yang ada di sistem
$all_badges = $conn->query("SELECT * FROM badges ORDER BY syarat_poin");

// --- Ambil Riwayat Kuis ---
// 5 riwayat kuis terakhir
$riwayat = $conn->query("SELECT n.*, m.judul, m.icon FROM nilai_kuis n JOIN materi m ON n.materi_id=m.id WHERE n.user_id=$uid ORDER BY n.created_at DESC LIMIT 5");

// --- Ambil Progress Per Materi ---
$progress_list = $conn->query("SELECT p.*, m.judul, m.icon, m.warna FROM progress p JOIN materi m ON p.materi_id=m.id WHERE p.user_id=$uid");

// --- Hitung Ranking User Saat Ini ---
$rank_row = $conn->query("SELECT COUNT(*)+1 as r FROM users WHERE poin > {$user['poin']} AND role='siswa'")->fetch_assoc();
$rank = $rank_row['r'];
?>

<!-- PAGE HEADER -->
<div class="bg-gradient-to-r from-primary to-blue-500 py-12 px-4 text-white shadow-inner">
    <div class="max-w-6xl mx-auto flex flex-wrap items-center justify-between gap-6">
        <div class="flex flex-wrap items-center gap-6">
        <div class="text-7xl animate-bounce-slow bg-white/10 p-4 rounded-3xl border-4 border-white/20 shadow-lg">
            <?= $user['avatar'] ?>
        </div>
        <div>
            <p class="text-purple-200 font-semibold mb-1 text-lg">Selamat datang kembali!</p>
            <h1 class="text-4xl font-black mb-3 drop-shadow-md"><?= htmlspecialchars($user['nama']) ?></h1>
            <div class="flex flex-wrap gap-3 mt-3">
                <span class="bg-yellow-400 text-yellow-900 px-4 py-1.5 rounded-full text-sm font-black shadow flex items-center gap-2">
                    ⭐ <?= number_format($user['poin']) ?> Poin
                </span>
                <span class="bg-white/20 text-white border border-white/40 px-4 py-1.5 rounded-full text-sm font-bold shadow flex items-center gap-2">
                    🏆 Rank #<?= $rank ?>
                </span>
                <span class="bg-white/20 text-white border border-white/40 px-4 py-1.5 rounded-full text-sm font-bold shadow">
                    @<?= htmlspecialchars($user['username']) ?>
                </span>
            </div>
        </div>
        </div>
        <?php if (in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['guru', 'admin'], true)): ?>
            <a href="guru.php" class="btn-primary bg-white text-primary hover:bg-purple-50">👨‍🏫 Kembali ke Dashboard Guru</a>
        <?php endif; ?>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-10">
    
    <!-- STATISTIK CARDS (4 Kotak di Atas) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-10">
        <?php
        $stats = [
            ['label'=>'Poin Total','val'=>number_format($user['poin']),'icon'=>'⭐','color'=>'#FFD93D','bg'=>'#FFFBEB'],
            ['label'=>'Materi Selesai','val'=>"$selesai_materi/$total_materi",'icon'=>'📚','color'=>'#6C63FF','bg'=>'#F5F3FF'],
            ['label'=>'Kuis Dikerjakan','val'=>$total_kuis_done,'icon'=>'🧩','color'=>'#FF6B6B','bg'=>'#FFF5F5'],
            ['label'=>'Rata-rata Skor','val'=>($avg_skor ? round($avg_skor).'%' : '-'),'icon'=>'📊','color'=>'#6BCB77','bg'=>'#F0FFF4'],
        ];
        foreach ($stats as $s): ?>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 text-center animate-on-scroll hover:-translate-y-1 transition-transform duration-300">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm"
                 style="background:<?= $s['bg'] ?>">
                <?= $s['icon'] ?>
            </div>
            <div class="text-3xl font-black mb-1" style="color:<?= $s['color'] ?>"><?= $s['val'] ?></div>
            <div class="text-sm text-gray-500 font-bold uppercase tracking-wider"><?= $s['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- KOLOM KIRI (Lebar 2/3): Progress & Riwayat -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Progress Belajar -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-black text-gray-800 flex items-center gap-3">
                        <span class="text-3xl">📈</span> Progress Belajar
                    </h2>
                    <span class="bg-primary/10 text-primary px-4 py-1.5 rounded-full font-black text-sm">
                        <?= $persen_progress ?>% Selesai
                    </span>
                </div>
                
                <!-- Progress Bar Animasi -->
                <div class="progress-bar mb-8 h-4 bg-gray-100 shadow-inner">
                    <div class="progress-fill relative overflow-hidden" data-width="<?= $persen_progress ?>">
                        <div class="absolute inset-0 bg-white/20 w-full h-full" style="background-image: linear-gradient(45deg, rgba(255, 255, 255, 0.15) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.15) 50%, rgba(255, 255, 255, 0.15) 75%, transparent 75%, transparent); background-size: 1rem 1rem;"></div>
                    </div>
                </div>

                <?php if ($progress_list->num_rows == 0): ?>
                <div class="text-center bg-gray-50 rounded-2xl p-10 border border-dashed border-gray-200">
                    <div class="text-5xl mb-4 grayscale opacity-50">📖</div>
                    <p class="font-bold text-gray-500 mb-4">Kamu belum mulai belajar materi apapun.</p>
                    <a href="materi.php" class="btn-primary inline-flex items-center gap-2">
                        🚀 Mulai Petualangan Pertamamu!
                    </a>
                </div>
                <?php else:
                    $progress_list->data_seek(0);
                    while ($p = $progress_list->fetch_assoc()): ?>
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 py-4 border-b border-gray-100 last:border-0 hover:bg-gray-50/50 p-3 rounded-xl transition-colors">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl shadow-sm flex-shrink-0" style="background:<?= $p['warna'] ?>22">
                        <?= $p['icon'] ?>
                    </div>
                    <div class="flex-1">
                        <div class="font-bold text-gray-800 text-base mb-1"><?= htmlspecialchars($p['judul']) ?></div>
                        <?php if ($p['selesai']): ?>
                            <div class="text-xs text-green-600 font-semibold flex items-center gap-1">
                                ✅ Kuis Selesai
                            </div>
                        <?php else: ?>
                            <div class="text-xs text-gray-500 font-semibold flex items-center gap-1">
                                📖 Sedang dipelajari
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="mt-2 sm:mt-0">
                        <?php if ($p['selesai']): ?>
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-4 py-2 rounded-full border border-green-200 shadow-sm inline-block">
                            🎉 Tuntas
                        </span>
                        <?php else: ?>
                        <a href="kuis.php?materi_id=<?= $p['materi_id'] ?>" class="bg-yellow-100 text-yellow-700 border border-yellow-300 text-xs font-bold px-4 py-2 rounded-full hover:bg-yellow-400 hover:text-yellow-900 transition-colors shadow-sm inline-block text-center w-full sm:w-auto">
                            🧩 Kerjakan Kuis
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>

            <!-- Riwayat Kuis Terakhir -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-black text-gray-800 flex items-center gap-3">
                        <span class="text-3xl">📋</span> Riwayat Kuis
                    </h2>
                </div>
                
                <?php if ($riwayat->num_rows == 0): ?>
                <div class="text-center text-gray-400 py-8">
                    <div class="text-4xl mb-3 grayscale opacity-40">🧩</div>
                    <p class="font-semibold text-sm">Belum ada riwayat kuis.</p>
                </div>
                <?php else: while ($r = $riwayat->fetch_assoc()):
                    $persen_r = round(($r['skor']/$r['total_soal'])*100);
                    
                    // Tentukan warna berdasarkan skor
                    if ($persen_r >= 80) {
                        $color_text = 'text-green-600';
                        $color_bg = 'bg-green-100';
                        $emoji = '🌟';
                    } elseif ($persen_r >= 50) {
                        $color_text = 'text-yellow-600';
                        $color_bg = 'bg-yellow-100';
                        $emoji = '👍';
                    } else {
                        $color_text = 'text-red-600';
                        $color_bg = 'bg-red-100';
                        $emoji = '💪';
                    }
                ?>
                <div class="flex items-center gap-4 py-4 border-b border-gray-100 last:border-0 hover:bg-gray-50/50 p-2 rounded-xl transition-colors">
                    <div class="text-3xl w-10 text-center"><?= $emoji ?></div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-gray-800 text-base truncate"><?= htmlspecialchars($r['judul']) ?></div>
                        <div class="text-xs text-gray-400 font-semibold mt-1">
                            ⏰ <?= date('d M Y, H:i', strtotime($r['created_at'])) ?>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <div class="font-black text-xl <?= $color_text ?>"><?= $persen_r ?>%</div>
                        <div class="text-xs text-gray-500 font-bold mt-1 bg-gray-100 px-2 py-0.5 rounded-full inline-block">
                            <?= $r['skor'] ?> dari <?= $r['total_soal'] ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>
        </div>

        <!-- KOLOM KANAN (Lebar 1/3): Badge & Menu -->
        <div class="space-y-8">
            
            <!-- Badge Koleksi -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-xl font-black text-gray-800 mb-5 flex items-center gap-2">
                    <span class="text-2xl">🏅</span> Etalase Badge
                </h2>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 gap-4">
                    <?php 
                    $all_badges->data_seek(0); 
                    while ($b = $all_badges->fetch_assoc()):
                        $earned = in_array($b['id'], $badge_ids);
                    ?>
                    <div class="badge-card bg-gray-50 border border-gray-100 rounded-2xl p-4 flex flex-col items-center justify-center relative group <?= $earned ? 'ring-2 ring-green-400 ring-offset-2' : 'opacity-60 grayscale' ?>" title="<?= htmlspecialchars($b['deskripsi']) ?>">
                        
                        <!-- Tooltip saat di-hover -->
                        <div class="absolute -top-12 left-1/2 -translate-x-1/2 w-48 bg-gray-800 text-white text-xs p-2 rounded-lg text-center opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 shadow-xl font-semibold">
                            <?= htmlspecialchars($b['deskripsi']) ?>
                            <!-- Segitiga panah bawah tooltip -->
                            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-gray-800 rotate-45"></div>
                        </div>

                        <div class="text-4xl mb-2 drop-shadow-sm transition-transform group-hover:scale-110"><?= $b['icon'] ?></div>
                        <div class="text-xs font-black text-gray-700 leading-tight text-center px-1 mb-1"><?= htmlspecialchars($b['nama']) ?></div>
                        
                        <?php if (!$earned): ?>
                            <div class="text-[10px] bg-gray-200 text-gray-500 font-bold px-2 py-0.5 rounded-full mt-1 flex items-center gap-1">
                                🔒 ⭐<?= $b['syarat_poin'] ?>
                            </div>
                        <?php else: ?>
                            <div class="text-[10px] bg-green-100 text-green-700 font-bold px-2 py-0.5 rounded-full mt-1 flex items-center gap-1 shadow-sm">
                                ✅ Dimiliki
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Menu Cepat (Shortcut) -->
            <div class="bg-gradient-to-br from-purple-500 to-primary rounded-3xl p-6 shadow-lg text-white border-4 border-purple-400/30 relative overflow-hidden">
                <!-- Hiasan Background -->
                <div class="absolute -right-6 -top-6 text-7xl opacity-10 rotate-12">⚡</div>
                <div class="absolute -left-6 -bottom-6 text-7xl opacity-10 -rotate-12">🚀</div>

                <h3 class="text-xl font-black mb-5 relative z-10 flex items-center gap-2">
                    <span>⚡</span> Menu Cepat
                </h3>
                <div class="flex flex-col gap-3 relative z-10">
                    <a href="materi.php" class="bg-white text-primary text-sm font-black text-center py-3.5 rounded-xl hover:bg-gray-50 hover:scale-[1.02] transition-all shadow-md flex items-center justify-center gap-2">
                        <span class="text-lg">📚</span> Lanjut Belajar
                    </a>
                    <a href="leaderboard.php" class="bg-purple-700 text-white text-sm font-black text-center py-3.5 rounded-xl hover:bg-purple-600 transition-all shadow-inner border border-purple-500 flex items-center justify-center gap-2">
                        <span class="text-lg">🏆</span> Lihat Leaderboard
                    </a>
                    <a href="../index.php" class="text-center text-sm text-purple-200 font-semibold py-2 hover:text-white transition-colors mt-1">
                        ← Kembali ke Beranda
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>