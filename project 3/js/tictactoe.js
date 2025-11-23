// ===========================
//   TIC TAC TOE — WITH EFFECTS
// ===========================

// Elements
const cells = document.querySelectorAll("[data-cell]");
const playerText = document.getElementById("player-mark");
const newGameBtn = document.getElementById("new-game-btn");

const scoreXEl = document.getElementById("score-x");
const scoreOEl = document.getElementById("score-o");
const scoreDrawEl = document.getElementById("score-draw");

const winMessage = document.getElementById("win-message");

// Sounds
const winSound = new Audio("../win.mp3");
const drawSound = new Audio("../draw.mp3");

// Game state
let board = ["", "", "", "", "", "", "", "", ""];
let currentPlayer = "X";
let active = true;

// Scores
let xWins = 0;
let oWins = 0;
let draws = 0;

// Winning combos
const wins = [
  [0,1,2],[3,4,5],[6,7,8],
  [0,3,6],[1,4,7],[2,5,8],
  [0,4,8],[2,4,6]
];

// Start game
startGame();

function startGame() {
  board.fill("");
  active = true;
  currentPlayer = "X";
  playerText.textContent = currentPlayer;

  winMessage.textContent = "";
  winMessage.classList.remove("show");

  cells.forEach((cell) => {
    cell.textContent = "";
    cell.classList.remove("x", "o", "win");
    cell.onclick = handleClick;
  });
}

function handleClick(e) {
  const cell = e.target;
  const index = [...cells].indexOf(cell);

  if (board[index] !== "" || !active) return;

  board[index] = currentPlayer;
  cell.textContent = currentPlayer;
  cell.classList.add(currentPlayer.toLowerCase());

  if (checkWin(currentPlayer)) {
    endGame(currentPlayer);
    return;
  }

  if (board.every(c => c !== "")) {
    endDraw();
    return;
  }

  currentPlayer = currentPlayer === "X" ? "O" : "X";
  playerText.textContent = currentPlayer;
}

function checkWin(player) {
  return wins.find(combo =>
    combo.every(i => board[i] === player)
  );
}

function endGame(player) {
  active = false;

  // Highlight winning cells
  const combo = checkWin(player);
  combo.forEach(i => cells[i].classList.add("win"));

  // Score update
  if (player === "X") {
    xWins++;
    scoreXEl.textContent = xWins;
  } else {
    oWins++;
    scoreOEl.textContent = oWins;
  }

  // Win message
  winMessage.textContent = `Player ${player} Wins! 🎉`;
  winMessage.classList.add("show");

  // Sound
  winSound.play();

  // Confetti
  launchConfetti();
}

function endDraw() {
  active = false;

  draws++;
  scoreDrawEl.textContent = draws;

  winMessage.textContent = "It's a Draw 🤝";
  winMessage.classList.add("show");

  drawSound.play();
}

newGameBtn.addEventListener("click", startGame);


// ===========================
//   CONFETTI FUNCTION 🎉
//   Lightweight, no library
// ===========================

function launchConfetti() {
  const confettiCount = 120;
  
  for (let i = 0; i < confettiCount; i++) {
    const confetti = document.createElement("div");
    confetti.classList.add("confetti");

    // Random direction + position
    confetti.style.left = Math.random() * 100 + "vw";
    confetti.style.backgroundColor = randomColor();
    confetti.style.animationDuration = (Math.random() * 1 + 1) + "s";
    confetti.style.animationDelay = (Math.random() * 0.5) + "s";

    document.body.appendChild(confetti);

    // Remove after animation
    setTimeout(() => confetti.remove(), 2000);
  }
}

function randomColor() {
  const colors = ["#2563eb", "#dc2626", "#16a34a", "#f59e0b", "#7c3aed"];
  return colors[Math.floor(Math.random() * colors.length)];
}
