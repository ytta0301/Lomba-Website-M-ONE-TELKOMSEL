-- Database Schema & Initial Data for KodingKids
-- M-ONE Telkomsel Coding Competition 2026

CREATE DATABASE IF NOT EXISTS `kodingkids` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kodingkids`;

-- 1. Tabel Pengguna (users)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `avatar` LONGTEXT NOT NULL DEFAULT '🐱',
    `role` ENUM('siswa', 'guru', 'admin') NOT NULL DEFAULT 'siswa',
    `poin` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabel Materi (materi)
CREATE TABLE IF NOT EXISTS `materi` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `judul` VARCHAR(150) NOT NULL,
    `deskripsi` VARCHAR(255) NOT NULL,
    `level` ENUM('Mudah', 'Sedang', 'Sulit') NOT NULL DEFAULT 'Mudah',
    `durasi` TINYINT UNSIGNED NOT NULL DEFAULT 10,
    `icon` VARCHAR(20) NOT NULL DEFAULT '💻',
    `warna` VARCHAR(20) NOT NULL DEFAULT '#6C63FF',
    `konten` LONGTEXT NOT NULL,
    `urutan` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabel Kuis (kuis)
CREATE TABLE IF NOT EXISTS `kuis` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `materi_id` INT UNSIGNED NOT NULL,
    `pertanyaan` TEXT NOT NULL,
    `pilihan_a` VARCHAR(255) NOT NULL,
    `pilihan_b` VARCHAR(255) NOT NULL,
    `pilihan_c` VARCHAR(255) NOT NULL,
    `pilihan_d` VARCHAR(255) NOT NULL,
    `jawaban_benar` ENUM('a', 'b', 'c', 'd') NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_kuis_materi` FOREIGN KEY (`materi_id`) REFERENCES `materi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabel Nilai Kuis (nilai_kuis)
CREATE TABLE IF NOT EXISTS `nilai_kuis` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `materi_id` INT UNSIGNED NOT NULL,
    `skor` INT NOT NULL DEFAULT 0,
    `total_soal` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_nilai_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_nilai_materi` FOREIGN KEY (`materi_id`) REFERENCES `materi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabel Progress Belajar (progress)
CREATE TABLE IF NOT EXISTS `progress` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `materi_id` INT UNSIGNED NOT NULL,
    `selesai` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_materi` (`user_id`, `materi_id`),
    CONSTRAINT `fk_prog_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_prog_materi` FOREIGN KEY (`materi_id`) REFERENCES `materi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabel Lencana / Badges (badges)
CREATE TABLE IF NOT EXISTS `badges` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `deskripsi` VARCHAR(255) NOT NULL,
    `icon` VARCHAR(20) NOT NULL DEFAULT '🏅',
    `syarat_poin` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabel Lencana Pengguna (user_badges)
CREATE TABLE IF NOT EXISTS `user_badges` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `badge_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_badge` (`user_id`, `badge_id`),
    CONSTRAINT `fk_ub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ub_badge` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabel Toko Poin (toko_items)
CREATE TABLE IF NOT EXISTS `toko_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `deskripsi` VARCHAR(255) NOT NULL,
    `kategori` ENUM('avatar', 'gelar', 'border') NOT NULL,
    `harga` INT NOT NULL DEFAULT 0,
    `icon` VARCHAR(20) NOT NULL DEFAULT '🎁',
    `nilai` VARCHAR(255) DEFAULT '',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabel Item Pengguna (user_items)
