<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: index.php"); 
    exit(); 
}

// 1. MEMBACA & MEMPERSIAPKAN FILE KONFIGURASI JSON (Anti-Bentrok dengan sidebar.php)
$config_file = 'config.json';
$default_config = [
    'admin_title' => 'Studio Kuis',
    'game_title' => 'Jeopardy Edukasi',
    'penalti_salah' => 'ya',
    'default_cat_count' => 5,
    'default_waktu_soal' => 30
];

$loaded_cfg = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : [];
if (!is_array($loaded_cfg)) { $loaded_cfg = []; }
$app_config = array_merge($default_config, $loaded_cfg);

// Pastikan kunci-kunci baru langsung tersimpan di config.json
if (count($loaded_cfg) < count($app_config)) {
    file_put_contents($config_file, json_encode($app_config, JSON_PRETTY_PRINT));
}

// 2. SIMPAN PENGATURAN IDENTITAS & ATURAN GAME
if (isset($_POST['simpan_pengaturan'])) {
    $app_config['admin_title'] = htmlspecialchars(trim($_POST['admin_title']));
    $app_config['game_title'] = htmlspecialchars(trim($_POST['game_title']));
    $app_config['penalti_salah'] = (isset($_POST['penalti_salah']) && $_POST['penalti_salah'] === 'tidak') ? 'tidak' : 'ya';
    $app_config['default_cat_count'] = max(1, (int)$_POST['default_cat_count']);
    $app_config['default_waktu_soal'] = max(5, (int)$_POST['default_waktu_soal']);
    
    file_put_contents($config_file, json_encode($app_config, JSON_PRETTY_PRINT));
    $_SESSION['notifikasi'] = "Pengaturan sistem dan aturan permainan berhasil disimpan!";
    header("Location: pengaturan.php"); 
    exit();
}

// 3. MASTER SETTING: UBAH BATAS WAKTU SEMUA SOAL SEKALIGUS
if (isset($_POST['ubah_semua_waktu'])) {
    $waktu_massal = (int)$_POST['waktu_massal'];
    if ($waktu_massal >= 5) {
        mysqli_query($conn, "UPDATE soal SET waktu = '$waktu_massal'");
        $_SESSION['notifikasi'] = "Berhasil! Batas waktu seluruh soal di database telah diseragamkan menjadi $waktu_massal detik.";
    } else {
        $_SESSION['notifikasi_error'] = "Batas waktu minimal adalah 5 detik!";
    }
    header("Location: pengaturan.php"); 
    exit();
}

// 4. GANTI PASSWORD ADMIN
if (isset($_POST['ganti_password'])) {
    $username = $_SESSION['username'];
    $pass_lama = md5($_POST['pass_lama']);
    $pass_baru = $_POST['pass_baru'];
    $konfirmasi = $_POST['konfirmasi_pass'];

    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$pass_lama'");
    if (mysqli_num_rows($cek_user) > 0) {
        if ($pass_baru === $konfirmasi) {
            if (strlen($pass_baru) >= 4) {
                $pass_hash = md5($pass_baru);
                mysqli_query($conn, "UPDATE users SET password='$pass_hash' WHERE username='$username'");
                $_SESSION['notifikasi'] = "Password Admin berhasil diperbarui! Gunakan password baru saat login berikutnya.";
            } else {
                $_SESSION['notifikasi_error'] = "Password baru minimal 4 karakter!";
            }
        } else {
            $_SESSION['notifikasi_error'] = "Konfirmasi password baru tidak cocok!";
        }
    } else {
        $_SESSION['notifikasi_error'] = "Password lama yang Anda masukkan salah!";
    }
    header("Location: pengaturan.php"); 
    exit();
}

// 5. KOSONGKAN SELURUH BANK SOAL (ZONA BAHAYA)
if (isset($_POST['kosongkan_bank_soal'])) {
    $media_soal = mysqli_query($conn, "SELECT gambar, audio FROM soal");
    while ($m = mysqli_fetch_assoc($media_soal)) {
        if (!empty($m['gambar']) && file_exists($m['gambar'])) { @unlink($m['gambar']); }
        if (!empty($m['audio']) && file_exists($m['audio'])) { @unlink($m['audio']); }
    }
    mysqli_query($conn, "TRUNCATE TABLE soal");
    $_SESSION['notifikasi'] = "Seluruh soal dan media berhasil dibersihkan dari sistem.";
    header("Location: pengaturan.php"); 
    exit();
}

