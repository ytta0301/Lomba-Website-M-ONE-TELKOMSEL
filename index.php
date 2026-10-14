<?php
$page_title = 'Beranda';
require_once 'includes/header.php';

// Ambil top 3 leaderboard
$top_users = $conn->query("SELECT nama, poin, avatar FROM users WHERE role='siswa' ORDER BY poin DESC LIMIT 3");

// Hitung statistik
$total_siswa = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='siswa'")->fetch_assoc()['c'];
$total_materi = $conn->query("SELECT COUNT(*) as c FROM materi")->fetch_assoc()['c'];
$total_kuis = $conn->query("SELECT COUNT(*) as c FROM kuis")->fetch_assoc()['c'];
?>

<!-- HERO SECTION -->
<section class="hero-section py-16 md:py-24 px-4">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <!-- Text -->
        <div class="animate-fadeInUp">
            <div class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-700 px-4 py-2 rounded-full font-bold text-sm mb-6">
                🎉 Platform Coding untuk Anak SD
            </div>
            <h1 class="hero-title text-5xl md:text-6xl font-black text-gray-800 leading-tight mb-6">
                Belajar Coding<br>
                <span class="text-primary">Itu</span>
                <span class="text-accent"> Seru!</span>
                <span class="animate-wiggle inline-block">🚀</span>
            </h1>
            <p class="text-gray-500 text-lg font-semibold mb-8 leading-relaxed">
                Ayo belajar coding sambil bermain! Kumpulkan poin, dapatkan badge keren,
                dan jadilah <span class="text-primary font-black">programmer cilik</span> terbaik Indonesia! 🌟
            </p>
            <div class="flex flex-wrap gap-4">
                <a href="pages/register.php" class="btn-accent text-base px-8 py-4">
                    🚀 Mulai Belajar Gratis!
                </a>
                <a href="pages/materi.php" class="btn-outline text-base px-8 py-4">
                    📚 Lihat Materi
                </a>
            </div>
            <!-- Stats -->
            <div class="flex gap-8 mt-10">
                <div class="text-center">
                    <div class="text-3xl font-black text-primary counter" data-target="<?= $total_siswa ?>"><?= $total_siswa ?></div>
                    <div class="text-xs text-gray-400 font-semibold">Siswa Aktif</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black text-accent counter" data-target="<?= $total_materi ?>"><?= $total_materi ?></div>
                    <div class="text-xs text-gray-400 font-semibold">Materi</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black text-green-500 counter" data-target="<?= $total_kuis ?>"><?= $total_kuis ?></div>
                    <div class="text-xs text-gray-400 font-semibold">Soal Kuis</div>
                </div>
            </div>
        </div>

        <!-- Ilustrasi -->
        <div class="flex justify-center animate-float">
            <div class="relative">
                <div class="text-[160px] md:text-[200px] leading-none select-none">🚀</div>
                <!-- Floating icons -->
                <div class="absolute -top-4 -left-8 text-5xl animate-bounce-slow">⭐</div>
                <div class="absolute top-10 -right-10 text-4xl animate-wiggle">💻</div>
                <div class="absolute -bottom-4 left-0 text-4xl animate-float" style="animation-delay:1s">🎨</div>
                <div class="absolute bottom-10 -right-6 text-3xl animate-bounce-slow" style="animation-delay:0.5s">🧩</div>
            </div>
        </div>
    </div>
</section>

<!-- FITUR SECTION -->
<section class="py-16 px-4 bg-white">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12 animate-on-scroll">
            <h2 class="text-4xl font-black text-gray-800 mb-3">Kenapa KodingKids? 🤔</h2>
            <p class="text-gray-500 font-semibold">Platform terbaik untuk belajar coding dengan cara yang menyenangkan!</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $features = [
                ['icon'=>'📚','title'=>'Materi Interaktif','desc'=>'Konten belajar bergambar dan mudah dipahami, dirancang khusus untuk anak SD!','color'=>'#4D96FF','bg'=>'#EFF6FF'],
                ['icon'=>'🧩','title'=>'Kuis Seru','desc'=>'Uji kemampuanmu dengan kuis pilihan ganda yang menyenangkan dan raih skor tertinggi!','color'=>'#FF6B6B','bg'=>'#FFF5F5'],
                ['icon'=>'🏆','title'=>'Kumpulkan Badge','desc'=>'Dapatkan badge keren dan lihat namamu di Leaderboard sebagai yang terbaik!','color'=>'#FFD93D','bg'=>'#FFFBEB'],
                ['icon'=>'🎯','title'=>'Belajar Bertahap','desc'=>'Mulai dari yang mudah, naik level sedikit demi sedikit. Semua anak pasti bisa!','color'=>'#6BCB77','bg'=>'#F0FFF4'],
                ['icon'=>'📊','title'=>'Pantau Progress','desc'=>'Dashboard pribadi untuk melihat seberapa jauh kamu sudah belajar hari ini!','color'=>'#6C63FF','bg'=>'#F5F3FF'],
                ['icon'=>'🌟','title'=>'Gratis 100%','desc'=>'Semua materi dan fitur tersedia gratis! Tidak perlu bayar apapun untuk belajar.','color'=>'#FF9F43','bg'=>'#FFF7ED'],
            ];
            foreach ($features as $f): ?>
            <div class="card animate-on-scroll text-center" style="border-top: 5px solid <?= $f['color'] ?>">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-4xl mx-auto mb-4"
                     style="background:<?= $f['bg'] ?>">
                    <?= $f['icon'] ?>
                </div>
                <h3 class="text-lg font-black text-gray-800 mb-2"><?= $f['title'] ?></h3>
                <p class="text-gray-500 text-sm leading-relaxed font-semibold"><?= $f['desc'] ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- MATERI PREVIEW -->
