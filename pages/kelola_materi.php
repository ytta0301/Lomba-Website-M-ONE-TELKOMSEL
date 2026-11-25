<?php
$page_title = 'Kelola Materi & Kuis';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$role_stmt = $conn->prepare('SELECT role FROM users WHERE id = ? LIMIT 1');
if (!$role_stmt) {
    die('Gagal memeriksa hak akses: ' . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}
$role_stmt->bind_param('i', $_SESSION['user_id']);
$role_stmt->execute();
$current_user = $role_stmt->get_result()->fetch_assoc();
$role_stmt->close();

$user_role = strtolower((string) ($current_user['role'] ?? ''));
if (!in_array($user_role, ['guru', 'admin'], true)) {
    redirect('login.php');
}
$_SESSION['user_role'] = $user_role;

$csrf_token = $_SESSION['csrf_token'] ?? '';
if ($csrf_token === '') {
    $csrf_token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $csrf_token;
}

$pesan = '';
$pesan_type = '';
$form = [
    'judul' => '',
    'deskripsi' => '',
    'level' => 'Mudah',
    'icon' => '🚀',
    'warna' => '#6C63FF',
    'urutan' => '1',
    'konten' => '',
];
$questions = [[
    'pertanyaan' => '',
    'a' => '',
    'b' => '',
    'c' => '',
    'd' => '',
    'kunci' => 'a',
]];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted_token = $_POST['csrf_token'] ?? '';
    if (!is_string($posted_token) || !hash_equals($csrf_token, $posted_token)) {
        $pesan = 'Permintaan tidak valid atau sesi sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.';
        $pesan_type = 'error';
    } elseif (isset($_POST['hapus_materi'])) {
        $hapus_id = filter_input(INPUT_POST, 'materi_id', FILTER_VALIDATE_INT);
        if (!$hapus_id || $hapus_id < 1) {
            $pesan = 'Materi yang akan dihapus tidak valid.';
            $pesan_type = 'error';
        } else {
            try {
                $conn->begin_transaction();
                foreach (['kuis', 'progress', 'nilai_kuis'] as $table) {
                    $delete_stmt = $conn->prepare("DELETE FROM $table WHERE materi_id = ?");
                    if (!$delete_stmt) {
                        throw new RuntimeException($conn->error);
                    }
                    $delete_stmt->bind_param('i', $hapus_id);
                    $delete_stmt->execute();
                    $delete_stmt->close();
                }

                $delete_stmt = $conn->prepare('DELETE FROM materi WHERE id = ?');
                if (!$delete_stmt) {
                    throw new RuntimeException($conn->error);
                }
                $delete_stmt->bind_param('i', $hapus_id);
                $delete_stmt->execute();
                $deleted = $delete_stmt->affected_rows > 0;
                $delete_stmt->close();
                $conn->commit();

                $pesan = $deleted ? 'Materi dan data kuis terkait berhasil dihapus.' : 'Materi tidak ditemukan.';
                $pesan_type = $deleted ? 'success' : 'error';
            } catch (mysqli_sql_exception | RuntimeException $exception) {
                $conn->rollback();
                error_log('Gagal menghapus materi: ' . $exception->getMessage());
                $pesan = 'Gagal menghapus materi. Periksa log server untuk detail kesalahan.';
                $pesan_type = 'error';
            }
        }
    } elseif (isset($_POST['simpan_materi'])) {
        foreach ($form as $key => $default) {
            $value = $_POST[$key] ?? $default;
            $form[$key] = is_string($value) ? trim($value) : $default;
        }

        $form['level'] = in_array($form['level'], ['Mudah', 'Sedang', 'Sulit'], true)
            ? $form['level']
            : '';
        $form['warna'] = preg_match('/^#[0-9a-fA-F]{6}$/', $form['warna']) ? $form['warna'] : '';
        $urutan = filter_var($form['urutan'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        $question_fields = [
            'pertanyaan' => $_POST['soal_pertanyaan'] ?? [],
            'a' => $_POST['soal_a'] ?? [],
            'b' => $_POST['soal_b'] ?? [],
            'c' => $_POST['soal_c'] ?? [],
            'd' => $_POST['soal_d'] ?? [],
            'kunci' => $_POST['soal_kunci'] ?? [],
        ];
        foreach ($question_fields as $field => $values) {
            $question_fields[$field] = is_array($values) ? $values : [];
        }

        $question_count = max(array_map('count', $question_fields));
        $questions = [];
        $validated_questions = [];
        $validation_errors = [];

        for ($i = 0; $i < $question_count; $i++) {
            $question = [];
            foreach ($question_fields as $field => $values) {
                $value = $values[$i] ?? '';
                $question[$field] = is_string($value) ? trim($value) : '';
            }
            if (implode('', [$question['pertanyaan'], $question['a'], $question['b'], $question['c'], $question['d']]) === '') {
                continue;
            }

            $questions[] = $question;
            $available_choices = array_filter(
                ['a' => $question['a'], 'b' => $question['b'], 'c' => $question['c'], 'd' => $question['d']],
                static fn($choice) => $choice !== ''
            );

            if ($question['pertanyaan'] === '' || count($available_choices) < 2) {
                $validation_errors[] = 'Setiap soal yang diisi perlu memiliki pertanyaan dan minimal dua pilihan jawaban.';
                continue;
            }
            if (!isset($available_choices[$question['kunci']])) {
                $validation_errors[] = 'Kunci jawaban setiap soal harus cocok dengan pilihan yang tersedia.';
                continue;
            }
            $validated_questions[] = $question;
        }
        if ($questions === []) {
            $questions = [['pertanyaan' => '', 'a' => '', 'b' => '', 'c' => '', 'd' => '', 'kunci' => 'a']];
        }

        if ($form['judul'] === '' || $form['deskripsi'] === '' || $form['konten'] === '') {
            $validation_errors[] = 'Judul, deskripsi, dan isi materi wajib diisi.';
        }
        if ($form['level'] === '' || $form['warna'] === '' || !$urutan) {
            $validation_errors[] = 'Level, warna, atau nomor urutan tidak valid.';
        }

        if ($validation_errors !== []) {
            $pesan = implode(' ', array_unique($validation_errors));
            $pesan_type = 'error';
        } else {
            $judul = $form['judul'];
            $deskripsi = $form['deskripsi'];
            $konten = nl2br(htmlspecialchars($form['konten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            $level = $form['level'];
            $icon = $form['icon'] !== '' ? $form['icon'] : '📚';
            $warna = $form['warna'];

            try {
                $conn->begin_transaction();
                $insert_stmt = $conn->prepare(
                    'INSERT INTO materi (judul, deskripsi, konten, level, icon, warna, urutan, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                if (!$insert_stmt) {
                    throw new RuntimeException($conn->error);
                }
                $created_by = (int) $_SESSION['user_id'];
                $insert_stmt->bind_param('ssssssii', $judul, $deskripsi, $konten, $level, $icon, $warna, $urutan, $created_by);
                $insert_stmt->execute();
                $materi_id = $insert_stmt->insert_id;
                $insert_stmt->close();

                if ($validated_questions !== []) {
                    $quiz_stmt = $conn->prepare(
                        'INSERT INTO kuis (materi_id, pertanyaan, pilihan_a, pilihan_b, pilihan_c, pilihan_d, jawaban_benar) VALUES (?, ?, ?, ?, ?, ?, ?)'
                    );
                    if (!$quiz_stmt) {
                        throw new RuntimeException($conn->error);
                    }
                    foreach ($validated_questions as $question) {
                        $quiz_stmt->bind_param(
                            'issssss',
                            $materi_id,
                            $question['pertanyaan'],
                            $question['a'],
                            $question['b'],
                            $question['c'],
                            $question['d'],
                            $question['kunci']
                        );
                        $quiz_stmt->execute();
                    }
                    $quiz_stmt->close();
                }

                $conn->commit();
                $pesan = 'Materi berhasil dipublikasikan dengan ' . count($validated_questions) . ' soal kuis.';
                $pesan_type = 'success';
                $form = [
                    'judul' => '',
                    'deskripsi' => '',
                    'level' => 'Mudah',
                    'icon' => '🚀',
                    'warna' => '#6C63FF',
                    'urutan' => '1',
                    'konten' => '',
                ];
                $questions = [['pertanyaan' => '', 'a' => '', 'b' => '', 'c' => '', 'd' => '', 'kunci' => 'a']];
            } catch (mysqli_sql_exception | RuntimeException $exception) {
                $conn->rollback();
                error_log('Gagal menyimpan materi: ' . $exception->getMessage());
                $pesan = 'Gagal menyimpan materi. Periksa log server untuk detail kesalahan.';
                $pesan_type = 'error';
            }
        }
    }
}

$daftar_materi = $conn->query(
    'SELECT m.*, (SELECT COUNT(*) FROM kuis WHERE materi_id = m.id) AS total_kuis
     FROM materi m ORDER BY m.urutan ASC, m.id DESC'
);

require_once '../includes/header.php';
?>

<main class="min-h-screen bg-gray-50 py-10 px-4">
    <div class="max-w-5xl mx-auto">
        <header class="bg-gradient-to-r from-purple-700 via-indigo-600 to-blue-600 rounded-3xl p-8 text-white mb-8 shadow-lg relative overflow-hidden">
            <div class="relative z-10">
                <span class="bg-white/20 text-xs font-black px-3 py-1 rounded-full uppercase tracking-wider">Formulir Guru</span>
                <h1 class="text-3xl md:text-4xl font-black mt-2">Buat & Publikasikan Materi Baru 📝</h1>
                <p class="text-purple-100 font-semibold mt-1">Buat materi dan soal kuis untuk ditampilkan kepada siswa.</p>
            </div>
            <div class="absolute -right-6 -bottom-6 text-9xl opacity-10" aria-hidden="true">📚</div>
        </header>

        <?php if ($pesan !== ''): ?>
        <div role="status" class="mb-6 px-6 py-4 rounded-2xl font-bold flex items-center gap-3 <?= $pesan_type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
            <span><?= $pesan_type === 'success' ? '✅' : '❌' ?></span>
            <span><?= htmlspecialchars($pesan, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

            <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="flex items-center gap-3 pb-4 mb-6 border-b border-gray-100">
                    <span class="text-3xl" aria-hidden="true">📖</span>
                    <div>
                        <h2 class="text-xl font-black text-gray-800">Bagian 1: Informasi Materi</h2>
                        <p class="text-xs text-gray-400 font-bold">Tentukan judul, tingkat, ikon, warna, dan isi pelajaran.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label for="judul" class="block font-black text-gray-700 text-sm mb-2">Judul Materi <span class="text-red-500">*</span></label>
                        <input id="judul" type="text" name="judul" maxlength="150" class="form-input text-lg font-bold" value="<?= htmlspecialchars($form['judul'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Contoh: Petualangan Variabel" required>
                    </div>
                    <div>
                        <label for="level" class="block font-black text-gray-700 text-sm mb-2">Tingkat Kesulitan</label>
                        <select id="level" name="level" class="form-input font-bold">
                            <?php foreach (['Mudah', 'Sedang', 'Sulit'] as $level_option): ?>
                            <option value="<?= $level_option ?>" <?= $form['level'] === $level_option ? 'selected' : '' ?>><?= $level_option ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="urutan" class="block font-black text-gray-700 text-sm mb-2">Nomor Urutan</label>
                        <input id="urutan" type="number" name="urutan" value="<?= htmlspecialchars($form['urutan'], ENT_QUOTES, 'UTF-8') ?>" min="1" class="form-input font-bold" required>
                    </div>
                    <div class="md:col-span-2">
                        <label for="icon" class="block font-black text-gray-700 text-sm mb-2">Ikon Emoji</label>
                        <input id="icon" type="text" name="icon" maxlength="20" value="<?= htmlspecialchars($form['icon'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="form-input text-2xl text-center font-bold" aria-describedby="emoji-help">
                        <p id="emoji-help" class="text-xs text-gray-500 font-semibold mt-2">Klik emoji untuk memilih, atau ketik emoji sendiri.</p>
                        <div id="emoji-picker" class="mt-3 grid grid-cols-6 sm:grid-cols-9 md:grid-cols-12 gap-2" role="group" aria-label="Pilihan emoji materi">
                            <?php
                            $emoji_options = [
                                '💻' => 'Komputer', '🖥️' => 'Monitor', '⌨️' => 'Keyboard', '🖱️' => 'Mouse',
                                '📱' => 'Ponsel', '🌐' => 'Internet', '🧑‍💻' => 'Programmer', '🤖' => 'Robot',
                                '🚀' => 'Roket', '🎮' => 'Game', '🧩' => 'Puzzle', '💡' => 'Ide',
                                '📚' => 'Buku', '📖' => 'Materi', '✏️' => 'Pensil', '📝' => 'Catatan',
                                '🔢' => 'Angka', '🔤' => 'Huruf', '🎨' => 'Seni', '🧠' => 'Otak',
                                '⭐' => 'Bintang', '🏆' => 'Piala', '🐱' => 'Kucing', '🐸' => 'Katak',
                            ];
                            foreach ($emoji_options as $emoji => $emoji_name):
                            ?>
                            <button type="button" class="emoji-choice h-12 rounded-xl border-2 border-gray-200 bg-white text-2xl transition hover:border-primary hover:bg-purple-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary" data-emoji="<?= htmlspecialchars($emoji, ENT_QUOTES, 'UTF-8') ?>" aria-label="Pilih emoji <?= htmlspecialchars($emoji_name, ENT_QUOTES, 'UTF-8') ?>" aria-pressed="false" title="<?= htmlspecialchars($emoji_name, ENT_QUOTES, 'UTF-8') ?>"><?= $emoji ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div>
                        <label for="warna" class="block font-black text-gray-700 text-sm mb-2">Warna Tema</label>
                        <div class="flex items-center gap-3">
                            <input id="warna" type="color" name="warna" value="<?= htmlspecialchars($form['warna'], ENT_QUOTES, 'UTF-8') ?>" class="w-12 h-11 p-1 rounded-xl border border-gray-200 cursor-pointer">
                            <span class="text-xs text-gray-500 font-semibold">Pilih warna untuk kartu materi.</span>
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label for="deskripsi" class="block font-black text-gray-700 text-sm mb-2">Deskripsi Singkat <span class="text-red-500">*</span></label>
                        <input id="deskripsi" type="text" name="deskripsi" maxlength="255" value="<?= htmlspecialchars($form['deskripsi'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="form-input" placeholder="Ringkasan yang muncul di kartu materi..." required>
                    </div>
                    <div class="md:col-span-2">
                        <label for="konten" class="block font-black text-gray-700 text-sm mb-2">Isi Materi <span class="text-red-500">*</span></label>
                        <textarea id="konten" name="konten" rows="8" class="form-input leading-relaxed" placeholder="Tuliskan materi pelajaran. Baris baru akan dipertahankan." required><?= htmlspecialchars($form['konten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                    </div>
                </div>
            </section>

            <section class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 mb-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl" aria-hidden="true">🧩</span>
                        <div>
                            <h2 class="text-xl font-black text-gray-800">Bagian 2: Soal Kuis</h2>
                            <p class="text-xs text-gray-400 font-bold">Tambahkan pilihan ganda; minimal dua pilihan per soal.</p>
                        </div>
                    </div>
                    <button type="button" id="add-question" class="bg-purple-100 text-primary hover:bg-purple-200 font-black px-4 py-2 rounded-xl text-sm transition-all">➕ Tambah Soal</button>
                </div>

                <div id="question-list" class="space-y-6">
                    <?php foreach ($questions as $index => $question): ?>
                    <fieldset class="question-item bg-gray-50/70 border-2 border-gray-200 rounded-2xl p-5">
                        <div class="flex items-center justify-between mb-3">
                            <legend class="font-black text-gray-700 text-sm question-number">Pertanyaan #<?= $index + 1 ?></legend>
                            <?php if ($index > 0): ?>
                            <button type="button" class="remove-question text-red-600 hover:text-red-700 font-black text-xs bg-red-50 px-2 py-1 rounded-lg">✕ Hapus Soal</button>
                            <?php endif; ?>
                        </div>
                        <label class="sr-only">Pertanyaan</label>
                        <input type="text" name="soal_pertanyaan[]" value="<?= htmlspecialchars($question['pertanyaan'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="form-input mb-4 font-bold bg-white" placeholder="Ketik pertanyaan kuis..." >
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <?php foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label): ?>
                            <div>
                                <label class="text-xs font-bold text-gray-500 mb-1 block" for="soal-<?= $index ?>-<?= $key ?>">Pilihan <?= $label ?></label>
                                <input id="soal-<?= $index ?>-<?= $key ?>" type="text" name="soal_<?= $key ?>[]" value="<?= htmlspecialchars($question[$key], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="form-input text-sm bg-white" placeholder="Jawaban <?= $label ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <label class="inline-flex items-center gap-3 bg-white p-3 rounded-xl border border-gray-200">
                            <span class="text-xs font-black text-gray-600">🎯 Kunci Jawaban:</span>
                            <select name="soal_kunci[]" class="font-black text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-1">
                                <?php foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label): ?>
                                <option value="<?= $key ?>" <?= $question['kunci'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </fieldset>
                    <?php endforeach; ?>
                </div>
            </section>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
                <a href="guru.php" class="text-gray-500 font-bold hover:text-gray-700 text-sm">← Kembali ke Panel Guru</a>
                <button type="submit" name="simpan_materi" value="1" class="btn-primary px-10 py-4 text-lg font-black shadow-lg hover:scale-105 transition-all w-full sm:w-auto flex items-center justify-center gap-2">
                    🚀 Publikasikan Materi
                </button>
            </div>
        </form>

        <section class="mt-14 bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-5 bg-gray-50 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-black text-gray-800 text-lg flex items-center gap-2"><span aria-hidden="true">📚</span> Materi yang Tayang</h2>
                <span class="text-xs bg-purple-100 text-primary font-bold px-3 py-1 rounded-full"><?= (int) $daftar_materi->num_rows ?> Materi</span>
            </div>
            <div class="divide-y divide-gray-100">
                <?php if ($daftar_materi->num_rows === 0): ?>
                <p class="p-6 text-center text-gray-500 font-semibold">Belum ada materi.</p>
                <?php else: while ($materi = $daftar_materi->fetch_assoc()): ?>
                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0" style="background: <?= htmlspecialchars($materi['warna'], ENT_QUOTES, 'UTF-8') ?>22">
                            <?= htmlspecialchars($materi['icon'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-black text-gray-800 text-base"><?= htmlspecialchars($materi['judul'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h3>
                            <div class="flex flex-wrap items-center gap-2 text-xs font-bold text-gray-500 mt-1">
                                <span class="text-primary">Urutan #<?= (int) $materi['urutan'] ?></span>
                                <span aria-hidden="true">•</span>
                                <span><?= htmlspecialchars($materi['level'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                <span aria-hidden="true">•</span>
                                <span class="text-yellow-700">🧩 <?= (int) $materi['total_kuis'] ?> soal</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="materi_detail.php?id=<?= (int) $materi['id'] ?>" target="_blank" rel="noopener" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">👁️ Lihat</a>
                        <form method="POST" onsubmit="return confirm('Hapus materi ini beserta seluruh kuis dan progress siswa terkait?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="materi_id" value="<?= (int) $materi['id'] ?>">
                            <button type="submit" name="hapus_materi" value="1" class="bg-red-50 hover:bg-red-100 text-red-700 font-bold text-xs px-3 py-2 rounded-xl transition-all">🗑️ Hapus</button>
                        </form>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>
        </section>
    </div>
</main>

<template id="question-template">
    <fieldset class="question-item bg-gray-50/70 border-2 border-gray-200 rounded-2xl p-5">
        <div class="flex items-center justify-between mb-3">
            <legend class="font-black text-gray-700 text-sm question-number"></legend>
            <button type="button" class="remove-question text-red-600 hover:text-red-700 font-black text-xs bg-red-50 px-2 py-1 rounded-lg">✕ Hapus Soal</button>
        </div>
        <input type="text" name="soal_pertanyaan[]" class="form-input mb-4 font-bold bg-white" placeholder="Ketik pertanyaan kuis...">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
            <div><label class="text-xs font-bold text-gray-500 mb-1 block">Pilihan A</label><input type="text" name="soal_a[]" class="form-input text-sm bg-white" placeholder="Jawaban A"></div>
            <div><label class="text-xs font-bold text-gray-500 mb-1 block">Pilihan B</label><input type="text" name="soal_b[]" class="form-input text-sm bg-white" placeholder="Jawaban B"></div>
            <div><label class="text-xs font-bold text-gray-500 mb-1 block">Pilihan C</label><input type="text" name="soal_c[]" class="form-input text-sm bg-white" placeholder="Jawaban C"></div>
            <div><label class="text-xs font-bold text-gray-500 mb-1 block">Pilihan D</label><input type="text" name="soal_d[]" class="form-input text-sm bg-white" placeholder="Jawaban D"></div>
        </div>
        <label class="inline-flex items-center gap-3 bg-white p-3 rounded-xl border border-gray-200">
            <span class="text-xs font-black text-gray-600">🎯 Kunci Jawaban:</span>
            <select name="soal_kunci[]" class="font-black text-sm text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-1">
                <option value="a">A</option><option value="b">B</option><option value="c">C</option><option value="d">D</option>
            </select>
        </label>
    </fieldset>
</template>

<script>
(() => {
    const list = document.getElementById('question-list');
    const template = document.getElementById('question-template');
    const addButton = document.getElementById('add-question');
    const iconInput = document.getElementById('icon');
    const emojiPicker = document.getElementById('emoji-picker');

    function updateSelectedEmoji() {
        emojiPicker.querySelectorAll('.emoji-choice').forEach((button) => {
            const isSelected = button.dataset.emoji === iconInput.value;
            button.setAttribute('aria-pressed', String(isSelected));
            button.classList.toggle('border-primary', isSelected);
            button.classList.toggle('bg-purple-100', isSelected);
            button.classList.toggle('ring-2', isSelected);
            button.classList.toggle('ring-purple-200', isSelected);
        });
    }

    emojiPicker.addEventListener('click', (event) => {
        const button = event.target.closest('.emoji-choice');
        if (!button) return;
        iconInput.value = button.dataset.emoji;
        updateSelectedEmoji();
    });
    iconInput.addEventListener('input', updateSelectedEmoji);
    updateSelectedEmoji();

    function updateQuestionNumbers() {
        list.querySelectorAll('.question-number').forEach((legend, index) => {
            legend.textContent = `Pertanyaan #${index + 1}`;
        });
    }

    addButton.addEventListener('click', () => {
        list.appendChild(template.content.cloneNode(true));
        updateQuestionNumbers();
    });

    list.addEventListener('click', (event) => {
        const button = event.target.closest('.remove-question');
        if (!button) return;
        button.closest('.question-item').remove();
        updateQuestionNumbers();
    });
})();
</script>

<?php require_once '../includes/footer.php'; ?>