CREATE TABLE IF NOT EXISTS `user_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `item_id` INT UNSIGNED NOT NULL,
    `aktif` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_user_item` (`user_id`, `item_id`),
    CONSTRAINT `fk_ui_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ui_item` FOREIGN KEY (`item_id`) REFERENCES `toko_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- DATA AWAL (SEED DATA)
-- ========================================================

-- Badges
INSERT INTO `badges` (`id`, `nama`, `deskripsi`, `icon`, `syarat_poin`) VALUES
(1, 'Pemula Bintang', 'Lencana perdana bagi murid baru yang siap menjelajah koding!', '⭐', 0),
(2, 'Penjelajah Kode', 'Membaca materi dan rajin menyelesaikan tantangan koding.', '🚀', 30),
(3, 'Jagoan Kuis', 'Berhasil mengumpulkan 100 poin dari kuis dan materi.', '🧠', 100),
(4, 'Master Koding', 'Hebat! Kamu telah mencapai lebih dari 250 poin koding.', '💻', 250),
(5, 'Legenda Programmer', 'Pencapaian bergengsi dengan mengumpulkan 500+ poin!', '👑', 500)
ON DUPLICATE KEY UPDATE `nama`=VALUES(`nama`);

-- Materi Belajar (5 Materi Lengkap)
INSERT INTO `materi` (`id`, `judul`, `deskripsi`, `level`, `durasi`, `icon`, `warna`, `konten`, `urutan`) VALUES
(1, 'Berkenalan dengan Dunia Coding', 'Kenali apa itu koding dan bagaimana komputer menjalankan perintahmu.', 'Mudah', 8, '💻', '#6C63FF', 
'<div class="space-y-4 text-gray-700 leading-relaxed font-medium">
<p class="text-lg">Halo calon programmer cilik! Pernahkah kamu membayangkan bagaimana permainan di komputer atau ponsel pintar bisa bergerak saat tombolnya ditekan? Jawabannya adalah karena ada <strong class="text-primary font-black">coding</strong>!</p>
<div class="bg-purple-50 border-l-4 border-primary p-4 rounded-r-xl">
<h4 class="font-bold text-primary mb-1">💡 Apa itu Coding?</h4>
<p>Coding adalah cara manusia berbicara dan memberi instruksi kepada komputer dalam bahasa yang dipahami komputer. Komputer itu sangat cepat, namun ia hanya bisa menjalankan perintah yang kita berikan secara runtut!</p>
</div>
<p>Bayangkan kamu sedang memberi tahu teman cara membuat roti selai cokelat. Kamu harus menyebutkan langkah demi langkah:</p>
<ol class="list-decimal pl-6 space-y-1">
<li>Ambil dua lembar roti tawar.</li>
<li>Buka toples selai cokelat.</li>
<li>Oleskan selai di atas permukaan roti.</li>
<li>Tutup dengan lembar roti lainnya.</li>
</ol>
<p>Orang yang menulis instruksi untuk komputer disebut <strong class="text-accent font-black">Programmer</strong>. Dengan coding, kita bisa membuat website, game seru, robot pintar, dan aplikasi animasi yang menakjubkan!</p>
</div>', 1),

(2, 'Logika dan Algoritma', 'Susun langkah-langkah teratur untuk memecahkan masalah dengan tepat.', 'Mudah', 10, '🧩', '#4D96FF',
'<div class="space-y-4 text-gray-700 leading-relaxed font-medium">
<p class="text-lg">Di dunia komputer, ada kata ajaib yang sangat penting bernama <strong class="text-blue-600 font-black">Algoritma</strong>!</p>
<div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-xl">
<h4 class="font-bold text-blue-700 mb-1">🧩 Apa itu Algoritma?</h4>
<p>Algoritma adalah urutan langkah demi langkah yang logis dan jelas untuk menyelesaikan sebuah masalah atau mencapai tujuan tertentu.</p>
</div>
<p>Setiap pagi sebelum berangkat ke sekolah, kamu sebenarnya sudah menjalankan algoritma:</p>
<ul class="list-disc pl-6 space-y-1">
<li>Bangun tidur di pagi hari</li>
<li>Mandi dan sikat gigi</li>
<li>Mengenakan seragam sekolah</li>
<li>Sarapan pagi bersama keluarga</li>
<li>Berangkat menuju sekolah</li>
</ul>
<p>Jika urutannya terbalik (misalnya memakai seragam dulu baru mandi), tentu hasilnya jadi berantakan! Begitu juga dalam coding: instruksi harus disusun rapi agar komputer tidak bingung.</p>
</div>', 2),

(3, 'Kenalan dengan Struktur HTML', 'Pelajari tag dasar untuk menyusun kerangka sebuah halaman web.', 'Sedang', 12, '🌐', '#FF6B6B',
'<div class="space-y-4 text-gray-700 leading-relaxed font-medium">
<p class="text-lg">Setiap website di internet, termasuk website KodingKids ini, dibangun menggunakan bahasa bernama <strong class="text-red-500 font-black">HTML</strong> (HyperText Markup Language).</p>
<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl">
<h4 class="font-bold text-red-700 mb-1">🌐 HTML seperti Kerangka Bangunan</h4>
<p>Jika website diibaratkan sebuah rumah, maka HTML adalah pondasi, tiang, dan dinding bata utamanya.</p>
</div>
<p>HTML tersusun dari potongan kode yang disebut <code class="bg-gray-100 text-red-600 px-2 py-1 rounded font-mono font-bold">Tag</code>. Kebanyakan tag memiliki pembuka dan penutup:</p>
<ul class="list-disc pl-6 space-y-2">
<li><code class="bg-gray-100 text-red-600 px-2 py-1 rounded font-mono font-bold">&lt;h1&gt;Judul Besar&lt;/h1&gt;</code> untuk membuat judul utama.</li>
<li><code class="bg-gray-100 text-red-600 px-2 py-1 rounded font-mono font-bold">&lt;p&gt;Halo kawan!&lt;/p&gt;</code> untuk membuat paragraf teks.</li>
<li><code class="bg-gray-100 text-red-600 px-2 py-1 rounded font-mono font-bold">&lt;a href="..."&gt;Tautan&lt;/a&gt;</code> untuk link ke halaman lain.</li>
<li><code class="bg-gray-100 text-red-600 px-2 py-1 rounded font-mono font-bold">&lt;img src="..."&gt;</code> untuk menampilkan gambar keren.</li>
</ul>
</div>', 3),

(4, 'Warna dan Gaya dengan CSS', 'Ubah warna, ukuran, dan tata letak halaman agar indah dipandang.', 'Sedang', 14, '🎨', '#FF9F43',
'<div class="space-y-4 text-gray-700 leading-relaxed font-medium">
<p class="text-lg">Setelah rumah memiliki kerangka bata (HTML), sekarang saatnya mengecat dinding, memasang tirai cantik, dan menata perabot dengan <strong class="text-orange-500 font-black">CSS</strong> (Cascading Style Sheets)!</p>
<div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-xl">
<h4 class="font-bold text-orange-700 mb-1">🎨 Mengapa Kita Butuh CSS?</h4>
<p>Tanpa CSS, halaman web hanya akan terlihat putih polos dan teks hitam yang membosankan. CSS membuat website ceria dan penuh warna!</p>
</div>
<p>Contoh aturan sederhana CSS:</p>
<pre class="bg-gray-800 text-yellow-300 p-4 rounded-2xl font-mono text-sm overflow-x-auto">
p {
    color: purple;          /* Warna teks jadi ungu */
    font-size: 18px;        /* Ukuran huruf 18 piksel */
    background-color: #FFF; /* Latar belakang putih */
}
</pre>
<p>Dengan CSS, kita juga bisa mengatur letak tombol di tengah, memberi bayangan yang lembut, dan membuat animasi tombol membal saat disentuh kursor!</p>
</div>', 4),

(5, 'Interaktivitas dengan JavaScript', 'Beri kekuatan agar websitemu bisa berpikir dan merespons klik pemain!', 'Sulit', 15, '⚡', '#6BCB77',
'<div class="space-y-4 text-gray-700 leading-relaxed font-medium">
<p class="text-lg">Kini websitemu sudah punya struktur (HTML) dan tampilan cantik (CSS). Agar website bisa bergerak aktif dan merespons pemain, kita tambahkan <strong class="text-green-600 font-black">JavaScript</strong>!</p>
<div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl">
<h4 class="font-bold text-green-700 mb-1">⚡ Otak dan Tenaga dari Website</h4>
<p>JavaScript membuat website hidup: menghitung skor game, menampilkan pesan ucapan selamat, dan membuat kuis interaktif seperti di KodingKids!</p>
</div>
<p>Mari lihat contoh sederhana kode JavaScript:</p>
<pre class="bg-gray-800 text-green-400 p-4 rounded-2xl font-mono text-sm overflow-x-auto">
function beriSelamat() {
    alert("Hore! Kamu berhasil menjawab kuis!");
}
</pre>
<p>Ketika tombol ditekan, JavaScript akan langsung memunculkan pesan gembira kepada pengguna!</p>
</div>', 5)
ON DUPLICATE KEY UPDATE `judul`=VALUES(`judul`), `konten`=VALUES(`konten`);

-- Soal Kuis (20 Soal: 4 Soal per Materi)
INSERT INTO `kuis` (`id`, `materi_id`, `pertanyaan`, `pilihan_a`, `pilihan_b`, `pilihan_c`, `pilihan_d`, `jawaban_benar`) VALUES
-- Kuis Materi 1
(1, 1, 'Apa yang dimaksud dengan coding?', 'Memberi instruksi kepada komputer', 'Menggambar di atas kertas', 'Memperbaiki kipas angin', 'Menonton video kartun', 'a'),
(2, 1, 'Orang yang menulis kode program komputer disebut?', 'Dokter', 'Programmer', 'Pilot', 'Koki', 'b'),
(3, 1, 'Komputer menjalankan perintah yang ditulis secara...', 'Acak dan terburu-buru', 'Jelas dan berurutan', 'Tanpa aturan', 'Rahasia tanpa kata-kata', 'b'),
(4, 1, 'Contoh karya yang bisa dibuat dari coding adalah...', 'Buku tulis cetak', 'Situs web dan game interaktif', 'Pintu kayu', 'Kue bolu', 'b'),

-- Kuis Materi 2
(5, 2, 'Apa nama urutan langkah teratur untuk menyelesaikan masalah?', 'Algoritma', 'Avatar', 'Browser', 'Keyboard', 'a'),
(6, 2, 'Manakah contoh algoritma dalam kehidupan sehari-hari?', 'Resep memasak puding cokelat', 'Melihat pemandangan awan', 'Tidur nyenyak semalaman', 'Mendengarkan suara hujan', 'a'),
(7, 2, 'Mengapa urutan langkah algoritma harus tepat?', 'Agar komputer tidak bingung dan hasilnya benar', 'Biar warnanya lebih terang', 'Supaya baterai cepat habis', 'Agar layarnya mati', 'a'),
(8, 2, 'Jika membuat algoritma memakai seragam sekolah, kapan seragam dipakai?', 'Sebelum mandi', 'Setelah mandi dan mengeringkan badan', 'Saat sedang tidur', 'Sebelum bangun pagi', 'b'),

-- Kuis Materi 3
(9, 3, 'Bahasa apa yang digunakan untuk menyusun kerangka halaman web?', 'HTML', 'Kamera', 'Speaker', 'Proyektor', 'a'),
(10, 3, 'Tag HTML manakah yang dipakai untuk membuat judul besar?', '<h1>', '<p>', '<img>', '<br>', 'a'),
(11, 3, 'Tag HTML manakah yang digunakan untuk membuat sebuah paragraf teks?', '<p>', '<link>', '<head>', '<title>', 'a'),
(12, 3, 'Tag HTML yang digunakan untuk menampilkan gambar adalah...', '<img>', '<foto>', '<suara>', '<warna>', 'a'),

-- Kuis Materi 4
(13, 4, 'Apa fungsi utama CSS pada pembuatan website?', 'Mengatur tampilan, warna, dan gaya halaman', 'Mematikan komputer secara otomatis', 'Mengirim email rahasia', 'Memperbaiki kabel listrik', 'a'),
(14, 4, 'Properti CSS manakah yang digunakan untuk mengganti warna teks?', 'color', 'background-audio', 'speed', 'jump', 'a'),
(15, 4, 'Jika HTML diibaratkan kerangka rumah, maka CSS diibaratkan sebagai...', 'Cat dinding dan dekorasi rumah', 'Kunci pintu gudang', 'Tanah tempat rumah berdiri', 'Mobil di garasi', 'a'),
(16, 4, 'Properti CSS untuk mengubah ukuran huruf teks adalah...', 'font-size', 'text-volume', 'screen-zoom', 'big-letters', 'a'),

-- Kuis Materi 5
(17, 5, 'Bahasa pemrograman web yang berfungsi membuat website interaktif dan hidup adalah...', 'JavaScript', 'Photoshop', 'Microwave', 'Printer', 'a'),
(18, 5, 'Perintah JavaScript apa yang sering dipakai memunculkan kotak pesan di layar?', 'alert()', 'stop()', 'shutDown()', 'color()', 'a'),
(19, 5, 'Apa yang terjadi saat kita menekan tombol kuis di website?', 'JavaScript memeriksa jawaban dan menghitung skor', 'Komputer langsung mati', 'Kertas keluar dari printer', 'Layar berubah jadi hitam', 'a'),
(20, 5, 'Gabungan 3 bahasa utama dalam membuat website modern adalah...', 'HTML, CSS, dan JavaScript', 'Word, Excel, dan PowerPoint', 'Monitor, Mouse, dan CPU', 'Paint, Kalkulator, dan Notepad', 'a')
ON DUPLICATE KEY UPDATE `pertanyaan`=VALUES(`pertanyaan`);

-- Toko Poin (toko_items)
INSERT INTO `toko_items` (`id`, `nama`, `deskripsi`, `kategori`, `harga`, `icon`, `nilai`) VALUES
(1, 'Ninja Koding', 'Avatar ninja lincah yang mahir menaklukkan tantangan.', 'avatar', 50, '🥷', ''),
(2, 'Robot AI Cilik', 'Avatar robot cerdas pelindung masa depan digital.', 'avatar', 80, '🤖', ''),
(3, 'Astronot Coder', 'Avatar penjelajah antariksa pencari galaxy kode.', 'avatar', 120, '👨‍🚀', ''),
(4, 'Penyihir Algoritma', 'Avatar penyihir sakti penguasa mantra rahasia.', 'avatar', 150, '🧙‍♂️', ''),
(5, 'Bug Hunter', 'Penemu dan pembasmi bug tercepat di markas.', 'gelar', 40, '🐞', ''),
(6, 'Algoritma Master', 'Ahli pemecah masalah langkah demi langkah.', 'gelar', 70, '⚡', ''),
(7, 'Tech Prodigy', 'Juara inovasi teknologi muda berbakat.', 'gelar', 100, '🏆', ''),
(8, 'Neon Violet Glow', 'Bingkai ungu menyala untuk avatar kerenmu.', 'border', 50, '✨', 'border: 3px solid #6C63FF; box-shadow: 0 0 12px #6C63FF;'),
(9, 'Golden Champion', 'Bingkai emas berkilau layaknya pemenang sejati.', 'border', 90, '🌟', 'border: 3px solid #F59E0B; box-shadow: 0 0 15px #FBBF24;'),
(10, 'Flame Runner', 'Bingkai merah menyala penuh energi dan semangat.', 'border', 120, '🔥', 'border: 3px solid #EF4444; box-shadow: 0 0 15px #F87171;')
ON DUPLICATE KEY UPDATE `nama`=VALUES(`nama`);

-- Akun Pengguna Bawaan (Users)
-- Password untuk semua akun bawaan adalah: password123
INSERT INTO `users` (`id`, `nama`, `username`, `email`, `password`, `avatar`, `role`, `poin`) VALUES
(1, 'Pak Guru Budi', 'guru', 'guru@kodingkids.id', '$2y$12$itdCzGKXPO95kWTe8ve/BOqDGI08PAlvdxqPAvQ9h3EH3csqHN9tW', '👨‍🏫', 'guru', 500),
(2, 'Andi Saputra', 'andi', 'andi@kodingkids.id', '$2y$12$itdCzGKXPO95kWTe8ve/BOqDGI08PAlvdxqPAvQ9h3EH3csqHN9tW', '🧑🏻‍💻', 'siswa', 320),
(3, 'Siti Aminah', 'siti', 'siti@kodingkids.id', '$2y$12$itdCzGKXPO95kWTe8ve/BOqDGI08PAlvdxqPAvQ9h3EH3csqHN9tW', '🦄', 'siswa', 240),
(4, 'Budi Santoso', 'budi', 'budi@kodingkids.id', '$2y$12$itdCzGKXPO95kWTe8ve/BOqDGI08PAlvdxqPAvQ9h3EH3csqHN9tW', '🦊', 'siswa', 150)
ON DUPLICATE KEY UPDATE `nama`=VALUES(`nama`);

-- Berikan badge awal untuk user
INSERT IGNORE INTO `user_badges` (`user_id`, `badge_id`) VALUES
(2, 1), (2, 2), (2, 3), (2, 4),
(3, 1), (3, 2), (3, 3),
(4, 1), (4, 2);

-- Berikan progress materi untuk murid
INSERT IGNORE INTO `progress` (`user_id`, `materi_id`, `selesai`) VALUES
(2, 1, 1), (2, 2, 1), (2, 3, 1), (2, 4, 1),
(3, 1, 1), (3, 2, 1), (3, 3, 1),
(4, 1, 1), (4, 2, 1);

-- Berikan riwayat nilai kuis
INSERT INTO `nilai_kuis` (`user_id`, `materi_id`, `skor`, `total_soal`) VALUES
(2, 1, 4, 4),
(2, 2, 4, 4),
(2, 3, 3, 4),
(3, 1, 4, 4),
(3, 2, 3, 4),
(4, 1, 4, 4)
ON DUPLICATE KEY UPDATE `skor`=VALUES(`skor`);
