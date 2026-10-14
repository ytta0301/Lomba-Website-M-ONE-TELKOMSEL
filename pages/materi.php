<?php
$page_title = 'Materi Belajar';
require_once '../includes/header.php';

// Fitur Filter Level
$level_filter = isset($_GET['level']) ? clean($_GET['level']) : '';
$where = $level_filter ? "WHERE level='$level_filter'" : '';

// Ambil semua materi sesuai filter
$materis = $conn->query("SELECT * FROM materi $where ORDER BY urutan");

// Pengaturan warna untuk badge level
$level_colors = [
    'Mudah' => 'bg-green-100 text-green-700 border-green-200',
    'Sedang'=> 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'Sulit' => 'bg-red-100 text-red-700 border-red-200'
];
?>

<!-- PAGE HEADER -->
<div class="bg-gradient-to-r from-red-50 via-white to-blue-50 py-16 px-4 text-gray-800 text-center shadow-inner relative overflow-hidden">
    <!-- Hiasan Background Latar -->
    <div class="absolute -top-10 -left-10 text-9xl opacity-10 rotate-12">📚</div>
    <div class="absolute -bottom-10 -right-10 text-9xl opacity-10 -rotate-12">💻</div>
    
    <div class="relative z-10">
        <div class="text-6xl mb-4 animate-float inline-block drop-shadow-md">🚀</div>
        <h1 class="text-4xl md:text-5xl font-black mb-3 drop-shadow-md"><span class="text-blue-700">Pusat Belajar</span> <span class="text-red-600">KodingKids</span></h1>
        <p class="font-semibold text-lg max-w-2xl mx-auto">
            <span class="text-blue-700">Pilih materi dan mulai petualangan coding-mu hari ini!</span>
            <span class="text-red-600">Kumpulkan bintang dan raih badge tertinggi.</span>
        </p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 py-12">
    
    <!-- Filter Level -->
    <div class="flex flex-wrap gap-3 justify-center mb-12">
        <a href="materi.php" class="px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-sm <?= !$level_filter ? 'bg-gray-800 text-white ring-2 ring-gray-800 ring-offset-2' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' ?>">
            🌟 Semua Level
        </a>
        <?php foreach (['Mudah','Sedang','Sulit'] as $lvl): ?>
        <a href="materi.php?level=<?= $lvl ?>" class="px-6 py-2.5 rounded-full font-bold text-sm transition-all shadow-sm flex items-center gap-2 <?= $level_filter==$lvl ? 'bg-gray-800 text-white ring-2 ring-gray-800 ring-offset-2' : 'bg-white text-gray-600 hover:bg-gray-50 border border-gray-200' ?>">
            <span><?= $lvl=='Mudah'?'🟢':($lvl=='Sedang'?'🟡':'🔴') ?></span> <?= $lvl ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Info Cara Dapat Poin -->
    <div class="mb-10 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-3xl p-6 text-center shadow-sm">
        <div class="flex flex-wrap items-center justify-center gap-4 md:gap-6 text-sm font-bold text-gray-700">
            <div class="flex items-center gap-2">
                <span class="text-2xl">💡</span> <span class="uppercase tracking-wide text-xs text-gray-500">Cara Dapat Poin:</span>
            </div>
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm">
                📖 Baca Materi = <span class="text-primary">+10⭐</span>
            </div>
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm">
                🧩 Jawab Benar = <span class="text-primary">+10⭐/soal</span>
            </div>
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl shadow-sm">
                🏆 Raih Poin = <span class="text-accent">Dapat Badge!</span>
            </div>
        </div>
    </div>

    <!-- Grid Kartu Materi -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php if ($materis->num_rows == 0): ?>
        <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-20 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
            <div class="text-6xl mb-4 grayscale opacity-50">📭</div>
            <p class="font-bold text-xl text-gray-600">Yah, belum ada materi untuk level ini.</p>
            <p class="text-gray-400 mt-2">Coba pilih level yang lain ya!</p>
            <a href="materi.php" class="btn-outline mt-6 inline-block">← Kembali ke Semua Level</a>
        </div>
        <?php endif; ?>

        <?php while ($m = $materis->fetch_assoc()):
            // Cek progress dan hitung jumlah kuis
            $is_done = false;
            $kuis_count = $conn->query("SELECT COUNT(*) as c FROM kuis WHERE materi_id={$m['id']}")->fetch_assoc()['c'];
            
            if (isLoggedIn()) {
                $uid = $_SESSION['user_id'];
                $prog = $conn->query("SELECT selesai FROM progress WHERE user_id=$uid AND materi_id={$m['id']}")->fetch_assoc();
                $is_done = $prog && $prog['selesai'];
            }
        ?>
        <a href="materi_detail.php?id=<?= $m['id'] ?>" class="materi-card group block bg-white rounded-3xl p-6 shadow-sm border border-gray-100 transition-all duration-300 hover:shadow-xl hover:-translate-y-2 relative overflow-hidden">
            
            <!-- Garis warna di atas kartu -->
            <div class="absolute top-0 left-0 right-0 h-2" style="background-color: <?= $m['warna'] ?>"></div>

            <!-- Badge Tuntas (Jika sudah selesai mengerjakan kuisnya) -->
            <?php if ($is_done): ?>
            <div class="absolute top-5 right-5 bg-green-100 text-green-700 border border-green-200 text-xs font-black px-3 py-1.5 rounded-full shadow-sm flex items-center gap-1 z-10">
                ✅ Selesai
            </div>
            <?php endif; ?>

            <div class="flex items-start gap-4 mb-5 pt-2">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl flex-shrink-0 shadow-sm transition-transform group-hover:scale-110 group-hover:rotate-3"
                     style="background:<?= $m['warna'] ?>22; color: <?= $m['warna'] ?>">
                    <?= $m['icon'] ?>
                </div>
                <div class="flex-1 pt-1">
                    <span class="text-[10px] font-black uppercase tracking-wider px-3 py-1 rounded-full border <?= $level_colors[$m['level']] ?> inline-block mb-2">
                        <?= $m['level'] ?>
                    </span>
                    <div class="text-xs text-gray-500 font-bold flex items-center gap-1">
                        🧩 <?= $kuis_count ?> soal kuis
                    </div>
                </div>
            </div>

            <h3 class="text-xl font-black text-gray-800 mb-3 group-hover:text-primary transition-colors leading-tight">
                <?= htmlspecialchars($m['judul']) ?>
            </h3>
            
            <p class="text-gray-500 text-sm font-semibold leading-relaxed mb-6 line-clamp-2">
                <?= htmlspecialchars($m['deskripsi']) ?>
            </p>

            <div class="flex items-center justify-between pt-4 border-t border-gray-50 mt-auto">
                <div class="flex items-center gap-2 font-black text-sm transition-colors" style="color:<?= $m['warna'] ?>">
                    <span class="group-hover:mr-1 transition-all"><?= $is_done ? 'Pelajari Ulang' : 'Mulai Belajar' ?></span>
                    <span class="text-lg group-hover:translate-x-1 transition-transform">→</span>
                </div>
                <!-- Estimasi poin maksimal yang bisa didapat (10 baca + kuis*10) -->
                <div class="bg-yellow-50 text-yellow-600 border border-yellow-200 px-3 py-1 rounded-lg font-black text-xs shadow-sm flex items-center gap-1">
                    Hingga +<?= 10 + ($kuis_count * 10) ?>⭐
                </div>
            </div>
        </a>
        <?php endwhile; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>