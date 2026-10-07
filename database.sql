CREATE DATABASE IF NOT EXISTS kodingkids CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kodingkids;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(20) NOT NULL DEFAULT '🧑🏻‍💻',
    points INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS materials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description VARCHAR(255) NOT NULL,
    level VARCHAR(40) NOT NULL,
    duration TINYINT UNSIGNED NOT NULL DEFAULT 10,
    icon VARCHAR(20) NOT NULL DEFAULT '💻',
    content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quiz_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    options TEXT NOT NULL,
    correct_answer VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quiz_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    score TINYINT UNSIGNED NOT NULL,
    total TINYINT UNSIGNED NOT NULL,
    attempt_date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_daily_attempt (user_id, attempt_date),
    CONSTRAINT fk_attempt_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT IGNORE INTO materials (id, title, description, level, duration, icon, content) VALUES
(1, 'Berkenalan dengan Dunia Coding', 'Kenali coding dan cara komputer menjalankan instruksi.', 'Pemula', 8, '💻', 'Coding adalah cara kita memberi instruksi kepada komputer. Komputer sangat cepat mengikuti instruksi, tetapi instruksi itu harus ditulis dengan jelas dan berurutan.\n\nBayangkan kamu sedang memberi tahu teman cara membuat roti isi. Kamu perlu menyebutkan langkah-langkahnya satu per satu. Program komputer bekerja dengan cara yang mirip: kumpulan instruksi yang dibuat untuk menyelesaikan tugas tertentu.\n\nOrang yang menulis instruksi untuk komputer disebut programmer. Dengan coding, kita bisa membuat situs web, permainan, animasi, dan banyak hal menarik lainnya.'),
(2, 'Logika dan Algoritma', 'Susun langkah-langkah sederhana untuk memecahkan masalah.', 'Pemula', 10, '🧩', 'Algoritma adalah urutan langkah yang jelas untuk menyelesaikan suatu masalah. Resep memasak dan petunjuk arah adalah contoh algoritma yang sering kita temui.\n\nAlgoritma yang baik memiliki langkah yang runtut dan tidak membingungkan. Contohnya, untuk menyiapkan diri ke sekolah: bangun tidur, mandi, memakai seragam, sarapan, lalu berangkat.\n\nSaat membuat program, pikirkan dulu tujuanmu. Pecah masalah besar menjadi langkah-langkah kecil, kemudian susun langkah tersebut dalam urutan yang masuk akal.'),
(3, 'Kenalan dengan HTML', 'Pelajari tag dasar untuk menyusun halaman web.', 'Pemula', 12, '🌐', 'HTML adalah bahasa yang digunakan untuk menyusun struktur halaman web. HTML menggunakan tag yang biasanya memiliki tag pembuka dan penutup.\n\nContohnya, <h1>Judul</h1> membuat judul besar, sedangkan <p>Halo!</p> membuat paragraf. Tag <a> dipakai untuk tautan dan tag <img> untuk menampilkan gambar.\n\nSebuah halaman HTML umumnya memiliki bagian <html>, <head>, dan <body>. Bagian body berisi hal-hal yang terlihat oleh pengunjung halaman.'),
(4, 'Warna dan Tampilan dengan CSS', 'Ubah warna, ukuran, dan tata letak halaman web.', 'Menengah', 14, '🎨', 'CSS adalah bahasa untuk mengatur tampilan halaman web. Jika HTML adalah kerangka rumah, CSS adalah cat, dekorasi, dan tata letaknya.\n\nKita bisa mengubah warna teks menggunakan color, warna latar dengan background-color, dan ukuran teks dengan font-size. Contoh: p { color: purple; } akan membuat teks paragraf berwarna ungu.\n\nCobalah mengubah satu aturan CSS pada satu waktu. Dengan begitu, kamu dapat melihat pengaruh setiap perubahan pada halamanmu.');

INSERT IGNORE INTO quiz_questions (id, question, options, correct_answer) VALUES
(1, 'Apa yang dimaksud dengan coding?', '[ "Menggambar di kertas", "Memberi instruksi kepada komputer", "Memperbaiki meja" ]', 'Memberi instruksi kepada komputer'),
(2, 'Apa nama urutan langkah untuk menyelesaikan masalah?', '[ "Algoritma", "Avatar", "Browser" ]', 'Algoritma'),
(3, 'Bahasa apa yang digunakan untuk menyusun struktur halaman web?', '[ "HTML", "Kamera", "Speaker" ]', 'HTML'),
(4, 'Apa fungsi CSS pada halaman web?', '[ "Mengatur tampilan halaman", "Mengirim email", "Mematikan komputer" ]', 'Mengatur tampilan halaman'),
(5, 'Tag HTML apa yang biasanya digunakan untuk membuat judul utama?', '[ "<h1>", "<img>", "<br>" ]', '<h1>');
