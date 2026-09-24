// VARIABEL GLOBAL PENGGANTI DATABASE HARDCODE
let databaseSoal = [];

const avatars = [
  "https://i.pinimg.com/736x/84/c2/f7/84c2f7bfbe09d435133610de59600989.jpg",
  "https://i.pinimg.com/736x/77/8c/a0/778ca057ddde3db0098064beaa1d62c1.jpg",
  "https://i.pinimg.com/736x/21/df/b8/21dfb85b4f0b2f5b4f3b5f096238381c.jpg",
  "https://i.pinimg.com/736x/c9/a7/39/c9a739564f9b8417c8008894fb4ecb44.jpg",
  "https://i.pinimg.com/736x/7a/a6/f3/7aa6f380be5be4e35759ed6e5f848f07.jpg",
  "https://i.pinimg.com/736x/6c/e0/b3/6ce0b3beaf8db5f5cc1149e29a997d9f.jpg",
  "https://i.pinimg.com/736x/44/22/0c/44220c8f58c740702d8f9c0c822e0e49.jpg",
  "https://i.pinimg.com/736x/c2/3b/b1/c23bb1d9715a3a79d033efb3438914b1.jpg",
  "https://i.pinimg.com/736x/6a/d2/d5/6ad2d5e2e9c708170b1338d8f763eb5d.jpg",
  "https://i.pinimg.com/736x/d4/0b/df/d40bdf19de699c264e1d6c8b93557d34.jpg",
];

let selectedCategories = [];
let teams = [
  { id: 1, nama: "Kelompok 1", anggota: "", avatar: avatars[0], skor: 0 },
  { id: 2, nama: "Kelompok 2", anggota: "", avatar: avatars[1], skor: 0 },
];
let openedQuestions = [];
let activeCardElement = null;

let timerInterval;
let timeLeft = 0;
let defaultTimeLimit = 30; // Waktu detik bawaan
let isPaused = false;
let currentQuestionData = null;

// --- FETCH DATA DARI MYSQL SAAT WEB DIBUKA ---
async function fetchDatabaseSoal() {
  try {
    const response = await fetch("api_soal.php");
    const data = await response.json();
    databaseSoal = data; // Masukkan data MySQL ke variabel utama kita
    initLobby(); // Jika berhasil memuat, baru munculkan Lobby
  } catch (error) {
    console.error("Gagal menyambung ke Database MySQL:", error);
    document.getElementById("lobby-container").innerHTML =
      `<h1 style="color:red;">🚨 KONEKSI DATABASE GAGAL!</h1><p>Pastikan MySQL XAMPP menyala dan file api_soal.php ada.</p>`;
  }
}

// --- LOBBY ---
function initLobby() {
  const catContainer = document.getElementById("category-options");
  if (!catContainer) return;

  // Perbarui jumlah maksimal kategori acak sesuai jumlah kategori yang ada di MySQL
  document.getElementById("random-cat-count").max = databaseSoal.length;

  renderCategoryCheckboxes();
  renderTeamLobby();
  renderAvatarOptions();
}

function renderCategoryCheckboxes() {
  const catContainer = document.getElementById("category-options");
  catContainer.innerHTML = "";
  databaseSoal.forEach((cat, idx) => {
    const isChecked = selectedCategories.includes(idx) ? "checked" : "";
    catContainer.innerHTML += `<label class="cat-checkbox"><input type="checkbox" value="${idx}" ${isChecked} onchange="updateCatCount()"> ${cat.nama}</label>`;
  });
  updateCatCount();
}

function updateCatCount() {
  selectedCategories = Array.from(
    document.querySelectorAll(".cat-checkbox input:checked"),
  ).map((cb) => parseInt(cb.value));
  document.getElementById("cat-count").innerText = selectedCategories.length;
}

