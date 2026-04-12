<!doctype html>
<html lang="ar">
  <head>
    <meta charset="UTF-8" />
    <title>Level 4 - ابحث عن الكلمات</title>
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <h1>🧩 ابحث عن الكلمات</h1>

    <div id="progress-container">
      <div id="progress-bar"></div>
    </div>

    <div id="words-list"></div>

    <div id="game-container"></div>

    <div id="popup" class="popup">
      <div class="popup-content">
        <h2 id="popup-text"></h2>
        <button id="next-btn">التالي</button>
        <button id="retry-btn">إعادة اللعبة</button>
      </div>
    </div>
    <button
      id="backBtn"
      style="
        position: fixed;
        top: 20px;
        left: 30px;
        padding: 20px 30px;
        background-color: #e8f6ff;
        color: #78c1b8;
        border: 2px solid #78c1b8;
        border-radius: 30px;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        z-index: 10000;
      "
    >
      رجوع
    </button>

    <script>
      const bgMusic = new Audio("./assest/bgMusic.mp3");
      bgMusic.loop = true;
      bgMusic.volume = 0.1;

      const correctSound = new Audio("./assest/win.mp3");
      const errorSound = new Audio("./assest/lose-sound.mp3");

      const words = [
        { word: "مدرسة", direction: "horizontal" },
        { word: "كتاب", direction: "vertical" },
        { word: "كرسي", direction: "horizontal" },
        { word: "ساعة", direction: "vertical" },
        { word: "نجمة", direction: "horizontal" },
        { word: "نار", direction: "vertical" },
        { word: "جبل", direction: "horizontal" },
      ];
      const gridSize = 7;
      const arabicLetters = [
        "ا",
        "ب",
        "ت",
        "ث",
        "ج",
        "ح",
        "خ",
        "د",
        "ذ",
        "ر",
        "ز",
        "س",
        "ش",
        "ص",
        "ض",
        "ط",
        "ظ",
        "ع",
        "غ",
        "ف",
        "ق",
        "ك",
        "ل",
        "م",
        "ن",
        "ه",
        "و",
        "ي",
      ];
      let foundWords = [];

      const gameContainer = document.getElementById("game-container");
      const wordsListContainer = document.getElementById("words-list");
      const popup = document.getElementById("popup");
      const popupText = document.getElementById("popup-text");
      const nextBtn = document.getElementById("next-btn");
      const retryBtn = document.getElementById("retry-btn");

      let timer;
      const totalTime = 90;
      let timeLeft = totalTime;
      const progressBar = document.getElementById("progress-bar");

      let isSelecting = false;
      let selectedCells = [];

      function startTimer() {
        timeLeft = totalTime;
        updateProgressBar();
        timer = setInterval(() => {
          timeLeft--;
          updateProgressBar();
          if (timeLeft <= 0) {
            clearInterval(timer);
            errorSound.play();
            showMissedThenPopup();
          }
        }, 1000);
      }

      function updateProgressBar() {
        progressBar.style.width = (timeLeft / totalTime) * 100 + "%";
        if (timeLeft / totalTime > 0.6)
          progressBar.style.background = "#4caf50";
        else if (timeLeft / totalTime > 0.3)
          progressBar.style.background = "#ffeb3b";
        else progressBar.style.background = "#f44336";
      }

      function createGrid() {
        gameContainer.innerHTML = "";
        foundWords = [];

        let letters = [];
        for (let i = 0; i < gridSize * gridSize; i++) {
          letters.push("");
        }

        words.forEach((item) => {
          const word = item.word;
          const dir = item.direction;
          let placed = false;
          let attempts = 0;

          while (!placed && attempts < 200) {
            attempts++;

            if (dir === "horizontal") {
              let row = Math.floor(Math.random() * gridSize);
              let startCol =
                Math.floor(Math.random() * (gridSize - word.length)) +
                word.length -
                1;

              let canPlace = true;

              for (let i = 0; i < word.length; i++) {
                let index = row * gridSize + (startCol - i);
                if (letters[index] !== "" && letters[index] !== word[i]) {
                  canPlace = false;
                  break;
                }
              }

              if (canPlace) {
                for (let i = 0; i < word.length; i++) {
                  let index = row * gridSize + (startCol - i);
                  letters[index] = word[i];
                }
                placed = true;
              }
            } else if (dir === "vertical") {
              let col = Math.floor(Math.random() * gridSize);
              let startRow = Math.floor(
                Math.random() * (gridSize - word.length),
              );

              let canPlace = true;

              for (let i = 0; i < word.length; i++) {
                let index = (startRow + i) * gridSize + col;
                if (letters[index] !== "" && letters[index] !== word[i]) {
                  canPlace = false;
                  break;
                }
              }

              if (canPlace) {
                for (let i = 0; i < word.length; i++) {
                  let index = (startRow + i) * gridSize + col;
                  letters[index] = word[i];
                }
                placed = true;
              }
            }
          }

          if (!placed) {
            console.log("لم يتم وضع الكلمة:", word);
          }
        });

        for (let i = 0; i < letters.length; i++) {
          if (letters[i] === "") {
            letters[i] =
              arabicLetters[Math.floor(Math.random() * arabicLetters.length)];
          }
        }

        for (let i = 0; i < gridSize; i++) {
          const rowDiv = document.createElement("div");
          rowDiv.className = "row";

          for (let j = 0; j < gridSize; j++) {
            const idx = i * gridSize + j;

            const cell = document.createElement("div");
            cell.className = "cell";
            cell.textContent = letters[idx];
            cell.dataset.row = i;
            cell.dataset.col = j;

            cell.addEventListener("mousedown", startSelection);
            cell.addEventListener("mouseenter", dragSelection);
            cell.addEventListener("mouseup", endSelection);

            cell.addEventListener("touchstart", startSelection);
            cell.addEventListener("touchmove", handleTouchMove);
            cell.addEventListener("touchend", endSelection);

            rowDiv.appendChild(cell);
          }

          gameContainer.appendChild(rowDiv);
        }

        updateWordsList();
        startTimer();
      }

      function updateWordsList() {
        wordsListContainer.innerHTML = "";
        words.forEach((item) => {
          const div = document.createElement("div");
          div.textContent = item.word;
          div.className = "word";
          if (foundWords.includes(item.word)) div.classList.add("correct");
          wordsListContainer.appendChild(div);
        });
      }

      function startSelection(e) {
        isSelecting = true;
        selectedCells = [];
        selectCell(e.target);
      }
      function dragSelection(e) {
        if (isSelecting) selectCell(e.target);
      }
      function endSelection() {
        isSelecting = false;
        checkWord();
        selectedCells.forEach((c) => c.classList.remove("selected"));
        selectedCells = [];
      }
      function selectCell(cell) {
        if (!selectedCells.includes(cell)) {
          selectedCells.push(cell);
          cell.classList.add("selected");
        }
      }

      function checkWord() {
        if (selectedCells.length === 0) return;

        let firstCell = selectedCells[0];
        let lastCell = selectedCells[selectedCells.length - 1];

        let isHorizontal = firstCell.dataset.row === lastCell.dataset.row;
        let selectedWord = selectedCells.map((c) => c.textContent).join("");

        let wordToCheck = selectedWord;

        let found = words.find((w) => w.word === wordToCheck);
        if (found && !foundWords.includes(wordToCheck)) {
          selectedCells.forEach((c) => {
            c.classList.add("correct");
            c.style.animation = "pop 0.3s";
          });
          correctSound.currentTime = 0;
          correctSound.play();

          foundWords.push(wordToCheck);
          updateWordsList();
          checkCompletion();
        }
      }

      function checkCompletion() {
        if (foundWords.length === words.length) {
          clearInterval(timer);
          correctSound.play();
          showPopup("🎉 ! أحسنت ", true);
        }
      }

      function showMissedThenPopup() {
        words.forEach((item) => {
          if (!foundWords.includes(item.word)) {
            const dir = item.direction;
            if (dir === "horizontal") {
              let wordLetters = item.word.split("").reverse();
              for (let i = 0; i < gridSize; i++) {
                let row = gameContainer.children[i];
                for (let j = 0; j <= gridSize - wordLetters.length; j++) {
                  let match = true;
                  for (let k = 0; k < wordLetters.length; k++) {
                    if (row.children[j + k].textContent !== wordLetters[k]) {
                      match = false;
                      break;
                    }
                  }
                  if (match) {
                    for (let k = 0; k < wordLetters.length; k++) {
                      row.children[j + k].classList.add("missed");
                    }
                    break;
                  }
                }
              }
            } else if (dir === "vertical") {
              for (let col = 0; col < gridSize; col++) {
                for (let row = 0; row <= gridSize - item.word.length; row++) {
                  let match = true;
                  for (let k = 0; k < item.word.length; k++) {
                    if (
                      gameContainer.children[row + k].children[col]
                        .textContent !== item.word[k]
                    ) {
                      match = false;
                      break;
                    }
                  }
                  if (match) {
                    for (let k = 0; k < item.word.length; k++) {
                      gameContainer.children[row + k].children[
                        col
                      ].classList.add("missed");
                    }
                    break;
                  }
                }
              }
            }
            const wordDivs = document.querySelectorAll("#words-list .word");
            wordDivs.forEach((d) => {
              if (d.textContent === item.word) d.classList.add("missed");
            });
          }
        });
        setTimeout(() => {
          showPopup("انتهى الوقت ⏰", false);
        }, 1500);
      }

      // --- النوافذ ---
      function showPopup(msg, showNext) {
        popupText.textContent = msg;
        popup.style.display = "flex";
        nextBtn.style.display = showNext ? "inline-block" : "none";
        retryBtn.style.display = showNext ? "none" : "inline-block";
      }

      nextBtn.addEventListener("click", () => {
        window.location.href = "level5.html";
      });
      retryBtn.addEventListener("click", () => {
        popup.style.display = "none";
        foundWords = [];
        createGrid();
      });

      document.body.addEventListener(
        "click",
        () => {
          if (bgMusic.paused) {
            bgMusic.play();
          }
        },
        { once: true },
      );
      function handleTouchMove(e) {
        e.preventDefault();

        const touch = e.touches[0];
        const element = document.elementFromPoint(touch.clientX, touch.clientY);

        if (element && element.classList.contains("cell")) {
          selectCell(element);
        }
      }

      window.onload = createGrid;

      document.getElementById("backBtn").addEventListener("click", () => {
        window.location.href = "../index.html";
      });
    </script>
  </body>
</html>
