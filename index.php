<?php
session_start();
// Cek apakah admin sudah login
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
?>

<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jeopardy Interaktif</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <nav class="glass-nav">
        <button onclick="navigate('home')">Beranda</button>
        <button onclick="navigate('game')">Papan Game</button>
        
        <!-- Logika PHP: Jika Admin, tampilkan Editor & Logout. Jika bukan, tampilkan Login -->
        <?php if($is_admin): ?>
            <button onclick="navigate('editor')">Editor Soal</button>
            <button onclick="navigate('settings')">Pengaturan</button>
            <button onclick="window.location.href='logout.php'" class="btn-danger">Logout</button>
        <?php else: ?>
            <button onclick="navigate('login')">Login Admin</button>
        <?php endif; ?>
    </nav>

    <!-- Halaman Beranda -->
    <section id="home" class="screen active">
      <div class="hero glass-panel">
        <h1>Jeopardy Kuis Interaktif</h1>
        <p>Platform belajar interaktif dan menyenangkan untuk siswa</p>
        <div class="hero-btns">
          <button class="btn-primary" onclick="navigate('game')">
            Mulai Permainan
          </button>
        </div>
      </div>
    </section>

    <!-- Halaman Game -->
    <section id="game" class="screen">
      <div id="game-board" class="board"></div>
      <div id="scoreboard" class="scoreboard"></div>
    </section>

    <!-- Halaman Login -->
    <section id="login" class="screen">
        <div class="glass-panel" style="max-width: 400px; margin: 50px auto; text-align: center;">
            <h2 style="margin-bottom: 10px; color: var(--accent);">Login Admin</h2>
            <p style="margin-bottom: 20px;">Silakan masuk untuk mengelola soal dan pengaturan.</p>
            
            <form action="proses_login.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="text" name="username" placeholder="Username" required style="padding: 12px; border-radius: 8px; border: none; background: #1e293b; color: white;">
                <input type="password" name="password" placeholder="Password" required style="padding: 12px; border-radius: 8px; border: none; background: #1e293b; color: white;">
                <button type="submit" class="btn-primary">Masuk</button>
            </form>
        </div>
    </section>  

    <?php if($is_admin): ?>
    <!-- Halaman Editor -->
    <section id="editor" class="screen">
      <div class="glass-panel editor-container">
        <h2>Editor Konten Game</h2>
        <p>
          Ubah kategori dan pertanyaan di sini. Data akan tersimpan otomatis di
          browser.
        </p>
        <div id="editor-form"></div>
        <button class="btn-primary" onclick="saveEditorData()">
          Simpan Perubahan
        </button>
        <button class="btn-danger" onclick="resetData()">
          Reset ke Default
        </button>
      </div>
    </section>

    <!-- Halaman Pengaturan -->
    <section id="settings" class="screen">
      <div class="glass-panel settings-container">
        <h2>Pengaturan Permainan</h2>
        <div class="form-group">
          <label>Durasi Timer (detik):</label>
          <input type="number" id="setting-timer" value="30" />
        </div>
        <div class="form-group">
          <label>Jumlah Tim (2-4):</label>
          <input type="number" id="setting-teams" min="2" max="4" value="4" />
        </div>
        <button class="btn-primary" onclick="saveSettings()">
          Simpan Pengaturan
        </button>
        <button class="btn-danger" onclick="resetGameProgress()">
          Reset Skor & Papan Game
        </button>
      </div>
    </section>
    <?php endif; ?>

    <!-- Modal Pertanyaan -->
    <div id="question-modal" class="modal hidden">
      <div class="modal-content glass-panel">
        <div class="modal-header">
          <h2 id="modal-category">Kategori</h2>
          <h2 id="modal-points" class="points-badge">100</h2>
        </div>
        <div class="timer-bar"><div id="timer-progress"></div></div>
        <h1 id="modal-question">Pertanyaan</h1>
        <img id="modal-image" src="" alt="Visual" class="hidden" />

        <button id="btn-show-answer" class="btn-primary">Lihat Jawaban</button>
        <h2 id="modal-answer" class="hidden answer-text">Jawaban</h2>

        <div class="modal-controls">
          <button id="btn-correct" class="btn-green">Jawaban Benar</button>
          <button id="btn-wrong" class="btn-red">Jawaban Salah</button>
          <button id="btn-close" class="btn-secondary">Tutup Modal</button>
        </div>
      </div>
    </div>

    <!-- Elemen Audio tersembunyi, mengacu pada struktur folder[cite: 1] -->
    <audio id="audio-correct" src="assets/audio/benar.mp3"></audio>
    <audio id="audio-wrong" src="assets/audio/salah.mp3"></audio>

    <footer>
      <p>Dibuat oleh Guru SMPN 3 Gunungsindur | Game Interaktif 2026</p>
    </footer>

    <script src="script.js"></script>
  </body>
</html>