function randomizeCategories() {
  let count = parseInt(document.getElementById("random-cat-count").value);
  if (count > databaseSoal.length) count = databaseSoal.length;
  let indices = Array.from({ length: databaseSoal.length }, (_, i) => i);
  for (let i = indices.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [indices[i], indices[j]] = [indices[j], indices[i]];
  }
  selectedCategories = indices.slice(0, count);
  renderCategoryCheckboxes();
}

function renderTeamLobby() {
  const container = document.getElementById("team-inputs");
  container.innerHTML = "";
  teams.forEach((t) => {
    container.innerHTML += `<div class="team-edit-btn" onclick="openEditTeamModal(${t.id})"><img src="${t.avatar}"><div class="team-edit-info"><h4>${t.nama}</h4><p>${t.anggota || "Tanpa anggota"}</p></div></div>`;
  });
}

function addTeam() {
  teams.push({
    id: Date.now(),
    nama: `Kelompok ${teams.length + 1}`,
    anggota: "",
    avatar: avatars[Math.floor(Math.random() * avatars.length)],
    skor: 0,
  });
  renderTeamLobby();
}

let tempEditingTeamId = null;
function openEditTeamModal(id) {
  tempEditingTeamId = id;
  const team = teams.find((t) => t.id === id);
  document.getElementById("edit-team-name").value = team.nama;
  document.getElementById("edit-team-members").value = team.anggota;
  document.querySelectorAll(".avatar-option").forEach((img) => {
    img.classList.remove("selected");
    if (img.src === team.avatar) img.classList.add("selected");
  });
  document.getElementById("edit-team-modal").classList.remove("hidden");
}

function renderAvatarOptions() {
  const grid = document.getElementById("avatar-selection-grid");
  grid.innerHTML = "";
  avatars.forEach((url) => {
    grid.innerHTML += `<img src="${url}" class="avatar-option" onclick="selectAvatar(this, '${url}')">`;
  });
}

let tempSelectedAvatar = "";
function selectAvatar(el, url) {
  document
    .querySelectorAll(".avatar-option")
    .forEach((img) => img.classList.remove("selected"));
  el.classList.add("selected");
  tempSelectedAvatar = url;
}

function saveTeamEdit() {
  const team = teams.find((t) => t.id === tempEditingTeamId);
  team.nama = document.getElementById("edit-team-name").value;
  team.anggota = document.getElementById("edit-team-members").value;
  if (tempSelectedAvatar !== "") team.avatar = tempSelectedAvatar;
  document.getElementById("edit-team-modal").classList.add("hidden");
  renderTeamLobby();
}

function deleteTeam() {
  if (teams.length <= 2) {
    alert("Minimal 2 tim yang bertanding.");
    return;
  }
  teams = teams.filter((t) => t.id !== tempEditingTeamId);
  document.getElementById("edit-team-modal").classList.add("hidden");
  renderTeamLobby();
}

function startGame() {
  if (selectedCategories.length === 0) {
    alert("Pilih minimal 1 kategori!");
    return;
  }
  document.getElementById("lobby").classList.remove("active");
  document.getElementById("game").classList.add("active");
  renderBoard();
  renderScoreboard();
}

// --- PAPAN PERMAINAN ---
function renderBoard() {
  const board = document.getElementById("game-board");
  board.style.gridTemplateColumns = `repeat(${selectedCategories.length}, 1fr)`;
  board.innerHTML = "";

  selectedCategories.forEach((idx) => {
    board.innerHTML += `<div class="category-header">${databaseSoal[idx].nama}</div>`;
  });
  for (let i = 0; i < 5; i++) {
    selectedCategories.forEach((catIdx) => {
      const cat = databaseSoal[catIdx];
      const q = cat.soal[i];
      const cardId = `${catIdx}-${i}`;
      const card = document.createElement("div");
      card.className = `card ${openedQuestions.includes(cardId) ? "disabled" : ""}`;
      card.id = `card-${cardId}`;
      card.innerHTML = q.points;
      card.onclick = () => openQuestion(cat.nama, q, cardId, card);
      board.appendChild(card);
    });
  }
}

