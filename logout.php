<?php
session_start();
session_destroy(); // Menghapus semua data sesi (keluar dari mode Admin)
header("Location: index.php");
?>