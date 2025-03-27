import { computeEffectiveGridSize, resizeGame } from "./grid-manager.js";
import { renderWordList, showCompletionMessage } from "./ui-components.js";
import {
  getRandomTransparentColor,
  getXYFromCell,
  checkWordInDirection,
} from "./utils.js";
import { createWordSearchGame } from "./game-creator.js";

export function updateFinalEntries(newEntries) {
  window.finalEntries = newEntries.map((entry) => entry);
  updateWordData(); // Your custom function that regenerates the word list, etc.
}

export function mergeEntries(arrayA, arrayB) {
  // Quick return if both arrays are empty
  if ((!arrayA || arrayA.length === 0) && (!arrayB || arrayB.length === 0)) {
    return [];
  }

  // Pre-allocate the map size based on the combined length of both arrays
  const totalItems =
    (arrayA ? arrayA.length : 0) + (arrayB ? arrayB.length : 0);
  const map = new Map();

  // Process both arrays in a single loop if possible
  if (arrayA && arrayA.length > 0) {
    for (let i = 0; i < arrayA.length; i++) {
      const item = arrayA[i];
      if (item.id) map.set(item.id, item);
    }
  }

  if (arrayB && arrayB.length > 0) {
    for (let i = 0; i < arrayB.length; i++) {
      const item = arrayB[i];
      if (item.id) map.set(item.id, item);
    }
  }

  // Convert map to array more efficiently
  return Array.from(map.values());
}

export function updateWordData() {
  // Create word data more efficiently
  const newWordData =
    window.finalEntries.length > 0 ? new Array(window.finalEntries.length) : [];

  // Use a standard for loop instead of map for better performance
  if (window.finalEntries.length > 0) {
    for (let i = 0; i < window.finalEntries.length; i++) {
      newWordData[i] = window.finalEntries[i].wordText.toUpperCase();
    }
  }

  // Only update if there's a change
  if (window.wordData !== newWordData) {
    window.wordData = newWordData.map((entry) => entry);

    // Update the word list in the UI
    renderWordList(window.wordData);

    // Cache DOM reference
    const containerEl = document.getElementById("game-container");

    // Only destroy if necessary
    if (window.gameInstance) {
      window.gameInstance.destroy(true);
      window.gameInstance = null;
    }

    // Clear and style the container
    if (containerEl) {
      containerEl.style.backgroundColor = "#f5e9d1";
      containerEl.style.borderRadius = "8px";
      containerEl.style.boxShadow = "0 4px 12px rgba(0,0,0,0.1)";
      // Use innerHTML = "" only if needed
      if (containerEl.children.length > 0) {
        containerEl.innerHTML = "";
      }
    }

    // Create new puzzle if needed
    if (window.wordData.length > 0) {
      // Calculate needed grid size
      const estimatedGridSize = computeEffectiveGridSize(window.wordData);

      // Add loading indicator for large word sets
      // if (newWordData.length > 10) {
      //   showLoadingIndicator();
      // }

      // Validate words against this grid size
      const validationResult = validateWords(
        window.wordData,
        estimatedGridSize
      );

      if (!validationResult.valid) {
        // Handle problematic words by filtering them out
        const filteredWordData = handleWordOverflow(
          window.wordData,
          validationResult
        );

        // Only continue if we have remaining words
        if (filteredWordData.length > 0) {
          processWordData(filteredWordData);
        } else {
          hideLoadingIndicator();
        }
      } else {
        // All words are valid, proceed normally
        processWordData(window.wordData);
      }
    }
  }
}

// Process word data in batches for large sets
function processWordData(wordData) {
  const container = document.getElementById("game-container");
  // For large word sets, use request animation frame to prevent UI freezing

  if (wordData.length > 0) {
    showLoadingIndicator();
    container.style.pointerEvents = "none";
  }

  if (wordData.length > 10) {
    requestAnimationFrame(() => {
      waitForWordSearch(wordData);

      // Hide loading indicator after processing
      // setTimeout(() => hideLoadingIndicator(), 500);
    });
  } else {
    // For smaller word sets, process immediately
    waitForWordSearch(wordData);
  }
}

