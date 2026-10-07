<?php
$page_title = 'Detail Materi';
require_once '../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$materi = $conn->query("SELECT * FROM materi WHERE id=$id")->fetch_assoc();

// Jika materi tidak ditemukan, kembalikan ke daftar
if (!$materi) { redirect('../pages/materi.php'); }

$page_title = $materi['judul'];

// Hitung soal kuis untuk materi ini
$kuis_count = $conn->query("SELECT COUNT(*) as c FROM kuis WHERE materi_id=$id")->fetch_assoc()['c'];

// Cek status & Beri Poin Baca (Hanya jika Login)
$is_done = false;
$pesan_poin = '';
if (isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $prog = $conn->query("SELECT * FROM progress WHERE user_id=$uid AND materi_id=$id")->fetch_assoc();
    
    if (!$prog) {
        // Baru pertama kali buka materi ini! Tambah ke progress dan beri poin.
        $conn->query("INSERT INTO progress (user_id, materi_id, selesai) VALUES ($uid, $id, 0)");
        tambahPoin($uid, 10); 
        $pesan_poin = '🎉 Selamat! Kamu dapat +10 Poin karena rajin membaca materi ini!';
    }
    $is_done = $prog && $prog['selesai'];
}

// Navigasi Previous / Next Materi
$prev = $conn->query("SELECT id, judul FROM materi WHERE urutan < {$materi['urutan']} ORDER BY urutan DESC LIMIT 1")->fetch_assoc();
$next = $conn->query("SELECT id, judul FROM materi WHERE urutan > {$materi['urutan']} ORDER BY urutan ASC LIMIT 1")->fetch_assoc();

$level_colors = ['Mudah'=>'bg-green-100 text-green-700','Sedang'=>'bg-yellow-100 text-yellow-700','Sulit'=>'bg-red-100 text-red-700'];
?>

