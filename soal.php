<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: index.php"); exit(); }

// 1. LOGIKA TAMBAH SOAL
if (isset($_POST['tambah_soal'])) {
    $id_kategori = $_POST['id_kategori']; $poin = $_POST['poin']; $waktu = $_POST['waktu'] ?: 30; 
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    $path_gambar = ""; $path_audio = "";
    
    if (!empty($_FILES['gambar']['name'])) { $path_gambar = "assets/images/" . time() . "_" . $_FILES['gambar']['name']; move_uploaded_file($_FILES['gambar']['tmp_name'], $path_gambar); }
    if (!empty($_FILES['audio']['name'])) { $path_audio = "assets/audio/" . time() . "_" . $_FILES['audio']['name']; move_uploaded_file($_FILES['audio']['tmp_name'], $path_audio); }

    mysqli_query($conn, "INSERT INTO soal (id_kategori, poin, waktu, pertanyaan, jawaban, gambar, audio) VALUES ('$id_kategori', '$poin', '$waktu', '$pertanyaan', '$jawaban', '$path_gambar', '$path_audio')");
    $_SESSION['notifikasi'] = "Berhasil menambahkan soal baru!"; header("Location: soal.php"); exit();
}

// 2. LOGIKA UPDATE (EDIT) SOAL
if (isset($_POST['update_soal'])) {
    $id_soal = $_POST['id_soal'];
    $id_kategori = $_POST['id_kategori']; $poin = $_POST['poin']; $waktu = $_POST['waktu']; 
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    
    $query_update = "UPDATE soal SET id_kategori='$id_kategori', poin='$poin', waktu='$waktu', pertanyaan='$pertanyaan', jawaban='$jawaban' WHERE id_soal='$id_soal'";
    mysqli_query($conn, $query_update);

    // Update file jika ada unggahan media baru
    if (!empty($_FILES['gambar']['name'])) { 
        $path_gambar = "assets/images/" . time() . "_" . $_FILES['gambar']['name']; 
        move_uploaded_file($_FILES['gambar']['tmp_name'], $path_gambar);
        mysqli_query($conn, "UPDATE soal SET gambar='$path_gambar' WHERE id_soal='$id_soal'");
    }
    if (!empty($_FILES['audio']['name'])) { 
        $path_audio = "assets/audio/" . time() . "_" . $_FILES['audio']['name']; 
        move_uploaded_file($_FILES['audio']['tmp_name'], $path_audio);
        mysqli_query($conn, "UPDATE soal SET audio='$path_audio' WHERE id_soal='$id_soal'");
    }

    $_SESSION['notifikasi'] = "Soal berhasil diperbarui!"; header("Location: soal.php"); exit();
}

// 3. LOGIKA HAPUS SOAL
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $cek_file = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar, audio FROM soal WHERE id_soal='$id'"));
    if($cek_file['gambar'] && file_exists($cek_file['gambar'])) { unlink($cek_file['gambar']); }
    if($cek_file['audio'] && file_exists($cek_file['audio'])) { unlink($cek_file['audio']); }
    mysqli_query($conn, "DELETE FROM soal WHERE id_soal='$id'");
    $_SESSION['notifikasi'] = "Soal berhasil dihapus."; header("Location: soal.php"); exit();
}

// 4. LOGIKA IMPORT DATA DARI CSV
if (isset($_POST['import_csv'])) {
    if ($_FILES['file_csv']['name']) {
        $filename = explode(".", $_FILES['file_csv']['name']);
        if (end($filename) == "csv") {
            $handle = fopen($_FILES['file_csv']['tmp_name'], "r");
            $first_line = fgets($handle);
            $delimiter = (strpos($first_line, ';') !== false) ? ';' : ',';
            rewind($handle);
            
            fgetcsv($handle, 10000, $delimiter); 
            fgetcsv($handle, 10000, $delimiter); 
            fgetcsv($handle, 10000, $delimiter); 
            
            $soal_berhasil = 0;
            while (($data = fgetcsv($handle, 10000, $delimiter)) !== FALSE) {
                if(empty($data[0]) || empty($data[3]) || count($data) < 5) continue; 
                $nama_kategori = mysqli_real_escape_string($conn, trim($data[0]));
                $poin = (int)trim($data[1]); 
                $waktu = (int)trim($data[2]);
                $pertanyaan = mysqli_real_escape_string($conn, trim($data[3]));
                $jawaban = mysqli_real_escape_string($conn, trim($data[4]));
                
                if($poin == 0) $poin = 100;
                if($waktu == 0) $waktu = 30;

                $cek_kat = mysqli_query($conn, "SELECT id_kategori FROM kategori WHERE nama_kategori = '$nama_kategori'");
                if(mysqli_num_rows($cek_kat) > 0) {
                    $row_kat = mysqli_fetch_assoc($cek_kat);
                    $id_kat = $row_kat['id_kategori'];
                    $query_insert = "INSERT INTO soal (id_kategori, poin, waktu, pertanyaan, jawaban) VALUES ('$id_kat', '$poin', '$waktu', '$pertanyaan', '$jawaban')";
                    if(mysqli_query($conn, $query_insert)) { $soal_berhasil++; }
                }
            }
            fclose($handle);
            $_SESSION['notifikasi'] = "Luar biasa! Berhasil mengimpor $soal_berhasil soal.";
            header("Location: soal.php"); exit();
        } else {
            $_SESSION['notifikasi_error'] = "Format gagal! Pastikan file berakhiran .csv";
            header("Location: soal.php"); exit();
        }
    }
}

