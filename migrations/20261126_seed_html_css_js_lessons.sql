-- Apply this migration to an existing KodingKids database.
-- Safe to rerun: lesson rows are upserted and extra questions are checked before insert.

INSERT INTO `materi` (`id`, `judul`, `deskripsi`, `level`, `durasi`, `icon`, `warna`, `konten`, `urutan`) VALUES
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
ON DUPLICATE KEY UPDATE
    `judul`=VALUES(`judul`),
    `deskripsi`=VALUES(`deskripsi`),
    `level`=VALUES(`level`),
    `durasi`=VALUES(`durasi`),
    `icon`=VALUES(`icon`),
    `warna`=VALUES(`warna`),
    `konten`=VALUES(`konten`),
    `urutan`=VALUES(`urutan`);

INSERT INTO `kuis` (`id`, `materi_id`, `pertanyaan`, `pilihan_a`, `pilihan_b`, `pilihan_c`, `pilihan_d`, `jawaban_benar`) VALUES
(9, 3, 'Bahasa apa yang digunakan untuk menyusun kerangka halaman web?', 'HTML', 'Kamera', 'Speaker', 'Proyektor', 'a'),
(10, 3, 'Deklarasi apa yang diletakkan di awal dokumen HTML modern?', '<!DOCTYPE html>', '<html-end>', '<start-page>', '<browser>', 'a'),
(11, 3, 'Bagian mana yang berisi konten yang terlihat di halaman?', '<head>', '<body>', '<title>', '<meta>', 'b'),
(12, 3, 'Tag manakah yang digunakan untuk membuat judul utama?', '<h1>', '<p>', '<img>', '<br>', 'a'),
(13, 4, 'Apa fungsi utama CSS pada pembuatan website?', 'Mengatur tampilan, warna, dan gaya halaman', 'Mematikan komputer secara otomatis', 'Mengirim email rahasia', 'Memperbaiki kabel listrik', 'a'),
(14, 4, 'Properti CSS manakah yang digunakan untuk mengganti warna teks?', 'color', 'background-audio', 'speed', 'jump', 'a'),
(15, 4, 'Aturan CSS apa yang membantu menyesuaikan tampilan untuk layar kecil?', '@media', '@picture', '@click', '@html', 'a'),
(16, 4, 'Properti CSS untuk mengubah ukuran huruf teks adalah...', 'font-size', 'text-volume', 'screen-zoom', 'big-letters', 'a'),
(17, 5, 'Bahasa pemrograman web yang berfungsi membuat website interaktif adalah...', 'JavaScript', 'Photoshop', 'Microwave', 'Printer', 'a'),
(18, 5, 'Perintah JavaScript apa yang dapat memunculkan kotak pesan?', 'alert()', 'stop()', 'shutDown()', 'color()', 'a'),
(19, 5, 'Kata kunci yang cocok untuk nilai yang tidak akan diberi nilai baru adalah...', 'const', 'change', 'repeat', 'style', 'a'),
(20, 5, 'Kata kunci yang cocok untuk variabel yang nilainya dapat berubah adalah...', 'const', 'let', 'href', 'class', 'b')
ON DUPLICATE KEY UPDATE
    `materi_id`=VALUES(`materi_id`),
    `pertanyaan`=VALUES(`pertanyaan`),
    `pilihan_a`=VALUES(`pilihan_a`),
    `pilihan_b`=VALUES(`pilihan_b`),
    `pilihan_c`=VALUES(`pilihan_c`),
    `pilihan_d`=VALUES(`pilihan_d`),
    `jawaban_benar`=VALUES(`jawaban_benar`);