<div class="max-w-5xl mx-auto px-4 py-8">
    
    <!-- Breadcrumb Navigasi -->
    <nav class="flex items-center gap-2 text-sm font-bold text-gray-400 mb-8 overflow-x-auto whitespace-nowrap pb-2">
        <a href="../index.php" class="hover:text-primary bg-white px-3 py-1.5 rounded-lg shadow-sm border border-gray-100">🏠 Beranda</a>
        <span class="text-gray-300">/</span>
        <a href="materi.php" class="hover:text-primary bg-white px-3 py-1.5 rounded-lg shadow-sm border border-gray-100">📚 Materi Belajar</a>
        <span class="text-gray-300">/</span>
        <span class="bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg border border-gray-200"><?= htmlspecialchars($materi['judul']) ?></span>
    </nav>

    <!-- Notifikasi Poin Pertama Kali Baca -->
    <?php if ($pesan_poin): ?>
    <div class="bg-green-50 border-2 border-green-200 text-green-800 px-6 py-4 rounded-2xl font-black mb-8 flex items-center justify-between shadow-sm animate-fadeInUp">
        <div class="flex items-center gap-3">
            <span class="text-3xl">⭐</span>
            <span><?= $pesan_poin ?></span>
        </div>
        <button onclick="this.parentElement.style.display='none'" class="text-green-600 hover:text-green-900">✖</button>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- KOLOM KIRI (Lebar 2/3): KONTEN UTAMA -->
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Materi -->
                <div class="p-8 md:p-10 relative overflow-hidden" style="background: linear-gradient(135deg, <?= $materi['warna'] ?>11, white)">
                    <!-- Hiasan Background -->
                    <div class="absolute -right-6 -top-6 text-8xl opacity-10 rotate-12"><?= $materi['icon'] ?></div>

                    <div class="flex flex-col sm:flex-row sm:items-center gap-6 relative z-10">
                        <div class="w-24 h-24 rounded-3xl flex items-center justify-center text-5xl shadow-md flex-shrink-0 bg-white border-4"
                             style="border-color:<?= $materi['warna'] ?>44">
                            <?= $materi['icon'] ?>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full <?= $level_colors[$materi['level']] ?>">
                                    Level: <?= $materi['level'] ?>
                                </span>
                                <?php if ($is_done): ?>
                                <span class="text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full bg-green-100 text-green-700 border border-green-200">
                                    ✅ Kuis Selesai
                                </span>
                                <?php endif; ?>
                            </div>
                            <h1 class="text-3xl md:text-4xl font-black text-gray-800 mb-2 leading-tight"><?= htmlspecialchars($materi['judul']) ?></h1>
                            <p class="text-gray-500 font-semibold text-base"><?= htmlspecialchars($materi['deskripsi']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Isi Konten Materi (HTML dari Database) -->
                <div class="p-8 md:p-10 materi-konten border-t border-gray-50">
                    <?= $materi['konten'] ?>
                </div>

                <!-- Tombol Aksi Bawah -->
                <div class="p-8 bg-gray-50 border-t border-gray-100 flex flex-wrap gap-4 items-center justify-between">
                    <a href="materi.php" class="btn-outline bg-white flex items-center gap-2">
                        <span>←</span> Kembali
                    </a>
                    
                    <?php if ($kuis_count > 0): ?>
                    <a href="kuis.php?materi_id=<?= $id ?>" class="btn-accent px-8 py-3.5 shadow-lg flex items-center gap-2 group">
                        <span class="text-xl group-hover:rotate-12 transition-transform">🧩</span> 
                        Uji Pengetahuanmu!
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Navigasi Prev/Next -->
            <div class="flex flex-col sm:flex-row justify-between gap-4">
                <?php if ($prev): ?>
                <a href="materi_detail.php?id=<?= $prev['id'] ?>" class="flex-1 bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-primary hover:shadow-md transition-all group flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white transition-colors">←</div>
                    <div>
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Materi Sebelumnya</div>
                        <div class="font-black text-gray-700 group-hover:text-primary text-base line-clamp-1"><?= htmlspecialchars($prev['judul']) ?></div>
                    </div>
                </a>
                <?php else: ?><div class="hidden sm:block flex-1"></div><?php endif; ?>

                <?php if ($next): ?>
                <a href="materi_detail.php?id=<?= $next['id'] ?>" class="flex-1 bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-primary hover:shadow-md transition-all group flex items-center justify-end gap-4 text-right">
                    <div>
                        <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Materi Selanjutnya</div>
                        <div class="font-black text-gray-700 group-hover:text-primary text-base line-clamp-1"><?= htmlspecialchars($next['judul']) ?></div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 group-hover:bg-primary group-hover:text-white transition-colors">→</div>
                </a>
                <?php else: ?><div class="hidden sm:block flex-1"></div><?php endif; ?>
            </div>
        </div>

        <!-- KOLOM KANAN (Lebar 1/3): SIDEBAR -->
        <div class="space-y-6">
            
            <!-- Jika Belum Login -->
            <?php if (!isLoggedIn()): ?>
            <div class="bg-gradient-to-br from-primary to-blue-500 rounded-3xl p-6 text-white text-center shadow-lg relative overflow-hidden border-4 border-white/20">
                <div class="absolute -right-4 -top-4 text-6xl opacity-20">🎁</div>
                <div class="text-5xl mb-4 relative z-10">🔐</div>
                <h3 class="text-xl font-black mb-2 relative z-10">Simpan Progressmu!</h3>
                <p class="text-blue-100 text-sm mb-5 font-semibold relative z-10">
                    Login sekarang untuk mengerjakan kuis, mendapat poin, dan mengoleksi badge keren!
                </p>
                <a href="login.php" class="bg-white text-primary font-black px-6 py-3 rounded-full text-sm hover:scale-105 transition-transform inline-block shadow-md relative z-10 w-full">
                    Masuk atau Daftar
                </a>
            </div>
            <?php endif; ?>

            <!-- Info Materi -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-black text-gray-800 mb-5 flex items-center gap-2">
                    <span class="text-xl">📊</span> Info Materi
                </h3>
                <div class="space-y-4 text-sm bg-gray-50 p-4 rounded-2xl border border-gray-100">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200 border-dashed">
                        <span class="text-gray-500 font-bold flex items-center gap-2"><span class="w-4">📶</span> Level</span>
                        <span class="font-black text-gray-700"><?= $materi['level'] ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200 border-dashed">
                        <span class="text-gray-500 font-bold flex items-center gap-2"><span class="w-4">🧩</span> Soal Kuis</span>
                        <span class="font-black text-gray-700"><?= $kuis_count ?> soal</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-gray-200 border-dashed">
                        <span class="text-gray-500 font-bold flex items-center gap-2"><span class="w-4">📖</span> Poin Baca</span>
                        <span class="font-black text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-md">+10 ⭐</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500 font-bold flex items-center gap-2"><span class="w-4">🎯</span> Poin Kuis</span>
                        <span class="font-black text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-md">+10/soal ⭐</span>
                    </div>
                </div>
                
                <?php if ($kuis_count > 0): ?>
                <a href="kuis.php?materi_id=<?= $id ?>" class="mt-5 w-full btn-green text-center py-3 flex justify-center items-center gap-2">
                    Mulai Kuis Sekarang! <span>→</span>
                </a>
                <?php endif; ?>
            </div>

            <!-- Rekomendasi Materi Lain -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-black text-gray-800 mb-4 flex items-center gap-2">
                    <span class="text-xl">🧭</span> Jelajahi Lainnya
                </h3>
                <div class="flex flex-col gap-2">
                    <?php
                    // Ambil materi lain (kecuali yang sedang dibuka)
                    $lainnya = $conn->query("SELECT id, judul, icon, warna FROM materi WHERE id != $id ORDER BY urutan LIMIT 4");
                    while ($l = $lainnya->fetch_assoc()): ?>
                    <a href="materi_detail.php?id=<?= $l['id'] ?>" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-100 transition-all group">
                        <span class="w-10 h-10 rounded-lg flex items-center justify-center text-xl bg-white shadow-sm border border-gray-100 group-hover:scale-110 transition-transform">
                            <?= $l['icon'] ?>
                        </span>
                        <span class="text-sm font-bold text-gray-700 group-hover:text-primary transition-colors flex-1 line-clamp-1">
                            <?= htmlspecialchars($l['judul']) ?>
                        </span>
                        <span class="text-gray-300 group-hover:text-primary font-bold">›</span>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>