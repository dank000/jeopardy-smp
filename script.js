let databaseSoal = [];
const avatars = [
  "https://robohash.org/tim1.png?set=set4",
  "https://robohash.org/tim2.png?set=set4",
  "https://robohash.org/tim3.png?set=set4",
  "https://robohash.org/tim4.png?set=set4",
  "https://robohash.org/tim5.png?set=set4",
  "https://robohash.org/tim6.png?set=set4",
  "https://robohash.org/tim7.png?set=set4",
  "https://robohash.org/tim8.png?set=set4",
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
let defaultTimeLimit = 30;
let isPaused = false;
let currentQuestionData = null;
let isGameActive = false;
let currentWinningTeamIndex = null;

async function fetchDatabaseSoal() {
  try {
    const response = await fetch("api_soal.php");
    if (!response.ok) throw new Error(`HTTP status: ${response.status}`);
    const textData = await response.text();
    try {
      databaseSoal = JSON.parse(textData);
      initLobby();
    } catch (e) {
      tampilkanErrorLayarUtama(`Format data JSON dari server rusak.`);
    }
  } catch (error) {
    tampilkanErrorLayarUtama(
      `Gagal menghubungi server database. Pastikan MySQL menyala.`,
    );
  }
}

function tampilkanErrorLayarUtama(pesan) {
  const homeScreen = document.querySelector(".home-layout");
  if (homeScreen) {
    homeScreen.innerHTML = `<div style="background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444; padding: 40px; border-radius: 15px; text-align: center; max-width: 600px; z-index: 100;"><h1 style="color:#ef4444; margin-bottom: 20px; font-size: 2rem;">🚨 KONEKSI GAGAL!</h1><p style="color: white; font-size: 1.1rem;">${pesan}</p></div>`;
  }
}

function showCustomToast(message) {
  const toast = document.getElementById("custom-toast");
  document.getElementById("toast-msg").innerText = message;
  toast.classList.add("show");
  setTimeout(() => {
    toast.classList.remove("show");
  }, 3000);
}

function showAlertModal(message) {
  document.getElementById("custom-alert-msg").innerText = message;
  document.getElementById("custom-alert-modal").classList.remove("hidden");
}

function masukLobi() {
  document.getElementById("home-screen").classList.remove("active");
  document.getElementById("home-screen").classList.add("hidden");
  document.getElementById("lobby").classList.remove("hidden");
  document.getElementById("lobby").classList.add("active");

  const btnLogin = document.getElementById("btn-login-nav");
  if (btnLogin) btnLogin.classList.add("hidden");
}

function confirmGoHome() {
  if (isGameActive) {
    document.getElementById("quit-modal").classList.remove("hidden");
  } else {
    location.reload();
  }
}
function closeQuitModal() {
  document.getElementById("quit-modal").classList.add("hidden");
}
function forceQuitGame() {
  location.reload();
}

function initLobby() {
  const catContainer = document.getElementById("category-options");
  if (!catContainer) return;
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
  if (count > databaseSoal.length) {
    showAlertModal(
      `Hanya ada ${databaseSoal.length} kategori yang tersedia di sistem!`,
    );
    count = databaseSoal.length;
    document.getElementById("random-cat-count").value = count;
  }
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
    container.innerHTML += `<div class="team-edit-btn" onclick="openEditTeamModal(${t.id})"><img src="${t.avatar}" style="background:#fff;"><div class="team-edit-info"><h4>${t.nama}</h4><p>${t.anggota || "Tanpa anggota"}</p></div></div>`;
  });
}

