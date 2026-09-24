<?php
$config_file = 'config.json';
$config = file_exists($config_file) ? json_decode(file_get_contents($config_file), true) : ['game_title' => 'Jeopardy Edukasi'];
$judul_game = $config['game_title'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($judul_game) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        :root { --bg-dark: #0f172a; --bg-panel: #1e293b; --primary: #3b82f6; --text: #f8fafc; --accent: #fbbf24; --wrong: #ef4444; --success: #10b981; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text); overflow-x: hidden; min-height: 100vh; display: flex; flex-direction: column; }
        
        .top-navbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 40px; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid #334155; position: fixed; width: 100%; top: 0; z-index: 1000; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        .top-navbar h2 { font-size: 1.6rem; font-weight: 900; color: white; letter-spacing: 1px; cursor: pointer; transition: 0.2s; }
        .top-navbar h2:hover { opacity: 0.8; transform: scale(1.02); }
        .btn-admin { background: transparent; border: 2px solid #475569; color: #cbd5e1; padding: 8px 20px; border-radius: 8px; font-weight: bold; text-decoration: none; transition: 0.3s; }
        .btn-admin:hover { border-color: var(--primary); color: white; background: rgba(59, 130, 246, 0.2); }

        .screen-section { display: none; padding-top: 90px; min-height: 100vh; width: 100%; padding-bottom: 50px; }
        .screen-section.active { display: block; }
        .hidden { display: none !important; }

        .home-layout { display: flex; flex-direction: column; align-items: center; justify-content: center; height: calc(100vh - 90px); text-align: center; position: relative; padding: 0 20px; z-index: 10; }
        .hero-title { font-size: 4.5rem; font-weight: 900; color: white; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; text-shadow: 0 10px 30px rgba(59, 130, 246, 0.5); }
        .hero-subtitle { font-size: 1.2rem; color: #94a3b8; max-width: 600px; margin-bottom: 40px; line-height: 1.6; }
        .btn-play-massive { background: linear-gradient(135deg, var(--primary), #2563eb); color: white; font-size: 1.5rem; font-weight: 900; padding: 18px 50px; border: none; border-radius: 50px; cursor: pointer; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4); transition: 0.3s; letter-spacing: 1.5px; }
        .btn-play-massive:hover { transform: translateY(-3px) scale(1.05); }
        
        .floating-element { position: absolute; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3)); animation: floatApp 6s ease-in-out infinite; z-index: 1; opacity: 0.9; }
        .f-1 { font-size: 5rem; top: 15%; left: 15%; animation-delay: 0s; transform: rotate(-15deg); }
        .f-2 { font-size: 4rem; top: 25%; right: 18%; animation-delay: 1.5s; transform: rotate(20deg); }
        .f-3 { font-size: 5.5rem; bottom: 15%; left: 20%; animation-delay: 3s; transform: rotate(10deg); filter: blur(1px); opacity: 0.7; }
        .f-4 { font-size: 4.5rem; bottom: 20%; right: 15%; animation-delay: 0.5s; transform: rotate(-25deg); }
        @keyframes floatApp { 0%, 100% { transform: translateY(0) rotate(0deg); } 50% { transform: translateY(-25px) rotate(10deg); } }

        .lobby-container { max-width: 950px; margin: 40px auto; background: var(--bg-panel); padding: 40px 50px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); border: 1px solid #334155; }
        .lobby-container h2 { text-align: center; color: var(--accent); font-size: 2.2rem; margin-bottom: 40px; font-weight: 900; }
        
        .random-group { display: flex; align-items: stretch; background: #0f172a; border-radius: 8px; overflow: hidden; border: 1px solid #334155; box-shadow: inset 0 2px 4px rgba(0,0,0,0.3); }
        .random-group input { width: 50px; background: transparent; color: white; border: none; text-align: center; font-size: 1.2rem; font-weight: bold; outline: none; -moz-appearance: textfield; }
        .random-group input::-webkit-outer-spin-button, .random-group input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        .spinner-controls { display: flex; flex-direction: column; border-left: 1px solid #334155; }
        .spinner-controls button { background: transparent; border: none; color: #94a3b8; cursor: pointer; padding: 2px 8px; font-size: 0.7rem; transition: 0.2s; }
        .spinner-controls button:hover { background: #334155; color: white; }
        .random-group .btn-acak-kat { background: var(--accent); color: #0f172a; border: none; padding: 0 20px; font-weight: 900; cursor: pointer; transition: 0.2s; border-left: 1px solid #334155; }
        .random-group .btn-acak-kat:hover { background: #f59e0b; }

        .cat-checkbox { display: flex; align-items: center; gap: 12px; background: #0f172a; padding: 15px; border-radius: 12px; border: 1px solid #334155; color: #e2e8f0; cursor: pointer; transition: 0.2s; font-weight: 600;}
        .cat-checkbox:hover { border-color: var(--primary); background: #1e293b; }
        
        .team-edit-btn { display: flex; align-items: center; gap: 15px; background: #0f172a; padding: 15px; border-radius: 12px; border: 1px solid #334155; cursor: pointer; transition: 0.2s; }
        .team-edit-btn:hover { border-color: var(--accent); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .team-edit-btn img { width: 55px; height: 55px; border-radius: 50%; border: 2px solid #475569; }
        .avatar-option { width: 60px; height: 60px; border-radius: 50%; cursor: pointer; border: 3px solid transparent; opacity: 0.5; transition: 0.2s; }
        .avatar-option:hover { opacity: 1; transform: scale(1.1); }
        .avatar-option.selected { border-color: var(--primary); opacity: 1; transform: scale(1.15); box-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }

        #game { background-image: linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px); background-size: 50px 50px; }
        .board { display: grid; gap: 15px; padding: 20px 40px; width: 100%; max-width: 100%; margin-bottom: 50px; }
        
        .category-header { background: linear-gradient(180deg, var(--primary), #2563eb); color: white; font-weight: 900; text-align: center; padding: 15px 10px; border-radius: 12px; font-size: 1.1rem; box-shadow: 0 6px 15px rgba(0,0,0,0.4); text-transform: uppercase; letter-spacing: 1px; }
        .card { background: linear-gradient(135deg, #1e293b, #0f172a); color: var(--accent); font-size: 2.8rem; font-weight: 900; display: flex; align-items: center; justify-content: center; height: 110px; border-radius: 12px; cursor: pointer; border: 2px solid #334155; transition: 0.2s; box-shadow: 0 8px 20px rgba(0,0,0,0.4); text-shadow: 2px 2px 5px rgba(0,0,0,0.8); }
        .card:hover { transform: scale(1.03); border-color: var(--accent); box-shadow: 0 0 20px rgba(251, 191, 36, 0.3); z-index: 10; }
        .card.disabled { background: transparent; color: transparent; border-color: #1e293b; cursor: default; box-shadow: none; pointer-events: none; }

        .podium-title { font-size: 5rem; font-weight: 900; color: white; text-transform: uppercase; letter-spacing: 4px; text-shadow: 0 0 30px rgba(251, 191, 36, 0.6); margin-bottom: 70px; } 
        .podium-title span { color: var(--accent); }

        .q-modal-layout { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); z-index: 2000; display: flex; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(8px); }
        .q-modal-content { background: var(--bg-panel); padding: 50px; border-radius: 25px; max-width: 800px; width: 100%; text-align: center; border: 2px solid var(--primary); box-shadow: 0 25px 60px rgba(0,0,0,0.6); max-height: 90vh; overflow-y: auto; }
        .timer-bar { width: 100%; height: 15px; background: #0f172a; border-radius: 10px; overflow: hidden; margin: 30px 0; border: 1px solid #334155;}
        .timer-progress { width: 100%; height: 100%; background: var(--primary); transition: width 0.1s linear, background-color 0.3s; }
        
        .btn-answer-outline { background: rgba(30, 41, 59, 0.6); color: #e2e8f0; border: 2px solid #64748b; padding: 15px 40px; font-size: 1.1rem; font-weight: 800; border-radius: 50px; cursor: pointer; transition: all 0.3s ease; margin-top: 25px; letter-spacing: 2px; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .btn-answer-outline:hover { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 0 20px rgba(59, 130, 246, 0.6); transform: translateY(-2px); }

        .btn-quit-cancel { background: transparent; border: 2px solid #64748b; color: #cbd5e1; padding: 12px 25px; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.2s; font-size: 1.1rem;}
        .btn-quit-cancel:hover { background: #334155; color: white; border-color: #94a3b8; }
        .btn-quit-confirm { background: var(--wrong); color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4); transition: 0.2s; font-size: 1.1rem;}
        .btn-quit-confirm:hover { background: #dc2626; transform: translateY(-2px); }

        #custom-toast { position: fixed; top: 20px; left: 50%; transform: translate(-50%, -150%); background: #ef4444; color: white; padding: 15px 30px; border-radius: 10px; font-weight: bold; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.5); z-index: 3000; transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); border: 2px solid #b91c1c; display: flex; align-items: center; gap: 15px; }
        #custom-toast.show { transform: translate(-50%, 0); }

        .btn-floating-score { position: fixed; bottom: 30px; right: 40px; background: var(--accent); color: #0f172a; border: none; padding: 15px 30px; border-radius: 50px; font-size: 1.1rem; font-weight: 900; cursor: pointer; box-shadow: 0 10px 25px rgba(0,0,0,0.5); z-index: 100; transition: 0.3s; letter-spacing: 1px; }
        .btn-floating-score:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 15px 35px rgba(251, 191, 36, 0.4); }

        .scoreboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; max-height: 60vh; overflow-y: auto; padding-right: 10px; margin-top: 20px; }
        .scoreboard-grid::-webkit-scrollbar { width: 8px; }
        .scoreboard-grid::-webkit-scrollbar-track { background: transparent; }
        .scoreboard-grid::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
        .scoreboard-grid::-webkit-scrollbar-thumb:hover { background: var(--primary); }
    </style>
</head>
<body>

    <div id="custom-toast">
        <span style="font-size: 1.5rem;">⚠️</span>
        <span id="toast-msg">Pesan Galat</span>
    </div>

    <nav class="top-navbar">
        <h2 onclick="confirmGoHome()"><?= htmlspecialchars($judul_game) ?></h2>
        <a href="admin.php" class="btn-admin" id="btn-login-nav">Login</a>
    </nav>

    <section id="home-screen" class="screen-section active">
        <div class="floating-element f-1">💡</div><div class="floating-element f-2">🏆</div><div class="floating-element f-3">🚀</div><div class="floating-element f-4">🎯</div>
        <div class="home-layout">
            <h1 class="hero-title"><?= htmlspecialchars($judul_game) ?></h1>
            <p class="hero-subtitle">Uji pengetahuan Anda, atur strategi kelompok, dan raih skor tertinggi dalam kompetisi edukasi interaktif ini.</p>
            <button class="btn-play-massive" onclick="masukLobi()">MULAI BERMAIN</button>
        </div>
    </section>

    <section id="lobby" class="screen-section hidden">
        <div class="lobby-container" id="lobby-container">
            <h2>Persiapan Permainan</h2>
            <div style="margin-bottom: 40px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="color:white; font-size:1.3rem;">1. Pilih Kategori (<span id="cat-count">0</span>)</h3>
                    <div class="random-group">
                        <input type="number" id="random-cat-count" value="5" min="1" max="9" readonly>
                        <div class="spinner-controls">
                            <button onclick="document.getElementById('random-cat-count').stepUp()">▲</button>
                            <button onclick="document.getElementById('random-cat-count').stepDown()">▼</button>
                        </div>
                        <button class="btn-acak-kat" onclick="randomizeCategories()">ACAK</button>
                    </div>
                </div>
                <div id="category-options" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:15px;"></div>
            </div>

            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="color:white; font-size:1.3rem;">2. Pengaturan Kelompok</h3>
                    <button onclick="addTeam()" style="background:var(--primary); color:white; padding:10px 20px; border-radius:10px; border:none; font-weight:bold; cursor:pointer; box-shadow: 0 4px 10px rgba(59,130,246,0.3);">+ TAMBAH KELOMPOK</button>
                </div>
                <div id="team-inputs" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(350px, 1fr)); gap:20px;"></div>
            </div>

            <button onclick="startGame()" class="btn-play-massive" style="width:100%; margin-top:50px; font-size:1.4rem; padding:18px;">MULAI GAME SEKARANG</button>
        </div>
    </section>

    <section id="game" class="screen-section hidden">
        <div class="board" id="game-board"></div>
        <button class="btn-floating-score" onclick="toggleScoreboard()">🏆 LIHAT SKOR</button>
    </section>

    <section id="podium" class="screen-section hidden">
        <div style="text-align:center; padding-top: 30px;">
            <h1 class="podium-title">🏆 SANG <span>JUARA</span> 🏆</h1>
            <div id="podium-stand" style="display:flex; justify-content:center; align-items:flex-end; gap:25px; height: 380px;"></div>
            <div id="other-ranks-container" class="hidden" style="margin-top: 60px;">
                <div id="other-ranks" style="display:flex; justify-content:center; gap:20px; flex-wrap:wrap;"></div>
            </div>
            <button class="btn-play-massive" onclick="location.reload()" style="margin-top: 60px; font-size:1.2rem; padding:15px 40px; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.5);">MAIN LAGI</button>
        </div>
    </section>

    <div id="scoreboard-modal" class="q-modal-layout hidden" style="z-index: 2500;">
        <div class="q-modal-content" style="max-width: 1000px; padding: 40px; background: rgba(15, 23, 42, 0.98); border-color: var(--accent);">
            <h2 style="color: var(--accent); font-size: 2.2rem; font-weight: 900; text-transform: uppercase;">Papan Skor Sementara</h2>
            <div class="scoreboard-grid" id="scoreboard-grid"></div>
            <button onclick="toggleScoreboard()" style="margin-top: 35px; background: #64748b; color: white; padding: 15px 40px; border: none; border-radius: 50px; font-weight: bold; cursor: pointer; font-size: 1.1rem; transition: 0.2s;">TUTUP SKOR</button>
        </div>
    </div>

    <div id="question-modal" class="q-modal-layout hidden">
        <div class="q-modal-content">
            <h2 id="modal-category" style="color:#94a3b8; font-size:1.2rem; margin-bottom: 10px; text-transform:uppercase; letter-spacing:2px;">Kategori</h2>
            <h1 id="modal-points" style="color: var(--accent); font-size: 4rem; line-height:1; margin-bottom: 25px; text-shadow: 0 0 20px rgba(251,191,36,0.4);">100</h1>
            <p id="modal-question" style="font-size: 2rem; color: white; margin-bottom: 35px; font-weight: 800; line-height:1.4;"></p>
            
            <img id="modal-image" src="" alt="Visual" class="hidden" style="max-width:100%; max-height:300px; border-radius:15px; margin-bottom:25px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            <div id="audio-container" class="hidden" style="margin-bottom: 25px; width: 100%;">
                <audio id="modal-audio-player" controls style="width: 100%; border-radius: 30px;"></audio>
            </div>

            <div class="timer-bar"><div class="timer-progress" id="timer-progress"></div></div>
            
            <div style="display:flex; justify-content:center; gap:20px;">
                <button id="btn-pause" onclick="pauseTimer()" style="background:var(--accent); color:#0f172a; padding:12px 25px; border:none; border-radius:10px; font-weight:900; cursor:pointer;">JEDA WAKTU</button>
                <button id="btn-resume" onclick="resumeTimer()" class="hidden" style="background:var(--primary); color:white; padding:12px 25px; border:none; border-radius:10px; font-weight:900; cursor:pointer;">LANJUT WAKTU</button>
            </div>

            <button class="btn-answer-outline" onclick="revealAnswer()">TAMPILKAN JAWABAN</button>

            <div id="answer-section" class="hidden" style="margin-top: 35px; padding-top:30px; border-top:2px dashed #475569;">
                <h3 style="color:var(--success); font-size:2.2rem; margin-bottom:25px; font-weight:900;" id="modal-answer-big">Jawaban</h3>
                <div style="display:flex; justify-content:center; gap:20px;">
                    <button onclick="showTeamSelector('benar')" style="background:var(--success); color:white; padding:15px 30px; border:none; border-radius:12px; font-weight:bold; cursor:pointer; font-size:1.1rem; box-shadow: 0 5px 15px rgba(16,185,129,0.4);">✓ BENAR</button>
                    <button onclick="showTeamSelector('salah')" style="background:var(--wrong); color:white; padding:15px 30px; border:none; border-radius:12px; font-weight:bold; cursor:pointer; font-size:1.1rem; box-shadow: 0 5px 15px rgba(239,68,68,0.4);">✗ SALAH</button>
                    <button onclick="manualCloseQuestion()" style="background:#64748b; color:white; padding:15px 25px; border:none; border-radius:12px; font-weight:bold; cursor:pointer; font-size:1.1rem;">Keluar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="team-selector-modal" class="q-modal-layout hidden"><div class="q-modal-content" style="max-width: 600px;"><h2 id="selector-title" style="color:white; font-size:2rem; margin-bottom:20px;">Pilih Kelompok</h2><div id="team-selector-buttons" style="display:grid; grid-template-columns:1fr 1fr; gap:20px;"></div><button onclick="closeTeamSelector()" style="margin-top:30px; background:transparent; border:2px solid #64748b; color:#cbd5e1; padding:12px 30px; border-radius:10px; font-weight:bold; cursor:pointer;">BATAL</button></div></div>
    
    <div id="edit-team-modal" class="q-modal-layout hidden"><div class="q-modal-content" style="max-width: 500px;"><h2 style="color:white; margin-bottom:25px; font-size:1.8rem;">Edit Kelompok</h2><input type="text" id="edit-team-name" placeholder="Nama Kelompok" style="width:100%; padding:15px; margin-bottom:15px; border-radius:12px; background:#0f172a; color:white; border:2px solid #334155; font-size:1.1rem; outline:none;"><input type="text" id="edit-team-members" placeholder="Nama Anggota (Opsional)" style="width:100%; padding:15px; margin-bottom:25px; border-radius:12px; background:#0f172a; color:white; border:2px solid #334155; font-size:1.1rem; outline:none;"><h4 style="color:#94a3b8; margin-bottom:15px; text-align:left;">Pilih Avatar:</h4><div id="avatar-selection-grid" style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:30px; justify-content:center;"></div><div style="display:flex; justify-content:space-between;"><button onclick="deleteTeam()" style="background:var(--wrong); color:white; padding:12px 25px; border:none; border-radius:10px; font-weight:bold; cursor:pointer;">Hapus Tim</button><button onclick="saveTeamEdit()" style="background:var(--primary); color:white; padding:12px 35px; border:none; border-radius:10px; font-weight:bold; cursor:pointer;">Simpan Data</button></div></div></div>

    <div id="quit-modal" class="q-modal-layout hidden">
        <div class="q-modal-content" style="max-width: 450px; padding: 40px;">
            <div style="font-size: 4rem; margin-bottom: 10px;">⚠️</div>
            <h2 style="color: white; margin-bottom: 15px; font-size: 1.8rem;">Keluar Permainan?</h2>
            <p style="color: #94a3b8; font-size: 1.1rem; line-height: 1.5; margin-bottom: 30px;">Semua progres skor dan pengaturan kelompok akan hilang. Apakah Anda yakin ingin kembali ke beranda?</p>
            <div style="display: flex; gap: 15px; justify-content: center;">
                <button onclick="closeQuitModal()" class="btn-quit-cancel">Batal</button>
                <button onclick="forceQuitGame()" class="btn-quit-confirm">Ya, Keluar</button>
            </div>
        </div>
    </div>

    <!-- MODAL CUSTOM ALERT (Desain Baru Elegan) -->
    <div id="custom-alert-modal" class="q-modal-layout hidden">
        <div class="q-modal-content" style="max-width: 400px; padding: 30px;">
            <h2 style="color: var(--accent); margin-bottom: 15px; font-size: 1.6rem;">Peringatan</h2>
            <p id="custom-alert-msg" style="color: white; font-size: 1.1rem; line-height: 1.5;"></p>
            <button onclick="document.getElementById('custom-alert-modal').classList.add('hidden')" style="background: var(--primary); color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4); transition: 0.2s; font-size: 1.1rem; margin-top: 20px;">Mengerti</button>
        </div>
    </div>

    <audio id="audio-correct" src="assets/audio/correct.mp3"></audio>
    <audio id="audio-wrong" src="assets/audio/wrong.mp3"></audio>

    <!-- SCRIPT JS TIDAK MENGALAMI PERUBAHAN -->
    <script src="script.js?v=<?= time(); ?>"></script>

</body>
</html>