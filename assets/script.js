// Struktur Data Soal
const gameData = [
  {
    category: "Wawasan Agama",
    questions: [
      {
        points: 100,
        q: "Tempat ibadah umat Hindu disebut?",
        a: "Pura",
        img: "",
      },
      {
        points: 200,
        q: "Kitab suci agama Buddha adalah?",
        a: "Tripitaka",
        img: "",
      },
      // Tambahkan soal 300, 400, 500
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
        q: "Saha anu biasana jadi panata acara dina hiji kagiatan resmi?",
        a: "MC / Girang Acara",
        img: "",
      },
      // Tambahkan soal 300, 400, 500
    ],
  },
  // Tambahkan 3 kategori lainnya di sini...
];

const board = document.getElementById("game-board");

// 1. Membuat Papan Grid Otomatis
function initBoard() {
  // Buat Header Kategori
  gameData.forEach((cat) => {
    const header = document.createElement("div");
    header.className = "category-header";
    header.innerText = cat.category;
    board.appendChild(header);
  });

  // Buat Kotak Nilai secara vertikal
  for (let i = 0; i < 5; i++) {
    // Loop 5 baris (100-500)
    gameData.forEach((cat) => {
      const qData = cat.questions[i];
      const card = document.createElement("div");
      card.className = "card";

      // Jika data soal belum diisi penuh (untuk testing awal), beri angka default
      card.innerText = qData ? qData.points : (i + 1) * 100;

      card.onclick = () => {
        if (!card.classList.contains("disabled") && qData) {
          openModal(cat.category, qData);
          card.classList.add("disabled"); // Gelapkan kotak setelah ditekan
        }
      };
      board.appendChild(card);
    });
  }
}

// 2. Logika Modal (Pop-up)
const modal = document.getElementById("question-modal");
const btnShowAnswer = document.getElementById("btn-show-answer");
const ansText = document.getElementById("modal-answer");
const modalImg = document.getElementById("modal-image");

function openModal(category, data) {
  document.getElementById("modal-category").innerText = category;
  document.getElementById("modal-points").innerText = data.points;
  document.getElementById("modal-question").innerText = data.q;
  ansText.innerText = data.a;

  // Tampilkan gambar jika ada
  if (data.img !== "") {
    modalImg.src = data.img;
    modalImg.classList.remove("hidden");
  } else {
    modalImg.classList.add("hidden");
  }

  ansText.classList.add("hidden");
  btnShowAnswer.style.display = "inline-block";
  modal.classList.remove("hidden");
}

btnShowAnswer.onclick = () => {
  ansText.classList.remove("hidden");
  btnShowAnswer.style.display = "none";
};

document.getElementById("btn-close").onclick = () => {
  modal.classList.add("hidden");
};

// 3. Logika Suara
document.getElementById("btn-correct").onclick = () => {
  document.getElementById("audio-correct").play();
};
document.getElementById("btn-wrong").onclick = () => {
  document.getElementById("audio-wrong").play();
};

// 4. Logika Papan Skor
let scores = { 1: 0, 2: 0, 3: 0, 4: 0 };
function updateScore(team, amount) {
  scores[team] += amount;
  document.getElementById(`score-${team}`).innerText = scores[team];
}

// Jalankan
initBoard();
