<?php
// includes/header.php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

$current_page = basename($_SERVER['PHP_SELF']);
$user_poin = 0;
$user_nama = '';
$user_avatar = '🐱';

if (isLoggedIn()) {
    $uid = $_SESSION['user_id'];
    $user_data = $conn->query("SELECT nama, poin, avatar FROM users WHERE id = $uid")->fetch_assoc();
    $user_poin = $user_data['poin'];
    $user_nama = $user_data['nama'];
    $user_avatar = renderAvatar($user_data['avatar']); // ⭐ PENTING: pakai renderAvatar()
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' — KodingKids' : 'KodingKids — Belajar Coding Itu Seru!' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>assets/css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6C63FF',
                        accent: '#FF6B6B',
                        yellow: '#FFD93D',
                        green: '#6BCB77',
                        blue: '#4D96FF',
                        orange: '#FF9F43',
                    },
                    fontFamily: {
                        nunito: ['Nunito', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-nunito bg-gray-50 min-h-screen">

<!-- NAVBAR -->
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
        <!-- Logo -->
        <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>index.php" class="flex items-center gap-2 text-primary font-black text-2xl hover:scale-105 transition-transform">
            <span class="text-3xl animate-bounce-slow">⭐</span>
            <span>Koding<span class="text-accent">Kids</span></span>
        </a>

        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center gap-1">
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>index.php"
               class="nav-link <?= $current_page == 'index.php' ? 'nav-active' : '' ?>">🏠 Beranda</a>
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/materi.php"
               class="nav-link <?= $current_page == 'materi.php' ? 'nav-active' : '' ?>">📚 Materi</a>
            <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/leaderboard.php"
               class="nav-link <?= $current_page == 'leaderboard.php' ? 'nav-active' : '' ?>">🏆 Leaderboard</a>
            <?php if (in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['guru', 'admin'], true)): ?>
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/guru.php"
                   class="nav-link <?= $current_page == 'guru.php' ? 'nav-active' : '' ?>">👨‍🏫 Panel Guru</a>
            <?php endif; ?>
        </div>

        <!-- Auth Buttons -->
        <div class="flex items-center gap-2">
            <?php if (isLoggedIn()): ?>

                <?php if (in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['guru', 'admin'], true)): ?>
                    <div class="flex items-center gap-2 bg-gray-100 border-2 border-gray-300 rounded-full px-3 py-1">
                        <span class="text-xl">👨‍🏫</span>
                        <span class="text-sm font-bold text-gray-700 hidden sm:block">Admin</span>
                    </div>
                <?php else: ?>
                    <!-- ⭐ AVATAR DI NAVBAR (overflow-hidden agar aman) -->
                    <div class="flex items-center gap-2 bg-yellow-50 border-2 border-yellow-300 rounded-full px-3 py-1">
                        <span class="w-6 h-6 rounded-full overflow-hidden inline-flex items-center justify-center flex-shrink-0"><?= $user_avatar ?></span>
                        <span class="text-sm font-bold text-gray-700 hidden sm:block"><?= htmlspecialchars(explode(' ', $user_nama)[0]) ?></span>
                        <span class="text-sm font-bold text-yellow-600">⭐ <?= $user_poin ?></span>
                    </div>
                <?php endif; ?>

                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/<?= in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['guru', 'admin'], true) ? 'guru.php' : 'dashboard.php' ?>"
                   class="btn-primary hidden sm:block"><?= in_array(strtolower((string) ($_SESSION['user_role'] ?? '')), ['guru', 'admin'], true) ? 'Panel Guru' : 'Dashboard' ?></a>

                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/profile.php"
                   class="bg-white border-2 border-gray-200 hover:bg-gray-100 hover:border-gray-300 text-xl rounded-full w-10 h-10 flex items-center justify-center transition-all shadow-sm" title="Edit Profil">⚙️</a>

                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/logout.php"
                   class="btn-outline text-sm">Keluar</a>

            <?php else: ?>
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/login.php"
                   class="btn-outline">Masuk</a>
                <a href="<?= str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) ?>pages/register.php"
                   class="btn-primary">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</nav>