<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// 1. LOGIKA TAMBAH SOAL
if (isset($_POST['tambah_soal'])) {
    $id_kategori = $_POST['id_kategori'];
    $poin = $_POST['poin'];
    $waktu = $_POST['waktu'] ? $_POST['waktu'] : 30; // Default 30 detik
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    
    // Proses Upload Gambar (Opsional)
    $path_gambar = "";
    if (!empty($_FILES['gambar']['name'])) {
        $nama_gambar = time() . "_" . $_FILES['gambar']['name'];
        $path_gambar = "assets/images/" . $nama_gambar;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $path_gambar);
    }

    // Proses Upload Audio (Opsional)
    $path_audio = "";
    if (!empty($_FILES['audio']['name'])) {
        $nama_audio = time() . "_" . $_FILES['audio']['name'];
        $path_audio = "assets/audio/" . $nama_audio;
        move_uploaded_file($_FILES['audio']['tmp_name'], $path_audio);
    }

    $query = "INSERT INTO soal (id_kategori, poin, waktu, pertanyaan, jawaban, gambar, audio) 
              VALUES ('$id_kategori', '$poin', '$waktu', '$pertanyaan', '$jawaban', '$path_gambar', '$path_audio')";
    mysqli_query($conn, $query);
    header("Location: soal.php");
    exit();
}

