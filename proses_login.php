<?php
session_start();
include 'koneksi.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = md5($_POST['password']);

$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");

if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    $_SESSION['username'] = $data['username'];
    $_SESSION['role'] = $data['role'];
    
    if ($data['role'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: index.php");
    }
    exit();
} else {
    echo "<script>
            alert('Username atau Password salah!');
            window.location.href='index.php';
          </script>";
}
?>