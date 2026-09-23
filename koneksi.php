<?php
// koneksi.php
$host = "localhost"; // Server database lokal bawaan XAMPP
$user = "root";      // Username bawaan XAMPP
$pass = "";          // Password bawaan XAMPP (kosong)
$db   = "db_jeopardy"; // Nama database yang tadi kita buat

// Mencoba terhubung ke database
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek apakah koneksi berhasil
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>