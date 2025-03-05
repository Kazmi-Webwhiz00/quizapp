jQuery(document).ready(function ($) {
  let localizedEntries = [];
  let wordData = [];
  let gameInstance = null;
  let previousFinalEntriesStr = JSON.stringify([]);
  let finalEntries = [];
  // Global variable to hold the timer ID.
  window.wordsearchGridTimerId = null;

  if (typeof wordSearchData !== "undefined") {
    localizedEntries = JSON.parse(wordSearchData.entries || "[]");
  }

  let cookieEntries = JSON.parse(getCookie("wordsearch_entries") || "[]");

  // Store previous counts to detect changes.
  // let previousLocalizedCount = localizedEntries.length;
  // let previousCookieCount = cookieEntries.length;

  // Access container dimensions and grid sizes
  // let adminContainerWidth = wordSearchData.containerWidth;
  // let adminGridSize = wordSearchData.gridSize;
  // let frontendContainerWidth = frontendData.containerWidth;
  // let frontendGridSize = frontendData.gridSize;

  finalEntries = mergeEntries(localizedEntries, cookieEntries);
  /************************************************************
   * 1) Cookie Helpers
   ************************************************************/
  // Helper: Set a cookie
  function setCookie(name, value, days) {
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
  }

  // Helper: Get a cookie
  function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(";");
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i].trim();
      if (c.indexOf(nameEQ) === 0) {
        return c.substring(nameEQ.length, c.length);
      }
    }
    return null;
  }

  // Helper: Clear a cookie by setting it to expire in the past
  function clearCookie(name) {
    setCookie(name, "", -1);
  }

  // 2) Merge them for the first time
  // finalEntries = mergeEntries(localizedEntries, cookieEntries);

  // Determine context first
  const isAdmin = typeof wordSearchData !== "undefined";
  const containerWidth = isAdmin
    ? wordSearchData.containerWidth
    : frontendData.containerWidth;
  const gridSize = isAdmin ? wordSearchData.gridSize : frontendData.gridSize;

  // 3) Initialize the puzzle with whatever we have
  if (finalEntries.length > 0) {
    updateWordData(); // This will create the game
  } else {
    console.log("No initial finalEntries. Waiting for new entries...");
  }

  /************************************************************
   * 2) Clear Cookie on "Add New WordSearch" Page
   ************************************************************/
  if (window.location.href.indexOf("post-new.php") !== -1) {
    clearCookie("wordsearch_entries");
    console.log("Cleared cookie: wordsearch_entries");
  }

  // Periodically check for changes
  function startGridTimer() {
    window.wordsearchGridTimerId = setInterval(() => {
      let hasChanged = false;
      if (typeof wordSearchData !== "undefined" && wordSearchData.entries) {
        try {
          const newLocalizedEntries = JSON.parse(wordSearchData.entries);
          if (
            JSON.stringify(newLocalizedEntries) !==
            JSON.stringify(localizedEntries)
          ) {
            localizedEntries = newLocalizedEntries;
            hasChanged = true;
          }
        } catch (e) {
          console.error("Error parsing localized data:", e);
        }
      }
      try {
        const rawCookieData = getCookie("wordsearch_entries");
        let newCookieEntries = [];
        newCookieEntries = rawCookieData ? JSON.parse(rawCookieData) : [];

        if (
          JSON.stringify(newCookieEntries) !== JSON.stringify(cookieEntries)
        ) {
          cookieEntries = newCookieEntries;
          hasChanged = true;
        }
      } catch (e) {
        console.error("Error parsing cookie data:", e);
      }

      if (hasChanged) {
        const merged = mergeEntries(localizedEntries, cookieEntries);
        const mergedStr = merged ? JSON.stringify(merged) : [];
        if (merged.length === 0) {
          finalEntries = [];
          updateFinalEntries([]);
          clearInterval(window.wordsearchGridTimerId); // Stop the timer.
          window.wordsearchGridTimerId = null;
        } else if (mergedStr !== previousFinalEntriesStr) {
          previousFinalEntriesStr = mergedStr;
          updateFinalEntries(merged);
        }
      }
    }, 1000);
  }

  // Expose a global function to start the timer only if it's not already running.
  window.startWordsearchGridTimer = function () {
    if (!window.wordsearchGridTimerId) {
      startGridTimer();
      console.log("Timer started.");
    }
  };

  // Expose a function to stop the timer manually.
  window.stopWordsearchGridTimer = function () {
    if (window.wordsearchGridTimerId) {
      clearInterval(window.wordsearchGridTimerId);
      window.wordsearchGridTimerId = null;
      console.log(
        "wordsearchGridTimer has been stopped via window.stopWordsearchGridTimer()."
      );
    }
  };

  /**
   * Merges two arrays of objects by unique 'id'.
   */
  // Merge by unique 'id'
  function updateFinalEntries(newEntries) {
    finalEntries = newEntries;

    updateWordData(); // Your custom function that regenerates the word list, etc.
  }

  function mergeEntries(arrayA, arrayB) {
    // // If cookieEntries is empty, return empty array.
    // if (!arrayB || arrayB.length === 0) {
    //   return [];
    // }

    const map = new Map();
    // Merge localizedEntries (arrayA)
    arrayA.forEach((item) => {
      if (item.id) map.set(item.id, item);
    });
    // Merge cookieEntries (arrayB)
    arrayB.forEach((item) => {
      if (item.id) map.set(item.id, item);
    });
    return Array.from(map.values());
  }

  // =====================
  //   HELPER FUNCTIONS
  // =====================
  // wordData =
  //   Array.isArray(finalEntries) && finalEntries.length > 0
  //     ? finalEntries.map((entry) => entry.wordText.toUpperCase())
  //     : [];

  //   WORD DATA & RE-INIT
  // =========================
  function updateWordData() {
    wordData =
      finalEntries.length > 0
        ? finalEntries.map((entry) => entry.wordText.toUpperCase())
        : [];

    // Immediately update the word list in the UI.
    renderWordList(wordData);

    // (2) Destroy the old Phaser instance completely
    if (gameInstance) {
      gameInstance.destroy(true);
      gameInstance = null;
    }

    // (3) Clear the DOM container so it's fresh
    const containerEl = document.getElementById("game-container");
    if (containerEl) {
      containerEl.innerHTML = "";
    }

    // (4) Now create a fresh puzzle if we have words
    if (wordData.length > 0) {
      waitForWordSearch(wordData);
    } else {
    }
  }

  function waitForWordSearch(wordData, retries = 5) {
    // First, check if wordData is available and an array
    if (!Array.isArray(wordData) || wordData.length === 0) {
      console.log("No wordData found. Skipping puzzle creation...");
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
    // Once both wordEntries and WordSearch are available, initialize the game.
    gameInstance = createWordSearchGame({
      containerId: "game-container",
      containerWidth: containerWidth,
      puzzleOptions: {
        gridSize: gridSize,
        words: wordData,
        maxGridSize: gridSize,
        debug: false,
        orientations: ["horizontal", "vertical", "diagonal"],
        // ... other WordSearch config options as needed
      },
      onGameReady: () => {
        console.log("WordSearch puzzle & Phaser game are ready!");
      },
    });
  }

  // if (finalEntries.length > 0) {
  //   updateWordData(); // This calls reInitPuzzle() if needed
  // } else {
  //   // If finalEntries is empty at first load, you might:
  //   // updateWordData();
  //   // - Wait for the intervals to detect data changes, or
  //   // - Force a manual update if you know the data is loading
  //   console.log("No initial finalEntries found. Waiting for updates...");
  // }

  // Start the wait-for-WordSearch logic.
  // waitForWordSearch();

  let foundWords = [];
  let previousWidth = null;
  let previousHeight = null;
  let previousGridSize = null;

  /**
   * Creates and renders a WordSearch puzzle along with a Phaser game
   * and custom canvas layers for drawing lines and highlights.
   *
   * @param {Object} options - Configuration for the puzzle and rendering.
   * @param {string} options.containerId - The DOM container ID where the Phaser canvas is attached.
   * @param {number} options.gameWidth - Width of the Phaser game/canvas.
   * @param {number} options.gameHeight - Height of the Phaser game/canvas.
   * @param {Object} options.puzzleOptions - WordSearch puzzle options (directions, gridSize, words, etc.).
   * @param {string} [options.canvasParentId="game-container"] - The parent container where dynamic/persistent canvases get appended.
   * @param {function} [options.onGameReady] - Optional callback to run after the game/puzzle is set up.
   *
   * Example usage:
   *  createWordSearchGame({
   *    containerId: "game-container",
   *    gameWidth: 600,
   *    gameHeight: 600,
   *    puzzleOptions: {
   *      gridSize: 10,
   *      words: ["NASIK", "SMELL", "EXAMPLE", "TOUCH", "TASTE"],
   *      ...
   *    },
   *    onGameReady: () => console.log("WordSearch game ready!")
   *  });
   */
  function createWordSearchGame({
    containerId = "game-container",
    containerWidth = "100%",
    gameWidth = 600,
    gameHeight = 600,
    puzzleOptions = {},
    canvasParentId = "game-container",
    onGameReady,
  }) {
    // Set the container's width dynamically
    // const container = document.getElementById(containerId);
    // if (container) {
    //   container.style.width = containerWidth;
    // } else {
    //   console.error(`Container with id ${containerId} not found.`);
    // }
    // const wordList = puzzleOptions.words;
    // const wordList = ["nsaik", "smell", "example", "touch", "taste"];
    // console.log("::wordList", wordList);
    // Default puzzle options if none provided.
    const defaultPuzzleOpts = {
      directions: ["W", "N", "WN", "EN"],
      orientations: ["horizontal", "vertical", "diagonal"],
      gridSize: 2,
      wordsList: [],
      debug: false,
    };
    // Merge user puzzle options with defaults
    const mergedPuzzleOptions = { ...defaultPuzzleOpts, ...puzzleOptions };
    const container = document.getElementById(containerId);
    if (container) {
      container.style.width = containerWidth;
    } else {
      console.error(`Container with id ${containerId} not found.`);
    }

    // Create the puzzle
    const tempDiv = document.createElement("div");
    const ws = new WordSearch(tempDiv, mergedPuzzleOptions);
    const gridMatrix = ws.matrix;
    const gridSize = mergedPuzzleOptions.gridSize;
    // const wordList = mergedPuzzleOptions.words;

    // Internal states
    let isDrawing = false;
    let letterTexts = [];
    let startPoint = null;
    let cellSize;
    let dynamicCanvas, persistentCanvas;
    let foundWordsCount = 0;
    // --- NEW: Render word list in the DOM so players see what to find ---
    // renderWordList(wordData);

    // PHASER CONFIG
    const config = {
      type: Phaser.CANVAS,
      parent: containerId,
      resolution: 2, // ID of the DOM container for Phaser
      width: gameWidth,
      height: gameHeight,
      transparent: true,
      scene: {
        preload,
        create,
        update,
      },
      // scale: {
      //   mode: Phaser.Scale.RESIZE, // Auto-adjust to parent container
      //   autoCenter: Phaser.Scale.CENTER_BOTH,
      // },
    };

    // Initialize Phaser Game
    const phaserGame = new Phaser.Game(config);

    function preload() {
      // Load assets as needed
      this.load.audio(
        "wordFound",
        pluginURL.url + "assets/audio/word-matched.mp3"
      );
    }

    function create() {
      const scene = this;

      function updateGridSize(newSize) {
        resizeGame(gameWidth, gameHeight, scene, letterTexts, newSize);
      }

      // Example usage
      updateGridSize(mergedPuzzleOptions.gridSize);

      if (this.cache.audio.exists("wordFound")) {
        this.wordFoundSound = this.sound.add("wordFound");
      } else {
        console.error("Sound file is missing from the cache!");
      }

      // Create the dynamic & persistent canvases inside the container
      const { persistentCanvas: pCanvas, dynamicCanvas: dCanvas } =
        createCanvasLayers({
          game: this.game,
          parentId: canvasParentId,
        });
      persistentCanvas = pCanvas;
      dynamicCanvas = dCanvas;

      // Calculate cellSize
      cellSize =
        Math.min(this.sys.game.config.width, this.sys.game.config.height) /
        gridSize;

      // Render grid letters
      for (let row = 0; row < gridSize; row++) {
        letterTexts[row] = [];
        for (let col = 0; col < gridSize; col++) {
          const { letter } = gridMatrix[row][col];
          const x = col * cellSize + cellSize * 0.5;
          const y = row * cellSize + cellSize * 0.5;
          const textObj = scene.add
            .text(x, y, letter, {
              fontFamily: "Arial",
              fontSize: `${cellSize * 0.6}px`,
              color: "#000",
            })
            .setOrigin(0.5);
          textObj.setDepth(10);
          textObj.setData("row", row);
          textObj.setData("col", col);
          letterTexts[row][col] = textObj;
        }
      }

      // POINTER EVENTS
      scene.input.on("pointerdown", (pointer) => {
        const dynamicCtx = dynamicCanvas.getContext("2d");
        dynamicCtx.clearRect(0, 0, dynamicCanvas.width, dynamicCanvas.height);

        const clampedX = Phaser.Math.Clamp(pointer.x, 0, dynamicCanvas.width);
        const clampedY = Phaser.Math.Clamp(pointer.y, 0, dynamicCanvas.height);
        const clickedCell = getCellFromPoint(
          { x: clampedX, y: clampedY },
          cellSize,
          gridSize
        );

        if (clickedCell.row >= 0 && clickedCell.col >= 0) {
          const cellCenter = getXYFromCell(
            clickedCell.row,
            clickedCell.col,
            cellSize,
            gridSize
          );
          startPoint = { x: cellCenter.x, y: cellCenter.y };
          isDrawing = true;
        }
      });

      scene.input.on("pointermove", (pointer) => {
        if (!isDrawing || !startPoint) return;

        const dynamicCtx = dynamicCanvas.getContext("2d");
        dynamicCtx.clearRect(0, 0, dynamicCanvas.width, dynamicCanvas.height);

        const currentX = Phaser.Math.Clamp(pointer.x, 0, dynamicCanvas.width);
        const currentY = Phaser.Math.Clamp(pointer.y, 0, dynamicCanvas.height);
        const tolerance = 30;

        // Restriction logic example:
        if (currentY < startPoint.y - tolerance) {
          console.log("Restricted: Upward movement not allowed.");
          dynamicCtx.clearRect(0, 0, dynamicCanvas.width, dynamicCanvas.height);
          return;
        }

        if (
          Math.abs(currentY - startPoint.y) < tolerance &&
          currentX < startPoint.x
        ) {
          console.log("Restricted: Backward horizontal movement not allowed.");
          dynamicCtx.clearRect(0, 0, dynamicCanvas.width, dynamicCanvas.height);
          return;
        }

        // Draw the line
        dynamicCtx.beginPath();
        dynamicCtx.moveTo(startPoint.x, startPoint.y);
        dynamicCtx.lineTo(currentX, currentY);
        dynamicCtx.strokeStyle = "rgba(0, 123, 255, 0.8)";
        dynamicCtx.lineWidth = cellSize * 0.8;
        dynamicCtx.lineCap = "round";
        dynamicCtx.stroke();
      });

      scene.input.on("pointerup", () => {
        if (!startPoint) return;
        isDrawing = false;

        const dynamicCtx = dynamicCanvas.getContext("2d");
        dynamicCtx.clearRect(0, 0, dynamicCanvas.width, dynamicCanvas.height);

        const finalX = Phaser.Math.Clamp(
          scene.input.activePointer.x + 8,
          0,
          dynamicCanvas.width
        );
        const finalY = Phaser.Math.Clamp(
          scene.input.activePointer.y,
          0,
          dynamicCanvas.height
        );
        const endPoint = { x: finalX, y: finalY };

        const startCell = getCellFromPoint(startPoint, cellSize, gridSize);
        const endCell = getCellFromPoint(endPoint, cellSize, gridSize);
        const selectedCells = getCellsInLine(
          startCell.row,
          startCell.col,
          endCell.row,
          endCell.col
        );
        const guessedWord = getStringFromCells(selectedCells, gridMatrix);

        console.log("::guessedWord", guessedWord);

        const isMatch = wordData.includes(guessedWord);
        if (isMatch) {
          highlightWord(
            scene,
            selectedCells,
            persistentCanvas,
            cellSize,
            wordList,
            guessedWord,
            gridSize
          );
          animateMatch(scene, selectedCells, letterTexts, cellSize);

          // --- NEW: Mark the word as found in the word list UI ---

          foundWordsCount++;
          if (foundWordsCount === wordList.length) {
            showCompletionMessage();
          }

          foundWords.push(guessedWord);
          updateWordListUI(guessedWord, foundWordsCount, wordData);
        }

        startPoint = null;
      });

      // Optional callback after setup
      if (onGameReady) {
        onGameReady();
      }
    }

    function update() {
      // Any real-time updates or animations
    }
  }

  function resizeGame(newWidth, newHeight, scene, letterTexts, gridSize) {
    const gameCanvas = scene.sys.game.canvas;

    // Determine if dimensions or grid size have changed
    const dimensionChanged =
      previousWidth !== newWidth || previousHeight !== newHeight;
    const gridChanged = previousGridSize !== gridSize;

    // If nothing changed, skip re-render
    if (!dimensionChanged && !gridChanged) {
      return;
    }

    // Update previous dimensions and grid size
    previousWidth = newWidth;
    previousHeight = newHeight;
    previousGridSize = gridSize;

    // Update canvas size
    gameCanvas.width = newWidth;
    gameCanvas.height = newHeight;

    // Ensure the game is active
    if (!scene || !scene.sys || !scene.sys.isActive()) return;

    // Clear existing text objects
    scene.children.removeAll();

    // Calculate the cell size based on the smallest dimension
    const cellSize = Math.min(newWidth, newHeight) / gridSize;

    // Redraw the text objects
    for (let row = 0; row < gridSize; row++) {
      letterTexts[row] = [];
      for (let col = 0; col < gridSize; col++) {
        const x = col * cellSize + cellSize * 0.5;
        const y = row * cellSize + cellSize * 0.5;
        const fontSize = Math.floor(cellSize * 0.6);
        const letterObj = scene.add
          .text(x, y, "LETTER", {
            fontFamily: "Arial",
            fontSize: `${fontSize}px`,
            color: "#000",
          })
          .setOrigin(0.5);

        letterTexts[row][col] = letterObj;
      }
    }
  }

  /**
   * createCanvasLayers
   * Dynamically creates persistentCanvas & dynamicCanvas inside a given parent container
   */
  function createCanvasLayers({ game, parentId = "game-container" }) {
    const container = document.getElementById(parentId);
    if (!container) {
      console.error(`Parent container #${parentId} not found.`);
      return {};
    }

    const persistentCanvas = document.createElement("canvas");
    persistentCanvas.id = "persistentCanvas";
    persistentCanvas.width = game.scale.width;
    persistentCanvas.height = game.scale.height;
    persistentCanvas.style.position = "absolute";
    persistentCanvas.style.top = "0px";
    persistentCanvas.style.left = "0px";
    persistentCanvas.style.pointerEvents = "none";
    persistentCanvas.style.zIndex = "5";
    persistentCanvas.style.backgroundColor = "transparent";
    container.appendChild(persistentCanvas);

    const dynamicCanvas = document.createElement("canvas");
    dynamicCanvas.id = "dynamicCanvas";
    dynamicCanvas.width = game.scale.width;
    dynamicCanvas.height = game.scale.height;
    dynamicCanvas.style.position = "absolute";
    dynamicCanvas.style.top = "0px";
    dynamicCanvas.style.left = "0px";
    dynamicCanvas.style.pointerEvents = "none";
    dynamicCanvas.style.zIndex = "10";
    dynamicCanvas.style.backgroundColor = "transparent";
    container.appendChild(dynamicCanvas);

    return { persistentCanvas, dynamicCanvas };
  }

  // Compute the center coordinate of a cell
  function getXYFromCell(row, col, cellSize, gridSize) {
    const container = document.getElementById("game-container");
    const computedStyle = window.getComputedStyle(container);
    const paddingLeft = parseInt(computedStyle.paddingLeft, 10) || 0;
    const paddingTop = parseInt(computedStyle.paddingTop, 10) || 0;
    const xPadding =
      col === 0
        ? paddingLeft / 2
        : col === gridSize - 1
        ? -(paddingLeft / 2)
        : 0;
    const yPadding =
      row === 0 ? paddingTop / 2 : row === gridSize - 1 ? -(paddingTop / 2) : 0;

    return {
      x: col * cellSize + cellSize * 0.5,
      y: row * cellSize + cellSize * 0.5,
    };
  }

  // Determine which cell a given (x,y) belongs to
  function getCellFromPoint(point, cellSize, gridSize) {
    let col = Math.floor(point.x / cellSize);
    let row = Math.floor(point.y / cellSize);
    row = Math.max(0, Math.min(row, gridSize - 1));
    col = Math.max(0, Math.min(col, gridSize - 1));
    return { row, col };
  }

  // Convert a list of cells to a string (the guessed word)
  function getStringFromCells(cells, gridMatrix) {
    if (!cells || cells.length === 0) {
      console.error(
        "Error: getStringFromCells received an empty or undefined cells array."
      );
      return ""; // Return an empty string instead of breaking the code
    }
    let word = "";
    cells.forEach((cell) => {
      if (cell && gridMatrix[cell.row] && gridMatrix[cell.row][cell.col]) {
        word += gridMatrix[cell.row][cell.col].letter;
      } else {
        console.error("Invalid cell data:", cell);
      }
    });

    return word;
  }

  // Compute the path of cells from (r1,c1) to (r2,c2)
  function getCellsInLine(r1, c1, r2, c2) {
    const cells = [];
    const deltaRow = r2 - r1;
    const deltaCol = c2 - c1;
    const steps = Math.max(Math.abs(deltaRow), Math.abs(deltaCol));
    if (!isFinite(steps) || steps > 100) {
      return [{ row: r1, col: c1 }];
    }
    if (steps === 0) return [{ row: r1, col: c1 }];

    const rowStep = deltaRow / steps;
    const colStep = deltaCol / steps;
    for (let i = 0; i <= steps; i++) {
      const row = Math.round(r1 + rowStep * i);
      const col = Math.round(c1 + colStep * i);
      cells.push({ row, col });
    }
    return cells;
  }

  /**
   * highlightWord - draws a line on the persistentCanvas to mark the found word
   */
  function highlightWord(
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
    if (scene.wordFoundSound) {
      scene.wordFoundSound.play();
    }

    // 1. Change the letter color to white for all cells under the highlight line

    cells.forEach((cell) => {
      scene.children.list.forEach((obj) => {
        if (obj.type === "Text") {
          // Ensure it's a text object
          const row = obj.getData("row");
          const col = obj.getData("col"); // Assuming you stored column data

          if (row === cell.row && col === cell.col) {
            obj.setColor("#FFFFFF"); // Change color to white
            obj.setFontStyle("bold");
          }
        }
      });
    });

    if (guessedWord && !foundWords.includes(guessedWord)) {
      foundWords.push(guessedWord);
    }

    // If all words have been found, show the completion message.
    if (foundWords.length === wordList.length) {
      console.log("::guessedWord1", guessedWord);
      showCompletionMessage();
    }
  }

  function showCompletionMessage() {
    const modal = document.getElementById("completionModal");
    if (modal) {
      modal.style.opacity = 0;
      modal.style.display = "block";
      // Animate the opacity for a smooth fade-in.
      setTimeout(() => {
        modal.style.opacity = 1;
      }, 10);

      // Add a click handler to the close button.
      const closeBtn = document.getElementById("closeModal");
      if (closeBtn) {
        closeBtn.onclick = () => {
          // Fade out the modal
          modal.style.opacity = 0;
          setTimeout(() => {
            modal.style.display = "none";
          }, 300);
        };
      }
    } else {
      console.error("Completion modal not found in the DOM!");
    }
  }

  /**
   * animateMatch - a simple bounce animation for matched letters
   */
  function animateMatch(scene, cells, letterTexts, cellSize) {
    cells.forEach(({ row, col }) => {
      const letterObj = letterTexts[row][col];
      scene.tweens.add({
        targets: letterObj,
        scale: 1.5,
        duration: 300,
        ease: "Bounce.easeOut",
        yoyo: true,
        onComplete: () => {
          letterObj.setScale(1);
        },
      });
    });
  }

  /**
   * getRandomTransparentColor - returns a random semi-transparent color string
   */
  function getRandomTransparentColor() {
    // Generate a random hue (0-359 degrees)
    const hue = Math.floor(Math.random() * 360);
    // Use high saturation (90%) and moderate lightness (50%)
    // The alpha is set to 0.8 for transparency.
    return `hsla(${hue}, 90%, 30%, 0.3)`;
  }

  // --- NEW: Renders the word list in the #wordList container
  function renderWordList(wordData) {
    console.log("::wordData", wordData);
    const listContainer = document.getElementById("wordList");
    if (!listContainer) return;

    listContainer.innerHTML = ""; // Clear any existing content

    if (wordData.length > 0) {
      wordData.forEach((word) => {
        word = word.length > 0 ? word.toLowerCase() : "";
        const li = document.createElement("li");
        li.id = `word-${word}`;
        li.textContent = word;
        listContainer.appendChild(li);
      });
    }
  }

  // --- NEW: Strikes out the found word in the #wordList
  function updateWordListUI(foundWord, foundWordsCount, wordList, wordData) {
    foundWord = foundWord ? foundWord.toLowerCase() : "";
    const foundItem = document.getElementById(`word-${foundWord}`);
    console.log("::foundItem", foundWord);
    if (foundItem) {
      foundItem.classList.add("found");
    }
    if (foundWordsCount === wordList.length) {
      showCompletionBanner();
    }
  }

  // --- NEW: Show a "Well Done!" banner
  function showCompletionBanner() {
    const banner = document.getElementById("completionBanner");
    if (banner) {
      banner.style.display = "block";
      banner.style.opacity = "1"; // in case you want a fade-in effect
    }
  }
});