// --- MODAL SOAL & WAKTU ---
const modal = document.getElementById("question-modal");
const timerBar = document.getElementById("timer-progress");
const playerAudio = document.getElementById("modal-audio-player");

function openQuestion(category, qData, cardId, cardEl) {
  if (openedQuestions.includes(cardId)) return;
  currentQuestionData = { qData, cardId };
  activeCardElement = cardEl;

  document.getElementById("modal-category").innerText = category;
  document.getElementById("modal-points").innerText = qData.points;
  document.getElementById("modal-question").innerText = qData.q;
  document.getElementById("modal-answer-big").innerText = qData.a;

  document.querySelector(".q-modal-layout").classList.remove("bg-success");
  document.getElementById("answer-section").classList.add("hidden");

  document.getElementById("btn-pause").classList.remove("hidden");
  document.getElementById("btn-resume").classList.add("hidden");

  // Mengatur Media (Gambar & Audio) dari MySQL
  const imgEl = document.getElementById("modal-image");
  if (qData.img && qData.img !== "") {
    imgEl.src = qData.img;
    imgEl.classList.remove("hidden");
  } else {
    imgEl.classList.add("hidden");
  }

  const audioContainer = document.getElementById("audio-container");
  if (qData.audio && qData.audio !== "") {
    playerAudio.src = qData.audio;
    audioContainer.classList.remove("hidden");
  } else {
    playerAudio.src = "";
    audioContainer.classList.add("hidden");
  }

  modal.classList.remove("hidden");

  // Mengambil batas waktu dari database, jika 0 maka paka default 30s
  let waktuSoal = parseInt(qData.time);
  if (isNaN(waktuSoal) || waktuSoal <= 0) waktuSoal = 30;
  startTimer(waktuSoal);
}

function startTimer(seconds) {
  clearInterval(timerInterval);
  isPaused = false;
  timeLeft = seconds;
  defaultTimeLimit = seconds; // Simpan durasi aslinya untuk perhitungan persentase
  timerBar.style.width = "100%";
  timerBar.style.backgroundColor = "var(--primary)";

  timerInterval = setInterval(() => {
    if (!isPaused) {
      timeLeft -= 0.1;
      timerBar.style.width = `${(timeLeft / defaultTimeLimit) * 100}%`;
      if ((timeLeft / defaultTimeLimit) * 100 <= 30)
        timerBar.style.backgroundColor = "var(--wrong)";
      if (timeLeft <= 0) {
        clearInterval(timerInterval);
        document.getElementById("audio-wrong").play();
        pauseTimer();
      }
    }
  }, 100);
}

function pauseTimer() {
  isPaused = true;
  timerBar.style.backgroundColor = "#fbbf24";
  document.getElementById("btn-pause").classList.add("hidden");
  document.getElementById("btn-resume").classList.remove("hidden");
  if (!playerAudio.paused) playerAudio.pause(); // Hentikan lagu jika ada
}

function resumeTimer() {
  isPaused = false;
  timerBar.style.backgroundColor =
    (timeLeft / defaultTimeLimit) * 100 > 30
      ? "var(--primary)"
      : "var(--wrong)";
  document.getElementById("btn-resume").classList.add("hidden");
  document.getElementById("btn-pause").classList.remove("hidden");
}

function revealAnswer() {
  document.getElementById("answer-section").classList.remove("hidden");
}

// --- VERIFIKASI JAWABAN ---
let currentAction = "";
function showTeamSelector(action) {
  if (!isPaused) pauseTimer();

  currentAction = action;
  document.getElementById("selector-title").innerText =
    action === "benar" ? "Tim Mana yang Benar?" : "Tim Mana yang Salah?";

  const btnContainer = document.getElementById("team-selector-buttons");
  btnContainer.innerHTML = "";
  teams.forEach((t, idx) => {
    btnContainer.innerHTML += `<div class="team-btn" onclick="executeTeamAction(${idx})"><img src="${t.avatar}"><span style="font-weight:bold; font-size:1.1rem; text-align:center;">${t.nama}</span></div>`;
  });
  document.getElementById("team-selector-modal").classList.remove("hidden");
}

