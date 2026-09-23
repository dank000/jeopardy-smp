// DATABASE SOAL MENTAH (9 Kategori)
const databaseSoal = [
  {
    id: "agm",
    nama: "Wawasan Agama",
    soal: [
      {
        points: 100,
        q: "Sebutkan tempat ibadah umat Hindu!",
        a: "Pura",
        img: "",
      },
      {
        points: 200,
        q: "Apa nama kitab suci agama Buddha?",
        a: "Tripitaka",
        img: "",
      },
      {
        points: 300,
        q: "Hari raya Nyepi merupakan hari besar umat beragama?",
        a: "Hindu",
        img: "",
      },
      {
        points: 400,
        q: "Sebutkan tokoh agama/pemuka agama Khonghucu!",
        a: "Xue Shi (Pendeta)",
        img: "",
      },
      {
        points: 500,
        q: "Tradisi membakar uang kertas biasanya dilakukan umat?",
        a: "Khonghucu",
        img: "",
      },
    ],
  },
  {
    id: "snd",
    nama: "Bahasa Sunda",
    soal: [
      {
        points: 100,
        q: "Karangan rekaan anu wangunna pondok disebut?",
        a: "Carpon",
        img: "",
      },
      {
        points: 200,
        q: "Saha anu ngatur jalanna hiji acara resmi?",
        a: "Panata Acara (MC)",
        img: "",
      },
      {
        points: 300,
        q: "Puisi heubeul anu diwengku ku pupuh disebut?",
        a: "Guguritan",
        img: "",
      },
      {
        points: 400,
        q: "Guru wilangan jeung guru lagu pupuh Kinanti nyaeta?",
        a: "8-u, 8-i, 8-a, 8-i, 8-a, 8-i",
        img: "",
      },
      {
        points: 500,
        q: "Conto tradisi Kampung Adat di Jawa Barat!",
        a: "Seren Taun",
        img: "",
      },
    ],
  },
  {
    id: "mat",
    nama: "Matematika",
    soal: [
      { points: 100, q: "Hasil dari 5 x 8 + 10 adalah?", a: "50", img: "" },
      {
        points: 200,
        q: "Rumus luas segitiga adalah?",
        a: "1/2 x alas x tinggi",
        img: "",
      },
      { points: 300, q: "Berapa akar kuadrat dari 144?", a: "12", img: "" },
      {
        points: 400,
        q: "Jika x + 5 = 12, berapakah nilai x?",
        a: "7",
        img: "",
      },
      {
        points: 500,
        q: "Rumus keliling lingkaran adalah?",
        a: "2 x π x r (atau π x d)",
        img: "",
      },
    ],
  },
  {
    id: "ing",
    nama: "Bahasa Inggris",
    soal: [
      { points: 100, q: "Apa bahasa Inggrisnya 'Buku'?", a: "Book", img: "" },
      {
        points: 200,
        q: "Bentuk lampau (Past Tense) dari 'Go' adalah?",
        a: "Went",
        img: "",
      },
      {
        points: 300,
        q: "Antonim dari kata 'Beautiful' adalah?",
        a: "Ugly",
        img: "",
      },
      {
        points: 400,
        q: "Tense yang digunakan untuk kejadian yang sedang berlangsung?",
        a: "Present Continuous",
        img: "",
      },
      {
        points: 500,
        q: "Lengkapi: 'I have ... (melihat) that movie.'",
        a: "Seen",
        img: "",
      },
    ],
  },
  {
    id: "ipa",
    nama: "Ilmu Pengetahuan Alam",
    soal: [
      {
        points: 100,
        q: "Pusat tata surya kita adalah?",
        a: "Matahari",
        img: "",
      },
      {
        points: 200,
        q: "Proses tumbuhan membuat makanan sendiri disebut?",
        a: "Fotosintesis",
        img: "",
      },
      {
        points: 300,
        q: "Simbol unsur kimia untuk Air adalah?",
        a: "H2O",
        img: "",
      },
      {
        points: 400,
        q: "Alat pernapasan pada ikan adalah?",
        a: "Insang",
        img: "",
      },
      {
        points: 500,
        q: "Hukum Newton yang menjelaskan Aksi-Reaksi adalah?",
        a: "Hukum Newton III",
        img: "",
      },
    ],
  },
  {
    id: "ips",
    nama: "Ilmu Pengetahuan Sosial",
    soal: [
      {
        points: 100,
        q: "Ibukota negara Indonesia adalah?",
        a: "Jakarta",
        img: "",
      },
      {
        points: 200,
        q: "Benua terluas di dunia adalah?",
        a: "Benua Asia",
        img: "",
      },
      {
        points: 300,
        q: "Organisasi PBB yang mengurus pendidikan dan budaya?",
        a: "UNESCO",
        img: "",
      },
      {
        points: 400,
        q: "Samudera yang mengelilingi kepulauan Indonesia?",
        a: "Hindia dan Pasifik",
        img: "",
      },
      {
        points: 500,
        q: "Sistem tanam paksa pada zaman Belanda disebut?",
        a: "Cultuurstelsel",
        img: "",
      },
    ],
  },
  {
    id: "gam",
    nama: "Dunia Game",
    soal: [
      {
        points: 100,
        q: "Bahan membuat Crafting Table di Minecraft?",
        a: "Wood Planks",
        img: "",
      },
      {
        points: 200,
        q: "Game MOBA populer 5v5 di HP?",
        a: "Mobile Legends",
        img: "",
      },
      {
        points: 300,
        q: "Layanan hosting gratis server Minecraft?",
        a: "Aternos",
        img: "",
      },
      {
        points: 400,
        q: "Karakter utama dalam game Mario Bros?",
        a: "Mario",
        img: "",
      },
      {
        points: 500,
        q: "Mob boss di dimensi The End (Minecraft)?",
        a: "Ender Dragon",
        img: "",
      },
    ],
  },
  {
    id: "jaj",
    nama: "Jajanan Viral",
    soal: [
      { points: 100, q: "Tepung kanji digulung telur?", a: "Cilung", img: "" },
      {
        points: 200,
        q: "Es krim manusia salju dari Tiongkok?",
        a: "Mixue",
        img: "",
      },
      {
        points: 300,
        q: "Bumbu utama seblak yang khas?",
        a: "Kencur (Cikur)",
        img: "",
      },
      { points: 400, q: "Singkatan dari Basreng?", a: "Bakso Goreng", img: "" },
      {
        points: 500,
        q: "Roti bakar khas Bandung dengan selai warna-warni?",
        a: "Roti Bakar",
        img: "",
      },
    ],
  },
  {
    id: "tbk",
    nama: "Tebak Gambar",
    soal: [
      { points: 100, q: "Gambar apakah ini?", a: "Isi gambar file", img: "" },
      { points: 200, q: "Logo apakah ini?", a: "Isi logo", img: "" },
      { points: 300, q: "Siluet apa ini?", a: "Nama benda", img: "" },
      { points: 400, q: "Siapa tokoh ini?", a: "Nama tokoh", img: "" },
      {
        points: 500,
        q: "Benda tradisional apa ini?",
        a: "Nama benda",
        img: "",
      },
    ],
  },
];

