// script.js
const defaultData = [
  {
    category: "Wawasan Agama",
    questions: [
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
        q: "Tradisi membakar uang kertas (Gim Coa) biasanya dilakukan oleh umat?",
        a: "Khonghucu / Tridharma",
        img: "",
      },
    ],
  },
  {
    category: "Bahasa Sunda",
    questions: [
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
        q: "Sebutkeun salah sahiji conto tradisi Kampung Adat di Jawa Barat!",
        a: "Seren Taun (Ciptagelar/Cigugur)",
        img: "",
      },
    ],
  },
  {
    category: "Tebak Gambar",
    questions: [
      {
        points: 100,
        q: "Gambar apakah ini?",
        a: "Isi gambar dari file",
        img: "",
      },
      { points: 200, q: "Logo apakah ini?", a: "Isi logo", img: "" },
      {
        points: 300,
        q: "Siluet bangunan apakah ini?",
        a: "Nama bangunan",
        img: "",
      },
      {
        points: 400,
        q: "Siapakah tokoh dalam siluet ini?",
        a: "Nama tokoh",
        img: "",
      },
      {
        points: 500,
        q: "Apa nama benda tradisional ini?",
        a: "Nama benda",
        img: "",
      },
    ],
  },
  {
    category: "Dunia Game",
    questions: [
      {
        points: 100,
        q: "Material utama untuk membuat Crafting Table di Minecraft?",
        a: "Wood Planks",
        img: "",
      },
      {
        points: 200,
        q: "Apa nama launcher tidak resmi yang populer untuk Minecraft?",
        a: "TLauncher",
        img: "",
      },
      {
        points: 300,
        q: "Situs layanan hosting server Minecraft gratis yang sering digunakan?",
        a: "Aternos",
        img: "",
      },
      {
        points: 400,
        q: "Berapa blok Obsidian yang dibutuhkan untuk membuat Nether Portal ukuran terkecil?",
        a: "10 Blok",
        img: "",
      },
      {
        points: 500,
        q: "Mob boss yang berada di dimensi The End?",
        a: "Ender Dragon",
        img: "",
      },
    ],
  },
  {
    category: "Jajanan Viral",
    questions: [
      {
        points: 100,
        q: "Jajanan dari tepung kanji yang digulung dengan telur disebut?",
        a: "Cilung",
        img: "",
      },
      {
        points: 200,
        q: "Minuman teh kekinian asal Tiongkok dengan logo manusia salju?",
        a: "Mixue",
        img: "",
      },
      {
        points: 300,
        q: "Seblak identik dengan bumbu rempah utamanya, yaitu?",
        a: "Kencur (Cikur)",
        img: "",
      },
      {
        points: 400,
        q: "Singkatan dari jajanan 'Basreng' adalah?",
        a: "Bakso Goreng",
        img: "",
      },
      {
        points: 500,
        q: "Jajanan manis mirip pancake berukuran kecil yang sempat viral?",
        a: "Pancake Mini / Pancong Lumer",
        img: "",
      },
    ],
  },
];

let gameData = JSON.parse(localStorage.getItem("jeopardyData")) || defaultData;
let gameState = JSON.parse(localStorage.getItem("jeopardyState")) || {
  scores: [0, 0, 0, 0],
  opened: [],
  config: { timer: 30, teams: 4 },
};

// --- ROUTING ---
function navigate(screenId) {
  document
    .querySelectorAll(".screen")
    .forEach((s) => s.classList.remove("active"));
  document.getElementById(screenId).classList.add("active");
  if (screenId === "game") renderGame();
  if (screenId === "editor") renderEditor();
  if (screenId === "settings") loadSettings();
}

// --- GAME LOGIC ---
const board = document.getElementById("game-board");
const scoreboard = document.getElementById("scoreboard");
let timerInterval;

function renderGame() {
  board.innerHTML = "";
  scoreboard.innerHTML = "";

  // Render Headers
  gameData.forEach((cat) => {
    const header = document.createElement("div");
    header.className = "category-header";
    header.innerText = cat.category;
    board.appendChild(header);
  });

  // Render Cards
  for (let i = 0; i < 5; i++) {
    gameData.forEach((cat, cIdx) => {
      const q = cat.questions[i];
      const cardId = `${cIdx}-${i}`;
      const card = document.createElement("div");
      card.className = `card ${gameState.opened.includes(cardId) ? "disabled" : ""}`;
      card.innerText = q.points;

      card.onclick = () => {
        if (!card.classList.contains("disabled"))
          openQuestion(cat.category, q, cardId, card);
      };
      board.appendChild(card);
    });
  }

  // Render Scoreboard
  for (let i = 0; i < gameState.config.teams; i++) {
    scoreboard.innerHTML += `
            <div class="team">
                <h3>Tim ${i + 1}</h3>
                <span id="score-${i}">${gameState.scores[i]}</span>
                <div class="score-btns">
                    <button class="btn-green" onclick="updateScore(${i}, 100)">+100</button>
                    <button class="btn-red" onclick="updateScore(${i}, -100)">-100</button>
                </div>
            </div>
        `;
  }
}