<section class="py-16 px-4 bg-gray-50">
    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-4xl font-black text-gray-800">Materi Belajar 📚</h2>
                <p class="text-gray-500 font-semibold mt-1">Pilih materi yang ingin kamu pelajari hari ini!</p>
            </div>
            <a href="pages/materi.php" class="btn-primary hidden md:block">Lihat Semua →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            $materis = $conn->query("SELECT * FROM materi ORDER BY urutan LIMIT 3");
            $level_colors = ['Mudah'=>'bg-green-100 text-green-700','Sedang'=>'bg-yellow-100 text-yellow-700','Sulit'=>'bg-red-100 text-red-700'];
            while ($m = $materis->fetch_assoc()):
            ?>
            <a href="pages/materi_detail.php?id=<?= $m['id'] ?>" class="materi-card animate-on-scroll" style="border-top: 5px solid <?= $m['warna'] ?>">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl"
                         style="background:<?= $m['warna'] ?>22">
                        <?= $m['icon'] ?>
                    </div>
                    <div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full <?= $level_colors[$m['level']] ?>">
                            <?= $m['level'] ?>
                        </span>
                    </div>
                </div>
                <h3 class="text-lg font-black text-gray-800 mb-2"><?= htmlspecialchars($m['judul']) ?></h3>
                <p class="text-gray-500 text-sm font-semibold"><?= htmlspecialchars($m['deskripsi']) ?></p>
                <div class="mt-4 flex items-center gap-2 font-bold text-sm" style="color:<?= $m['warna'] ?>">
                    <span>Mulai Belajar</span><span>→</span>
                </div>
            </a>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-8 md:hidden">
            <a href="pages/materi.php" class="btn-primary">Lihat Semua Materi →</a>
        </div>
    </div>
</section>

<!-- LEADERBOARD PREVIEW -->
<section class="py-16 px-4 bg-white">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-black text-gray-800">🏆 Top Siswa</h2>
            <p class="text-gray-500 font-semibold mt-1">Siapa yang paling banyak belajar minggu ini?</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-blue-50 rounded-3xl p-8 shadow-lg">
            <?php
            $rank = 1;
            $rank_icons = ['🥇','🥈','🥉'];
            $top_users = $conn->query("SELECT nama, poin, avatar FROM users WHERE role='siswa' ORDER BY poin DESC LIMIT 3");
            if ($top_users->num_rows == 0): ?>
                <div class="text-center text-gray-400 font-bold py-8">
                    <div class="text-5xl mb-3">🌟</div>
                    <p>Belum ada siswa. Jadilah yang pertama!</p>
                    <a href="pages/register.php" class="btn-primary mt-4 inline-block">Daftar Sekarang</a>
                </div>
            <?php else: while ($u = $top_users->fetch_assoc()): ?>
                <div class="leaderboard-item rank-<?= $rank ?> animate-on-scroll">
                    <div class="text-3xl font-black w-10 text-center"><?= $rank_icons[$rank-1] ?></div>
                    <div class="text-3xl"><?= renderAvatar($u['avatar'], '2.2rem') ?></div>
                    <div class="flex-1">
                        <div class="font-black text-gray-800"><?= htmlspecialchars($u['nama']) ?></div>
                    </div>
                    <div class="flex items-center gap-2 font-black text-yellow-600 text-lg">
                        ⭐ <?= number_format($u['poin']) ?>
                    </div>
                </div>
            <?php $rank++; endwhile; endif; ?>
            <div class="text-center mt-6">
                <a href="pages/leaderboard.php" class="btn-primary">Lihat Semua Peringkat 🏆</a>
            </div>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="py-20 px-4">
    <div class="max-w-3xl mx-auto text-center">
        <div class="bg-gradient-to-r from-primary to-blue-500 rounded-3xl p-12 text-white shadow-2xl">
            <div class="text-6xl mb-4 animate-bounce-slow">🎯</div>
            <h2 class="text-4xl font-black mb-4">Siap Jadi Programmer Cilik? 💪</h2>
            <p class="text-purple-100 font-semibold mb-8 text-lg">
                Bergabung dengan ribuan anak Indonesia yang sudah mulai belajar coding di KodingKids!
            </p>
            <div class="flex justify-center gap-4 flex-wrap">
                <a href="pages/register.php" class="bg-white text-primary font-black px-8 py-4 rounded-full hover:scale-105 transition-transform shadow-lg">
                    🚀 Daftar Gratis Sekarang!
                </a>
                <a href="pages/materi.php" class="border-2 border-white text-white font-black px-8 py-4 rounded-full hover:bg-white hover:text-primary transition-all">
                    📚 Lihat Materi Dulu
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>