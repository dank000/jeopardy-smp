<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: index.php"); exit(); }

if (isset($_POST['tambah_kategori'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    $_SESSION['notifikasi'] = "Topik Kuis berhasil ditambahkan!";
    header("Location: kategori.php"); exit();
}

if (isset($_POST['edit_kategori'])) {
    $id = $_POST['id_kategori'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama' WHERE id_kategori='$id'");
    $_SESSION['notifikasi'] = "Nama Topik berhasil diperbarui!";
    header("Location: kategori.php"); exit();
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori='$id'");
    $_SESSION['notifikasi'] = "Topik beserta seluruh soal di dalamnya berhasil dihapus!";
    header("Location: kategori.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Topik Kuis - Studio Kuis</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <h1 class="header-title">Daftar Topik Kuis</h1>
        
        <div class="form-panel">
            <h3 style="margin-bottom:15px; color:var(--primary);">+ Tambah Topik Baru</h3>
            <form action="kategori.php" method="POST" style="display:flex; gap:15px;">
                <input type="text" name="nama_kategori" placeholder="Misal: Sejarah Indonesia..." required style="flex:1;">
                <button type="submit" name="tambah_kategori" class="btn-submit" style="width:auto;">Simpan Topik</button>
            </form>
        </div>

        <!-- Filter Bar Interaktif -->
        <div class="filter-bar">
            <input type="text" id="cariTopik" onkeyup="filterTabel()" placeholder="🔍 Cari nama topik...">
            <select id="urutTopik" onchange="urutTabel()">
                <option value="abjad">Urutkan: Abjad (A-Z)</option>
                <option value="terbaru">Urutkan: ID Terbaru</option>
            </select>
        </div>

        <table id="tabelKategori">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Topik</th>
                    <th>Jumlah Soal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT k.id_kategori, k.nama_kategori, COUNT(s.id_soal) as total_soal 
                          FROM kategori k LEFT JOIN soal s ON k.id_kategori = s.id_kategori 
                          GROUP BY k.id_kategori ORDER BY k.nama_kategori ASC";
                $result = mysqli_query($conn, $query);
                while($row = mysqli_fetch_assoc($result)):
                ?>
                <tr>
                    <td class="id-cell"><?= $row['id_kategori'] ?></td>
                    <td class="nama-cell"><strong><?= htmlspecialchars($row['nama_kategori']) ?></strong></td>
                    <td><span style="color:var(--primary); font-weight:bold;"><?= $row['total_soal'] ?> Soal</span></td>
                    <td>
                        <!-- Tombol Detail akan melempar ke soal.php dengan filter ID -->
                        <a href="soal.php?filter_kategori=<?= $row['id_kategori'] ?>" class="btn-action">Lihat Detail Soal</a>
                        <a href="kategori.php?hapus=<?= $row['id_kategori'] ?>" class="btn-hapus" onclick="return confirm('Hapus topik ini? Semua soal di dalamnya akan ikut terhapus!');">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- JS untuk Filter Lokal -->
    <script>
    function filterTabel() {
        let input = document.getElementById("cariTopik").value.toUpperCase();
        let tr = document.getElementById("tabelKategori").getElementsByTagName("tr");
        for (let i = 1; i < tr.length; i++) {
            let td = tr[i].getElementsByClassName("nama-cell")[0];
            if (td) {
                let txtValue = td.textContent || td.innerText;
                tr[i].style.display = txtValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
            }
        }
    }
    function urutTabel() {
        // Implementasi sortir sederhana dengan membalik urutan tabel HTML
        let table = document.getElementById("tabelKategori");
        let rows = Array.from(table.rows).slice(1);
        let urut = document.getElementById("urutTopik").value;
        
        rows.sort((a, b) => {
            if (urut === 'abjad') return a.cells[1].innerText.localeCompare(b.cells[1].innerText);
            return parseInt(b.cells[0].innerText) - parseInt(a.cells[0].innerText); // Terbaru
        });
        rows.forEach(row => table.tBodies[0].appendChild(row));
    }
    </script>
</body>
</html>