INSERT INTO `kuis` (`materi_id`, `pertanyaan`, `pilihan_a`, `pilihan_b`, `pilihan_c`, `pilihan_d`, `jawaban_benar`)
SELECT 3, 'Tag manakah yang digunakan untuk membuat paragraf?', '<p>', '<link>', '<head>', '<title>', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=3 AND `pertanyaan`='Tag manakah yang digunakan untuk membuat paragraf?')
UNION ALL
SELECT 3, 'Pasangan tag manakah yang membuat daftar tanpa nomor?', '<ol> dan <li>', '<ul> dan <li>', '<p> dan <h1>', '<a> dan <img>', 'b'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=3 AND `pertanyaan`='Pasangan tag manakah yang membuat daftar tanpa nomor?')
UNION ALL
SELECT 3, 'Atribut apa yang menentukan tujuan sebuah tautan?', 'src', 'alt', 'href', 'class', 'c'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=3 AND `pertanyaan`='Atribut apa yang menentukan tujuan sebuah tautan?')
UNION ALL
SELECT 3, 'Atribut apa yang memberi deskripsi teks pada gambar?', 'href', 'alt', 'id', 'style', 'b'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=3 AND `pertanyaan`='Atribut apa yang memberi deskripsi teks pada gambar?')
UNION ALL
SELECT 3, 'Tag manakah yang menampilkan gambar dan tidak memerlukan tag penutup?', '<img>', '<p>', '<h1>', '<body>', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=3 AND `pertanyaan`='Tag manakah yang menampilkan gambar dan tidak memerlukan tag penutup?')
UNION ALL
SELECT 3, 'Atribut apa yang cocok untuk memberi nama kelompok gaya CSS pada elemen?', 'class', 'href', 'src', 'alt', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=3 AND `pertanyaan`='Atribut apa yang cocok untuk memberi nama kelompok gaya CSS pada elemen?')
UNION ALL
SELECT 4, 'Selector CSS apakah yang memilih elemen dengan class="kartu"?', '.kartu', '#kartu', 'kartu()', '@kartu', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=4 AND `pertanyaan`='Selector CSS apakah yang memilih elemen dengan class="kartu"?')
UNION ALL
SELECT 4, 'Selector CSS apakah yang memilih elemen dengan id="menu"?', '.menu', '#menu', 'menu()', '*menu', 'b'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=4 AND `pertanyaan`='Selector CSS apakah yang memilih elemen dengan id="menu"?')
UNION ALL
SELECT 4, 'Bagian Box Model yang memberi jarak di antara isi dan border adalah...', 'margin', 'padding', 'outline', 'font-size', 'b'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=4 AND `pertanyaan`='Bagian Box Model yang memberi jarak di antara isi dan border adalah...')
UNION ALL
SELECT 4, 'Bagian Box Model yang memberi jarak di luar border elemen adalah...', 'padding', 'content', 'margin', 'color', 'c'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=4 AND `pertanyaan`='Bagian Box Model yang memberi jarak di luar border elemen adalah...')
UNION ALL
SELECT 4, 'Nilai display apa yang membantu menyusun item dalam satu arah?', 'display: flex', 'display: sound', 'display: paint', 'display: jump', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=4 AND `pertanyaan`='Nilai display apa yang membantu menyusun item dalam satu arah?')
UNION ALL
SELECT 4, 'Pseudo-class apa yang dapat memberi gaya saat penunjuk berada di atas tombol?', ':hover', ':color', ':click-now', ':font', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=4 AND `pertanyaan`='Pseudo-class apa yang dapat memberi gaya saat penunjuk berada di atas tombol?')
UNION ALL
SELECT 5, 'Blok perintah untuk membuat keputusan berdasarkan kondisi menggunakan...', 'if dan else', 'h1 dan p', 'src dan alt', 'margin dan padding', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=5 AND `pertanyaan`='Blok perintah untuk membuat keputusan berdasarkan kondisi menggunakan...')
UNION ALL
SELECT 5, 'Apa yang dimaksud dengan fungsi dalam JavaScript?', 'Kumpulan perintah yang dapat dipanggil saat dibutuhkan', 'Warna latar halaman', 'Tag untuk menampilkan gambar', 'Nama lain untuk file CSS', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=5 AND `pertanyaan`='Apa yang dimaksud dengan fungsi dalam JavaScript?')
UNION ALL
SELECT 5, 'Perintah JavaScript yang menampilkan pesan di console adalah...', 'console.log()', 'page.color()', 'html.show()', 'css.print()', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=5 AND `pertanyaan`='Perintah JavaScript yang menampilkan pesan di console adalah...')
UNION ALL
SELECT 5, 'Perintah apa yang memilih elemen berdasarkan selector CSS?', 'document.querySelector()', 'document.paint()', 'window.makeHTML()', 'style.getColor()', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=5 AND `pertanyaan`='Perintah apa yang memilih elemen berdasarkan selector CSS?')
UNION ALL
SELECT 5, 'Method apa yang digunakan untuk menjalankan fungsi saat tombol diklik?', 'addEventListener("click", ...)', 'addColor("click", ...)', 'makePage("click", ...)', 'setFont("click", ...)', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=5 AND `pertanyaan`='Method apa yang digunakan untuk menjalankan fungsi saat tombol diklik?')
UNION ALL
SELECT 5, 'Pasangan tugas yang benar untuk HTML, CSS, dan JavaScript adalah...', 'HTML struktur, CSS tampilan, JavaScript interaksi', 'HTML warna, CSS data, JavaScript kerangka', 'HTML suara, CSS gambar, JavaScript kabel', 'HTML tombol, CSS komputer, JavaScript monitor', 'a'
WHERE NOT EXISTS (SELECT 1 FROM `kuis` WHERE `materi_id`=5 AND `pertanyaan`='Pasangan tugas yang benar untuk HTML, CSS, dan JavaScript adalah...');