// Data Avatar Haikyuu (URL Statis)
const avatars = [
  "https://i.pinimg.com/736x/84/c2/f7/84c2f7bfbe09d435133610de59600989.jpg", // Hinata
  "https://i.pinimg.com/736x/77/8c/a0/778ca057ddde3db0098064beaa1d62c1.jpg", // Kageyama
  "https://i.pinimg.com/736x/21/df/b8/21dfb85b4f0b2f5b4f3b5f096238381c.jpg", // Kuroo
  "https://i.pinimg.com/736x/c9/a7/39/c9a739564f9b8417c8008894fb4ecb44.jpg", // Kenma
  "https://i.pinimg.com/736x/7a/a6/f3/7aa6f380be5be4e35759ed6e5f848f07.jpg", // Bokuto
  "https://i.pinimg.com/736x/6c/e0/b3/6ce0b3beaf8db5f5cc1149e29a997d9f.jpg", // Akaashi
  "https://i.pinimg.com/736x/44/22/0c/44220c8f58c740702d8f9c0c822e0e49.jpg", // Oikawa
  "https://i.pinimg.com/736x/c2/3b/b1/c23bb1d9715a3a79d033efb3438914b1.jpg", // Ushijima
  "https://i.pinimg.com/736x/6a/d2/d5/6ad2d5e2e9c708170b1338d8f763eb5d.jpg", // Tsukishima
  "https://i.pinimg.com/736x/d4/0b/df/d40bdf19de699c264e1d6c8b93557d34.jpg", // Nishinoya
];

