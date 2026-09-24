<?php
$current_page = basename($_SERVER['PHP_SELF']);
// Baca judul dari config
$config = file_exists('config.json') ? json_decode(file_get_contents('config.json'), true) : ['admin_title' => 'Studio Kuis'];
$judul_admin = $config['admin_title'];
?>
<aside class="sidebar">
    <h2><?= htmlspecialchars($judul_admin) ?></h2>
    <ul class="nav-menu">
        <li><a href="admin.php" class="<?= $current_page == 'admin.php' ? 'active' : '' ?>">📊 Dasbor Utama</a></li>
        <li><a href="kategori.php" class="<?= $current_page == 'kategori.php' ? 'active' : '' ?>">📑 Topik Kuis</a></li>
        <li><a href="soal.php" class="<?= $current_page == 'soal.php' ? 'active' : '' ?>">📝 Bank Soal (Acak)</a></li>
        <li><a href="pengaturan.php" class="<?= $current_page == 'pengaturan.php' ? 'active' : '' ?>">⚙️ Pengaturan</a></li>
        
        <!-- Garis pembatas dan tombol Keluar yang dirapatkan ke atas -->
        <li style="margin-top: 15px; border-top: 1px solid #334155; padding-top: 15px;">
            <a href="logout.php" class="btn-logout" style="margin-top: 0; display: block;">🚪 Keluar (Logout)</a>
        </li>
    </ul>
</aside>