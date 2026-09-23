<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman: Jika bukan admin, tendang kembali ke halaman depan
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Jeopardy - Dasbor Admin</title>
    <style>
        :root {
            --bg-dark: #0f172a; --bg-panel: #1e293b; --primary: #3b82f6; --text: #f8fafc; --accent: #fbbf24;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text); display: flex; min-height: 100vh; }
        
        /* SIDEBAR NAVIGASI */
        .sidebar {
            width: 250px; background-color: var(--bg-panel); padding: 20px;
            border-right: 1px solid #334155; display: flex; flex-direction: column;
        }
        .sidebar h2 { color: var(--accent); font-size: 1.5rem; margin-bottom: 30px; text-align: center; }
        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 10px; flex-grow: 1; }
        .nav-menu li a {
            text-decoration: none; color: #cbd5e1; font-weight: 600; padding: 12px 15px;
            display: block; border-radius: 8px; transition: 0.2s;
        }
        .nav-menu li a:hover, .nav-menu li a.active {
            background-color: var(--primary); color: white;
        }
        .btn-logout {
            background-color: #ef4444; color: white; text-align: center; padding: 12px;
            border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: auto;
        }
        
        /* KONTEN UTAMA */
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .header-title { font-size: 2rem; margin-bottom: 10px; }
        .header-subtitle { color: #94a3b8; margin-bottom: 30px; }
        
        /* KARTU STATISTIK */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;}
        .stat-card { background-color: var(--bg-panel); padding: 25px; border-radius: 12px; border: 1px solid #334155; }
        .stat-card h3 { color: #94a3b8; font-size: 1rem; margin-bottom: 10px; }
        .stat-card .number { font-size: 2.5rem; font-weight: 900; color: var(--accent); }

        .welcome-panel { background: linear-gradient(135deg, #1e3a8a, #1e40af); padding: 30px; border-radius: 12px; border: 1px solid #3b82f6; }
        .welcome-panel h2 { margin-bottom: 10px; color: white; }
    </style>
</head>
<body>

    <!-- SIDEBAR KIRI -->
    <aside class="sidebar">
        <h2>CMS Edukasi</h2>
        <ul class="nav-menu">
            <li><a href="admin.php" class="active">🏠 Dasbor</a></li>
            <li><a href="kategori.php">📚 Kelola Kategori</a></li>
            <li><a href="soal.php">📝 Bank Soal (Acak)</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Game</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Keluar (Logout)</a>
    </aside>

    <!-- AREA KONTEN KANAN -->
    <main class="main-content">
        <h1 class="header-title">Selamat Datang, Guru!</h1>
        <p class="header-subtitle">Kelola seluruh konten permainan Jeopardy Anda di panel ini.</p>

        <?php
        // Menghitung jumlah data untuk ditampilkan di Dasbor
        $total_kategori = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM kategori"));
        $total_soal = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM soal"));
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Kategori Terdaftar</h3>
                <div class="number"><?= $total_kategori ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Bank Soal</h3>
                <div class="number"><?= $total_soal ?></div>
            </div>
        </div>

        <div class="welcome-panel">
            <h2>Langkah Selanjutnya:</h2>
            <p style="color: #bfdbfe; line-height: 1.6;">
                1. Buka menu <b>Kelola Kategori</b> untuk menambahkan mata pelajaran.<br>
                2. Buka menu <b>Bank Soal</b> untuk memasukkan pertanyaan, jawaban, batas waktu, dan gambar/audio pendukung.<br>
                3. Sistem secara otomatis akan mengacak soal jika Anda memasukkan lebih dari 1 soal untuk nilai poin yang sama (misalnya: 3 soal di kategori Agama bernilai 100 poin).
            </p>
        </div>
    </main>

</body>
</html>