// 2. LOGIKA HAPUS SOAL
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    // Hapus file fisik dari folder jika ada
    $cek_file = mysqli_query($conn, "SELECT gambar, audio FROM soal WHERE id_soal='$id'");
    $data_file = mysqli_fetch_assoc($cek_file);
    if($data_file['gambar'] && file_exists($data_file['gambar'])) { unlink($data_file['gambar']); }
    if($data_file['audio'] && file_exists($data_file['audio'])) { unlink($data_file['audio']); }
    
    mysqli_query($conn, "DELETE FROM soal WHERE id_soal='$id'");
    header("Location: soal.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Soal - CMS Jeopardy</title>
    <style>
        /* Gaya dasar dari admin */
        :root { --bg-dark: #0f172a; --bg-panel: #1e293b; --primary: #3b82f6; --text: #f8fafc; --accent: #fbbf24; --danger: #ef4444; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text); display: flex; min-height: 100vh; }
        
        .sidebar { width: 250px; background-color: var(--bg-panel); padding: 20px; border-right: 1px solid #334155; display: flex; flex-direction: column; }
        .sidebar h2 { color: var(--accent); font-size: 1.5rem; margin-bottom: 30px; text-align: center; }
        .nav-menu { list-style: none; display: flex; flex-direction: column; gap: 10px; flex-grow: 1; }
        .nav-menu li a { text-decoration: none; color: #cbd5e1; font-weight: 600; padding: 12px 15px; display: block; border-radius: 8px; transition: 0.2s; }
        .nav-menu li a:hover, .nav-menu li a.active { background-color: var(--primary); color: white; }
        .btn-logout { background-color: var(--danger); color: white; text-align: center; padding: 12px; border-radius: 8px; text-decoration: none; font-weight: bold; margin-top: auto; }
        
        .main-content { flex: 1; padding: 40px; overflow-y: auto; }
        .header-title { font-size: 2rem; margin-bottom: 30px; border-bottom: 2px solid #334155; padding-bottom: 10px;}
        
        /* Form Soal */
        .form-panel { background-color: var(--bg-panel); padding: 25px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #334155; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;}
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: 1 / -1; }
        
        label { color: #94a3b8; font-weight: bold; font-size: 0.9rem;}
        input, select, textarea { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: white; outline: none; }
        textarea { resize: vertical; min-height: 80px; }
        input[type="file"] { background: transparent; padding: 0; border: none; }
        
        button { padding: 12px 24px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; color: white;}
        .btn-submit { background-color: var(--primary); width: 100%; font-size: 1.1rem; }
        .btn-hapus { background-color: var(--danger); padding: 6px 12px; font-size: 0.9rem; text-decoration: none; border-radius: 6px; }

        table { width: 100%; border-collapse: collapse; background-color: var(--bg-panel); border-radius: 12px; overflow: hidden; margin-top: 20px;}
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
        th { background-color: #0f172a; color: var(--accent); }
        .badge { background: #3b82f6; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;}
    </style>
</head>
<body>

    <aside class="sidebar">
        <h2>CMS Edukasi</h2>
        <ul class="nav-menu">
            <li><a href="admin.php">🏠 Dasbor</a></li>
            <li><a href="kategori.php">📚 Kelola Kategori</a></li>
            <li><a href="soal.php" class="active">📝 Bank Soal (Acak)</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Game</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Keluar (Logout)</a>
    </aside>

    <main class="main-content">
        <h1 class="header-title">Bank Soal & Pengacakan</h1>

        <div class="form-panel">
            <h3 style="margin-bottom: 20px; color: var(--accent);">+ Tambah Soal Baru</h3>
            
            <!-- Perhatikan enctype="multipart/form-data" wajib untuk upload file -->
            <form action="soal.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Pilih Kategori Mata Pelajaran</label>
                        <select name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $kat = mysqli_query($conn, "SELECT * FROM kategori");
                            while($k = mysqli_fetch_assoc($kat)) {
                                echo "<option value='{$k['id_kategori']}'>{$k['nama_kategori']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Nilai Poin (Tingkat Kesulitan)</label>
                        <select name="poin" required>
                            <option value="100">100 Poin</option>
                            <option value="200">200 Poin</option>
                            <option value="300">300 Poin</option>
                            <option value="400">400 Poin</option>
                            <option value="500">500 Poin</option>
                        </select>
                    </div>

                    <div class="form-group full">
                        <label>Teks Pertanyaan</label>
                        <textarea name="pertanyaan" placeholder="Ketik pertanyaan di sini..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Kunci Jawaban</label>
                        <input type="text" name="jawaban" placeholder="Kunci jawaban singkat" required>
                    </div>

                    <div class="form-group">
                        <label>Batas Waktu Menjawab (Detik)</label>
                        <input type="number" name="waktu" value="30" min="5" max="120" required>
                    </div>

                    <div class="form-group">
                        <label>Sematkan Gambar (Opsional)</label>
                        <input type="file" name="gambar" accept="image/png, image/jpeg, image/jpg">
                    </div>

                    <div class="form-group">
                        <label>Sematkan Audio (Opsional)</label>
                        <input type="file" name="audio" accept="audio/mp3, audio/mpeg">
                    </div>
                </div>

                <button type="submit" name="tambah_soal" class="btn-submit">Simpan ke Bank Soal</button>
            </form>
        </div>

        <h3 style="color: var(--accent);">Daftar Soal Tersimpan</h3>
        <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 10px;">(Jika ada poin yang sama di kategori yang sama, sistem akan memilih salah satunya secara acak saat bermain)</p>
        
        <table>
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Poin</th>
                    <th>Pertanyaan</th>
                    <th>Waktu</th>
                    <th>Media</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // JOIN tabel soal dan kategori untuk mendapatkan nama kategori
                $query = "SELECT s.*, k.nama_kategori FROM soal s JOIN kategori k ON s.id_kategori = k.id_kategori ORDER BY k.nama_kategori ASC, s.poin ASC";
                $result = mysqli_query($conn, $query);
                
                if(mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)):
                ?>
                <tr>
                    <td><span class="badge"><?= $row['nama_kategori'] ?></span></td>
                    <td style="color: var(--accent); font-weight: bold; font-size: 1.2rem;"><?= $row['poin'] ?></td>
                    <td>
                        <strong style="color: white;"><?= htmlspecialchars($row['pertanyaan']) ?></strong><br>
                        <small style="color: #10b981;">Jwb: <?= htmlspecialchars($row['jawaban']) ?></small>
                    </td>
                    <td><?= $row['waktu'] ?>s</td>
                    <td>
                        <?php if($row['gambar']) echo "📷 "; ?>
                        <?php if($row['audio']) echo "🎵 "; ?>
                        <?php if(!$row['gambar'] && !$row['audio']) echo "-"; ?>
                    </td>
                    <td>
                        <a href="soal.php?hapus=<?= $row['id_soal'] ?>" class="btn-hapus" onclick="return confirm('Hapus soal ini?');">Hapus</a>
                    </td>
                </tr>
                <?php 
                    endwhile;
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>Belum ada soal di dalam database.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

</body>
</html>