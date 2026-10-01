<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: index.php"); exit(); }

// Baca konfigurasi default waktu soal baru jika ada
$config_file = 'config.json';
$config = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [];
$default_waktu_baru = isset($config['default_waktu_soal']) ? (int)$config['default_waktu_soal'] : 30;

// Pastikan folder penyimpanan media selalu tersedia
if (!is_dir('assets/images')) { mkdir('assets/images', 0777, true); }
if (!is_dir('assets/audio')) { mkdir('assets/audio', 0777, true); }

function bersihkanNamaFile($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    $clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
    return time() . "_" . substr($clean_name, 0, 50) . "." . $ext;
}

// 0. LOGIKA UNDUH TEMPLATE SOAL (EXCEL / CSV)
if (isset($_GET['download_template'])) {
    $tipe = $_GET['download_template'];
    
    if ($tipe === 'xlsx') {
        $lokasi_xlsx = '';
        if (file_exists('Template_Soal_Jeopardy.xlsx')) {
            $lokasi_xlsx = 'Template_Soal_Jeopardy.xlsx';
        } elseif (file_exists('soal/Template_Soal_Jeopardy.xlsx')) {
            $lokasi_xlsx = 'soal/Template_Soal_Jeopardy.xlsx';
        }
        
        if ($lokasi_xlsx !== '') {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="Template_Soal_Jeopardy.xlsx"');
            header('Content-Length: ' . filesize($lokasi_xlsx));
            readfile($lokasi_xlsx);
            exit();
        }
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="Template_Soal_Jeopardy.csv"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, [
        '⚠️ PENTING: Setelah selesai mengisi, file ini WAJIB di-Save As dengan format CSV (Comma delimited) (*.csv) sebelum diunggah ke aplikasi!',
        '', '', '', ''
    ]);
    fputcsv($output, [
        'Nama Kategori',
        'Poin (Contoh: 100, 200, 300)',
        'Waktu (Detik) (Contoh: 30, 45, 60)',
        'Pertanyaan',
        'Jawaban Singkat'
    ]);
    fputcsv($output, [
        '(Pastikan ejaan persis seperti di web)',
        '(Hanya ketik angka)',
        '(Hanya ketik angka)',
        'Siapakah nama presiden pertama RI?',
        'Soekarno'
    ]);
    fputcsv($output, [
        'Matematika',
        '100',
        '30',
        'Berapakah hasil dari 12 dikali 12?',
        '144'
    ]);
    fclose($output);
    exit();
}

// 1. LOGIKA TAMBAH SOAL
if (isset($_POST['tambah_soal'])) {
    $id_kategori = $_POST['id_kategori']; 
    $poin = $_POST['poin']; 
    $waktu = (int)$_POST['waktu'] ?: $default_waktu_baru; 
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    $path_gambar = ""; 
    $path_audio = "";
    
    if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) { 
        $path_gambar = "assets/images/" . bersihkanNamaFile($_FILES['gambar']['name']); 
        move_uploaded_file($_FILES['gambar']['tmp_name'], $path_gambar); 
    }
    if (!empty($_FILES['audio']['name']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) { 
        $path_audio = "assets/audio/" . bersihkanNamaFile($_FILES['audio']['name']); 
        move_uploaded_file($_FILES['audio']['tmp_name'], $path_audio); 
    }

    mysqli_query($conn, "INSERT INTO soal (id_kategori, poin, waktu, pertanyaan, jawaban, gambar, audio) VALUES ('$id_kategori', '$poin', '$waktu', '$pertanyaan', '$jawaban', '$path_gambar', '$path_audio')");
    $_SESSION['notifikasi'] = "Berhasil menambahkan soal baru!"; 
    header("Location: soal.php"); 
    exit();
}

