<?php
// Membaca pengaturan judul dari config.json
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
    <!-- Asumsi Anda menggunakan CSS bawaan, kita tambahkan gaya khusus Homepage di sini -->
    <style>
        :root { --bg-dark: #0f172a; --bg-panel: #1e293b; --primary: #3b82f6; --text: #f8fafc; --accent: #fbbf24; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, sans-serif; }
        body { background-color: var(--bg-dark); color: var(--text); overflow-x: hidden; min-height: 100vh; display: flex; flex-direction: column; }
        
        /* Navbar Atas */
        .top-navbar { display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background: rgba(30, 41, 59, 0.8); backdrop-filter: blur(10px); border-bottom: 1px solid #334155; position: fixed; width: 100%; top: 0; z-index: 100; }
        .top-navbar h2 { font-size: 1.5rem; font-weight: 800; color: white; letter-spacing: 1px; }
        .top-navbar h2 span { color: var(--accent); }
        .btn-admin { background: transparent; border: 2px solid #475569; color: #cbd5e1; padding: 8px 20px; border-radius: 8px; font-weight: bold; text-decoration: none; transition: 0.3s; }
        .btn-admin:hover { border-color: var(--primary); color: var(--primary); }

        /* Container Layar (Home, Lobby, Game, Podium) */
        .screen-section { display: none; padding-top: 100px; min-height: 100vh; width: 100%; }
        .screen-section.active { display: block; }
        
        /* ================= HOMEPAGE KHUSUS ================= */
        .home-layout { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center; position: relative; padding: 0 20px; z-index: 10; }
        
        .hero-title { font-size: 4.5rem; font-weight: 900; color: white; margin-bottom: 20px; line-height: 1.1; text-transform: uppercase; letter-spacing: 2px; text-shadow: 0 10px 30px rgba(59, 130, 246, 0.5); }
        .hero-title span { color: var(--accent); }
        .hero-subtitle { font-size: 1.2rem; color: #94a3b8; max-width: 600px; margin-bottom: 40px; line-height: 1.6; }
        
        .btn-play-massive { background: linear-gradient(135deg, var(--primary), #2563eb); color: white; font-size: 1.8rem; font-weight: 900; padding: 20px 50px; border: none; border-radius: 100px; cursor: pointer; box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4), inset 0 -4px 0 rgba(0,0,0,0.2); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); letter-spacing: 2px; }
        .btn-play-massive:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 15px 40px rgba(59, 130, 246, 0.6), inset 0 -4px 0 rgba(0,0,0,0.2); }
        .btn-play-massive:active { transform: translateY(2px); box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4); }

        /* Stiker / Ornamen Melayang */
        .floating-element { position: absolute; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3)); animation: floatApp 6s ease-in-out infinite; z-index: -1; opacity: 0.9; }
        .f-1 { font-size: 5rem; top: 20%; left: 15%; animation-delay: 0s; transform: rotate(-15deg); }
        .f-2 { font-size: 4rem; top: 30%; right: 18%; animation-delay: 1.5s; transform: rotate(20deg); }
        .f-3 { font-size: 6rem; bottom: 20%; left: 20%; animation-delay: 3s; transform: rotate(10deg); filter: blur(2px); opacity: 0.6; }
        .f-4 { font-size: 4.5rem; bottom: 25%; right: 15%; animation-delay: 0.5s; transform: rotate(-25deg); }
        
        @keyframes floatApp {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
        }

        /* LOBBY CSS (Mempertahankan desain Anda yang sudah ada) */
        .lobby-container { max-width: 900px; margin: 0 auto; background: var(--bg-panel); padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); border: 1px solid #334155; }
        .lobby-container h2 { text-align: center; color: var(--accent); font-size: 2rem; margin-bottom: 30px; }
        /* ... (CSS Lobby, Board, Modal, dan Podium Anda sebelumnya tetap digunakan) ... */
    </style>
    <!-- Panggil style.css milik front-end jika ada -->
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

    <nav class="top-navbar">
        <h2>Kuis<span>Pintar</span></h2>
        <a href="admin.php" class="btn-admin">Admin Login</a>
    </nav>

    <!-- FASE 1: HOMEPAGE (LANDING) -->
    <section id="home-screen" class="screen-section active">
        <!-- Elemen Dekoratif -->
        <div class="floating-element f-1">💡</div>
        <div class="floating-element f-2">🏆</div>
        <div class="floating-element f-3">🚀</div>
        <div class="floating-element f-4">🎯</div>

        <div class="home-layout">
            <h1 class="hero-title"><?= htmlspecialchars($judul_game) ?></h1>
            <p class="hero-subtitle">Uji pengetahuan Anda, atur strategi kelompok, dan raih skor tertinggi dalam kompetisi edukasi interaktif ini.</p>
            
            <!-- Tombol yang memicu transisi ke Lobi -->
            <button class="btn-play-massive" onclick="masukLobi()">MULAI BERMAIN</button>
        </div>
    </section>

    <!-- FASE 2: LOBI PENGATURAN -->
    <section id="lobby" class="screen-section">
        <div class="lobby-container" id="lobby-container">
            <h2>Persiapan Permainan</h2>
            
            <!-- BAGIAN 1: KATEGORI (Kode Lobby Anda Sebelumnya) -->
            <div style="margin-bottom: 30px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="color:white;">1. Pilih Kategori (<span id="cat-count">0</span>)</h3>
                    <div>
                        <input type="number" id="random-cat-count" value="5" min="1" max="9" style="width:60px; padding:8px; border-radius:8px; background:#0f172a; color:white; border:1px solid #475569; text-align:center;">
                        <button onclick="randomizeCategories()" style="background:var(--accent); color:#0f172a; padding:8px 15px; border-radius:8px; border:none; font-weight:bold; cursor:pointer;">ACAK KATEGORI</button>
                    </div>
                </div>
                <!-- Tempat Checkbox Kategori dari JS -->
                <div id="category-options" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:15px;"></div>
            </div>

            <!-- BAGIAN 2: TIM (Kode Lobby Anda Sebelumnya) -->
            <div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <h3 style="color:white;">2. Pengaturan Kelompok</h3>
                    <button onclick="addTeam()" style="background:var(--primary); color:white; padding:8px 15px; border-radius:8px; border:none; font-weight:bold; cursor:pointer;">+ TAMBAH KELOMPOK</button>
                </div>
                <div id="team-inputs" style="display:grid; grid-template-columns:repeat(2, 1fr); gap:15px;"></div>
            </div>

            <button onclick="startGame()" class="btn-play-massive" style="width:100%; margin-top:40px; font-size:1.5rem; padding:15px;">MULAI GAME ➔</button>
        </div>
    </section>

    <!-- FASE 3: PAPAN GAME (Pertahankan div id="game" Anda di sini) -->
    <section id="game" class="screen-section hidden">
        <!-- Kode papan game board Anda -->
    </section>

    <!-- FASE 4: PODIUM (Pertahankan div id="podium" Anda di sini) -->
    <section id="podium" class="screen-section hidden">
        <!-- Kode podium Anda -->
    </section>

    <!-- MODAL SOAL DLL (Pertahankan modal Anda di sini) -->

    <script src="script.js"></script>
    <script>
        // Fungsi Transisi dari Homepage ke Lobi
        function masukLobi() {
            document.getElementById('home-screen').classList.remove('active');
            document.getElementById('lobby').classList.add('active');
        }
    </script>
</body>
</html>