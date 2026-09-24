<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: index.php"); exit(); }

// Membaca file konfigurasi
$config_file = 'config.json';
if (!file_exists($config_file)) {
    // Buat default jika belum ada
    $default_config = ['admin_title' => 'Studio Kuis', 'game_title' => 'Jeopardy Edukasi'];
    file_put_contents($config_file, json_encode($default_config));
}
$config = json_decode(file_get_contents($config_file), true);

// Menyimpan Perubahan
if (isset($_POST['simpan_pengaturan'])) {
    $config['admin_title'] = htmlspecialchars($_POST['admin_title']);
    $config['game_title'] = htmlspecialchars($_POST['game_title']);
    
    file_put_contents($config_file, json_encode($config));
    $_SESSION['notifikasi'] = "Pengaturan judul berhasil diperbarui!";
    header("Location: pengaturan.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan - Studio Kuis</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div id="toast-container"><div class="toast" id="toast-box"><span id="toast-message"></span></div></div>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <h1 class="header-title">Pengaturan Sistem</h1>

        <div class="form-panel">
            <h3 style="margin-bottom:20px; color:var(--primary);">Identitas Aplikasi</h3>
            <p style="color:#94a3b8; margin-bottom: 25px; font-size:0.95rem;">Ubah nama yang akan tampil di menu sebelah kiri (Admin) dan layar utama pemain.</p>
            
            <form action="pengaturan.php" method="POST">
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Judul Menu Admin (Sidebar)</label>
                        <input type="text" name="admin_title" value="<?= $config['admin_title'] ?>" required>
                    </div>
                    <div class="form-group full">
                        <label>Judul Utama Layar Permainan (Lobi Game)</label>
                        <input type="text" name="game_title" value="<?= $config['game_title'] ?>" required>
                    </div>
                </div>
                <button type="submit" name="simpan_pengaturan" class="btn-submit" style="margin-top: 15px; width: auto;">Simpan Pengaturan</button>
            </form>
        </div>
    </main>

    <?php if(isset($_SESSION['notifikasi'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('toast-message').innerText = "<?= $_SESSION['notifikasi'] ?>";
            document.getElementById('toast-container').classList.add('show');
            setTimeout(() => { document.getElementById('toast-container').classList.remove('show'); }, 3000);
        });
    </script>
    <?php unset($_SESSION['notifikasi']); endif; ?>
</body>
</html>