// STATE PERMAINAN
let selectedCategories = [];
let teams = [];
let openedQuestions = []; // Format: "catIndex-pointIndex" (hanya yang sudah ditutup)
let activeCardElement = null; // Menyimpan kotak yang sedang diklik

let timerInterval;
let timeLeft = 0;
let isPaused = false;
let currentQuestionData = null; // Data soal yang sedang tayang

// --- FASE 1: LOBBY & PERSIAPAN ---
function initLobby() {
  const catContainer = document.getElementById("category-options");
  databaseSoal.forEach((cat, idx) => {
    catContainer.innerHTML += `
            <label class="cat-checkbox">
                <input type="checkbox" value="${idx}" onchange="checkCategorySelection()">
                ${cat.nama}
            </label>
        `;
  });
  renderTeamInputs();
}

function checkCategorySelection() {
  const checkboxes = document.querySelectorAll(".cat-checkbox input:checked");
  document.getElementById("cat-count").innerText = checkboxes.length;

  // Nonaktifkan centang lebih dari 5
  document
    .querySelectorAll(".cat-checkbox input:not(:checked)")
    .forEach((cb) => {
      cb.disabled = checkboxes.length >= 5;
    });

  const btnStart = document.getElementById("btn-start-game");
  if (checkboxes.length === 5) {
    btnStart.classList.remove("disabled");
  } else {
    btnStart.classList.add("disabled");
  }
}

function renderTeamInputs() {
  let count = parseInt(document.getElementById("team-count").value);
  if (count > 10) count = 10;
  if (count < 2) count = 2;
  document.getElementById("team-count").value = count;

  const container = document.getElementById("team-inputs");
  container.innerHTML = "";

  for (let i = 0; i < count; i++) {
    container.innerHTML += `
            <div class="team-input-row">
                <img src="${avatars[i]}" alt="Avatar">
                <input type="text" id="team-name-${i}" value="Kelompok ${i + 1}">
            </div>
        `;
  }
}

function startGame() {
  if (document.querySelectorAll(".cat-checkbox input:checked").length !== 5)
    return;

  // Simpan Kategori
  selectedCategories = [];
  document.querySelectorAll(".cat-checkbox input:checked").forEach((cb) => {
    selectedCategories.push(databaseSoal[cb.value]);
  });

  // Simpan Tim
  teams = [];
  const teamCount = parseInt(document.getElementById("team-count").value);
  for (let i = 0; i < teamCount; i++) {
    teams.push({
      id: i,
      nama: document.getElementById(`team-name-${i}`).value,
      avatar: avatars[i],
      skor: 0,
      menjawab: 0,
    });
  }

  document.getElementById("lobby").classList.remove("active");
  document.getElementById("game").classList.add("active");

  renderBoard();
  renderScoreboard();
}

// --- FASE 2: PAPAN PERMAINAN ---
function renderBoard() {
  const board = document.getElementById("game-board");
  board.innerHTML = "";

  // Header Kategori
  selectedCategories.forEach((cat) => {
    board.innerHTML += `<div class="category-header">${cat.nama}</div>`;
  });

  // Kotak Soal
  for (let i = 0; i < 5; i++) {
    selectedCategories.forEach((cat, cIdx) => {
      const q = cat.soal[i];
      const cardId = `${cIdx}-${i}`;
      const card = document.createElement("div");
      card.className = `card ${openedQuestions.includes(cardId) ? "disabled" : ""}`;
      card.id = `card-${cardId}`;
      card.innerHTML = q.points;

      // Jika sudah dijawab sebelumnya (untuk fitur badge tim)
      // Cek apakah data tim ada di array openedQuestions atau state terpisah,
      // untuk versi ini kita asumsikan bisa diklik ulang meskipun redup.

      card.onclick = () => openQuestion(cat.nama, q, cardId, card);
      board.appendChild(card);
    });
  }
}

// --- FASE 3: MODAL SOAL & WAKTU ---
const modal = document.getElementById("question-modal");
const timerBar = document.getElementById("timer-progress");
const ansSection = document.getElementById("answer-section");