// Show loading indicator
export function showLoadingIndicator() {
  const container = document.getElementById("game-container");
  if (!container) return;

  let loader = document.getElementsByClassName("grid-loading-indicator");

  if (!loader) {
    loader = document.createElement("div");
    loader.id = "word-search-loader";
    loader.style.position = "absolute";
    loader.style.top = "50%";
    loader.style.left = "50%";
    loader.style.transform = "translate(-50%, -50%)";
    loader.style.backgroundColor = "rgba(245, 233, 209, 0.8)";
    loader.style.padding = "20px";
    loader.style.borderRadius = "10px";
    loader.style.zIndex = "1000";
    loader.innerHTML = `
          <div style="text-align: center;">
              <div style="border: 5px solid #f3f3f3; border-top: 5px solid #b8860b; border-radius: 50%; width: 40px; height: 40px; animation: spin 2s linear infinite; margin: 0 auto;"></div>
              <p style="margin-top: 10px; color: #473214; font-weight: bold;">Creating word search...</p>
          </div>
          <style>
              @keyframes spin {
                  0% { transform: rotate(0deg); }
                  100% { transform: rotate(360deg); }
              }
          </style>
      `;

    container.style.position = "relative";
    container.appendChild(loader);
  } else {
    loader[0].style.display = "block";
  }
}

// Hide loading indicator
export function hideLoadingIndicator() {
  const loader = document.getElementsByClassName("grid-loading-indicator");
  if (loader) {
    loader[0].style.display = "none";
  }
}

export function validateWords(wordData, gridSize) {
  if (!wordData || wordData.length === 0) {
    return { valid: false, message: "No words provided" };
  }

  // Check for words that exceed grid size
  const problematicWords = [];

  for (let i = 0; i < wordData.length; i++) {
    const word = wordData[i];
    if (word.length > gridSize) {
      problematicWords.push({ word, length: word.length });
    }
  }

  if (problematicWords.length > 0) {
    // Sort by length descending to show longest problematic words first
    problematicWords.sort((a, b) => b.length - a.length);

    // Format error message
    const wordList = problematicWords
      .slice(0, 3) // Show at most 3 words in the error
      .map((w) => `${w.word} (${w.length} letters)`) // Use backticks for template literals
      .join(", ");

    const suffix =
      problematicWords.length > 3
        ? `and ${problematicWords.length - 3} more`
        : "";

    return {
      valid: false,
      message: `The following words are too long for the grid size (${gridSize}): ${wordList}${suffix},
      problematicWords`,
    };
  }

  return { valid: true };
}

export function handleWordOverflow(wordData, validationResult) {
  if (validationResult.valid) {
    return wordData; // No changes needed
  }

  // Log for debugging
  console.warn(validationResult.message);

  // Filter out problematic words
  const problematicWordIds = new Set(
    validationResult.problematicWords.map((w) => w.word)
  );

  return wordData.filter((word) => !problematicWordIds.has(word));
}

export function waitForWordSearch(wordData, retries = 5) {
  // Quick validation
  if (!Array.isArray(wordData) || wordData.length === 0) {
    return;
  }

  if (typeof WordSearch === "undefined") {
    if (retries > 0) {
      console.error("WordSearch is not loaded. Retrying in 1 second...");
      setTimeout(() => waitForWordSearch(wordData, retries - 1), 1000);
    } else {
      console.error("WordSearch failed to load after multiple attempts.");
    }
    return;
  }

  try {
    // Find longest word for validation
    let longestWord = "";
    let longestLength = 0;

    for (let i = 0; i < wordData.length; i++) {
      if (wordData[i].length > longestLength) {
        longestLength = wordData[i].length;
        longestWord = wordData[i];
      }
    }

    // Compute grid size with validation
    let effectiveGridSize = computeEffectiveGridSize(window.wordData);

    // Safety check - ensure grid is big enough
    if (effectiveGridSize < longestLength) {
      console.error(
        `Grid size ${effectiveGridSize} is too small for word "${longestWord}" (length ${longestLength})`
      );
      // Force grid to be at least as big as longest word + 2
      effectiveGridSize = longestLength + 2;
    }

    // Create a new WordSearch instance to generate the grid matrix
    // const tempDiv = document.createElement("div");
    // const ws = new WordSearch(tempDiv, {
    //   gridSize: effectiveGridSize,
    //   words: wordData,
    //   orientations: ["horizontal", "vertical", "diagonal"],
    //   maxAttempts: 20,
    //   preferOverlap: true,
    //   fillBlanks: true,
    // });

    // // Important: Store the matrix globally and log it for debugging
    // window.gridMatrix = ws.matrix;

    // tempDiv.remove();

    // Clean up old game instance
    if (window.gameInstance) {
      window.gameInstance.destroy(true);
      window.gameInstance = null;
    }
    // Create game with optimized settings
    window.gameInstance = createWordSearchGame({
      containerId: "game-container",
      puzzleOptions: {
        gridSize: effectiveGridSize,
        words: wordData,
        debug: false,
        orientations: ["horizontal", "vertical", "diagonal"],
        maxAttempts: 20,
        preferOverlap: true,
        fillBlanks: true,
        maxGridGrowth: 5,
      },
    });
  } catch (error) {
    console.error("Error creating word search:", error);
  }
}