function addTeam() {
  teams.push({
    id: Date.now(),
    nama: `Kelompok ${teams.length + 1}`,
    anggota: "",
    avatar: avatars[teams.length % avatars.length],
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
    grid.innerHTML += `<img src="${url}" class="avatar-option" onclick="selectAvatar(this, '${url}')" style="background:#fff;">`;
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
    showAlertModal("Minimal harus ada 2 tim yang bertanding.");
    return;
  }
  teams = teams.filter((t) => t.id !== tempEditingTeamId);
  document.getElementById("edit-team-modal").classList.add("hidden");
  renderTeamLobby();
}

function startGame() {
  if (selectedCategories.length === 0) {
    showAlertModal("Silakan centang minimal 1 kategori pelajaran!");
    return;
  }
  document.getElementById("lobby").classList.remove("active");
  document.getElementById("lobby").classList.add("hidden");
  document.getElementById("game").classList.remove("hidden");
  document.getElementById("game").classList.add("active");
  isGameActive = true;
  renderBoard();
  renderScoreboard();
}

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

const modal = document.getElementById("question-modal");
const timerBar = document.getElementById("timer-progress");
const playerAudio = document.getElementById("modal-audio-player");

// --- FUNGSI DIPERBARUI: Mendeteksi apakah soal sudah pernah dibuka ---
function openQuestion(category, qData, cardId, cardEl) {
  let isAlreadyOpened = openedQuestions.includes(cardId);

  currentQuestionData = { qData, cardId };
  activeCardElement = cardEl;
  currentWinningTeamIndex = null;

  document.getElementById("modal-category").innerText = category;
  document.getElementById("modal-points").innerText = qData.points;
  document.getElementById("modal-question").innerText = qData.q;
  document.getElementById("modal-answer-big").innerText = qData.a;

  document.querySelector(".q-modal-layout").classList.remove("bg-success");
  document.getElementById("answer-section").classList.add("hidden");

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

  // Logika Jika Soal Sudah Terjawab Sebelumnya
  if (isAlreadyOpened) {
    clearInterval(timerInterval);
    document.getElementById("timer-progress").style.width = "100%";
    document.getElementById("timer-progress").style.backgroundColor = "#64748b"; // Abu-abu menandakan nonaktif
    document.getElementById("btn-pause").classList.add("hidden");
    document.getElementById("btn-resume").classList.add("hidden");
  } else {
    document.getElementById("btn-pause").classList.remove("hidden");
    document.getElementById("btn-resume").classList.add("hidden");
    let waktuSoal = parseInt(qData.time);
    if (isNaN(waktuSoal) || waktuSoal <= 0) waktuSoal = 30;
    startTimer(waktuSoal);
  }
}

function startTimer(seconds) {
  clearInterval(timerInterval);
  isPaused = false;
  timeLeft = seconds;
  defaultTimeLimit = seconds;
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
        const aw = document.getElementById("audio-wrong");
        if (aw) aw.play();
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
  if (!playerAudio.paused) playerAudio.pause();
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

// --- FUNGSI DIPERBARUI: Otomatis mematikan waktu saat Tampilkan Jawaban ditekan ---
function revealAnswer() {
  document.getElementById("answer-section").classList.remove("hidden");
  clearInterval(timerInterval);
  isPaused = true;
  if (!playerAudio.paused) playerAudio.pause();

  // Menghilangkan tombol Jeda/Lanjut karena sesi waktu sudah habis
  document.getElementById("btn-pause").classList.add("hidden");
  document.getElementById("btn-resume").classList.add("hidden");

  // Mengubah bilah waktu menjadi hijau pertanda sukses/selesai
  document.getElementById("timer-progress").style.backgroundColor =
    "var(--success)";
}

let currentAction = "";
function showTeamSelector(action) {
  if (!isPaused) pauseTimer();
  currentAction = action;
  document.getElementById("selector-title").innerText =
    action === "benar" ? "Tim Mana yang Benar?" : "Tim Mana yang Salah?";
  const btnContainer = document.getElementById("team-selector-buttons");
  btnContainer.innerHTML = "";
  teams.forEach((t, idx) => {
    btnContainer.innerHTML += `<div class="team-btn" onclick="executeTeamAction(${idx})" style="display:flex; flex-direction:column; align-items:center; background:#1e293b; padding:20px; border-radius:15px; cursor:pointer; border:2px solid #475569; transition:0.2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='#475569'"><img src="${t.avatar}" style="width:70px; height:70px; border-radius:50%; margin-bottom:15px; background:#fff;"><span style="font-weight:900; font-size:1.2rem; color:white; text-align:center;">${t.nama}</span></div>`;
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
    const aw = document.getElementById("audio-wrong");
    if (aw) aw.play();
    renderScoreboard();
    resumeTimer();
  } else if (currentAction === "benar") {
    teams[teamIndex].skor += poin;
    const ac = document.getElementById("audio-correct");
    if (ac) ac.play();
    renderScoreboard();
    clearInterval(timerInterval);
    document.querySelector(".q-modal-layout").classList.add("bg-success");
    revealAnswer();
    currentWinningTeamIndex = teamIndex;
  }
}

function manualCloseQuestion() {
  if (currentWinningTeamIndex !== null) {
    closeQuestion(true, currentWinningTeamIndex);
  } else {
    closeQuestion(false);
  }
}

// --- FUNGSI DIPERBARUI: Membuat kartu yang sudah mati tetap bisa diklik ---
function closeQuestion(hasWinner, winningTeamIndex = null) {
  modal.classList.add("hidden");
  clearInterval(timerInterval);
  playerAudio.pause();
  playerAudio.src = "";

  if (!openedQuestions.includes(currentQuestionData.cardId)) {
    openedQuestions.push(currentQuestionData.cardId);
    activeCardElement.classList.add("disabled");

    // Memaksa kartu yang transparan untuk tetap bisa merespons klik tetikus
    activeCardElement.style.pointerEvents = "auto";
    activeCardElement.style.cursor = "pointer";
  }

  if (hasWinner && winningTeamIndex !== null) {
    const badge = document.createElement("img");
    badge.src = teams[winningTeamIndex].avatar;
    badge.style.width = "50px";
    badge.style.height = "50px";
    badge.style.borderRadius = "50%";
    badge.style.marginTop = "15px";
    badge.style.background = "#fff";
    badge.style.border = "3px solid #10b981";
    activeCardElement.innerHTML = "";
    activeCardElement.appendChild(badge);
  }
  if (openedQuestions.length >= selectedCategories.length * 5)
    setTimeout(showPodium, 1500);
}

function toggleScoreboard() {
  const modal = document.getElementById("scoreboard-modal");
  if (modal.classList.contains("hidden")) {
    modal.classList.remove("hidden");
  } else {
    modal.classList.add("hidden");
  }
}

function renderScoreboard() {
  const container = document.getElementById("scoreboard-grid");
  if (!container) return;

  container.innerHTML = "";
  teams.forEach((t, idx) => {
    container.innerHTML += `
        <div style="background:#1e293b; padding:20px; border-radius:15px; border:2px solid #334155; display:flex; align-items:center; gap:15px; box-shadow:0 10px 25px rgba(0,0,0,0.3);">
            <img src="${t.avatar}" style="width:70px; height:70px; border-radius:50%; background:#fff; border:3px solid #475569;">
            <div style="flex:1; text-align:left;">
                <h3 style="margin-bottom:5px; color:white; font-size:1.2rem;">${t.nama}</h3>
                <span style="font-size:2.2rem; font-weight:900; color:var(--accent);">${t.skor}</span>
            </div>
            <div style="display:flex; flex-direction:column; gap:10px;">
                <button style="background:var(--success); border:none; padding:10px 15px; border-radius:8px; color:white; font-weight:900; cursor:pointer;" onclick="adjustScore(${idx}, 100)">+100</button>
                <button style="background:var(--wrong); border:none; padding:10px 15px; border-radius:8px; color:white; font-weight:900; cursor:pointer;" onclick="adjustScore(${idx}, -100)">-100</button>
            </div>
        </div>`;
  });
}
function adjustScore(teamIdx, amount) {
  teams[teamIdx].skor += amount;
  renderScoreboard();
}

function showPodium() {
  isGameActive = false;
  document.getElementById("game").classList.remove("active");
  document.getElementById("game").classList.add("hidden");
  document.getElementById("podium").classList.remove("hidden");
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
      others.innerHTML += `<div style="background:#1e293b; padding:15px 30px; border-radius:15px; display:flex; align-items:center; gap:20px; border:2px solid #334155;"><h2 style="color:#94a3b8; margin:0;">#${i + 1}</h2><img src="${ranked[i].avatar}" style="width:60px; height:60px; border-radius:50%; background:#fff;"><div style="text-align:left;"><h3 style="color:white; margin:0 0 5px 0; font-size:1.3rem;">${ranked[i].nama}</h3><span style="color:var(--accent); font-weight:900; font-size:1.2rem;">${ranked[i].skor} Pts</span></div></div>`;
    }
  }

  var duration = 15 * 1000;
  var animationEnd = Date.now() + duration;
  var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };
  function randomInRange(min, max) {
    return Math.random() * (max - min) + min;
  }
  var interval = setInterval(function () {
    var timeLeft = animationEnd - Date.now();
    if (timeLeft <= 0) {
      return clearInterval(interval);
    }
    var particleCount = 50 * (timeLeft / duration);
    confetti(
      Object.assign({}, defaults, {
        particleCount,
        origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
      }),
    );
    confetti(
      Object.assign({}, defaults, {
        particleCount,
        origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
      }),
    );
  }, 250);
}