function openQuestion(category, qData, cardId, cardEl) {
  currentQuestionData = { qData, cardId };
  activeCardElement = cardEl; // Simpan elemen kotak yang diklik

  document.getElementById("modal-category").innerText = category;
  document.getElementById("modal-points").innerText = qData.points;
  document.getElementById("modal-question").innerText = qData.q;
  document.getElementById("modal-answer").innerText = qData.a;

  // Reset Modal UI
  document.querySelector(".modal-content").classList.remove("bg-success");
  ansSection.classList.add("hidden");
  document.getElementById("controls-main").classList.remove("hidden");
  document.getElementById("controls-verify").classList.add("hidden");

  const imgEl = document.getElementById("modal-image");
  if (qData.img) {
    imgEl.src = qData.img;
    imgEl.classList.remove("hidden");
  } else {
    imgEl.classList.add("hidden");
  }

  modal.classList.remove("hidden");

  startTimer(30);
}

function startTimer(seconds) {
  clearInterval(timerInterval);
  isPaused = false;
  timeLeft = seconds;

  timerBar.style.width = "100%";
  timerBar.style.backgroundColor = "var(--primary)";

  timerInterval = setInterval(() => {
    if (!isPaused) {
      timeLeft -= 0.1; // Hitung presisi
      const percentage = (timeLeft / seconds) * 100;
      timerBar.style.width = `${percentage}%`;

      if (percentage <= 30) timerBar.style.backgroundColor = "var(--wrong)";

      if (timeLeft <= 0) {
        clearInterval(timerInterval);
        document.getElementById("audio-wrong").play();
      }
    }
  }, 100);
}

function pauseTimer() {
  isPaused = true;
  timerBar.style.backgroundColor = "#fbbf24"; // Warna kuning pause
  document.getElementById("controls-main").classList.add("hidden");
  document.getElementById("controls-verify").classList.remove("hidden");
}

function resumeTimer() {
  isPaused = false;
  timerBar.style.backgroundColor =
    (timeLeft / 30) * 100 > 30 ? "var(--primary)" : "var(--wrong)";
  document.getElementById("controls-verify").classList.add("hidden");
  document.getElementById("controls-main").classList.remove("hidden");
}

// --- FASE 4: PEMILIHAN TIM SAAT BENAR/SALAH ---
let currentAction = ""; // 'benar' atau 'salah'

function showTeamSelector(action) {
  currentAction = action;
  const selectorModal = document.getElementById("team-selector-modal");
  const btnContainer = document.getElementById("team-selector-buttons");

  document.getElementById("selector-title").innerText =
    action === "benar" ? "Pilih Tim yang Benar ✅" : "Pilih Tim yang Salah ❌";
  document.getElementById("selector-title").style.color =
    action === "benar" ? "var(--correct)" : "var(--wrong)";

  btnContainer.innerHTML = "";
  teams.forEach((t) => {
    btnContainer.innerHTML += `
            <div class="team-btn" onclick="executeTeamAction(${t.id})">
                <img src="${t.avatar}" alt="Avatar">
                <span style="font-weight:bold; font-size:0.9rem; text-align:center;">${t.nama}</span>
            </div>
        `;
  });

  selectorModal.classList.remove("hidden");
}

function closeTeamSelector() {
  document.getElementById("team-selector-modal").classList.add("hidden");
}

function executeTeamAction(teamId) {
  closeTeamSelector();
  const poin = currentQuestionData.qData.points;
  const teamIndex = teams.findIndex((t) => t.id === teamId);

  if (currentAction === "salah") {
    // Kurangi Skor, mainkan suara salah, dan JALANKAN WAKTU LAGI
    teams[teamIndex].skor -= poin;
    document.getElementById("audio-wrong").play();
    renderScoreboard();
    resumeTimer();
  } else if (currentAction === "benar") {
    // Tambah Skor, mainkan suara benar, MATIKAN WAKTU
    teams[teamIndex].skor += poin;
    teams[teamIndex].menjawab += 1;
    document.getElementById("audio-correct").play();
    renderScoreboard();

    clearInterval(timerInterval);

    // Ubah tampilan modal jadi hijau dan tunjukkan jawaban
    document.querySelector(".modal-content").classList.add("bg-success");
    ansSection.classList.remove("hidden");
    document.getElementById("controls-verify").classList.add("hidden");

    // Berikan tombol khusus untuk menutup soal sukses
    document.getElementById("controls-main").innerHTML = `
            <button class="btn-primary" style="width:100%; font-size:1.5rem;" onclick="closeQuestion(true, ${teamIndex})">Selesai & Kembali ke Papan</button>
        `;
    document.getElementById("controls-main").classList.remove("hidden");
  }
}

