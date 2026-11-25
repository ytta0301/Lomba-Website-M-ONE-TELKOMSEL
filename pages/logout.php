<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Hapus semua data session
session_unset();
session_destroy();

// Arahkan kembali ke halaman utama (Beranda)
header("Location: ../index.php");
exit();
?>