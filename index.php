<?php
session_start();
// Cek apakah yang membuka halaman ini sudah login sebagai admin/guru
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
// Trik Developer: Gunakan time() agar browser selalu memuat file css & js versi terbaru saat di-refresh
$version = time(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeopardy SMP Interaktif</title>
    <!-- CSS dengan versi dinamis -->
    <link rel="stylesheet" href="style.css?v=<?= $version ?>">
</head>
<body>
    <nav class="glass-nav">
        <?php if($is_admin): ?>
            <button onclick="window.location.href='index.php'">Reset / Beranda</button>
            <button onclick="window.location.href='logout.php'" class="btn-danger">Logout</button>
        <?php else: ?>
            <span style="color: white; font-weight: bold; margin-top: 10px;">Portal Guru SMPN 3 Gunungsindur</span>
        <?php endif; ?>
    </nav>

    <?php if($is_admin): ?>
        <!-- ========================================== -->
        <!-- AREA KHUSUS GURU (LOBBY & GAME)            -->
        <!-- ========================================== -->
        
        <!-- LOBBY (Halaman Persiapan) -->
        <section id="lobby" class="screen active">
            <div class="glass-panel lobby-container">
                <h1>Persiapan Permainan</h1>
                
                <div class="lobby-section">
                    <h3>1. Pilih 5 Kategori (<span id="cat-count">0</span>/5)</h3>
                    <div id="category-options" class="category-grid">
                        <!-- Checkbox Kategori dirender oleh JS -->
                    </div>
                </div>

                <div class="lobby-section">
                    <h3>2. Pengaturan Kelompok</h3>
                    <div class="team-setup-controls">
                        <label>Jumlah Kelompok (Maks 10): </label>
                        <input type="number" id="team-count" min="2" max="10" value="4" onchange="renderTeamInputs()">
                    </div>
                    <div id="team-inputs" class="team-inputs-grid">
                        <!-- Input Nama Tim dirender oleh JS -->
                    </div>
                </div>

                <button id="btn-start-game" class="btn-primary btn-large disabled" onclick="startGame()">MULAI PERMAINAN (Pilih 5 Kategori Dulu)</button>
            </div>
        </section>

        <!-- PAPAN PERMAINAN -->
        <section id="game" class="screen">
            <div id="game-board" class="board"></div>
            <!-- Tombol untuk melihat skor (mengambang di bawah) -->
            <button class="btn-view-score" onclick="toggleScoreboard()">Lihat Papan Skor 🏆</button>
        </section>

        <!-- PODIUM KEMENANGAN -->
        <section id="podium" class="screen">
            <div class="glass-panel podium-container">
                <h1>🎉 PERMAINAN SELESAI! 🎉</h1>
                <div class="podium-stand" id="podium-stand">
                    <!-- Juara 1, 2, 3 dirender oleh JS -->
                </div>
                <div id="other-ranks" class="other-ranks">
                    <!-- Peringkat lainnya -->
                </div>
                <button class="btn-primary" onclick="window.location.reload()">Main Lagi</button>
            </div>
        </section>

        <!-- MODAL SOAL -->
        <div id="question-modal" class="modal hidden">
            <div class="modal-content glass-panel">
                <div class="modal-header">
                    <h2 id="modal-category">Kategori</h2>
                    <h2 id="modal-points" class="points-badge">100</h2>
                </div>
                
                <div class="timer-bar"><div id="timer-progress"></div></div>
                
                <h1 id="modal-question">Pertanyaan</h1>
                <img id="modal-image" src="" alt="Visual" class="hidden">
                
                <div class="answer-section hidden" id="answer-section">
                    <h3 style="color: #64748b; margin-bottom:10px;">Jawaban yang Benar:</h3>
                    <h2 id="modal-answer" class="answer-text">Jawaban</h2>
                </div>
                
                <!-- Kontrol Utama Saat Soal Terbuka -->
                <div class="modal-controls" id="controls-main">
                    <button id="btn-pause" class="btn-warning" onclick="pauseTimer()">⏸️ Pause (Anak Ingin Menjawab)</button>
                    <button id="btn-close-early" class="btn-secondary" onclick="closeQuestion(false)">Tutup Tanpa Pemenang</button>
                </div>

                <!-- Kontrol Verifikasi (Muncul Saat Pause) -->
                <div class="modal-controls hidden" id="controls-verify">
                    <h3 style="width:100%; text-align:center; margin-bottom:15px; color:#fbbf24;">Siapa yang menjawab?</h3>
                    <button class="btn-green" onclick="showTeamSelector('benar')">✅ Jawaban BENAR</button>
                    <button class="btn-red" onclick="showTeamSelector('salah')">❌ Jawaban SALAH</button>
                    <button class="btn-secondary" onclick="resumeTimer()">Lanjutkan Waktu</button>
                </div>
            </div>
        </div>

        <!-- MODAL PEMILIH TIM -->
        <div id="team-selector-modal" class="modal hidden" style="z-index: 1100; background: rgba(0,0,0,0.95);">
            <div class="modal-content">
                <h2 id="selector-title">Pilih Kelompok</h2>
                <div id="team-selector-buttons" class="team-selector-grid">
                    <!-- Tombol Avatar Tim dirender oleh JS -->
                </div>
                <button class="btn-secondary" style="margin-top:20px;" onclick="closeTeamSelector()">Batal</button>
            </div>
        </div>

        <!-- MODAL PAPAN SKOR TERSEMBUNYI -->
        <div id="score-modal" class="modal hidden" style="z-index: 1050; align-items: flex-end; padding-bottom:50px;">
            <div class="glass-panel" style="width: 90%; max-width: 1000px; padding: 20px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h2 style="color:var(--accent);">Papan Skor Sementara</h2>
                    <button class="btn-secondary" onclick="toggleScoreboard()">Tutup</button>
                </div>
                <div id="scoreboard" class="scoreboard-grid">
                    <!-- Skor Tim dirender oleh JS -->
                </div>
            </div>
        </div>

        <!-- Audio -->
        <audio id="audio-correct" src="assets/audio/benar.mp3"></audio>
        <audio id="audio-wrong" src="assets/audio/salah.mp3"></audio>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- AREA LOGIN UNTUK TAMU/SISWA                -->
        <!-- ========================================== -->
        <section id="login" class="screen active">
            <div class="glass-panel" style="max-width: 400px; margin: 50px auto; text-align: center;">
                <h2 style="margin-bottom: 10px; color: var(--accent);">Login Guru</h2>
                <p style="margin-bottom: 20px;">Silakan masuk untuk mengelola dan memulai kuis Jeopardy.</p>
                
                <form action="proses_login.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                    <input type="text" name="username" placeholder="Username" required style="padding: 12px; border-radius: 8px; border: none; background: #1e293b; color: white;">
                    <input type="password" name="password" placeholder="Password" required style="padding: 12px; border-radius: 8px; border: none; background: #1e293b; color: white;">
                    <button type="submit" class="btn-primary">Masuk</button>
                </form>
            </div>
        </section>
    <?php endif; ?>

    <!-- JS dengan versi dinamis untuk mencegah cache -->
    <script src="script.js?v=<?= $version ?>"></script>
</body>
</html>