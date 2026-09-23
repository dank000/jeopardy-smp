<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// 1. Logika Tambah Kategori
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    header("Location: kategori.php");
    exit();
}

// 2. Logika Edit Kategori
if (isset($_POST['edit'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_kategori']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori='$id'");
    header("Location: kategori.php");
    exit();
}

// 3. Logika Hapus Kategori
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    // Menghapus kategori (karena di database kita pakai ON DELETE CASCADE, 
    // semua soal di dalam kategori ini otomatis akan ikut terhapus agar tidak ada data nyangkut)
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori='$id'");
    header("Location: kategori.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - CMS Jeopardy</title>
    <style>
        /* Menggunakan gaya dasar yang sama dengan admin.php */
        :root { --bg-dark: #0f172a; --bg-panel: #1e293b; --primary: #3b82f6; --text: #f8fafc; --accent: #fbbf24; --danger: #ef4444; --success: #10b981;}
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
        
        /* GAYA FORM & TABEL */
        .form-panel { background-color: var(--bg-panel); padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #334155; }
        .form-panel input[type="text"] { width: 100%; max-width: 400px; padding: 10px; border-radius: 6px; border: 1px solid #475569; background: #0f172a; color: white; margin-bottom: 15px;}
        button { padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; color: white;}
        .btn-submit { background-color: var(--primary); }
        .btn-cancel { background-color: #64748b; display: none; }
        .btn-edit { background-color: var(--accent); color: #0f172a; padding: 6px 12px; font-size: 0.9rem;}
        .btn-hapus { background-color: var(--danger); padding: 6px 12px; font-size: 0.9rem; text-decoration: none; display: inline-block; border-radius: 6px; font-weight: bold;}

        table { width: 100%; border-collapse: collapse; background-color: var(--bg-panel); border-radius: 12px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #334155; }
        th { background-color: #0f172a; color: var(--accent); }
        tr:hover { background-color: rgba(255,255,255,0.05); }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2>CMS Edukasi</h2>
        <ul class="nav-menu">
            <li><a href="admin.php">🏠 Dasbor</a></li>
            <li><a href="kategori.php" class="active">📚 Kelola Kategori</a></li>
            <li><a href="soal.php">📝 Bank Soal (Acak)</a></li>
            <li><a href="pengaturan.php">⚙️ Pengaturan Game</a></li>
        </ul>
        <a href="logout.php" class="btn-logout">🚪 Keluar (Logout)</a>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
        <h1 class="header-title">Kelola Kategori Mata Pelajaran</h1>

        <!-- FORM TAMBAH / EDIT -->
        <div class="form-panel">
            <h3 id="form-title" style="margin-bottom: 15px;">Tambah Kategori Baru</h3>
            <form action="kategori.php" method="POST">
                <!-- Input tersembunyi untuk ID saat mode Edit -->
                <input type="hidden" name="id_kategori" id="cat_id">
                
                <input type="text" name="nama_kategori" id="cat_name" placeholder="Misal: Ilmu Pengetahuan Alam" required>
                <br>
                <button type="submit" name="tambah" id="btn_submit" class="btn-submit">Simpan Kategori</button>
                <button type="button" id="btn_cancel" class="btn-cancel" onclick="resetForm()">Batal Edit</button>
            </form>
        </div>

        <!-- TABEL DATA -->
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Nama Kategori</th>
                    <th width="20%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");
                $no = 1;
                while($row = mysqli_fetch_assoc($query)):
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td style="font-weight: bold;"><?= $row['nama_kategori'] ?></td>
                    <td>
                        <button class="btn-edit" onclick="editCategory('<?= $row['id_kategori'] ?>', '<?= htmlspecialchars($row['nama_kategori'], ENT_QUOTES) ?>')">Edit</button>
                        <a href="kategori.php?hapus=<?= $row['id_kategori'] ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus kategori ini? Semua soal di dalamnya juga akan terhapus!');">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- Script untuk memindahkan data ke Form saat tombol Edit ditekan -->
    <script>
        function editCategory(id, nama) {
            document.getElementById('form-title').innerText = "Edit Kategori";
            document.getElementById('cat_id').value = id;
            document.getElementById('cat_name').value = nama;
            
            // Ubah tombol submit menjadi tombol Edit
            let btnSubmit = document.getElementById('btn_submit');
            btnSubmit.name = "edit";
            btnSubmit.innerText = "Simpan Perubahan";
            
            // Munculkan tombol Batal
            document.getElementById('btn_cancel').style.display = "inline-block";
            
            // Fokuskan kursor ke kotak input
            document.getElementById('cat_name').focus();
        }

        function resetForm() {
            document.getElementById('form-title').innerText = "Tambah Kategori Baru";
            document.getElementById('cat_id').value = "";
            document.getElementById('cat_name').value = "";
            
            let btnSubmit = document.getElementById('btn_submit');
            btnSubmit.name = "tambah";
            btnSubmit.innerText = "Simpan Kategori";
            
            document.getElementById('btn_cancel').style.display = "none";
        }
    </script>

</body>
</html>