$q_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM soal"));
$total_soal_aktif = $q_count['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan - Studio Kuis</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Perbaikan Desain Input Password & Keamanan Akun */
        input[type="password"] {
            width: 100%;
            padding: 14px 45px 14px 14px;
            border-radius: 10px;
            border: 1px solid #475569;
            background: #0f172a;
            color: white;
            outline: none;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        input[type="password"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .password-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 22px;
        }
        .pass-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .pass-input-wrapper input {
            padding-right: 45px !important;
        }
        .btn-toggle-pass {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
        }
        .btn-toggle-pass:hover {
            color: var(--accent);
            transform: scale(1.1);
        }
        .security-card {
            border-top: 4px solid #38bdf8;
            background: linear-gradient(180deg, rgba(56, 189, 248, 0.05) 0%, var(--bg-panel) 100%);
        }
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(56, 189, 248, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div id="toast-container"><div class="toast" id="toast-box"><span id="toast-message"></span></div></div>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <h1 class="header-title">Pengaturan Sistem</h1>

        <!-- PANEL 1: IDENTITAS & ATURAN GAMEPLAY -->
        <div class="form-panel">
            <h3 style="margin-bottom:10px; color:var(--primary);">🎮 Identitas & Aturan Permainan</h3>
            <p style="color:#94a3b8; margin-bottom: 25px; font-size:0.95rem;">Sesuaikan identitas nama dan mekanisme kuis interaktif yang berlangsung di kelas.</p>
            
            <form action="pengaturan.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Judul Menu Admin (Sidebar)</label>
                        <input type="text" name="admin_title" value="<?= htmlspecialchars($app_config['admin_title'] ?? 'Studio Kuis') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Judul Layar Utama (Lobi Permainan)</label>
                        <input type="text" name="game_title" value="<?= htmlspecialchars($app_config['game_title'] ?? 'Jeopardy Edukasi') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Sistem Penalti Jawaban Salah (Skor Kelompok)</label>
                        <select name="penalti_salah">
                            <option value="ya" <?= ($app_config['penalti_salah'] ?? 'ya') === 'ya' ? 'selected' : '' ?>>
                                🔻 Aktif (Standar Jeopardy: Nilai Berkurang Sesuai Poin)
                            </option>
                            <option value="tidak" <?= ($app_config['penalti_salah'] ?? 'ya') === 'tidak' ? 'selected' : '' ?>>
                                🛡️ Nonaktif (Ramah Siswa: 0 Poin jika Salah / Tidak Berkurang)
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Kategori Bawaan di Lobi Game</label>
                        <input type="number" name="default_cat_count" value="<?= (int)($app_config['default_cat_count'] ?? 5) ?>" min="1" max="9" required>
                    </div>
                    <div class="form-group full">
                        <label>Batas Waktu Bawaan untuk Soal Baru (Detik)</label>
                        <input type="number" name="default_waktu_soal" value="<?= (int)($app_config['default_waktu_soal'] ?? 30) ?>" min="5" required>
                        <small style="color: #64748b; font-size: 0.85rem; margin-top: 2px;">Durasi ini otomatis terisi saat Anda membuka menu tambah soal manual.</small>
                    </div>
                </div>
                <button type="submit" name="simpan_pengaturan" class="btn-submit" style="width: auto;">Simpan Pengaturan Game</button>
            </form>
        </div>

        <!-- PANEL 2: MASTER SETTING WAKTU SOAL -->
        <div class="form-panel" style="border-top: 4px solid var(--accent); background: rgba(251, 191, 36, 0.04);">
            <h3 style="margin-bottom:10px; color:var(--accent);">⏱️ Master Timer: Seragamkan Batas Waktu Soal</h3>
            <p style="color:#94a3b8; margin-bottom: 20px; font-size:0.95rem;">
                Gunakan fitur ini untuk mengubah batas waktu <strong>seluruh soal yang ada di database (Total saat ini: <?= $total_soal_aktif ?> soal)</strong> secara serentak tanpa perlu mengedit soal satu per satu.
            </p>
            
            <form action="pengaturan.php" method="POST" onsubmit="return konfirmasiUbahWaktuMassal();" style="display:flex; gap:20px; align-items:flex-end; flex-wrap:wrap;">
                <div class="form-group" style="min-width: 280px; flex: 1; max-width: 400px;">
                    <label>Batas Waktu Baru untuk Semua Soal (Detik)</label>
                    <input type="number" name="waktu_massal" id="waktu_massal" value="<?= (int)($app_config['default_waktu_soal'] ?? 30) ?>" min="5" max="300" required>
                </div>
                <button type="submit" name="ubah_semua_waktu" class="btn-submit" style="background:var(--accent); color:#0f172a; width:auto; height: 48px;">
                    ⚡ Terapkan ke Seluruh Soal
                </button>
            </form>
        </div>

        <!-- PANEL 3: KEAMANAN AKUN ADMIN (DESAIN BARU RAPI & MODERN) -->
        <div class="form-panel security-card">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                <h3 style="color:#38bdf8; margin: 0;">🔐 Keamanan Akun Admin</h3>
                <span class="security-badge">👤 Akun Aktif: <?= htmlspecialchars($_SESSION['username'] ?? 'admin') ?></span>
            </div>
            <p style="color:#94a3b8; margin-bottom: 25px; font-size:0.95rem;">Perbarui kata sandi akun administrator secara berkala untuk menjaga keamanan bank soal.</p>
            
            <form action="pengaturan.php" method="POST">
                <div class="password-grid">
                    <div class="form-group">
                        <label>Password Saat Ini</label>
                        <div class="pass-input-wrapper">
                            <input type="password" name="pass_lama" id="pass_lama" placeholder="Masukkan password lama..." required>
                            <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility('pass_lama', this)" title="Lihat/Sembunyikan">👁️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <div class="pass-input-wrapper">
                            <input type="password" name="pass_baru" id="pass_baru" placeholder="Minimal 4 karakter..." required>
                            <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility('pass_baru', this)" title="Lihat/Sembunyikan">👁️</button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Ulangi Password Baru</label>
                        <div class="pass-input-wrapper">
                            <input type="password" name="konfirmasi_pass" id="konfirmasi_pass" placeholder="Konfirmasi password baru..." required>
                            <button type="button" class="btn-toggle-pass" onclick="togglePassVisibility('konfirmasi_pass', this)" title="Lihat/Sembunyikan">👁️</button>
                        </div>
                    </div>
                </div>
                <button type="submit" name="ganti_password" class="btn-submit" style="background: linear-gradient(135deg, #0284c7, #2563eb); width: auto;">
                    🔒 Perbarui Password Admin
                </button>
            </form>
        </div>

        <!-- PANEL 4: ZONA BAHAYA (RESET SOAL) -->
        <div class="form-panel" style="border: 1px solid rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.04);">
            <h3 style="margin-bottom:10px; color:var(--danger);">⚠️ Zona Bahaya: Kosongkan Bank Soal</h3>
            <p style="color:#94a3b8; margin-bottom: 20px; font-size:0.95rem;">
                Menghapus seluruh bank soal yang tersimpan di sistem beserta file gambar dan audio yang melekat. Gunakan opsi ini saat ingin memulai bank soal semester baru dari awal.
            </p>
            
            <form action="pengaturan.php" method="POST" onsubmit="return konfirmasiKosongkanBank();">
                <button type="submit" name="kosongkan_bank_soal" style="background:transparent; border:2px solid var(--danger); color:var(--danger); padding:12px 24px; border-radius:10px; font-weight:bold; cursor:pointer; transition: 0.2s;" onmouseover="this.style.background='var(--danger)'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='var(--danger)';">
                    🗑️ Kosongkan Seluruh Bank Soal (<?= $total_soal_aktif ?> Soal)
                </button>
            </form>
        </div>
    </main>

    <!-- SCRIPT KONFIRMASI & TOGGLE PASSWORD -->
    <script>
    function togglePassVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            btn.innerText = "🙈";
        } else {
            input.type = "password";
            btn.innerText = "👁️";
        }
    }

    function konfirmasiUbahWaktuMassal() {
        const waktu = document.getElementById('waktu_massal').value;
        const pesan = "⚠️ KONFIRMASI PERUBAHAN MASSAL:\n\n" +
                      "Apakah Anda yakin ingin mengubah batas waktu SEMUA soal di database menjadi " + waktu + " detik?\n\n" +
                      "Tindakan ini akan menimpa batas waktu pada seluruh soal yang saat ini tersimpan.";
        return confirm(pesan);
    }

    function konfirmasiKosongkanBank() {
        const pesan1 = "🚨 PERINGATAN KERAS:\n\nApakah Anda benar-benar yakin ingin MENGHAPUS SEMUA SOAL?\nSemua pertanyaan, kunci jawaban, file gambar, dan rekaman audio akan DIHAPUS PERMANEN.";
        if (confirm(pesan1)) {
            return confirm("Konfirmasi Terakhir: Tindakan ini TIDAK DAPAT DIBATALKAN. Lanjutkan penghapusan seluruh bank soal?");
        }
        return false;
    }
    </script>

    <?php if(isset($_SESSION['notifikasi'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('toast-message').innerText = "<?= $_SESSION['notifikasi'] ?>";
            document.getElementById('toast-container').classList.add('show');
            setTimeout(() => { document.getElementById('toast-container').classList.remove('show'); }, 3500);
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
            }, 3500);
        });
    </script>
    <?php unset($_SESSION['notifikasi_error']); endif; ?>
</body>
</html>