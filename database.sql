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
<p class="text-lg"><strong class="text-red-500 font-black">HTML</strong> (HyperText Markup Language) adalah bahasa markup untuk menyusun struktur dan isi halaman web. Dalam perumpamaan rumah, HTML adalah kerangka dan ruangannya.</p>
<div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl"><h4 class="font-bold text-red-700 mb-1">🌐 Dokumen HTML</h4><p>Browser membaca tag HTML lalu menampilkan teks, gambar, tautan, dan bagian halaman. HTML mengatur struktur, bukan warna atau tata letak.</p></div>
<h3 class="font-black text-gray-800">Kerangka dasar halaman</h3>
<pre class="bg-gray-800 text-green-300 p-4 rounded-2xl font-mono text-sm overflow-x-auto">&lt;!DOCTYPE html&gt;
&lt;html lang="id"&gt;
  &lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Halaman Pertamaku&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;h1&gt;Halo, dunia!&lt;/h1&gt;
    &lt;p&gt;Aku sedang belajar HTML.&lt;/p&gt;
  &lt;/body&gt;
&lt;/html&gt;</pre>
<ul class="list-disc pl-6 space-y-2"><li><code>&lt;!DOCTYPE html&gt;</code> memberi tahu browser bahwa dokumen memakai HTML modern.</li><li><code>&lt;head&gt;</code> berisi informasi halaman; <code>&lt;title&gt;</code> menjadi judul di tab browser.</li><li><code>&lt;body&gt;</code> berisi konten yang terlihat oleh pengunjung.</li></ul>
<h3 class="font-black text-gray-800">Tag untuk menyusun konten</h3>
<ul class="list-disc pl-6 space-y-2"><li><code>&lt;h1&gt;</code> sampai <code>&lt;h6&gt;</code> membuat judul bertingkat. Gunakan sesuai urutan agar isi mudah dipahami.</li><li><code>&lt;p&gt;</code> membuat paragraf; <code>&lt;ul&gt;</code> atau <code>&lt;ol&gt;</code> membuat daftar dengan item <code>&lt;li&gt;</code>.</li><li><code>&lt;a href="..."&gt;</code> membuat tautan. Nilai <code>href</code> menunjukkan tujuan tautan.</li><li><code>&lt;img src="..." alt="..."&gt;</code> menampilkan gambar. <code>src</code> berisi lokasi gambar dan <code>alt</code> menjelaskan gambarnya.</li></ul>
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-xl"><h4 class="font-bold text-yellow-800 mb-1">🧩 Tag dan atribut</h4><p>Tag biasanya memiliki tag pembuka dan penutup, misalnya <code>&lt;p&gt;Halo!&lt;/p&gt;</code>. Tag gambar dan <code>&lt;br&gt;</code> tidak memiliki tag penutup. Atribut seperti <code>class</code>, <code>href</code>, dan <code>alt</code> memberi informasi tambahan.</p></div>
<p class="font-bold">Pilih tag sesuai arti kontennya. Struktur HTML yang rapi lebih mudah dibaca, ditata dengan CSS, dan digunakan oleh semua orang.</p>
</div>', 3),