function updateScore(teamIdx, amount) {
  gameState.scores[teamIdx] += amount;
  document.getElementById(`score-${teamIdx}`).innerText =
    gameState.scores[teamIdx];
  saveState();
}

// --- MODAL & TIMER ---
const modal = document.getElementById("question-modal");
const btnShowAnswer = document.getElementById("btn-show-answer");
const ansText = document.getElementById("modal-answer");
const imgEl = document.getElementById("modal-image");
const timerBar = document.getElementById("timer-progress");
const audioCorrect = document.getElementById("audio-correct");
const audioWrong = document.getElementById("audio-wrong");

function openQuestion(category, qData, cardId, cardEl) {
  document.getElementById("modal-category").innerText = category;
  document.getElementById("modal-points").innerText = qData.points;
  document.getElementById("modal-question").innerText = qData.q;
  ansText.innerText = qData.a;

  if (qData.img) {
    imgEl.src = qData.img;
    imgEl.classList.remove("hidden");
  } else {
    imgEl.classList.add("hidden");
  }

  ansText.classList.add("hidden");
  btnShowAnswer.style.display = "inline-block";
  modal.classList.remove("hidden");

  // Disable card and save state
  gameState.opened.push(cardId);
  cardEl.classList.add("disabled");
  saveState();

  startTimer(gameState.config.timer);
}

function startTimer(seconds) {
  clearInterval(timerInterval);
  timerBar.style.width = "100%";
  timerBar.style.backgroundColor = "var(--accent)";

  let timeLeft = seconds;
  timerInterval = setInterval(() => {
    timeLeft -= 1;
    const percentage = (timeLeft / seconds) * 100;
    timerBar.style.width = `${percentage}%`;

    if (percentage <= 30) timerBar.style.backgroundColor = "var(--wrong)";

    if (timeLeft <= 0) {
      clearInterval(timerInterval);
      audioWrong.play();
    }
  }, 1000);
}

btnShowAnswer.onclick = () => {
  clearInterval(timerInterval);
  ansText.classList.remove("hidden");
  btnShowAnswer.style.display = "none";
};

document.getElementById("btn-close").onclick = () => {
  clearInterval(timerInterval);
  modal.classList.add("hidden");
};

// Audio controls
document.getElementById("btn-correct").onclick = () => audioCorrect.play();
document.getElementById("btn-wrong").onclick = () => audioWrong.play();

// --- EDITOR LOGIC ---
const editorForm = document.getElementById("editor-form");

function renderEditor() {
  editorForm.innerHTML = "";
  gameData.forEach((cat, cIdx) => {
    let html = `<div class="editor-category">
            <input type="text" id="cat-${cIdx}" value="${cat.category}" style="font-size: 1.2rem; font-weight: bold; width: 100%; margin-bottom: 10px;">`;

    cat.questions.forEach((q, qIdx) => {
      html += `<div class="editor-q">
                <input type="number" id="pts-${cIdx}-${qIdx}" value="${q.points}" style="width: 80px;">
                <input type="text" id="q-${cIdx}-${qIdx}" value="${q.q}" placeholder="Pertanyaan">
                <input type="text" id="a-${cIdx}-${qIdx}" value="${q.a}" placeholder="Jawaban">
                <input type="text" id="img-${cIdx}-${qIdx}" value="${q.img}" placeholder="URL Gambar / Path Lokal (Opsional)">
            </div>`;
    });
    html += `</div>`;
    editorForm.innerHTML += html;
  });
}

function saveEditorData() {
  for (let c = 0; c < 5; c++) {
    gameData[c].category = document.getElementById(`cat-${c}`).value;
    for (let q = 0; q < 5; q++) {
      gameData[c].questions[q].points = parseInt(
        document.getElementById(`pts-${c}-${q}`).value,
      );
      gameData[c].questions[q].q = document.getElementById(`q-${c}-${q}`).value;
      gameData[c].questions[q].a = document.getElementById(`a-${c}-${q}`).value;
      gameData[c].questions[q].img = document.getElementById(
        `img-${c}-${q}`,
      ).value;
    }
  }
  localStorage.setItem("jeopardyData", JSON.stringify(gameData));
  alert("Data soal berhasil disimpan!");
  navigate("game");
}

// --- SETTINGS & UTILS ---
function loadSettings() {
  document.getElementById("setting-timer").value = gameState.config.timer;
  document.getElementById("setting-teams").value = gameState.config.teams;
}

function saveSettings() {
  gameState.config.timer = parseInt(
    document.getElementById("setting-timer").value,
  );
  gameState.config.teams = parseInt(
    document.getElementById("setting-teams").value,
  );
  saveState();
  alert("Pengaturan disimpan!");
  navigate("game");
}

function saveState() {
  localStorage.setItem("jeopardyState", JSON.stringify(gameState));
}

function resetGameProgress() {
  if (confirm("Yakin ingin mereset skor dan membuka semua papan soal?")) {
    gameState.scores = [0, 0, 0, 0];
    gameState.opened = [];
    saveState();
    navigate("game");
  }
}

function resetData() {
  if (confirm("Yakin ingin mengembalikan semua soal ke setelan pabrik?")) {
    localStorage.removeItem("jeopardyData");
    gameData = defaultData;
    renderEditor();
  }
}

// Init
navigate("home");
