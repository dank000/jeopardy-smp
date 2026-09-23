<?php
session_start();
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
$version = time(); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jeopardy SMP Interaktif</title>
    <link rel="stylesheet" href="style.css?v=<?= $version ?>">
</head>
<body>
    <nav class="glass-nav">
        <span style="color: white; font-weight: bold; font-size:1.2rem;">Portal Game Edukasi</span>
        <?php if($is_admin): ?>
            <button onclick="alert('Halaman Edit Soal akan segera hadir!')" class="btn-warning">Mode Admin Aktif</button>
            <button onclick="window.location.href='logout.php'" class="btn-danger">Keluar Admin</button>
        <?php else: ?>
            <button onclick="document.getElementById('login-modal').classList.remove('hidden')" class="btn-secondary">Admin Login</button>
        <?php endif; ?>
    </nav>

    <!-- LOBBY BERSIH DAN PROFESIONAL -->
    <section id="lobby" class="screen active">
        <div class="glass-panel lobby-container">
            <h1 style="margin-bottom: 30px; font-size: 2.5rem; color: var(--accent);">Persiapan Permainan</h1>
            
            <div class="lobby-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                    <h3 style="margin:0; color:#f8fafc;">1. Pilih Kategori (<span id="cat-count">0</span>)</h3>
                    <div style="display:flex; gap:10px; align-items:center;">
                        <input type="number" id="random-cat-count" value="5" min="1" max="9" class="input-number">
                        <button class="btn-warning" style="padding:8px 15px; font-size:0.9rem;" onclick="randomizeCategories()">Acak Kategori</button>
                    </div>
                </div>
                <div id="category-options" class="category-grid"></div>
            </div>

            <div class="lobby-section">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                    <h3 style="margin:0; color:#f8fafc;">2. Pengaturan Kelompok</h3>
                    <button class="btn-primary" style="padding:8px 15px; font-size:0.9rem;" onclick="addTeam()">+ Tambah Kelompok</button>
                </div>
                <div id="team-inputs" class="team-inputs-grid"></div>
            </div>

            <button id="btn-start-game" class="btn-primary btn-large" onclick="startGame()">Mulai Permainan</button>
        </div>
    </section>

    <!-- PAPAN PERMAINAN -->
    <section id="game" class="screen">
        <div id="game-board" class="board"></div>
        <button class="btn-view-score" onclick="toggleScoreboard()">Cek Papan Skor</button>
    </section>

    <!-- PODIUM KEMENANGAN (Dibersihkan agar tidak menumpuk) -->
    <section id="podium" class="screen">
        <div class="glass-panel podium-container">
            <h1 class="podium-title" style="margin-bottom: 60px;">CHAMPIONS</h1>
            
            <div class="podium-stand" id="podium-stand"></div>
            
            <div id="other-ranks-container" class="hidden">
                <h3 style="margin-top:20px; color:#94a3b8;">Peringkat Lainnya:</h3>
                <div id="other-ranks" class="other-ranks"></div>
            </div>
            
            <button class="btn-primary btn-large" style="max-width:300px; margin:30px auto 0;" onclick="window.location.reload()">Main Lagi</button>
        </div>
    </section>

    <!-- MODAL SOAL (Dengan Tombol Jawaban di Atas) -->
    <div id="question-modal" class="modal hidden">
        <div class="modal-content glass-panel q-modal-layout">
            
            <div class="q-header">
                <h2 id="modal-category" style="color:#94a3b8; font-size:1.5rem;">Kategori</h2>
                <h2 id="modal-points" class="points-badge">100</h2>
            </div>
            
            <div class="timer-bar"><div id="timer-progress"></div></div>
            
            <div class="q-body custom-scroll">
                <h1 id="modal-question">Pertanyaan</h1>
                <img id="modal-image" src="" alt="Visual" class="hidden">
                
                <div class="answer-section hidden" id="answer-section">
                    <h2 id="modal-answer-big" class="answer-text">Jawaban</h2>
                </div>
            </div>
            
            <div class="q-footer">
                <!-- Tombol Tampilkan Jawaban (Tersedia kapan saja) -->
                <button class="btn-primary btn-big" style="margin-bottom:15px; width:100%; max-width:400px;" onclick="revealAnswer()">👁️ TAMPILKAN JAWABAN</button>
                
                <div class="modal-controls">
                    <button id="btn-pause" class="btn-warning btn-big" onclick="pauseTimer()">✋ PAUSE</button>
                    <button id="btn-resume" class="btn-secondary btn-big hidden" onclick="resumeTimer()">▶ LANJUT</button>
                    
                    <button class="btn-green btn-big" onclick="showTeamSelector('benar')">✅ BENAR</button>
                    <button class="btn-red btn-big" onclick="showTeamSelector('salah')">❌ SALAH</button>
                    
                    <button class="btn-danger btn-big" onclick="closeQuestion(false)">⏩ TUTUP</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PEMILIH TIM -->
    <div id="team-selector-modal" class="modal hidden" style="z-index: 1100;">
        <div class="modal-content" style="max-width:600px; background: rgba(2, 6, 23, 0.95);">
            <h2 id="selector-title" style="font-size:2rem; margin-bottom:20px;">Pilih Tim</h2>
            <div id="team-selector-buttons" class="team-selector-grid"></div>
            <button class="btn-secondary btn-large" style="margin-top:20px;" onclick="closeTeamSelector()">BATAL</button>
        </div>
    </div>

    <!-- MODAL EDIT TIM -->
    <div id="edit-team-modal" class="modal hidden" style="z-index: 1050;">
        <div class="glass-panel" style="width: 90%; max-width: 400px; padding: 30px; text-align:left;">
            <h2 style="color:var(--accent); margin-bottom:20px; text-align:center;">Edit Profil Tim</h2>
            <input type="hidden" id="edit-team-id">
            
            <label style="color:#94a3b8; display:block; margin-bottom:5px;">Nama Tim:</label>
            <input type="text" id="edit-team-name" style="width:100%; padding:12px; border-radius:8px; border:none; background:#1e293b; color:white; font-size:1.1rem; margin-bottom:15px;">
            
            <label style="color:#94a3b8; display:block; margin-bottom:5px;">Anggota (Opsional):</label>
            <textarea id="edit-team-members" rows="3" style="width:100%; padding:12px; border-radius:8px; border:none; background:#1e293b; color:white; font-size:1rem; margin-bottom:15px; resize:none;"></textarea>
            
            <label style="color:#94a3b8; display:block; margin-bottom:10px;">Pilih Avatar:</label>
            <div id="avatar-selection-grid" style="display:flex; flex-wrap:wrap; gap:10px; justify-content:center; margin-bottom:20px;"></div>
            
            <div style="display:flex; gap:10px;">
                <button class="btn-primary" style="flex:1;" onclick="saveTeamEdit()">Simpan</button>
                <button class="btn-secondary" style="flex:1;" onclick="document.getElementById('edit-team-modal').classList.add('hidden')">Batal</button>
            </div>
            <button class="btn-danger" style="width:100%; margin-top:10px;" onclick="deleteTeam()">Hapus Tim Ini</button>
        </div>
    </div>

    <!-- MODAL SKOR -->
    <div id="score-modal" class="modal hidden" style="z-index: 1050; align-items: flex-end; padding-bottom:50px;">
        <div class="glass-panel" style="width: 90%; max-width: 1000px; padding: 30px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                <h2 style="color:var(--accent); font-size:2rem;">Papan Peringkat Sementara</h2>
                <button class="btn-secondary" onclick="toggleScoreboard()">Tutup Papan</button>
            </div>
            <div id="scoreboard" class="scoreboard-grid"></div>
        </div>
    </div>

    <!-- MODAL LOGIN ADMIN -->
    <div id="login-modal" class="modal hidden" style="z-index: 1200;">
        <div class="glass-panel" style="width: 90%; max-width: 400px; padding: 30px;">
            <h2 style="color:var(--accent); margin-bottom:20px; text-align:center;">Login Admin</h2>
            <form action="proses_login.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="text" name="username" placeholder="Username" required style="padding: 12px; border-radius: 8px; border: none; background: #1e293b; color: white;">
                <input type="password" name="password" placeholder="Password" required style="padding: 12px; border-radius: 8px; border: none; background: #1e293b; color: white;">
                <div style="display:flex; gap:10px;">
                    <button type="submit" class="btn-primary" style="flex:1;">Masuk</button>
                    <button type="button" class="btn-secondary" style="flex:1;" onclick="document.getElementById('login-modal').classList.add('hidden')">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <audio id="audio-correct" src="assets/audio/benar.mp3"></audio>
    <audio id="audio-wrong" src="assets/audio/salah.mp3"></audio>

    <script src="script.js?v=<?= $version ?>"></script>
</body>
</html>