export function updateGridBasedOnWords(newWordList, scene, letterTexts) {
  // Generate a new matrix if needed for the updated word list
  if (
    newWordList.length > 0 &&
    (!window.gridMatrix || window.gridMatrix.length === 0)
  ) {
    const effectiveGridSize = computeEffectiveGridSize(newWordList);
    const tempDiv = document.createElement("div");
    const ws = new WordSearch(tempDiv, {
      gridSize: effectiveGridSize,
      words: newWordList,
      orientations: ["horizontal", "vertical", "diagonal"],
    });
    window.gridMatrix = ws.matrix;
    tempDiv.remove();
  }
  // Use the optimized grid renderer
  updateGridRenderer({
    newWordList,
    scene,
    letterTexts,
    gridMatrix: scene.gridMatrix,
  });
}

export function updateGridRenderer(gridData) {
  if (window.gridUpdateScheduled) {
    return;
  }

  window.gridUpdateScheduled = true;

  requestAnimationFrame(() => {
    const { newWordList, scene, letterTexts, gridMatrix } = gridData;
    const newGridSize = computeEffectiveGridSize(newWordList);

    // Force a redraw if we have a matrix but no letters are showing
    const forceRedraw =
      gridMatrix &&
      (!letterTexts ||
        letterTexts.length === 0 ||
        (letterTexts[0] && letterTexts[0].length === 0));

    // Check if grid size changed or if we need to force a redraw
    if (newGridSize !== window.previousGridSize || forceRedraw) {
      window.previousGridSize = newGridSize;

      // Import resizeGame and call it
      resizeGame(
        scene.sys.game.config.width,
        scene.sys.game.config.height,
        scene,
        letterTexts,
        newGridSize,
        gridMatrix
      );
    }

    window.gridUpdateScheduled = false;
  });
}

export function startGameTimer() {
  // Ensure a valid allowed time is set; if not, do nothing.
  if (!window.gamerTimerValue || window.gamerTimerValue <= 0) return;

  const timerDivs = document.querySelectorAll(".timer-container");

  if (window.gamerTimerValue) {
    timerDivs.forEach((timerDiv) => {
      timerDiv.style.display = "flex";
    });
  }

  // Prevent multiple timers from starting.
  if (!window.gameTimerID) {
    window.elapsedTime = 0;
    window.gameTimerID = setInterval(() => {
      window.elapsedTime++;
      const remainingTime = Math.max(
        window.gamerTimerValue - window.elapsedTime,
        0
      );
      let timeFinished = checkTimeLimit(window.gamerTimerValue);

      // Update the display with the formatted remaining time.

      const timerDisplay = document.getElementById("timerDisplay");
      if (timerDisplay) {
        timerDisplay.innerText = formatTime(remainingTime);
      }

      // Stop the timer when time is up.
      // if (remainingTime === 0) {
      //   stopGameTimer();

      // }
    }, 1000);
  }
}

/**
 * Stop the game timer and display a time's up modal
 * Completely stops all game functionality and offers restart option
 */
/**
 * Stop the game timer and display a time's up modal using SweetAlert2
 * Completely stops all game functionality and offers restart option
 */
export function stopGameTimer(timeFinished = false) {
  if (window.gameTimerID) {
    // Stop the timer
    clearInterval(window.gameTimerID);
    window.gameTimerID = null;

    // Destroy the game instance
    if (window.gameInstance) {
      // Stop all input events first
      if (window.gameInstance.scene && window.gameInstance.scene.scenes) {
        window.gameInstance.scene.scenes.forEach((scene) => {
          if (scene.input) {
            scene.input.enabled = false;
          }
        });
      }

      // Pause all game processes
      window.gameInstance.scene.pause();

      // Show SweetAlert modal
      if (timeFinished) {
        showTimeUpModal();
      }
    }
  }
}

/**
 * Show professional time's up modal using SweetAlert2
 */