(4, 'CSS: Menata Tampilan Halaman Web', 'Atur warna, huruf, jarak, dan tata letak agar halaman nyaman dilihat.', 'Sedang', 14, '🎨', '#FF9F43',
'<div class="space-y-4 text-gray-700 leading-relaxed font-medium">
<p class="text-lg"><strong class="text-orange-500 font-black">CSS</strong> (Cascading Style Sheets) mengatur presentasi visual halaman: warna, huruf, jarak, dan tata letak. Jika HTML adalah kerangka rumah, CSS adalah cat dan dekorasinya.</p>
<div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-xl"><h4 class="font-bold text-orange-700 mb-1">🎨 Anatomi aturan CSS</h4><p>Selector memilih elemen. Di dalam kurung kurawal, deklarasi berisi nama properti, titik dua, nilai, dan titik koma.</p></div>
<pre class="bg-gray-800 text-yellow-300 p-4 rounded-2xl font-mono text-sm overflow-x-auto">p {
  color: purple;
  font-size: 18px;
  background-color: #FFF;
}</pre>
<ul class="list-disc pl-6 space-y-2"><li><strong>Selector tag</strong>, seperti <code>p</code>, memilih semua paragraf.</li><li><strong>Selector class</strong>, seperti <code>.kartu</code>, memilih elemen dengan <code>class="kartu"</code>. Class dapat dipakai berulang.</li><li><strong>Selector ID</strong>, seperti <code>#menu</code>, memilih elemen dengan <code>id="menu"</code>. ID sebaiknya unik dalam satu halaman.</li></ul>
<h3 class="font-black text-gray-800">Jarak dan tata letak</h3>
<p>Box Model memandang setiap elemen seperti kotak: <code>content</code> adalah isi, <code>padding</code> jarak di dalam tepi, <code>border</code> garis tepi, dan <code>margin</code> jarak di luar elemen.</p>
<pre class="bg-gray-800 text-yellow-300 p-4 rounded-2xl font-mono text-sm overflow-x-auto">.kartu {
  padding: 16px;
  border: 2px solid #ddd;
  margin: 12px;
  border-radius: 12px;
}</pre>
<p>Gunakan <code>display: flex</code> untuk menyusun item dalam satu arah, misalnya berjajar atau bertumpuk. Properti seperti <code>justify-content</code> dan <code>align-items</code> membantu merapikan posisinya.</p>
<h3 class="font-black text-gray-800">Warna, interaksi, dan layar berbeda</h3>
<p>CSS dapat memberi warna dengan nama seperti <code>tomato</code>, kode hex seperti <code>#2563EB</code>, atau format RGB. Gunakan ukuran huruf dan kontras yang nyaman dibaca.</p>
<pre class="bg-gray-800 text-yellow-300 p-4 rounded-2xl font-mono text-sm overflow-x-auto">button:hover {
  background-color: #2563EB;
  transition: background-color 0.2s;
}

@media (max-width: 600px) {
  .kartu { padding: 12px; }
}</pre>
<p><code>:hover</code> memberi gaya saat penunjuk berada di atas elemen. <code>transition</code> membuat perubahan terasa halus. <code>@media</code> membantu menyesuaikan tampilan untuk layar kecil.</p>
<div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-xl"><h4 class="font-bold text-orange-700 mb-1">✨ Pisahkan file CSS</h4><p>Aturan CSS bisa ditulis di atribut <code>style</code>, di tag <code>&lt;style&gt;</code>, atau di file terpisah. File eksternal seperti <code>style.css</code> membuat HTML lebih rapi dan gaya mudah dipakai ulang.</p></div>
</div>', 4),

(5, 'JavaScript: Membuat Halaman Web Interaktif', 'Buat halaman merespons klik, memeriksa pilihan, dan memperbarui konten.', 'Sedang', 15, '⚡', '#6BCB77',
'<div class="space-y-4 text-gray-700 leading-relaxed font-medium">
<p class="text-lg"><strong class="text-green-600 font-black">JavaScript</strong> membuat halaman web bisa merespons tindakan pengguna. HTML menyusun isi, CSS mengatur tampilannya, dan JavaScript menambahkan perilaku.</p>
<div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-xl"><h4 class="font-bold text-green-700 mb-1">⚡ Perintah dijalankan oleh browser</h4><p>JavaScript dapat membaca klik, memeriksa jawaban, menghitung skor, lalu memperbarui halaman. Kode bisa ditulis pada tag <code>&lt;script&gt;</code> atau file terpisah berakhiran <code>.js</code>.</p></div>
<h3 class="font-black text-gray-800">Menyimpan informasi</h3>
<p>Variabel menyimpan nilai agar dapat dipakai lagi. Gunakan <code>const</code> jika nama variabel tidak akan diberi nilai baru, dan <code>let</code> jika nilainya perlu berubah.</p>
<pre class="bg-gray-800 text-green-300 p-4 rounded-2xl font-mono text-sm overflow-x-auto">const nama = "Dina";
let skor = 0;
skor = skor + 10;
console.log(nama, skor);</pre>
<p>Teks ditulis dengan tanda kutip, angka dapat dipakai untuk hitungan, dan nilai benar/salah disebut boolean. <code>console.log()</code> membantu melihat nilai saat belajar.</p>
<h3 class="font-black text-gray-800">Membuat halaman merespons</h3>
<p>DOM adalah cara JavaScript mengenali elemen HTML. Pilih elemen dengan <code>document.querySelector()</code>, lalu ubah teks melalui <code>textContent</code>.</p>
<pre class="bg-gray-800 text-green-300 p-4 rounded-2xl font-mono text-sm overflow-x-auto">&lt;button id="tombol"&gt;Klik aku&lt;/button&gt;
&lt;p id="pesan"&gt;Halo!&lt;/p&gt;

const tombol = document.querySelector("#tombol");
const pesan = document.querySelector("#pesan");

tombol.addEventListener("click", function () {
  pesan.textContent = "Kamu berhasil!";
});</pre>
<p><code>addEventListener("click", ...)</code> menjalankan fungsi saat tombol diklik. Fungsi adalah kumpulan perintah yang bisa dipanggil saat dibutuhkan.</p>
<h3 class="font-black text-gray-800">Mengambil keputusan</h3>
<pre class="bg-gray-800 text-green-300 p-4 rounded-2xl font-mono text-sm overflow-x-auto">if (skor &gt;= 10) {
  console.log("Hebat!");
} else {
  console.log("Coba lagi!");
}</pre>
<p><code>if</code> dan <code>else</code> memilih perintah berdasarkan kondisi. Susun langkahnya dengan teliti, lalu uji dengan menekan tombol dan melihat hasilnya.</p>
</div>', 5)
ON DUPLICATE KEY UPDATE `judul`=VALUES(`judul`), `deskripsi`=VALUES(`deskripsi`), `level`=VALUES(`level`), `durasi`=VALUES(`durasi`), `icon`=VALUES(`icon`), `warna`=VALUES(`warna`), `konten`=VALUES(`konten`), `urutan`=VALUES(`urutan`);

-- Soal Kuis (38 soal: 4 soal pengantar dan 10 soal untuk masing-masing HTML, CSS, dan JavaScript)
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
(10, 3, 'Deklarasi apa yang diletakkan di awal dokumen HTML modern?', '<!DOCTYPE html>', '<html-end>', '<start-page>', '<browser>', 'a'),
(11, 3, 'Bagian mana yang berisi konten yang terlihat di halaman?', '<head>', '<body>', '<title>', '<meta>', 'b'),
(12, 3, 'Tag manakah yang digunakan untuk membuat judul utama?', '<h1>', '<p>', '<img>', '<br>', 'a'),
(13, 3, 'Tag manakah yang digunakan untuk membuat paragraf?', '<p>', '<link>', '<head>', '<title>', 'a'),
(14, 3, 'Pasangan tag manakah yang membuat daftar tanpa nomor?', '<ol> dan <li>', '<ul> dan <li>', '<p> dan <h1>', '<a> dan <img>', 'b'),
(15, 3, 'Atribut apa yang menentukan tujuan sebuah tautan?', 'src', 'alt', 'href', 'class', 'c'),
(16, 3, 'Atribut apa yang memberi deskripsi teks pada gambar?', 'href', 'alt', 'id', 'style', 'b'),
(17, 3, 'Tag manakah yang menampilkan gambar dan tidak memerlukan tag penutup?', '<img>', '<p>', '<h1>', '<body>', 'a'),
(18, 3, 'Atribut apa yang cocok untuk memberi nama kelompok gaya CSS pada elemen?', 'class', 'href', 'src', 'alt', 'a'),

-- Kuis Materi 4
(19, 4, 'Apa fungsi utama CSS pada pembuatan website?', 'Mengatur tampilan, warna, dan gaya halaman', 'Mematikan komputer secara otomatis', 'Mengirim email rahasia', 'Memperbaiki kabel listrik', 'a'),
(20, 4, 'Properti CSS manakah yang digunakan untuk mengganti warna teks?', 'color', 'background-audio', 'speed', 'jump', 'a'),
(21, 4, 'Aturan CSS apa yang membantu menyesuaikan tampilan untuk layar kecil?', '@media', '@picture', '@click', '@html', 'a'),
(22, 4, 'Properti CSS untuk mengubah ukuran huruf teks adalah...', 'font-size', 'text-volume', 'screen-zoom', 'big-letters', 'a'),
(23, 4, 'Selector CSS apakah yang memilih elemen dengan class="kartu"?', '.kartu', '#kartu', 'kartu()', '@kartu', 'a'),
(24, 4, 'Selector CSS apakah yang memilih elemen dengan id="menu"?', '.menu', '#menu', 'menu()', '*menu', 'b'),
(25, 4, 'Bagian Box Model yang memberi jarak di antara isi dan border adalah...', 'margin', 'padding', 'outline', 'font-size', 'b'),
(26, 4, 'Bagian Box Model yang memberi jarak di luar border elemen adalah...', 'padding', 'content', 'margin', 'color', 'c'),
(27, 4, 'Nilai display apa yang membantu menyusun item dalam satu arah?', 'display: flex', 'display: sound', 'display: paint', 'display: jump', 'a'),
(28, 4, 'Pseudo-class apa yang dapat memberi gaya saat penunjuk berada di atas tombol?', ':hover', ':color', ':click-now', ':font', 'a'),

-- Kuis Materi 5
(29, 5, 'Blok perintah untuk membuat keputusan berdasarkan kondisi menggunakan...', 'if dan else', 'h1 dan p', 'src dan alt', 'margin dan padding', 'a'),
(30, 5, 'Apa yang dimaksud dengan fungsi dalam JavaScript?', 'Kumpulan perintah yang dapat dipanggil saat dibutuhkan', 'Warna latar halaman', 'Tag untuk menampilkan gambar', 'Nama lain untuk file CSS', 'a'),
(31, 5, 'Bahasa pemrograman web yang berfungsi membuat website interaktif adalah...', 'JavaScript', 'Photoshop', 'Microwave', 'Printer', 'a'),
(32, 5, 'Perintah JavaScript apa yang dapat memunculkan kotak pesan?', 'alert()', 'stop()', 'shutDown()', 'color()', 'a'),
(33, 5, 'Kata kunci yang cocok untuk nilai yang tidak akan diberi nilai baru adalah...', 'const', 'change', 'repeat', 'style', 'a'),
(34, 5, 'Kata kunci yang cocok untuk variabel yang nilainya dapat berubah adalah...', 'const', 'let', 'href', 'class', 'b'),
(35, 5, 'Perintah JavaScript yang menampilkan pesan di console adalah...', 'console.log()', 'page.color()', 'html.show()', 'css.print()', 'a'),
(36, 5, 'Perintah apa yang memilih elemen berdasarkan selector CSS?', 'document.querySelector()', 'document.paint()', 'window.makeHTML()', 'style.getColor()', 'a'),
(37, 5, 'Method apa yang digunakan untuk menjalankan fungsi saat tombol diklik?', 'addEventListener("click", ...)', 'addColor("click", ...)', 'makePage("click", ...)', 'setFont("click", ...)', 'a'),
(38, 5, 'Pasangan tugas yang benar untuk HTML, CSS, dan JavaScript adalah...', 'HTML struktur, CSS tampilan, JavaScript interaksi', 'HTML warna, CSS data, JavaScript kerangka', 'HTML suara, CSS gambar, JavaScript kabel', 'HTML tombol, CSS komputer, JavaScript monitor', 'a')
ON DUPLICATE KEY UPDATE `materi_id`=VALUES(`materi_id`), `pertanyaan`=VALUES(`pertanyaan`), `pilihan_a`=VALUES(`pilihan_a`), `pilihan_b`=VALUES(`pilihan_b`), `pilihan_c`=VALUES(`pilihan_c`), `pilihan_d`=VALUES(`pilihan_d`), `jawaban_benar`=VALUES(`jawaban_benar`);

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