function createRankElement(teamData, rank, isFirst = false) {
  let crownHTML = isFirst
    ? `<div style="font-size:4rem; margin-bottom:-15px; z-index:10; position:relative; animation: floatApp 3s infinite;">👑</div>`
    : "";
  let piala = rank === 1 ? "🥇" : rank === 2 ? "🥈" : "🥉";
  let height = rank === 1 ? "220px" : rank === 2 ? "170px" : "140px";
  let color = rank === 1 ? "var(--accent)" : rank === 2 ? "#cbd5e1" : "#b45309";
  return `<div style="display:flex; flex-direction:column; align-items:center; width:180px; z-index:10;">${crownHTML}<img src="${teamData.avatar}" style="width:100px; height:100px; border-radius:50%; border:5px solid ${color}; margin-bottom:15px; z-index:5; background:#fff; box-shadow: 0 10px 20px rgba(0,0,0,0.5);"><h3 style="color:white; margin-bottom:10px; font-size:1.3rem; text-align:center;">${teamData.nama}</h3><h2 style="color:${color}; margin-bottom:20px; font-size:2rem; text-shadow: 0 0 10px rgba(0,0,0,0.5);">${teamData.skor}</h2><div style="background:linear-gradient(180deg, ${color}, #0f172a); width:100%; height:${height}; border-radius:15px 15px 0 0; display:flex; flex-direction:column; justify-content:center; align-items:center; box-shadow:inset 0 10px 30px rgba(0,0,0,0.3); border: 2px solid ${color}; border-bottom:none;"><span style="font-size:3.5rem; filter:drop-shadow(0 5px 5px rgba(0,0,0,0.5));">${piala}</span><span style="color:#0f172a; font-size:2rem; font-weight:900;">#${rank}</span></div></div>`;
}

window.onload = fetchDatabaseSoal;