function showTimeUpModal() {
  // Ensure SweetAlert2 is available
  if (typeof Swal === "undefined") {
    console.error("SweetAlert2 is not loaded. Please include the library.");
    return;
  }

  const title = window.customStyles["timeupPopupTitle"];
  const bodyText = window.customStyles["timeupPopupBodyText"];
  const challengeText = window.customStyles["timeupPopupChallengeText"];
  const buttonText = window.customStyles["timeupPopupButtonText"];

  Swal.fire({
    title: `<span style="color: #e74c3c">${title}</span>`,
    html:
      `<p style="font-size: 1.1rem; margin-bottom: 0.8rem">${bodyText}</p>` +
      `<p style="font-size: 1.1rem">${challengeText}</p>`,
    icon: "warning",
    confirmButtonText: `${buttonText}`,
    didRender: (popup) => {
      const confirmButton = Swal.getConfirmButton();
      if (confirmButton) {
        confirmButton.setAttribute("type", "button");
      }
    },
    confirmButtonColor: "#2ecc71",
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    focusConfirm: false,
    backdrop: `
      rgba(0,0,0,0.7)
      center
      no-repeat
    `,
    customClass: {
      title: "wordsearch-modal-title",
      container: "wordsearch-modal-container",
      popup: "wordsearch-modal-popup",
      confirmButton: "wordsearch-modal-confirm-btn",
    },
    showClass: {
      popup: "animate__animated animate__fadeIn animate__faster",
    },
    hideClass: {
      popup: "animate__animated animate__fadeOut animate__faster",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      // Prevent scrolling/jumping behavior
      // Destroy existing game instance completely
      if (window.gameInstance) {
        window.gameInstance.destroy(true);
        window.gameInstance = null;
      }

      // Reset game state
      window.elapsedTime = 0;
      window.gameTimerID = null;

      // Restart the game by calling updateWordData function
      if (typeof updateWordData === "function") {
        updateWordData();
      } else {
        console.error(
          "updateWordData function not found. Unable to restart game."
        );

        // Show error if updateWordData not found
        Swal.fire({
          title: "Error",
          text: "Unable to restart the game. Please refresh the page.",
          icon: "error",
          confirmButtonColor: "#3085d6",
        });
      }
    } else if (result.dismiss === Swal.DismissReason.cancel) {
      Swal.close();
    }
  });

  // Add custom styles for the SweetAlert modal
  if (!document.getElementById("wordsearch-swal-styles")) {
    const swalStyles = document.createElement("style");
    swalStyles.id = "wordsearch-swal-styles";
    swalStyles.innerHTML = `
      .wordsearch-modal-popup {
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      }
      
      .wordsearch-modal-title {
        font-size: 28px;
        font-weight: 700;
      }
      
      .wordsearch-modal-confirm-btn {
        font-weight: 600;
        padding: 12px 30px;
        border-radius: 50px;
      }
      
      .wordsearch-modal-cancel-btn {
        font-weight: 600;
        padding: 12px 30px;
        border-radius: 50px;
      }
      
      .swal2-icon {
        border-color: #e74c3c;
        color: #e74c3c;
      }
    `;
    document.head.appendChild(swalStyles);
  }
}

/**
 * Check if time limit has been reached
 * @param {number} timeLimit - Time limit in seconds
 */
export function checkTimeLimit(timeLimit) {
  if (window.elapsedTime >= timeLimit) {
    stopGameTimer(true);
    return true;
  }
  return false;
}

export function formatTime(seconds) {
  const hrs = Math.floor(seconds / 3600);
  const mins = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;
  // Format with leading zeros if necessary
  const hrsStr = hrs > 0 ? hrs + ":" : "";
  const minsStr = (hrs > 0 && mins < 10 ? "0" : "") + mins;
  const secsStr = (secs < 10 ? "0" : "") + secs;
  return hrsStr + minsStr + ":" + secsStr;
}