// Tutup soal (bisa karena benar, atau diskip tanpa pemenang)
function closeQuestion(hasWinner, winningTeamIndex = null) {
  modal.classList.add("hidden");
  clearInterval(timerInterval);

  // Kembalikan tombol kontrol utama seperti semula
  document.getElementById("controls-main").innerHTML = `
        <button id="btn-pause" class="btn-warning" onclick="pauseTimer()">⏸️ Pause (Anak Ingin Menjawab)</button>
        <button id="btn-close-early" class="btn-secondary" onclick="closeQuestion(false)">Tutup Tanpa Pemenang</button>
    `;

  // Tandai kotak di papan sudah dibuka
  if (!openedQuestions.includes(currentQuestionData.cardId)) {
    openedQuestions.push(currentQuestionData.cardId);
    activeCardElement.classList.add("disabled");

    // Beri lencana avatar tim yang menang
    if (hasWinner && winningTeamIndex !== null) {
      const badge = document.createElement("img");
      badge.src = teams[winningTeamIndex].avatar;
      badge.className = "team-badge";
      activeCardElement.appendChild(badge);
    }
  }

  checkGameEnd();
}

// --- FASE 5: SKOR & PODIUM ---
function renderScoreboard() {
  const container = document.getElementById("scoreboard");
  container.innerHTML = "";

  teams.forEach((t, idx) => {
    container.innerHTML += `
            <div class="score-card">
                <img src="${t.avatar}">
                <h3>${t.nama}</h3>
                <span id="display-score-${idx}">${t.skor}</span>
                <div class="score-adjust">
                    <button class="btn-green" onclick="adjustScore(${idx}, 100)">+100</button>
                    <button class="btn-red" onclick="adjustScore(${idx}, -100)">-100</button>
                </div>
            </div>
        `;
  });
}

function adjustScore(teamIdx, amount) {
  teams[teamIdx].skor += amount;
  renderScoreboard();
}

function toggleScoreboard() {
  const smodal = document.getElementById("score-modal");
  smodal.classList.toggle("hidden");
}

function checkGameEnd() {
  if (openedQuestions.length >= 25) {
    setTimeout(showPodium, 1000);
  }
}

function showPodium() {
  document.getElementById("game").classList.remove("active");
  document.getElementById("podium").classList.add("active");

  // Urutkan tim berdasarkan skor tertinggi
  let ranked = [...teams].sort((a, b) => b.skor - a.skor);

  const stand = document.getElementById("podium-stand");
  const others = document.getElementById("other-ranks");
  stand.innerHTML = "";
  others.innerHTML = "";

  // Juara 2 (Kiri)
  if (ranked[1]) stand.innerHTML += createRankElement(ranked[1], 2);
  // Juara 1 (Tengah)
  if (ranked[0]) stand.innerHTML += createRankElement(ranked[0], 1);
  // Juara 3 (Kanan)
  if (ranked[2]) stand.innerHTML += createRankElement(ranked[2], 3);

  // Peringkat 4 dst
  for (let i = 3; i < ranked.length; i++) {
    others.innerHTML += `
            <div class="other-rank-card">
                <h2>#${i + 1}</h2>
                <img src="${ranked[i].avatar}">
                <div>
                    <h3 style="margin:0;">${ranked[i].nama}</h3>
                    <span style="color:var(--accent); font-weight:bold;">Skor: ${ranked[i].skor}</span>
                </div>
            </div>
        `;
  }
}

function createRankElement(teamData, rank) {
  return `
        <div class="podium-rank rank-${rank}">
            <img src="${teamData.avatar}">
            <h3>${teamData.nama}</h3>
            <h2>${teamData.skor} Poin</h2>
            <div class="stand">#${rank}</div>
        </div>
    `;
}

// Jalankan fungsi awal saat halaman dimuat
window.onload = initLobby;