// 2. LOGIKA UPDATE (EDIT) SOAL
if (isset($_POST['update_soal'])) {
    $id_soal = $_POST['id_soal'];
    $id_kategori = $_POST['id_kategori']; 
    $poin = $_POST['poin']; 
    $waktu = (int)$_POST['waktu'] ?: $default_waktu_baru; 
    $pertanyaan = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $jawaban = mysqli_real_escape_string($conn, $_POST['jawaban']);
    
    $lama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar, audio FROM soal WHERE id_soal='$id_soal'"));

    $query_update = "UPDATE soal SET id_kategori='$id_kategori', poin='$poin', waktu='$waktu', pertanyaan='$pertanyaan', jawaban='$jawaban' WHERE id_soal='$id_soal'";
    mysqli_query($conn, $query_update);

    if (isset($_POST['hapus_gambar_lama']) && $_POST['hapus_gambar_lama'] == '1') {
        if (!empty($lama['gambar']) && file_exists($lama['gambar'])) { @unlink($lama['gambar']); }
        mysqli_query($conn, "UPDATE soal SET gambar='' WHERE id_soal='$id_soal'");
    }

    if (isset($_POST['hapus_audio_lama']) && $_POST['hapus_audio_lama'] == '1') {
        if (!empty($lama['audio']) && file_exists($lama['audio'])) { @unlink($lama['audio']); }
        mysqli_query($conn, "UPDATE soal SET audio='' WHERE id_soal='$id_soal'");
    }

    if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) { 
        if (!empty($lama['gambar']) && file_exists($lama['gambar'])) { @unlink($lama['gambar']); }
        $path_gambar = "assets/images/" . bersihkanNamaFile($_FILES['gambar']['name']); 
        move_uploaded_file($_FILES['gambar']['tmp_name'], $path_gambar); 
        mysqli_query($conn, "UPDATE soal SET gambar='$path_gambar' WHERE id_soal='$id_soal'");
    }

    if (!empty($_FILES['audio']['name']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) { 
        if (!empty($lama['audio']) && file_exists($lama['audio'])) { @unlink($lama['audio']); }
        $path_audio = "assets/audio/" . bersihkanNamaFile($_FILES['audio']['name']); 
        move_uploaded_file($_FILES['audio']['tmp_name'], $path_audio); 
        mysqli_query($conn, "UPDATE soal SET audio='$path_audio' WHERE id_soal='$id_soal'");
    }

    $_SESSION['notifikasi'] = "Soal berhasil diperbarui!"; 
    header("Location: soal.php"); 
    exit();
}

// 3. LOGIKA HAPUS SOAL
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $cek_file = mysqli_fetch_assoc(mysqli_query($conn, "SELECT gambar, audio FROM soal WHERE id_soal='$id'"));
    if(!empty($cek_file['gambar']) && file_exists($cek_file['gambar'])) { @unlink($cek_file['gambar']); }
    if(!empty($cek_file['audio']) && file_exists($cek_file['audio'])) { @unlink($cek_file['audio']); }
    mysqli_query($conn, "DELETE FROM soal WHERE id_soal='$id'");
    $_SESSION['notifikasi'] = "Soal berhasil dihapus."; 
    header("Location: soal.php"); 
    exit();
}