export function highlightWord(
  scene,
  cells,
  persistentCanvas,
  cellSize,
  wordList,
  guessedWord,
  gridSize
) {
  // Check that we have at least two cells to form a word.
  if (!cells || cells.length < 2) {
    console.error(
      "Error: highlightWord received an empty or undefined cells array."
    );
    return;
  }

  // Get the 2D context of the persistent canvas.
  const persistentCtx = persistentCanvas.getContext("2d");
  const strokeWidth = cellSize * 0.8;
  persistentCtx.lineWidth = strokeWidth;
  persistentCtx.strokeStyle = getRandomTransparentColor(); // Use a helper function to generate a semi-transparent color.
  persistentCtx.lineCap = "round";
  const higlightedCellTextColor = window.customStyles
    ? window.customStyles["higlightedCellTextColor"]
    : "#fff";

  // Get the centers of the first and last cells.
  const firstCell = cells[0];
  const lastCell = cells[cells.length - 1];
  const startXY = getXYFromCell(
    firstCell.row,
    firstCell.col,
    cellSize,
    gridSize
  );
  const endXY = getXYFromCell(lastCell.row, lastCell.col, cellSize, gridSize);

  // Draw the highlight line from the start cell center to the end cell center.
  persistentCtx.beginPath();
  persistentCtx.moveTo(startXY.x, startXY.y);
  persistentCtx.lineTo(endXY.x, endXY.y);
  persistentCtx.stroke();

  // Play the word found sound if available.
  // Guard the sound play call
  if (scene.wordFoundSound && typeof scene.wordFoundSound.play === "function") {
    scene.wordFoundSound.play();
  } else {
    console.warn("wordFoundSound is not available; skipping sound play.");
  }

  // 1. Change the letter color to white for all cells under the highlight line
  cells.forEach((cell) => {
    scene.children.list.forEach((obj) => {
      if (obj.type === "Text") {
        // Ensure it's a text object
        const row = obj.getData("row");
        const col = obj.getData("col"); // Assuming you stored column data

        if (row === cell.row && col === cell.col) {
          obj.setColor(higlightedCellTextColor); // Change color to white
          // obj.setFontStyle("bold");
        }
      }
    });
  });

  if (guessedWord && !window.foundWords.includes(guessedWord)) {
    window.foundWords.push(guessedWord);
  }
}

export function animateMatch(scene, cells, letterTexts, cellSize) {
  const higlightedCellTextColor = window.customStyles
    ? window.customStyles["higlightedCellTextColor"]
    : "#fff";

  cells.forEach(({ row, col }) => {
    const letterObj = letterTexts[row][col];
    scene.tweens.add({
      targets: letterObj,
      scale: 1.5,
      duration: 300,
      ease: "Bounce.easeOut",
      yoyo: true,
      onUpdate: () => {
        // NEW CODE: Continuously enforce white color during animation.
        letterObj.setColor(higlightedCellTextColor);
      },
      onComplete: () => {
        letterObj.setScale(1);
        // NEW CODE: Reapply white color once animation is finished.
        letterObj.setColor(higlightedCellTextColor);
      },
    });
  });
}

export function updateWordListUI(foundWord, foundWordsCount, wordList) {
  foundWord = foundWord ? foundWord.toLowerCase() : "";
  const foundItem = document.getElementById(`word-${foundWord}`);
  if (foundItem) {
    foundItem.classList.add("found");
  }

  if (foundWordsCount === wordList.length) {
    stopGameTimer();
    window.elapsedTime = 0;
    !window.isAdmin && showCompletionMessage();
  }
}

export function autoSolvePuzzle(
  scene,
  gridMatrix,
  wordData,
  persistentCanvas,
  cellSize,
  gridSize,
  showAnswers
) {
  if (!showAnswers && !window.isAdmin) return;

  // Directions to check
  const SOLVER_DIRECTIONS = [
    { deltaRow: 0, deltaCol: 1 }, // horizontal
    { deltaRow: 1, deltaCol: 0 }, // vertical
    { deltaRow: 1, deltaCol: 1 }, // diagonal down-right
    { deltaRow: 1, deltaCol: -1 }, // diagonal down-left
  ];

  // For each word in the list
  wordData.forEach((word) => {
    let found = false;

    // If we find it, we highlight & skip further searching
    for (let row = 0; row < window.gridMatrix.length && !found; row++) {
      for (let col = 0; col < window.gridMatrix[row].length && !found; col++) {
        // Try each direction
        for (const dir of SOLVER_DIRECTIONS) {
          const matchedCells = checkWordInDirection(
            window.gridMatrix,
            row,
            col,
            word,
            dir.deltaRow,
            dir.deltaCol
          );
          if (matchedCells) {
            let foundWordsCount = window.foundWords.length + 1;
            const visualClues = document.getElementsByClassName("visual-clue");

            // Loop through the elements
            for (let i = 0; i < visualClues.length; i++) {
              const clue = visualClues[i];
              // Get the data-word attribute and compare (case-insensitive) to guessedWord
              if (
                clue.getAttribute("data-word").toLowerCase() ===
                word.toLowerCase()
              ) {
                clue.classList.add("found");
              }
            }
            // Found the word! highlight it
            highlightWord(
              scene,
              matchedCells,
              persistentCanvas,
              cellSize,
              wordData,
              word,
              gridSize
            );
            animateMatch(scene, matchedCells, window.letterTexts, cellSize);

            updateWordListUI(word, foundWordsCount, wordData);
            found = true;
            break;
          }
        }
      }
    }
  });
}
