<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Hitung total kategori
$query_kat = mysqli_query($conn, "SELECT COUNT(*) as total FROM kategori");
$data_kat = mysqli_fetch_assoc($query_kat);
$total_kategori = $data_kat['total'];

// Hitung total bank soal
$query_soal = mysqli_query($conn, "SELECT COUNT(*) as total FROM soal");
$data_soal = mysqli_fetch_assoc($query_soal);
$total_soal = $data_soal['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor - Studio Kuis</title>
    <!-- Menautkan ke gaya desain global yang baru -->
    <link rel="stylesheet" href="assets/style.css">
    
    <style>
        /* Gaya khusus untuk ornamen Dasbor */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 30px; 
            margin-bottom: 45px; 
        }
        .stat-card { 
            background: var(--bg-panel); 
            padding: 35px 30px; 
            border-radius: 16px; 
            border: 1px solid #334155; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
        }
        .stat-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); 
        }
        .stat-info h3 { 
            color: #94a3b8; 
            font-size: 1.05rem; 
            margin-bottom: 12px; 
            font-weight: 600; 
            text-transform: uppercase; 
            letter-spacing: 1.5px;
        }
        .stat-info h2 { 
            color: white; 
            font-size: 3rem; 
            font-weight: 900; 
            line-height: 1;
        }
        .stat-icon { 
            font-size: 4.5rem; 
            opacity: 0.9; 
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
        }
        
        /* Panel Panduan Modern */
        .guide-panel { 
            background: linear-gradient(145deg, var(--bg-panel) 0%, rgba(59, 130, 246, 0.05) 100%); 
            border-left: 6px solid var(--primary); 
            padding: 40px; 
            border-radius: 16px; 
            border-top: 1px solid #334155; 
            border-right: 1px solid #334155; 
            border-bottom: 1px solid #334155; 
        }
        .guide-panel h3 { 
            color: white; 
            font-size: 1.6rem; 
            margin-bottom: 25px; 
            display: flex; 
            align-items: center; 
            gap: 12px;
        }
        .guide-list { list-style: none; }
        .guide-list li { 
            margin-bottom: 20px; 
            display: flex; 
            gap: 20px; 
            color: #cbd5e1; 
            font-size: 1.1rem; 
            line-height: 1.6; 
            background: rgba(255,255,255,0.02);
            padding: 15px 20px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .guide-list li span { 
            background: var(--primary); 
            color: white; 
            width: 32px; 
            height: 32px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 50%; 
            font-weight: bold; 
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.4);
        }
        strong { color: white; }
    </style>
</head>
<body>
    
    <!-- Memanggil Sidebar Independen -->
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div style="margin-bottom: 45px;">
            <h1 class="header-title" style="border:none; margin-bottom: 12px; padding-bottom:0;">👋 Selamat Datang, Admin!</h1>
            <p style="color: #94a3b8; font-size: 1.15rem;">Pantau dan kelola seluruh konten permainan interaktif Anda melalui panel kontrol ini.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-bottom: 5px solid var(--primary);">
                <div class="stat-info">
                    <h3>Total Topik Kuis</h3>
                    <h2><?= $total_kategori ?></h2>
                </div>
                <div class="stat-icon">📑</div>
            </div>
            
            <div class="stat-card" style="border-bottom: 5px solid var(--accent);">
                <div class="stat-info">
                    <h3>Total Bank Soal</h3>
                    <h2><span style="color: var(--accent);"><?= $total_soal ?></span></h2>
                </div>
                <div class="stat-icon">📝</div>
            </div>
        </div>

        <div class="guide-panel">
            <h3>🚀 Panduan Singkat Penggunaan</h3>
            <ul class="guide-list">
                <li>
                    <span>1</span>
                    <div>Buka menu <strong>Topik Kuis</strong> di sebelah kiri untuk mendaftarkan mata pelajaran atau ruang lingkup pertanyaan baru.</div>
                </li>
                <li>
                    <span>2</span>
                    <div>Masuk ke menu <strong>Bank Soal (Acak)</strong> untuk mulai menginput pertanyaan, jawaban, batas waktu, serta media pendukung (gambar/audio).</div>
                </li>
                <li>
                    <span>3</span>
                    <div>Sistem akan <strong>mengacak soal secara otomatis</strong> saat permainan dimulai jika Anda memasukkan lebih dari 1 soal untuk nilai poin yang sama (misal: 3 opsi soal di topik Bahasa Inggris yang bernilai 100 poin).</div>
                </li>
            </ul>
        </div>
    </main>

</body>
</html>