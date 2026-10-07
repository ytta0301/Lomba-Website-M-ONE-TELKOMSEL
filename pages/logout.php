<?php
require_once '../includes/header.php'; // Mengaktifkan session_start() dari header

// Hapus semua data session
session_unset();
session_destroy();

// Arahkan kembali ke halaman utama (Beranda)
header("Location: ../index.php");
exit();
?>