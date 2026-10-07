<?php
$page_title = 'Kuis';
require_once '../includes/header.php';

$materi_id = isset($_GET['materi_id']) ? (int)$_GET['materi_id'] : 0;
$materi = $conn->query("SELECT * FROM materi WHERE id=$materi_id")->fetch_assoc();

// Cek apakah materi ada
if (!$materi) redirect('materi.php');

$page_title = 'Kuis: ' . $materi['judul'];
$soal_list = $conn->query("SELECT * FROM kuis WHERE materi_id=$materi_id ORDER BY id")->fetch_all(MYSQLI_ASSOC);
$total_soal = count($soal_list);

// Jika tidak ada soal untuk materi ini
if ($total_soal == 0) {
    echo "<div class='max-w-3xl mx-auto mt-20 text-center'><h2 class='text-2xl font-bold'>Belum ada kuis untuk materi ini.</h2><a href='materi_detail.php?id=$materi_id' class='text-primary mt-4 inline-block underline'>Kembali ke materi</a></div>";
    require_once '../includes/footer.php';
    exit;
}

// Handle Form Submit (Ketika Kuis Selesai Dikerjakan)
$hasil = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['jawaban'])) {
    $jawaban = $_POST['jawaban'];
    $skor = 0;
    $detail = [];
    
    // Periksa setiap jawaban
    foreach ($soal_list as $s) {
        $user_jwb = isset($jawaban[$s['id']]) ? $jawaban[$s['id']] : '';
        $benar = $user_jwb === $s['jawaban_benar'];
        if ($benar) $skor++;
        $detail[$s['id']] = [
            'user' => $user_jwb, 
            'benar' => $s['jawaban_benar'], 
            'correct' => $benar
        ];
    }
    
    // Simpan nilai & poin HANYA jika user sudah login
    if (isLoggedIn()) {
        $uid = $_SESSION['user_id'];
        $poin_dapat = $skor * 10;
        
        // Simpan ke riwayat kuis
        $stmt = $conn->prepare("INSERT INTO nilai_kuis (user_id, materi_id, skor, total_soal) VALUES (?,?,?,?)");
        $stmt->bind_param("iiii", $uid, $materi_id, $skor, $total_soal);
        $stmt->execute();
        
        // Tambah poin user
        tambahPoin($uid, $poin_dapat);
        
        // Tandai progress materi sebagai selesai
        $conn->query("INSERT INTO progress (user_id, materi_id, selesai) VALUES ($uid, $materi_id, 1) ON DUPLICATE KEY UPDATE selesai=1");
    }
    
    $hasil = ['skor' => $skor, 'total' => $total_soal, 'detail' => $detail];
}

$opsi_labels = ['a'=>'A','b'=>'B','c'=>'C','d'=>'D'];
?>