function closeTeamSelector() {
  document.getElementById("team-selector-modal").classList.add("hidden");
}

function executeTeamAction(teamIndex) {
  closeTeamSelector();
  const poin = currentQuestionData.qData.points;

  if (currentAction === "salah") {
    teams[teamIndex].skor -= poin;
    document.getElementById("audio-wrong").play();
    renderScoreboard();
    resumeTimer();
  } else if (currentAction === "benar") {
    teams[teamIndex].skor += poin;
    document.getElementById("audio-correct").play();
    renderScoreboard();
    clearInterval(timerInterval);

    document.querySelector(".q-modal-layout").classList.add("bg-success");
    revealAnswer();

    setTimeout(() => {
      closeQuestion(true, teamIndex);
    }, 2500);
  }
}

function closeQuestion(hasWinner, winningTeamIndex = null) {
  modal.classList.add("hidden");
  clearInterval(timerInterval);
  playerAudio.pause();
  playerAudio.src = ""; // Bersihkan audio player

  if (!openedQuestions.includes(currentQuestionData.cardId)) {
    openedQuestions.push(currentQuestionData.cardId);
    activeCardElement.classList.add("disabled");
    if (hasWinner && winningTeamIndex !== null) {
      const badge = document.createElement("img");
      badge.src = teams[winningTeamIndex].avatar;
      badge.className = "team-badge";
      activeCardElement.appendChild(badge);
    }
  }
  if (openedQuestions.length >= selectedCategories.length * 5)
    setTimeout(showPodium, 1500);
}

// --- SKOR & PODIUM ---
function renderScoreboard() {
  const container = document.getElementById("scoreboard");
  container.innerHTML = "";
  teams.forEach((t, idx) => {
    container.innerHTML += `
            <div class="score-card">
                <img src="${t.avatar}">
                <h3>${t.nama}</h3>
                <span>${t.skor}</span>
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
  document.getElementById("score-modal").classList.toggle("hidden");
  renderScoreboard();
}

function showPodium() {
  document.getElementById("game").classList.remove("active");
  document.getElementById("podium").classList.add("active");

  let ranked = [...teams].sort((a, b) => b.skor - a.skor);
  const stand = document.getElementById("podium-stand");
  const others = document.getElementById("other-ranks");
  const othersContainer = document.getElementById("other-ranks-container");

  stand.innerHTML = "";
  others.innerHTML = "";

  if (ranked[1]) stand.innerHTML += createRankElement(ranked[1], 2);
  if (ranked[0]) stand.innerHTML += createRankElement(ranked[0], 1, true);
  if (ranked[2]) stand.innerHTML += createRankElement(ranked[2], 3);

  if (ranked.length > 3) {
    othersContainer.classList.remove("hidden");
    for (let i = 3; i < ranked.length; i++) {
      others.innerHTML += `
                <div class="other-rank-card">
                    <h2>#${i + 1}</h2>
                    <img src="${ranked[i].avatar}">
                    <div class="other-text-info"><h3 style="color:white; font-size:1.2rem;">${ranked[i].nama}</h3><span style="color:var(--accent); font-weight:bold;">${ranked[i].skor} Poin</span></div>
                </div>
            `;
    }
  }
}

function createRankElement(teamData, rank, isFirst = false) {
  let crownHTML = isFirst ? `<div class="crown-emoji">👑</div>` : "";
  let piala = rank === 1 ? "🥇" : rank === 2 ? "🥈" : "🥉";
  return `
        <div class="podium-rank rank-${rank}">
            ${crownHTML}
            <img src="${teamData.avatar}">
            <h3>${teamData.nama}</h3>
            <h2>${teamData.skor} Pts</h2>
            <div class="stand"><span style="font-size:2rem;">${piala}</span> <br> #${rank}</div>
        </div>
    `;
}

// KITA PANGGIL FETCH DULU SEBELUM INISIALISASI LOBBY
window.onload = fetchDatabaseSoal;
