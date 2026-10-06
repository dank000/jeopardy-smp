<?php
session_start();
$config_file = 'config.json';
$default_cfg = [
    'admin_title' => 'Studio Kuis',
    'game_title' => 'Jeopardy Edukasi',
    'penalti_salah' => 'ya',
    'default_cat_count' => 5,
    'default_waktu_soal' => 30
];
$config = file_exists($config_file) 
    ? array_merge($default_cfg, json_decode(file_get_contents($config_file), true)) 
    : $default_cfg;
$judul_game = $config['game_title'];
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title><?= htmlspecialchars($judul_game) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

    <style>
        :root { --bg-dark: #0f172a; --bg-panel: #1e293b; --primary: #3b82f6; --text: #f8fafc; --accent: #fbbf24; --wrong: #ef4444; --success: #10b981; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text); overflow-x: hidden; min-height: 100vh; display: flex; flex-direction: column; }
        
        .top-navbar { display: flex; justify-content: space-between; align-items: center; padding: 12px 40px; background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid #334155; position: fixed; width: 100%; top: 0; z-index: 1000; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        
        .brand-home-logo {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: 2px solid rgba(251, 191, 36, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4), inset 0 2px 4px rgba(255, 255, 255, 0.25);
            transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        .brand-home-logo:hover {
            transform: translateY(-2px) scale(1.06);
            border-color: var(--accent);
            box-shadow: 0 6px 20px rgba(251, 191, 36, 0.45), 0 0 15px rgba(59, 130, 246, 0.6);
        }
        .brand-home-logo svg {
            width: 24px;
            height: 24px;
            fill: none;
            stroke: #ffffff;
            stroke-width: 2.2;
            stroke-linecap: round;
            stroke-linejoin: round;
            filter: drop-shadow(0 2px 3px rgba(0,0,0,0.3));
        }

        .nav-buttons { display: flex; align-items: center; gap: 12px; }
        
        .btn-admin { background: transparent; border: 2px solid #475569; color: #cbd5e1; padding: 8px 20px; border-radius: 10px; font-weight: bold; text-decoration: none; cursor: pointer; font-size: 0.95rem; transition: 0.3s; }
        .btn-admin:hover { border-color: var(--primary); color: white; background: rgba(59, 130, 246, 0.2); }
        
        .btn-icon-nav { background: rgba(30, 41, 59, 0.7); border: 1px solid #334155; color: var(--accent); width: 42px; height: 42px; border-radius: 10px; font-size: 1.25rem; font-weight: bold; cursor: pointer; transition: 0.25s; display: flex; align-items: center; justify-content: center; line-height: 1; }
        .btn-icon-nav:hover { background: var(--accent); color: #0f172a; border-color: var(--accent); box-shadow: 0 0 15px rgba(251, 191, 36, 0.4); }

        .volume-control { display: flex; align-items: center; gap: 10px; background: rgba(30, 41, 59, 0.7); padding: 6px 14px; border-radius: 10px; border: 1px solid #334155; height: 42px; }
        .btn-vol-icon { background: transparent; border: none; color: white; font-size: 1.15rem; cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 2px; transition: 0.2s; line-height: 1; }
        .btn-vol-icon:hover { transform: scale(1.15); color: var(--accent); }
        .volume-control input[type=range] { width: 85px; accent-color: var(--accent); cursor: pointer; height: 6px; border-radius: 5px; outline: none; }

        button:active, .cat-checkbox:active, .team-edit-btn:active, .btn-acak-kat:active, .btn-play-massive:active, .brand-home-logo:active { 
            transform: scale(0.95) !important; 
            transition: transform 0.1s !important; 
        }

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
        
        .counter-group { display: flex; align-items: center; background: #0f172a; border-radius: 12px; border: 2px solid #334155; padding: 5px; gap: 5px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.3); }
        .counter-group .btn-spin { background: #1e293b; color: #cbd5e1; border: none; width: 35px; height: 35px; border-radius: 8px; font-size: 1.5rem; font-weight: bold; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; line-height: 1; }
        .counter-group .btn-spin:hover { background: var(--primary); color: white; transform: scale(1.05); }
        .counter-group input { width: 45px; background: transparent; color: var(--accent); border: none; text-align: center; font-size: 1.4rem; font-weight: 900; outline: none; pointer-events: none; -moz-appearance: textfield; appearance: textfield; }
        .counter-group input::-webkit-outer-spin-button, .counter-group input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        
        .btn-acak-kat { background: var(--accent); color: #0f172a; border: none; padding: 0 25px; height: 48px; border-radius: 12px; font-weight: 900; cursor: pointer; transition: 0.3s; font-size: 1rem; letter-spacing: 1px; box-shadow: 0 4px 10px rgba(251, 191, 36, 0.3); }
        .btn-acak-kat:hover { background: #f59e0b; transform: translateY(-2px); box-shadow: 0 8px 15px rgba(251, 191, 36, 0.5); }

        .cat-checkbox { display: flex; align-items: center; gap: 15px; background: #0f172a; padding: 15px 20px; border-radius: 12px; border: 2px solid #334155; color: #94a3b8; cursor: pointer; transition: 0.3s; font-weight: 700; position: relative; }
        .cat-checkbox input { position: absolute; opacity: 0; cursor: pointer; height: 0; width: 0; }
        .custom-check { width: 24px; height: 24px; background: #1e293b; border: 2px solid #475569; border-radius: 6px; display: flex; align-items: center; justify-content: center; transition: 0.2s; flex-shrink: 0; }
        .custom-check::after { content: ''; width: 6px; height: 12px; border: solid white; border-width: 0 3px 3px 0; transform: rotate(45deg) scale(0); transition: 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .cat-checkbox:hover { border-color: #64748b; color: white; }
        .cat-checkbox.checked-style { border-color: var(--success); background: rgba(16, 185, 129, 0.15); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2); }
        .cat-checkbox.checked-style .custom-check { background: var(--success); border-color: var(--success); box-shadow: 0 0 10px rgba(16,185,129,0.5); }
        .cat-checkbox.checked-style .custom-check::after { transform: rotate(45deg) scale(1); }

        .team-edit-btn { display: flex; align-items: center; gap: 15px; background: #0f172a; padding: 15px; border-radius: 12px; border: 1px solid #334155; cursor: pointer; transition: 0.2s; }
        .team-edit-btn:hover { border-color: var(--accent); transform: translateY(-3px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .team-edit-btn img { width: 55px; height: 55px; border-radius: 50%; border: 2px solid #475569; }
        .avatar-option { width: 60px; height: 60px; border-radius: 50%; cursor: pointer; border: 3px solid transparent; opacity: 0.5; transition: 0.2s; }
        .avatar-option:hover { opacity: 1; transform: scale(1.1); }
        .avatar-option.selected { border-color: var(--primary); opacity: 1; transform: scale(1.15); box-shadow: 0 0 15px rgba(59, 130, 246, 0.5); }

        #game { background-image: linear-gradient(rgba(255, 255, 255, 0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.02) 1px, transparent 1px); background-size: 50px 50px; }
        .board { display: grid; gap: 15px; padding: 20px 40px; width: 100%; max-width: 100%; margin-bottom: 50px; perspective: 1200px; }
        
        .category-header { 
            background: linear-gradient(180deg, var(--primary), #2563eb); 
            color: white; 
            font-weight: 900; 
            text-align: center; 
            padding: 12px 15px; 
            border-radius: 12px; 
            font-size: 1.1rem; 
            box-shadow: 0 6px 15px rgba(0,0,0,0.4); 
            text-transform: uppercase; 
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 75px;
            line-height: 1.3;
        }
        
        .card { background: linear-gradient(135deg, #1e293b, #0f172a); color: var(--accent); font-size: 2.8rem; font-weight: 900; display: flex; align-items: center; justify-content: center; height: 110px; border-radius: 12px; cursor: pointer; border: 2px solid #334155; transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.2s, box-shadow 0.2s; box-shadow: 0 8px 20px rgba(0,0,0,0.4); text-shadow: 2px 2px 5px rgba(0,0,0,0.8); transform-style: preserve-3d; }
        .card:hover { transform: scale(1.03); border-color: var(--accent); box-shadow: 0 0 20px rgba(251, 191, 36, 0.3); z-index: 10; }
        .card.flipping { transform: rotateY(90deg) scale(1.05); }
        .card.disabled { background: rgba(15, 23, 42, 0.45); color: #475569; border-color: #1e293b; box-shadow: none; text-shadow: none; }
        .card.disabled:hover { border-color: #475569; transform: scale(1.02); }

        @keyframes modalFlipOpen {
            0% { transform: perspective(1200px) rotateY(-85deg) scale(0.85); opacity: 0; }
            60% { transform: perspective(1200px) rotateY(8deg) scale(1.02); opacity: 1; }
            100% { transform: perspective(1200px) rotateY(0deg) scale(1); opacity: 1; }
        }
        .q-modal-content.flip-animate {
            animation: modalFlipOpen 0.48s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .podium-title { 
            font-size: 3.4rem; 
            font-weight: 900; 
            color: white; 
            text-transform: uppercase; 
            letter-spacing: 3px; 
            text-shadow: 0 0 30px rgba(251, 191, 36, 0.5); 
            margin-top: 0;
            margin-bottom: 50px; 
        } 
        .podium-title span { color: var(--accent); }
        
        #podium-stand {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 30px;
            margin-top: 30px;
            padding-top: 20px;
            min-height: 440px;
        }

        .q-modal-layout { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.95); z-index: 2000; display: flex; justify-content: center; align-items: center; padding: 20px; backdrop-filter: blur(8px); }
        .q-modal-content { background: var(--bg-panel); padding: 45px 50px; border-radius: 25px; max-width: 800px; width: 100%; text-align: center; border: 2px solid var(--primary); box-shadow: 0 25px 60px rgba(0,0,0,0.6); max-height: 90vh; overflow-y: auto; }
        .timer-bar { width: 100%; height: 15px; background: #0f172a; border-radius: 10px; overflow: hidden; margin: 25px 0; border: 1px solid #334155;}
        .timer-progress { width: 100%; height: 100%; background: var(--primary); transition: width 0.1s linear, background-color 0.3s; }
        
        .btn-answer-outline { background: rgba(30, 41, 59, 0.6); color: #e2e8f0; border: 2px solid #64748b; padding: 15px 40px; font-size: 1.1rem; font-weight: 800; border-radius: 50px; cursor: pointer; transition: all 0.3s ease; margin-top: 20px; letter-spacing: 2px; text-transform: uppercase; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .btn-answer-outline:hover { background: var(--primary); color: white; border-color: var(--primary); box-shadow: 0 0 20px rgba(59, 130, 246, 0.6); transform: translateY(-2px); }

        /* KOTAK JAWABAN MINIMALIS: Teks Jawaban Tepat di Tengah Border */
        #answer-section {
            margin-top: 28px;
            padding-top: 28px;
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            animation: fadeInAnswer 0.3s ease-out forwards;
        }
        @keyframes fadeInAnswer {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .answer-showcase-box {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.14) 0%, rgba(15, 23, 42, 0.85) 100%);
            border: 2px solid rgba(16, 185, 129, 0.55);
            border-radius: 18px;
            padding: 24px 35px;
            min-height: 92px;
            margin-bottom: 26px;
            width: 100%;
            max-width: 640px;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.15), inset 0 1px 2px rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        #modal-answer-big {
            color: #ffffff;
            font-size: 2.3rem;
            font-weight: 900;
            line-height: 1.3;
            margin: 0;
            text-align: center;
            text-shadow: 0 2px 12px rgba(16, 185, 129, 0.4);
            word-break: break-word;
        }
        .answer-actions-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            width: 100%;
            flex-wrap: wrap;
        }
        .btn-judge {
            min-width: 155px;
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-weight: 900;
            font-size: 1.05rem;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s ease;
            color: white;
        }
        .btn-judge:hover { transform: translateY(-2px); }
        .btn-judge-correct { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); }
        .btn-judge-correct:hover { box-shadow: 0 8px 25px rgba(16, 185, 129, 0.6); }
        .btn-judge-wrong { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4); }
        .btn-judge-wrong:hover { box-shadow: 0 8px 25px rgba(239, 68, 68, 0.6); }
        .btn-judge-exit { background: #334155; color: #e2e8f0; border: 1.5px solid #475569; }
        .btn-judge-exit:hover { background: #475569; color: white; }

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

        .login-input { width: 100%; padding: 15px; margin-bottom: 18px; border-radius: 12px; background: #0f172a; color: white; border: 2px solid #334155; font-size: 1.05rem; outline: none; transition: 0.2s; }
        .login-input:focus { border-color: var(--primary); }
    </style>
</head>
<body>

    <div id="custom-toast">
        <span style="font-size: 1.5rem;">⚠️</span>
        <span id="toast-msg">Pesan Galat</span>
    </div>

    <!-- AUDIO ELEMENTS -->
    <audio id="bgm-lobby" src="assets/audio/bgm_lobby.mp3" loop></audio>
    <audio id="bgm-game" src="assets/audio/bgm_game.mp3" loop></audio>
    <audio id="bgm-podium" src="assets/audio/bgm_podium.mp3" loop></audio>
    
    <audio id="sfx-click" src="assets/audio/sfx_click.mp3"></audio>
    <audio id="sfx-transition" src="assets/audio/sfx_transition.mp3"></audio>
    <audio id="sfx-card" src="assets/audio/sfx_card.mp3"></audio>
    <audio id="sfx-tick" src="assets/audio/sfx_tick.mp3" loop></audio>
    <audio id="sfx-reveal" src="assets/audio/sfx_reveal.mp3"></audio>
    <audio id="audio-correct" src="assets/audio/correct.mp3"></audio>
    <audio id="audio-wrong" src="assets/audio/wrong.mp3"></audio>

    <nav class="top-navbar">
        <div class="brand-home-logo" onclick="confirmGoHome()" title="Kembali ke Beranda Utama">
            <svg viewBox="0 0 24 24">
                <path d="M3 10.5L12 3l9 7.5v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-9z"/>
                <path d="M9 21V13h6v8"/>
            </svg>
        </div>

        <div class="nav-buttons">
            <div class="volume-control" title="Atur Volume Suara">
                <button type="button" class="btn-vol-icon" id="btn-mute-icon" onclick="toggleAudio()">🔊</button>
                <input type="range" id="vol-master" min="0" max="1" step="0.05" value="0.7" oninput="updateMasterVolume(this.value)">
            </div>
            
            <button type="button" class="btn-icon-nav" onclick="toggleFullScreen()" id="btn-fullscreen" title="Layar Penuh / Minimize">⛶</button>
            
            <?php if ($is_admin): ?>
                <a href="admin.php" class="btn-admin" id="btn-login-nav">Panel Admin</a>
            <?php else: ?>
                <button type="button" class="btn-admin" id="btn-login-nav" onclick="openLoginModal()">Login</button>
            <?php endif; ?>
        </div>
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
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div class="counter-group">
                            <button type="button" class="btn-spin" onclick="adjustCatCount(-1)">-</button>
                            <input type="number" id="random-cat-count" value="5" min="1" max="9" readonly>
                            <button type="button" class="btn-spin" onclick="adjustCatCount(1)">+</button>
                        </div>
                        <button class="btn-acak-kat" onclick="randomizeCategories()">ACAK KATEGORI</button>
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
        <div style="text-align:center; padding-top: 10px;">
            <h1 class="podium-title">🏆 KLASEMEN <span>AKHIR</span> 🏆</h1>
            <div id="podium-stand"></div>
            <div id="other-ranks-container" class="hidden" style="margin-top: 50px;">
                <div id="other-ranks" style="display:flex; justify-content:center; gap:20px; flex-wrap:wrap;"></div>
            </div>
            <button class="btn-play-massive" onclick="location.reload()" style="margin-top: 50px; font-size:1.2rem; padding:15px 40px; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.5);">MAIN LAGI</button>
        </div>
    </section>

    <!-- MODAL LOGIN -->
    <div id="login-modal" class="q-modal-layout hidden" style="z-index: 2600;">
        <div class="q-modal-content" style="max-width: 420px; padding: 40px;">
            <h2 style="color: white; margin-bottom: 10px; font-size: 1.9rem; font-weight: 900;">Masuk Akun</h2>
            <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 25px;">Silakan masuk untuk mengelola kuis dan pengaturan.</p>
            <form action="proses_login.php" method="POST">
                <input type="text" name="username" class="login-input" placeholder="Username" required autocomplete="off">
                <input type="password" name="password" class="login-input" placeholder="Password" required>
                <div style="display: flex; gap: 12px; margin-top: 10px;">
                    <button type="button" onclick="closeLoginModal()" class="btn-quit-cancel" style="flex: 1;">Batal</button>
                    <button type="submit" style="flex: 1; background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 10px; font-weight: bold; cursor: pointer; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);">Login</button>
                </div>
            </form>
        </div>
    </div>

    <div id="scoreboard-modal" class="q-modal-layout hidden" style="z-index: 2500;">
        <div class="q-modal-content" style="max-width: 1000px; padding: 40px; background: rgba(15, 23, 42, 0.98); border-color: var(--accent);">
            <h2 style="color: var(--accent); font-size: 2.2rem; font-weight: 900; text-transform: uppercase;">Papan Skor Sementara</h2>
            <div class="scoreboard-grid" id="scoreboard-grid"></div>
            <button onclick="toggleScoreboard()" style="margin-top: 35px; background: #64748b; color: white; padding: 15px 40px; border: none; border-radius: 50px; font-weight: bold; cursor: pointer; font-size: 1.1rem; transition: 0.2s;">TUTUP SKOR</button>
        </div>
    </div>

    <div id="question-modal" class="q-modal-layout hidden">
        <div class="q-modal-content" id="question-modal-box">
            <h2 id="modal-category" style="color:#94a3b8; font-size:1.2rem; margin-bottom: 10px; text-transform:uppercase; letter-spacing:2px;">Kategori</h2>
            <h1 id="modal-points" style="color: var(--accent); font-size: 4rem; line-height:1; margin-bottom: 25px; text-shadow: 0 0 20px rgba(251,191,36,0.4);">100</h1>
            <p id="modal-question" style="font-size: 2rem; color: white; margin-bottom: 30px; font-weight: 800; line-height:1.4;"></p>
            
            <img id="modal-image" src="" alt="Visual" class="hidden" style="max-width:100%; max-height:300px; border-radius:15px; margin-bottom:25px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
            <div id="audio-container" class="hidden" style="margin-bottom: 25px; width: 100%;">
                <audio id="modal-audio-player" controls style="width: 100%; border-radius: 30px;"></audio>
            </div>

            <div class="timer-bar"><div class="timer-progress" id="timer-progress"></div></div>
            
            <div style="display:flex; justify-content:center; gap:20px;">
                <button id="btn-pause" onclick="pauseTimer()" style="background:var(--accent); color:#0f172a; padding:12px 25px; border:none; border-radius:10px; font-weight:900; cursor:pointer;">JEDA WAKTU</button>
                <button id="btn-resume" onclick="resumeTimer()" class="hidden" style="background:var(--primary); color:white; padding:12px 25px; border:none; border-radius:10px; font-weight:900; cursor:pointer;">LANJUT WAKTU</button>
            </div>

            <button id="btn-reveal-answer" class="btn-answer-outline" onclick="revealAnswer()">TAMPILKAN JAWABAN</button>

            <!-- TAMPILAN JAWABAN SIMPEL & PRESISI DI TENGAH BORDER -->
            <div id="answer-section" class="hidden">
                <div class="answer-showcase-box">
                    <h3 id="modal-answer-big">Jawaban</h3>
                </div>
                <div class="answer-actions-row">
                    <button onclick="showTeamSelector('benar')" class="btn-judge btn-judge-correct">✓ Benar</button>
                    <button onclick="showTeamSelector('salah')" class="btn-judge btn-judge-wrong">✗ Salah</button>
                    <button onclick="manualCloseQuestion()" class="btn-judge btn-judge-exit">Keluar</button>
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

    <div id="custom-alert-modal" class="q-modal-layout hidden">
        <div class="q-modal-content" style="max-width: 400px; padding: 30px;">
            <h2 style="color: var(--accent); margin-bottom: 15px; font-size: 1.6rem;">Peringatan</h2>
            <p id="custom-alert-msg" style="color: white; font-size: 1.1rem; line-height: 1.5;"></p>
            <button onclick="document.getElementById('custom-alert-modal').classList.add('hidden')" style="background: var(--primary); color: white; border: none; padding: 12px 30px; border-radius: 10px; font-weight: bold; cursor: pointer; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4); transition: 0.2s; font-size: 1.1rem; margin-top: 20px;">Mengerti</button>
        </div>
    </div>

    <script>
        const APP_CONFIG = <?= json_encode($config); ?>;
    </script>
    <script src="script.js?v=<?= time(); ?>"></script>
</body>
</html>