<div class="max-w-3xl mx-auto px-4 py-10">
    
    <!-- Header Kuis -->
    <div class="text-center mb-10">
        <div class="inline-block w-24 h-24 bg-white rounded-full shadow-md flex items-center justify-center text-5xl mb-4 animate-bounce-slow border-4 border-gray-50">
            <?= $materi['icon'] ?>
        </div>
        <h1 class="text-3xl md:text-4xl font-black text-gray-800 mb-2">Kuis: <?= htmlspecialchars($materi['judul']) ?></h1>
        <div class="inline-flex items-center gap-2 bg-yellow-100 text-yellow-800 px-4 py-2 rounded-full font-bold text-sm">
            <span>🧩 <?= $total_soal ?> soal</span>
            <span class="text-yellow-400">•</span>
            <span>⭐ +10 poin/soal benar</span>
        </div>
    </div>

    <?php if ($hasil): ?>
    
    <!-- ========================================== -->
    <!-- HASIL KUIS (Tampil setelah form disubmit)  -->
    <!-- ========================================== -->
    <?php
    $persen = round(($hasil['skor'] / $hasil['total']) * 100);
    
    if ($persen >= 80) {
        $emoji = '🎉';
        $pesan = 'Luar Biasa! Kamu Jago!';
        $color_theme = 'green';
        // Panggil script confetti
        echo '<script>document.addEventListener("DOMContentLoaded", ()=>launchConfetti())</script>';
    } elseif ($persen >= 50) {
        $emoji = '👍';
        $pesan = 'Bagus! Terus Semangat!';
        $color_theme = 'yellow';
    } else {
        $emoji = '💪';
        $pesan = 'Jangan Menyerah, Coba Lagi!';
        $color_theme = 'red';
    }
    ?>
    
    <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 mb-10 text-center relative overflow-hidden">
        <!-- Hiasan BG -->
        <div class="absolute -right-10 -top-10 text-9xl opacity-5 rotate-12">🏅</div>
        
        <div class="text-7xl mb-4 relative z-10 animate-pop"><?= $emoji ?></div>
        <h2 class="text-3xl font-black text-gray-800 mb-2 relative z-10"><?= $pesan ?></h2>
        <p class="text-gray-500 font-semibold mb-8 relative z-10">Berikut adalah hasil kuis kamu:</p>
        
        <div class="flex flex-wrap justify-center gap-4 md:gap-8 my-8 relative z-10">
            <!-- Kotak Jawaban Benar -->
            <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 min-w-[120px]">
                <div class="text-5xl font-black text-blue-600 mb-1"><?= $hasil['skor'] ?><span class="text-2xl text-blue-300">/<?= $hasil['total'] ?></span></div>
                <div class="text-blue-800 font-bold text-xs uppercase tracking-wide">Jawaban Benar</div>
            </div>
            
            <!-- Kotak Persentase -->
            <div class="bg-<?= $color_theme ?>-50 border border-<?= $color_theme ?>-100 rounded-2xl p-4 min-w-[120px]">
                <div class="text-5xl font-black text-<?= $color_theme ?>-600 mb-1"><?= $persen ?><span class="text-2xl text-<?= $color_theme ?>-300">%</span></div>
                <div class="text-<?= $color_theme ?>-800 font-bold text-xs uppercase tracking-wide">Skor Kamu</div>
            </div>
            
            <!-- Kotak Poin (Jika Login) -->
            <?php if (isLoggedIn()): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 min-w-[120px] shadow-sm transform scale-105">
                <div class="text-5xl font-black text-yellow-500 mb-1">+<?= $hasil['skor'] * 10 ?></div>
                <div class="text-yellow-800 font-bold text-xs uppercase tracking-wide">Poin Didapat ⭐</div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Progress bar visualisasi skor -->
        <div class="max-w-sm mx-auto mb-8 bg-gray-100 h-4 rounded-full overflow-hidden relative z-10 shadow-inner">
            <div class="h-full bg-gradient-to-r from-<?= $color_theme ?>-400 to-<?= $color_theme ?>-600 transition-all duration-1000" style="width: 0%" id="score-bar"></div>
        </div>
        <script>setTimeout(() => document.getElementById('score-bar').style.width = '<?= $persen ?>%', 300);</script>
        
        <!-- Tombol Aksi -->
        <div class="flex flex-wrap justify-center gap-3 relative z-10">
            <a href="kuis.php?materi_id=<?= $materi_id ?>" class="bg-gray-800 text-white font-black px-6 py-3 rounded-full hover:bg-gray-700 transition-colors shadow-md flex items-center gap-2">
                🔄 Coba Lagi
            </a>
            <a href="materi_detail.php?id=<?= $materi_id ?>" class="bg-white text-gray-700 border-2 border-gray-200 font-black px-6 py-3 rounded-full hover:bg-gray-50 transition-colors flex items-center gap-2">
                📖 Baca Materi
            </a>
            <?php if (isLoggedIn()): ?>
            <a href="dashboard.php" class="bg-primary text-white font-black px-6 py-3 rounded-full hover:bg-purple-700 transition-colors shadow-md flex items-center gap-2">
                📊 Ke Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Review Jawaban -->
    <div class="flex items-center gap-3 mb-6 mt-12">
        <span class="text-3xl">📋</span>
        <h3 class="text-2xl font-black text-gray-800">Review Jawaban</h3>
    </div>
    
    <?php foreach ($soal_list as $i => $s):
        $d = $hasil['detail'][$s['id']];
        $opsi_arr = ['a'=>$s['pilihan_a'],'b'=>$s['pilihan_b'],'c'=>$s['pilihan_c'],'d'=>$s['pilihan_d']];
    ?>
    <div class="bg-white rounded-2xl shadow-sm p-6 mb-5 border-2 <?= $d['correct'] ? 'border-green-300 bg-green-50/30' : 'border-red-300 bg-red-50/30' ?>">
        
        <div class="flex items-start gap-4 mb-5">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl flex-shrink-0 <?= $d['correct'] ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?>">
                <?= $d['correct'] ? '✅' : '❌' ?>
            </div>
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Soal <?= $i+1 ?></div>
                <p class="font-bold text-gray-800 text-lg leading-snug"><?= htmlspecialchars($s['pertanyaan']) ?></p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pl-14">
            <?php foreach ($opsi_arr as $key => $val):
                $is_benar = $key === $s['jawaban_benar'];
                $is_user  = $key === $d['user'];
                
                $cls = 'border-gray-200 text-gray-500 bg-white'; // Default
                $icon = '';
                
                if ($is_benar) {
                    $cls = 'border-green-500 bg-green-100 text-green-800 shadow-sm ring-1 ring-green-500';
                    $icon = '✅';
                } elseif ($is_user && !$is_benar) {
                    $cls = 'border-red-400 bg-red-100 text-red-800';
                    $icon = '❌';
                }
            ?>
            <div class="flex items-center gap-3 p-3.5 rounded-xl border-2 <?= $cls ?> font-semibold text-sm transition-all relative">
                <span class="w-6 h-6 rounded bg-white/50 flex items-center justify-center font-black opacity-70"><?= strtoupper($key) ?></span>
                <span class="flex-1"><?= htmlspecialchars($val) ?></span>
                <?php if ($icon): ?>
                    <span class="text-lg absolute right-3"><?= $icon ?></span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <?php else: ?>
    
    <!-- ========================================== -->
    <!-- FORM KUIS (Tampil saat belum disubmit)     -->
    <!-- ========================================== -->
    
    <?php if (!isLoggedIn()): ?>
    <div class="bg-yellow-50 border-2 border-yellow-300 text-yellow-800 px-6 py-4 rounded-2xl font-bold mb-8 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⚠️</span>
            <span>Kamu belum Login. <a href="login.php" class="underline text-yellow-900 hover:text-primary">Login dulu yuk</a> biar nilaimu tersimpan dan dapat poin!</span>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" id="quiz-form" class="space-y-8">
        <?php foreach ($soal_list as $i => $s):
            $opsi_arr = ['a'=>$s['pilihan_a'],'b'=>$s['pilihan_b'],'c'=>$s['pilihan_c'],'d'=>$s['pilihan_d']];
        ?>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 animate-on-scroll">
            
            <!-- Pertanyaan -->
            <div class="flex items-start gap-4 mb-6">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white flex-shrink-0 shadow-sm" style="background:<?= $materi['warna'] ?>">
                    <?= $i+1 ?>
                </div>
                <p class="font-black text-gray-800 text-xl leading-snug pt-1"><?= htmlspecialchars($s['pertanyaan']) ?></p>
            </div>
            
            <!-- Pilihan Jawaban -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pl-0 md:pl-14">
                <?php foreach ($opsi_arr as $key => $val): ?>
                <label class="group cursor-pointer relative">
                    <!-- Radio input tersembunyi, tapi fungsi tetap jalan -->
                    <input type="radio" name="jawaban[<?= $s['id'] ?>]" value="<?= $key ?>" class="peer sr-only" required>
                    
                    <!-- Desain kustom tombol pilihan -->
                    <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-gray-200 bg-white text-gray-600 font-bold transition-all peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary peer-checked:shadow-sm hover:border-gray-300 hover:bg-gray-50">
                        
                        <!-- Lingkaran penanda -->
                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 flex items-center justify-center peer-checked:border-primary group-hover:border-primary/50 transition-colors">
                            <div class="w-3 h-3 rounded-full bg-primary scale-0 peer-checked:scale-100 transition-transform"></div>
                        </div>
                        
                        <span class="w-6 font-black opacity-50 peer-checked:opacity-100"><?= strtoupper($key) ?>.</span>
                        <span class="flex-1 text-[15px]"><?= htmlspecialchars($val) ?></span>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Tombol Submit -->
        <div class="text-center pt-6 pb-10 sticky bottom-4 z-20">
            <div class="bg-white/80 backdrop-blur-md p-4 rounded-3xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)] inline-block border border-gray-100">
                <button type="submit" class="btn-accent px-10 py-4 text-lg w-full md:w-auto flex items-center justify-center gap-3">
                    <span class="text-2xl">✅</span> Selesai & Lihat Nilai!
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>