// 5. AMBIL DATA JIKA TOMBOL EDIT DIKLIK
$data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $data_edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM soal WHERE id_soal='$id_edit'"));
}

$filter_kategori = isset($_GET['filter_kategori']) ? $_GET['filter_kategori'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bank Soal - Studio Kuis</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Toast Notifikasi -->
    <div id="toast-container"><div class="toast" id="toast-box"><span id="toast-message"></span></div></div>
    
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <h1 class="header-title">Manajemen Bank Soal</h1>

        <!-- Sembunyikan form Import CSV jika sedang dalam mode Edit -->
        <?php if(!$data_edit): ?>
        <div class="form-panel" style="border-top: 4px solid var(--accent); background: rgba(251, 191, 36, 0.05);">
            <h3 style="margin-bottom: 15px; color: var(--accent);">🚀 Import Masal via CSV</h3>
            <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 20px;">Unggah file CSV untuk memasukkan puluhan soal sekaligus.</p>
            <form action="soal.php" method="POST" enctype="multipart/form-data" style="display:flex; gap:20px; align-items:center;">
                <div class="file-upload-wrapper" style="flex:1;">
                    <input type="file" name="file_csv" accept=".csv" required>
                    <span class="custom-file-upload">📁 Klik untuk memilih file CSV...</span>
                </div>
                <button type="submit" name="import_csv" class="btn-submit" style="width: auto;">Unggah & Proses</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- FORM TAMBAH ATAU EDIT SOAL -->
        <div class="form-panel" <?= $data_edit ? 'style="border: 2px solid var(--accent); box-shadow: 0 0 15px rgba(251,191,36,0.2);"' : '' ?>>
            <h3 style="margin-bottom: 25px; color: <?= $data_edit ? 'var(--accent)' : 'var(--primary)' ?>;">
                <?= $data_edit ? '✏️ Edit Data Soal' : '+ Tambah Soal Manual' ?>
            </h3>
            <form action="soal.php" method="POST" enctype="multipart/form-data">
                <?php if($data_edit): ?> <input type="hidden" name="id_soal" value="<?= $data_edit['id_soal'] ?>"> <?php endif; ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Pilih Kategori Mata Pelajaran</label>
                        <select name="id_kategori" required>
                            <option value="" disabled <?= !$data_edit ? 'selected' : '' ?>>-- Silakan Pilih Kategori --</option>
                            <?php
                            $kat = mysqli_query($conn, "SELECT * FROM kategori");
                            while($k = mysqli_fetch_assoc($kat)) {
                                $sel = ($data_edit && $data_edit['id_kategori'] == $k['id_kategori']) ? 'selected' : '';
                                echo "<option value='{$k['id_kategori']}' $sel>{$k['nama_kategori']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nilai Poin</label>
                        <select name="poin" required>
                            <?php foreach([100,200,300,400,500] as $p): ?>
                                <option value="<?= $p ?>" <?= ($data_edit && $data_edit['poin'] == $p) ? 'selected' : '' ?>><?= $p ?> Poin</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Teks Pertanyaan</label>
                        <textarea name="pertanyaan" required><?= $data_edit ? htmlspecialchars($data_edit['pertanyaan']) : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Kunci Jawaban</label>
                        <input type="text" name="jawaban" value="<?= $data_edit ? htmlspecialchars($data_edit['jawaban']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Batas Waktu (Detik)</label>
                        <input type="number" name="waktu" value="<?= $data_edit ? $data_edit['waktu'] : 30 ?>" min="5" required>
                    </div>
                    <div class="form-group">
                        <label>Sematkan Gambar Baru (Opsional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="gambar" accept="image/*">
                            <span class="custom-file-upload"><?= ($data_edit && $data_edit['gambar']) ? '🖼️ Timpa Gambar Lama...' : '🖼️ Unggah Gambar...' ?></span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Sematkan Audio Baru (Opsional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="audio" accept="audio/*">
                            <span class="custom-file-upload"><?= ($data_edit && $data_edit['audio']) ? '🎵 Timpa Audio Lama...' : '🎵 Unggah Audio...' ?></span>
                        </div>
                    </div>
                </div>
                
                <div style="display:flex; gap:15px; margin-top:10px;">
                    <button type="submit" name="<?= $data_edit ? 'update_soal' : 'tambah_soal' ?>" class="btn-submit" style="<?= $data_edit ? 'background:var(--accent); color:#0f172a; width:auto;' : 'width:auto;' ?>">
                        <?= $data_edit ? 'Simpan Perubahan Soal' : 'Simpan ke Bank Soal' ?>
                    </button>
                    <?php if($data_edit): ?>
                        <a href="soal.php" class="btn-action" style="background:#475569; padding: 14px 28px; text-align:center;">Batal Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <h3 style="color: var(--accent); margin-bottom: 15px; font-size: 1.4rem;">Daftar Soal Tersimpan</h3>
        
        <!-- BAR FILTER YANG KEMBALI HADIR -->
        <div class="filter-bar">
            <select id="filterKategori" onchange="filterSoal()">
                <option value="ALL">Semua Topik Kuis</option>
                <?php
                $kat = mysqli_query($conn, "SELECT * FROM kategori");
                while($k = mysqli_fetch_assoc($kat)) {
                    $selected = ($filter_kategori == $k['id_kategori']) ? 'selected' : '';
                    echo "<option value='{$k['nama_kategori']}' $selected>{$k['nama_kategori']}</option>";
                }
                ?>
            </select>
            <select id="filterPoin" onchange="filterSoal()">
                <option value="ALL">Semua Poin</option>
                <option value="100">100 Poin</option>
                <option value="200">200 Poin</option>
                <option value="300">300 Poin</option>
                <option value="400">400 Poin</option>
                <option value="500">500 Poin</option>
            </select>
            <input type="text" id="cariSoal" onkeyup="filterSoal()" placeholder="🔍 Ketik kata kunci soal atau jawaban...">
        </div>

        <table id="tabelSoal">
            <thead>
                <tr>
                    <th>Kategori</th>
                    <th>Poin</th>
                    <th style="width: 45%;">Pertanyaan & Jawaban</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = mysqli_query($conn, "SELECT s.*, k.nama_kategori FROM soal s JOIN kategori k ON s.id_kategori = k.id_kategori ORDER BY k.nama_kategori ASC, s.poin ASC");
                while($row = mysqli_fetch_assoc($result)):
                ?>
                <!-- Atribut data-kategori dan data-poin penting agar fungsi Filter JS bekerja -->
                <tr class="baris-soal" data-kategori="<?= htmlspecialchars($row['nama_kategori']) ?>" data-poin="<?= $row['poin'] ?>">
                    <td><span style="color:#94a3b8; font-weight:bold;"><?= $row['nama_kategori'] ?></span></td>
                    <td style="color: var(--accent); font-weight: 900; font-size: 1.2rem;"><?= $row['poin'] ?></td>
                    <td class="teks-soal">
                        <strong style="color: white; font-size: 1.05rem; display: block; margin-bottom: 6px;"><?= htmlspecialchars($row['pertanyaan']) ?></strong>
                        <span style="color: var(--success); font-weight: 600; font-size:0.9rem;">Jwb: <?= htmlspecialchars($row['jawaban']) ?> (<?= $row['waktu'] ?>s)</span>
                    </td>
                    <td>
                        <a href="soal.php?edit=<?= $row['id_soal'] ?>" class="btn-action btn-edit">Edit</a>
                        <a href="soal.php?hapus=<?= $row['id_soal'] ?>" class="btn-hapus" onclick="return confirm('Hapus soal ini?');">Hapus</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>

    <!-- SCRIPT NOTIFIKASI -->
    <?php if(isset($_SESSION['notifikasi'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('toast-message').innerText = "<?= $_SESSION['notifikasi'] ?>";
            document.getElementById('toast-container').classList.add('show');
            setTimeout(() => { document.getElementById('toast-container').classList.remove('show'); }, 3000);
        });
    </script>
    <?php unset($_SESSION['notifikasi']); endif; ?>
    
    <?php if(isset($_SESSION['notifikasi_error'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let toastBox = document.getElementById('toast-box');
            document.getElementById('toast-message').innerText = "<?= $_SESSION['notifikasi_error'] ?>";
            toastBox.classList.add('error');
            document.getElementById('toast-container').classList.add('show');
            setTimeout(() => { 
                document.getElementById('toast-container').classList.remove('show'); 
                toastBox.classList.remove('error');
            }, 3000);
        });
    </script>
    <?php unset($_SESSION['notifikasi_error']); endif; ?>

    <!-- SCRIPT FILTER PENCARIAN -->
    <script>
    function filterSoal() {
        let cat = document.getElementById('filterKategori').value;
        let poin = document.getElementById('filterPoin').value;
        let cari = document.getElementById('cariSoal').value.toLowerCase();
        let baris = document.getElementsByClassName('baris-soal');

        for (let i = 0; i < baris.length; i++) {
            let rowCat = baris[i].getAttribute('data-kategori');
            let rowPoin = baris[i].getAttribute('data-poin');
            let rowTeks = baris[i].querySelector('.teks-soal').innerText.toLowerCase();

            let matchCat = (cat === 'ALL' || rowCat === cat);
            let matchPoin = (poin === 'ALL' || rowPoin === poin);
            let matchCari = (rowTeks.includes(cari));

            if (matchCat && matchPoin && matchCari) {
                baris[i].style.display = "";
            } else {
                baris[i].style.display = "none";
            }
        }
    }
    window.onload = filterSoal;
    </script>
</body>
</html>