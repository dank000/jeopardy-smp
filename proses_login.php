<?php
session_start();
include 'koneksi.php'; // Menyambungkan ke database db_jeopardy

// Menangkap data dari form login di index.php
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = md5($_POST['password']); // Enkripsi MD5 sesuai yang kita buat di database

// Mencari data di tabel users
$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");

if (mysqli_num_rows($query) > 0) {
    // Jika cocok, simpan data ke dalam sesi browser
    $data = mysqli_fetch_assoc($query);
    $_SESSION['username'] = $data['username'];
    $_SESSION['role'] = $data['role'];
    
    // Alihkan kembali ke halaman utama
    header("Location: index.php");
} else {
    // Jika salah, tampilkan peringatan dan kembalikan ke halaman utama
    echo "<script>
            alert('Username atau Password salah!');
            window.location.href='index.php';
          </script>";
}
?>