// 4. LOGIKA IMPORT DATA DARI CSV
if (isset($_POST['import_csv'])) {
    if ($_FILES['file_csv']['name']) {
        $filename = explode(".", $_FILES['file_csv']['name']);
        if (strtolower(end($filename)) == "csv") {
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
                
                $csv_gambar = !empty($data[5]) ? mysqli_real_escape_string($conn, trim($data[5])) : '';
                $csv_audio  = !empty($data[6]) ? mysqli_real_escape_string($conn, trim($data[6])) : '';
                if ($csv_gambar !== '' && strpos($csv_gambar, 'assets/') === false) { $csv_gambar = 'assets/images/' . $csv_gambar; }
                if ($csv_audio !== '' && strpos($csv_audio, 'assets/') === false) { $csv_audio = 'assets/audio/' . $csv_audio; }

                if($poin == 0) $poin = 100;
                if($waktu == 0) $waktu = $default_waktu_baru;

                $cek_kat = mysqli_query($conn, "SELECT id_kategori FROM kategori WHERE nama_kategori = '$nama_kategori'");
                if(mysqli_num_rows($cek_kat) > 0) {
                    $row_kat = mysqli_fetch_assoc($cek_kat);
                    $id_kat = $row_kat['id_kategori'];
                    $query_insert = "INSERT INTO soal (id_kategori, poin, waktu, pertanyaan, jawaban, gambar, audio) VALUES ('$id_kat', '$poin', '$waktu', '$pertanyaan', '$jawaban', '$csv_gambar', '$csv_audio')";
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
$has_xlsx_template = (file_exists('Template_Soal_Jeopardy.xlsx') || file_exists('soal/Template_Soal_Jeopardy.xlsx'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bank Soal - Studio Kuis</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .template-btn-group { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-download-template {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            border: 1.5px solid #10b981;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.25s ease;
        }
        .btn-download-template:hover {
            background: #10b981;
            color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }
        .btn-download-template.csv-alt {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border-color: #3b82f6;
        }
        .btn-download-template.csv-alt:hover {
            background: #3b82f6;
            color: white;
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
        }

        .media-preview-card {
            margin-top: 12px;
            padding: 15px;
            background: #0f172a;
            border: 1.5px solid #334155;
            border-radius: 12px;
            display: none;
            flex-direction: column;
            gap: 10px;
            animation: fadeInPreview 0.25s ease-out;
        }
        .media-preview-card.active {
            display: flex;
        }
        @keyframes fadeInPreview {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 700;
        }
        .preview-badge {
            background: rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.78rem;
            font-weight: 800;
        }
        .preview-badge.audio-dur {
            background: rgba(251, 191, 36, 0.2);
            color: var(--accent);
        }
        .preview-img-box {
            width: 100%;
            max-height: 210px;
            object-fit: contain;
            border-radius: 8px;
            background: #1e293b;
            border: 1px solid #334155;
            padding: 6px;
        }
        .btn-clear-media {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.4);
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            align-self: flex-start;
        }
        .btn-clear-media:hover {
            background: #ef4444;
            color: white;
        }
        .media-tag-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #0f172a;
            border: 1px solid #334155;
            color: #cbd5e1;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            margin-top: 6px;
            margin-right: 6px;
        }
    </style>
</head>
<body>
    <div id="toast-container"><div class="toast" id="toast-box"><span id="toast-message"></span></div></div>
    
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <h1 class="header-title">Manajemen Bank Soal</h1>

        <?php if(!$data_edit): ?>
        <div class="form-panel" style="border-top: 4px solid var(--accent); background: rgba(251, 191, 36, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                <div>
                    <h3 style="margin-bottom: 8px; color: var(--accent);">🚀 Import Masal via CSV</h3>
                    <p style="color: #94a3b8; font-size: 0.95rem;">Unduh file template terlebih dahulu, isi daftar soal, lalu unggah kembali dalam format <strong>.csv</strong>.</p>
                </div>
                <div class="template-btn-group">
                    <?php if($has_xlsx_template): ?>
                        <a href="soal.php?download_template=xlsx" class="btn-download-template">📥 Unduh Template Excel (.xlsx)</a>
                    <?php endif; ?>
                    <a href="soal.php?download_template=csv" class="btn-download-template csv-alt">📄 Unduh Template Siap Pakai (.csv)</a>
                </div>
            </div>

            <form action="soal.php" method="POST" enctype="multipart/form-data" style="display:flex; gap:20px; align-items:center;">
                <div class="file-upload-wrapper" style="flex:1;">
                    <input type="file" name="file_csv" id="input-csv" accept=".csv" required onchange="updateCsvLabel(this)">
                    <span class="custom-file-upload" id="label-csv">📁 Klik untuk memilih file CSV...</span>
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
                <?php if($data_edit): ?> 
                    <input type="hidden" name="id_soal" value="<?= $data_edit['id_soal'] ?>"> 
                    <input type="hidden" name="hapus_gambar_lama" id="hapus_gambar_lama" value="0">
                    <input type="hidden" name="hapus_audio_lama" id="hapus_audio_lama" value="0">
                <?php endif; ?>

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
                        <input type="number" name="waktu" id="input-waktu-soal" value="<?= $data_edit ? $data_edit['waktu'] : $default_waktu_baru ?>" min="5" required>
                    </div>

                    <!-- INPUT & PREVIEW GAMBAR -->
                    <div class="form-group">
                        <label>Sematkan Gambar Baru (Opsional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="gambar" id="input-gambar" accept="image/*" onchange="previewImageFile(this)">
                            <span class="custom-file-upload" id="label-gambar">
                                <?= ($data_edit && !empty($data_edit['gambar'])) ? '🖼️ Ganti / Timpa Gambar...' : '🖼️ Unggah Gambar...' ?>
                            </span>
                        </div>

                        <?php $has_old_img = ($data_edit && !empty($data_edit['gambar']) && file_exists($data_edit['gambar'])); ?>
                        <div id="image-preview-card" class="media-preview-card <?= $has_old_img ? 'active' : '' ?>">
                            <div class="preview-header">
                                <span id="img-preview-name"><?= $has_old_img ? 'Gambar Tersimpan: ' . basename($data_edit['gambar']) : 'Preview Gambar' ?></span>
                                <span class="preview-badge" id="img-preview-size"><?= $has_old_img ? round(filesize($data_edit['gambar'])/1024, 1) . ' KB' : 'Baru' ?></span>
                            </div>
                            <img id="img-preview-el" class="preview-img-box" src="<?= $has_old_img ? htmlspecialchars($data_edit['gambar']) : '' ?>" alt="Preview Gambar">
                            <button type="button" class="btn-clear-media" onclick="clearImagePreview()">✕ Hapus / Batalkan Gambar</button>
                        </div>
                    </div>

                    <!-- INPUT & PREVIEW AUDIO (HANYA INFORMASI DURASI, TANPA MENGUBAH WAKTU FORM SECARA OTOMATIS) -->
                    <div class="form-group">
                        <label>Sematkan Audio Baru (Opsional)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="audio" id="input-audio" accept="audio/*" onchange="previewAudioFile(this)">
                            <span class="custom-file-upload" id="label-audio">
                                <?= ($data_edit && !empty($data_edit['audio'])) ? '🎵 Ganti / Timpa Audio...' : '🎵 Unggah Audio...' ?>
                            </span>
                        </div>

                        <?php $has_old_aud = ($data_edit && !empty($data_edit['audio']) && file_exists($data_edit['audio'])); ?>
                        <div id="audio-preview-card" class="media-preview-card <?= $has_old_aud ? 'active' : '' ?>">
                            <div class="preview-header">
                                <span id="audio-preview-name"><?= $has_old_aud ? 'Audio Tersimpan: ' . basename($data_edit['audio']) : 'Preview Audio' ?></span>
                                <span class="preview-badge audio-dur" id="audio-preview-duration">⏱ Memuat durasi...</span>
                            </div>
                            <audio id="audio-preview-el" controls style="width: 100%; height: 40px;" src="<?= $has_old_aud ? htmlspecialchars($data_edit['audio']) : '' ?>"></audio>
                            <button type="button" class="btn-clear-media" onclick="clearAudioPreview()">✕ Hapus / Batalkan Audio</button>
                        </div>
                    </div>
                </div>
                
                <div style="display:flex; gap:15px; margin-top:15px;">
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
                <tr class="baris-soal" data-kategori="<?= htmlspecialchars($row['nama_kategori']) ?>" data-poin="<?= $row['poin'] ?>">
                    <td><span style="color:#94a3b8; font-weight:bold;"><?= $row['nama_kategori'] ?></span></td>
                    <td style="color: var(--accent); font-weight: 900; font-size: 1.2rem;"><?= $row['poin'] ?></td>
                    <td class="teks-soal">
                        <strong style="color: white; font-size: 1.05rem; display: block; margin-bottom: 6px;"><?= htmlspecialchars($row['pertanyaan']) ?></strong>
                        <span style="color: var(--success); font-weight: 600; font-size:0.9rem;">Jwb: <?= htmlspecialchars($row['jawaban']) ?> (<?= $row['waktu'] ?>s)</span>
                        <div>
                            <?php if(!empty($row['gambar'])): ?>
                                <span class="media-tag-pill">🖼️️ Gambar</span>
                            <?php endif; ?>
                            <?php if(!empty($row['audio'])): ?>
                                <span class="media-tag-pill">🎵 Audio</span>
                            <?php endif; ?>
                        </div>
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

    <script>
    function updateCsvLabel(input) {
        const label = document.getElementById('label-csv');
        if (input.files && input.files[0]) {
            label.innerText = "✅ File siap: " + input.files[0].name;
            label.style.color = "#10b981";
        }
    }

    function formatBytes(bytes) {
        if (bytes < 1024) return bytes + " B";
        else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + " KB";
        else return (bytes / 1048576).toFixed(2) + " MB";
    }

    function formatDuration(sec) {
        if (!sec || isNaN(sec)) return "00:00";
        const mins = Math.floor(sec / 60);
        const secs = Math.round(sec % 60);
        return String(mins).padStart(2, '0') + ":" + String(secs).padStart(2, '0') + ` (${Math.ceil(sec)} dtk)`;
    }

    function previewImageFile(input) {
        const card = document.getElementById('image-preview-card');
        const imgEl = document.getElementById('img-preview-el');
        const nameEl = document.getElementById('img-preview-name');
        const sizeEl = document.getElementById('img-preview-size');
        const label = document.getElementById('label-gambar');
        const hapusFlag = document.getElementById('hapus_gambar_lama');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                imgEl.src = e.target.result;
                nameEl.innerText = "File Baru: " + file.name;
                sizeEl.innerText = formatBytes(file.size);
                label.innerText = "🖼️ Terpilih: " + file.name;
                card.classList.add('active');
                if (hapusFlag) hapusFlag.value = "0";
            };
            reader.readAsDataURL(file);
        }
    }

    function clearImagePreview() {
        const input = document.getElementById('input-gambar');
        const card = document.getElementById('image-preview-card');
        const imgEl = document.getElementById('img-preview-el');
        const label = document.getElementById('label-gambar');
        const hapusFlag = document.getElementById('hapus_gambar_lama');

        input.value = "";
        imgEl.src = "";
        card.classList.remove('active');
        label.innerText = "🖼️ Unggah Gambar...";
        if (hapusFlag) hapusFlag.value = "1";
    }

    function previewAudioFile(input) {
        const card = document.getElementById('audio-preview-card');
        const audEl = document.getElementById('audio-preview-el');
        const nameEl = document.getElementById('audio-preview-name');
        const durEl = document.getElementById('audio-preview-duration');
        const label = document.getElementById('label-audio');
        const hapusFlag = document.getElementById('hapus_audio_lama');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const objectUrl = URL.createObjectURL(file);
            audEl.src = objectUrl;
            nameEl.innerText = "File Baru: " + file.name + " (" + formatBytes(file.size) + ")";
            durEl.innerText = "⏱️ Menghitung durasi...";
            label.innerText = "🎵 Terpilih: " + file.name;
            card.classList.add('active');
            if (hapusFlag) hapusFlag.value = "0";

            audEl.onloadedmetadata = function() {
                durEl.innerText = "⏱️ Durasi: " + formatDuration(audEl.duration);
            };
        }
    }

    function clearAudioPreview() {
        const input = document.getElementById('input-audio');
        const card = document.getElementById('audio-preview-card');
        const audEl = document.getElementById('audio-preview-el');
        const label = document.getElementById('label-audio');
        const hapusFlag = document.getElementById('hapus_audio_lama');

        audEl.pause();
        audEl.src = "";
        input.value = "";
        card.classList.remove('active');
        label.innerText = "🎵 Unggah Audio...";
        if (hapusFlag) hapusFlag.value = "1";
    }

    document.addEventListener('DOMContentLoaded', function() {
        const existingAud = document.getElementById('audio-preview-el');
        const durEl = document.getElementById('audio-preview-duration');
        if (existingAud && existingAud.getAttribute('src') !== '') {
            existingAud.onloadedmetadata = function() {
                durEl.innerText = "⏱️ Durasi: " + formatDuration(existingAud.duration);
            };
        }
        filterSoal();
    });

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
    </script>
</body>
</html>