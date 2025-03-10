jQuery(document).ready(function ($) {
  let localizedEntries = [];
  let wordData = [];
  let gameInstance = null;
  let previousFinalEntriesStr = JSON.stringify([]);
  let finalEntries = [];
  let letterTexts = [];
  let previousGridSize = 0;
  // Global variable to hold the timer ID.
  window.wordsearchGridTimerId = null;
  // Global timer ID for grid updates (or game clock)
  let gameTimerID = null;
  // Global variable to store elapsed time (in seconds, for example)
  let elapsedTime = 0;
  let customStyles = [];

  if (typeof wordSearchData !== "undefined") {
    localizedEntries = JSON.parse(wordSearchData.entries || "[]");
  }

  let cookieEntries = JSON.parse(getCookie("wordsearch_entries") || "[]");
  customStyles =
    typeof wordSearchData !== "undefined"
      ? wordSearchData["gridStyles"]
      : frontendData["gridStyles"];

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
  const maximunGridSize = isAdmin
    ? wordSearchData.maximunGridSize
    : frontendData.maximunGridSize;

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
          // ✅ Update grid size dynamically after merging new words
          if (
            gameInstance &&
            gameInstance.scene &&
            gameInstance.scene.scenes[0]
          ) {
            const scene = gameInstance.scene.scenes[0];
            if (scene.letterTexts && scene.letterTexts.length > 0) {
              updateGridBasedOnWords(merged, scene, scene.letterTexts);
            } else {
              console.warn("letterTexts not ready; postponing grid update.");
              // Optionally, schedule a delayed call:
              // setTimeout(() => {
              if (scene.letterTexts && scene.letterTexts.length > 0) {
                updateGridBasedOnWords(merged, scene, scene.letterTexts);
              }
              // }, 500);
            }
          }
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
      containerEl.style.backgroundColor = customStyles
        ? customStyles["bgColor"]
        : "#808080a1";
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

    // Compute gridSize dynamically
    const effectiveGridSize = computeEffectiveGridSize(wordData);
    previousGridSize = effectiveGridSize ? effectiveGridSize : 0;
    // Once both wordEntries and WordSearch are available, initialize the game.
    gameInstance = createWordSearchGame({
      containerId: "game-container",
      containerWidth: containerWidth,
      puzzleOptions: {
        gridSize: effectiveGridSize,
        words: wordData,
        // maxGridSize: maximunGridSize,
        debug: false,
        orientations: ["horizontal", "vertical", "diagonal"],
        // ... other WordSearch config options as needed
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
  let showAnswers = false;
  let checkBoxElement = null;

  function createWordSearchGame({
    containerId = "game-container",
    containerWidth = "100%",
    containerMaxWidth = "800px",
    // gameWidth = 600,
    // gameHeight = 600,
    puzzleOptions = {},
    canvasParentId = "game-container",
    onGameReady: localOnGameReady,
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
      // gridSize: 2,
      wordsList: [],
      debug: false,
    };
    // Merge user puzzle options with defaults
    const mergedPuzzleOptions = { ...defaultPuzzleOpts, ...puzzleOptions };
    const container = document.getElementById(containerId);
    if (container) {
      container.style.width = containerWidth;
      container.style.maxWidth = containerMaxWidth;
    } else {
      console.error(`Container with id ${containerId} not found.`);
    }

    // Create the puzzle
    const tempDiv = document.createElement("div");
    const ws = new WordSearch(tempDiv, mergedPuzzleOptions);
    const gridMatrix = ws.matrix;
    const gridSize = mergedPuzzleOptions.gridSize;
    const wordList = mergedPuzzleOptions.words;
    // Directions you want to check (example: left->right, top->down, diagonal down-right, diagonal down-left)
    const SOLVER_DIRECTIONS = [
      { name: "W", deltaRow: 0, deltaCol: 1 }, // horizontal
      { name: "N", deltaRow: 1, deltaCol: 0 }, // vertical
      { name: "WN", deltaRow: 1, deltaCol: 1 }, // diagonal down-right
      { name: "EN", deltaRow: 1, deltaCol: -1 }, // diagonal down-left
    ];

    // Step B: Calculate the dynamic canvas size
    const { width: dynamicWidth, height: dynamicHeight } = getDynamicCanvasSize(
      containerId,
      gridSize,
      800 // or any max size you want
    );

    // Internal states
    let isDrawing = false;
    // let letterTexts = [];
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
      width: dynamicWidth,
      height: dynamicHeight,
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

    // ✅ Handle dynamic resizing of the grid on window resize
    function handleResize() {
      if (gameInstance && gameInstance.scene && gameInstance.scene.scenes[0]) {
        const scene = gameInstance.scene.scenes[0];
        const newGridSize = gridSize;
        const { width: newWidth, height: newHeight } = getDynamicCanvasSize(
          containerId,
          newGridSize,
          800
        );
        resizeGame(
          newWidth,
          newHeight,
          scene,
          letterTexts,
          previousGridSize,
          gridMatrix // Use the dynamically stored grid size
        );
      }
    }

    function preload() {
      // Load assets as needed
      this.load.audio(
        "wordFound",
        pluginURL.url + "assets/audio/word-matched.mp3"
      );
    }

    function create() {
      const scene = this;
      scene.letterTexts = letterTexts;
      const fontColor = customStyles ? customStyles["fontColor"] : "#000";
      const fontFamily = customStyles ? customStyles["fontFamily"] : "Roboto";
      // Update grid size for the first time (using mergedPuzzleOptions.gridSize)
      updateGridSize(mergedPuzzleOptions.gridSize); // This calls resizeGame internally

      if (this.cache.audio.exists("wordFound")) {
        if (this.wordFoundSound) {
          this.wordFoundSound.destroy(); // Destroy any existing instance
        }
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

      // Step G: Calculate cellSize from dynamic dimensions
      const cellSize = Math.min(dynamicWidth, dynamicHeight) / gridSize;
      const fontSize = Math.floor(cellSize * 0.5);
      letterTexts = [];
      const finalStyle = {
        fontFamily: fontFamily,
        fontSize: `${fontSize}px`,
        color: fontColor,
        stroke: fontColor,
        strokeThickness: 0,
        shadow: {
          offsetX: 0,
          offsetY: 0,
          color: fontColor,
          blur: 0,
          stroke: false,
          fill: false,
        },
      };

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
          // Ensure no stroke/shadow if previously set
          textObj.setStroke(fontColor, 0); // stroke color + thickness=0
          textObj.setShadow(0, 0, fontColor, 0, false, false); // Disable any shadow
          textObj.setColor(fontColor); // Re-apply solid color
          textObj.setDepth(10);
          textObj.setData("row", row);
          textObj.setData("col", col);
          letterTexts[row][col] = textObj;
        }
      }

      // (Optional) If you have a dedicated updateGridSize function that calls resizeGame:
      function updateGridSize(newSize) {
        resizeGame(
          scene.sys.game.config.width,
          scene.sys.game.config.height,
          scene,
          letterTexts,
          newSize,
          gridMatrix
        );
      }

      // Start a timer only if it's not already running
      startGameTimer();

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
        // const lineColor = customStyles
        //   ? customStyles["lineColor"]
        //   : "rgba(0, 123, 255, 0.8)";

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
          // if (foundWordsCount === wordList.length) {
          //   showCompletionMessage();
          // }

          foundWords.push(guessedWord);
          updateWordListUI(guessedWord, foundWordsCount, wordData);
        }

        startPoint = null;
      });

      // Optional callback after setup
      if (typeof localOnGameReady === "function") {
        localOnGameReady(scene);
        window.startWordsearchGridTimer();
      }

      if (typeof frontendData !== "undefined") {
        checkBoxElement = document.getElementsByClassName(
          frontendData.checkBoxElement
        );
        if (checkBoxElement) {
          $(checkBoxElement).off("change"); // Remove old handlers
          $(checkBoxElement).on("change", function () {
            showAnswers = $(this).is(":checked");
            console.log("Show Answers:", showAnswers);
            if (typeof autoSolvePuzzle === "function") {
              autoSolvePuzzle(
                scene,
                gridMatrix,
                wordData,
                persistentCanvas,
                cellSize,
                gridSize,
                showAnswers
              );
            }
          });
        }
      } else {
        console.log("::enterd outside of conditional block");
      }
    }

    function autoSolvePuzzle(
      scene,
      gridMatrix,
      wordData,
      persistentCanvas,
      cellSize,
      gridSize,
      showAnswers
    ) {
      if (!showAnswers && !isAdmin) return; // If the user doesn't want answers, do nothing.

      // For each word in the list
      wordData.forEach((word) => {
        let found = false;

        // If we find it, we highlight & skip further searching
        for (let row = 0; row < gridMatrix.length && !found; row++) {
          for (let col = 0; col < gridMatrix[row].length && !found; col++) {
            // Try each direction
            for (const dir of SOLVER_DIRECTIONS) {
              const matchedCells = checkWordInDirection(
                gridMatrix,
                row,
                col,
                word,
                dir.deltaRow,
                dir.deltaCol
              );
              if (matchedCells) {
                foundWordsCount++;
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
                updateWordListUI(word, foundWordsCount, wordData);
                found = true;
                break;
              }
            }
          }
        }
      });
    }

    /**
     * checkWordInDirection
     * Attempts to match a word starting at (row, col) in the grid along
     * the given deltas. If all letters match, returns the list of cells
     * that form that word; otherwise returns null.
     */
    function checkWordInDirection(matrix, row, col, word, deltaRow, deltaCol) {
      const maxRow = matrix.length; // Number of rows
      const maxCol = matrix[0].length; // Number of columns
      const lettersNeeded = word.length;

      // Make sure we have enough space in this direction
      const endRow = row + (lettersNeeded - 1) * deltaRow;
      const endCol = col + (lettersNeeded - 1) * deltaCol;
      if (endRow < 0 || endRow >= maxRow || endCol < 0 || endCol >= maxCol) {
        // Out of bounds, can't fit the word in this direction
        return null;
      }

      // Gather letters and compare
      const matchedCells = [];
      for (let i = 0; i < lettersNeeded; i++) {
        const curRow = row + i * deltaRow;
        const curCol = col + i * deltaCol;
        if (matrix[curRow][curCol].letter !== word[i]) {
          return null;
        }
        matchedCells.push({ row: curRow, col: curCol });
      }

      // If we got here, the entire word matched
      return matchedCells;
    }

    function localOnGameReady(scene) {
      scene.letterTexts = letterTexts;
      // Now that the scene is ready, register your resize listener
      window.addEventListener("resize", handleResize);
    }

    function update() {
      // Any real-time updates or animations
    }
    return phaserGame;
  }

  function updateGridBasedOnWords(newWordList, scene, letterTexts) {
    const newGridSize = computeEffectiveGridSize(newWordList);

    // Check if grid size really changed to avoid unnecessary redraws:
    if (newGridSize !== previousGridSize) {
      previousGridSize = newGridSize;
      // Update your puzzle options if needed:
      mergedPuzzleOptions.gridSize = newGridSize;
      // Redraw the grid with the new cell size
      resizeGame(
        scene.sys.game.config.width,
        scene.sys.game.config.height,
        scene,
        letterTexts,
        newGridSize,
        gridMatrix
      );
    }
  }

  function getDynamicCanvasSize(containerId, gridSize, maxCanvasSize = 800) {
    const container = document.getElementById(containerId);
    if (!container) {
      console.error(`Container with id ${containerId} not found.`);
      // Fallback to a default size if container is missing
      return { width: 600, height: 600 };
    }

    // Use the container width to determine a base cell size
    const containerWidth = container.clientWidth;

    // Example: keep the puzzle square by using containerWidth alone
    // If you want a rectangular puzzle, also measure container.clientHeight
    let desiredCellSize = containerWidth / gridSize;

    // Preliminary dynamic dimensions
    let dynamicWidth = gridSize * desiredCellSize;
    let dynamicHeight = gridSize * desiredCellSize; // square puzzle

    // If dimensions exceed the maxCanvasSize, scale down proportionally
    if (dynamicWidth > maxCanvasSize) {
      const scaleFactor = maxCanvasSize / dynamicWidth;
      dynamicWidth *= scaleFactor;
      dynamicHeight *= scaleFactor;
    }

    return {
      width: Math.floor(dynamicWidth),
      height: Math.floor(dynamicHeight),
    };
  }

  function resizeGame(
    newWidth,
    newHeight,
    scene,
    letterTexts = [],
    gridSize,
    gridMatrix
  ) {
    const gameCanvas = scene.sys.game.canvas;
    gameCanvas.width = newWidth;
    gameCanvas.height = newHeight;
    scene.children.removeAll();
    const cellSize = Math.min(newWidth, newHeight) / gridSize;
    const fontSize = Math.floor(cellSize * 0.5);
    const fontColor = customStyles ? customStyles["fontColor"] : "#000";
    const fontFamily = customStyles ? customStyles["fontFamily"] : "Roboto";
    const finalStyle = {
      fontFamily: fontFamily,
      fontSize: `${fontSize}px`,
      color: fontColor,
      stroke: fontColor,
      strokeThickness: 0,
      shadow: {
        offsetX: 0,
        offsetY: 0,
        color: fontColor,
        blur: 0,
        stroke: false,
        fill: false,
      },
    };
    for (let row = 0; row < gridSize; row++) {
      // Initialize each row if not already defined
      if (!letterTexts[row]) {
        letterTexts[row] = [];
      }
      for (let col = 0; col < gridSize; col++) {
        const x = col * cellSize + cellSize * 0.5;
        const y = row * cellSize + cellSize * 0.5;
        const fontSize = Math.floor(cellSize * 0.6);
        const letterObj = scene.add
          .text(x, y, gridMatrix[row][col].letter, {
            fontFamily: "Arial",
            fontSize: `${fontSize}px`,
            color: "#000",
          })
          .setOrigin(0.5);
        letterTexts[row][col] = letterObj;
      }
    }
  }

  function startGameTimer() {
    if (!gameTimerID) {
      console.log("::Entered");
      gameTimerID = setInterval(() => {
        elapsedTime++;
        document.getElementById("timerDisplay").innerText =
          formatTime(elapsedTime);
      }, 1000);
    }
  }

  function stopGameTimer() {
    if (gameTimerID) {
      clearInterval(gameTimerID);
      gameTimerID = null;
      console.log("Timer stopped at", elapsedTime, "seconds.");
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
    const higlightedCellTextColor = customStyles
      ? customStyles["higlightedCellTextColor"]
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
    if (
      scene.wordFoundSound &&
      typeof scene.wordFoundSound.play === "function"
    ) {
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

    if (guessedWord && !foundWords.includes(guessedWord)) {
      foundWords.push(guessedWord);
    }

    // If all words have been found, show the completion message.
    // if (foundWords.length === wordData.length) {
    //   console.log("::guessedWord1", guessedWord);
    //   showCompletionMessage();
    // }
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
        closeBtn.onclick = (event) => {
          event.preventDefault();
          // Fade out the modal
          modal.style.opacity = 0;
          setTimeout(() => {
            modal.style.display = "none";
            // Stop timer on modal close
            stopGameTimer();
            // NEW CODE: Destroy the game instance if it exists and refresh the grid.
            if (gameInstance) {
              gameInstance.destroy(true);
              gameInstance = null;
            }
            $(checkBoxElement).prop("checked", false);
            updateWordData(); // Reinitialize the grid with current word data
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
    const higlightedCellTextColor = customStyles
      ? customStyles["higlightedCellTextColor"]
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
  function updateWordListUI(foundWord, foundWordsCount, wordList) {
    foundWord = foundWord ? foundWord.toLowerCase() : "";
    const foundItem = document.getElementById(`word-${foundWord}`);
    console.log("::foundItem", foundWord);
    if (foundItem) {
      foundItem.classList.add("found");
    }

    if (foundWordsCount === wordList.length) {
      stopGameTimer();
      elapsedTime = 0;
      showCompletionMessage();
    }
    // updateWordData();
  }

  function computeEffectiveGridSize(wordData) {
    if (!Array.isArray(wordData) || wordData.length === 0) return 10; // Default minimum grid size
    let longest = 0,
      totalLetters = 0;
    wordData.forEach((word) => {
      // Convert non-string values to string (or handle them appropriately)
      const str = typeof word === "string" ? word : String(word);
      const trimmed = str.trim();
      longest = Math.max(longest, trimmed.length);
      totalLetters += trimmed.length;
    });
    const heuristic = Math.ceil(Math.sqrt(totalLetters));
    const effectiveGridSize = Math.max(longest, heuristic, 6);
    console.log("Effective Grid Size:", effectiveGridSize, longest, heuristic);
    return effectiveGridSize;
  }

  // --- NEW: Show a "Well Done!" banner
  // function showCompletionBanner() {
  //   const banner = document.getElementById("completionBanner");
  //   if (banner) {
  //     banner.style.display = "block";
  //     banner.style.opacity = "1"; // in case you want a fade-in effect
  //   }
  // }
  if (typeof frontendData !== "undefined") {
    const shuffleElement = document.getElementById(frontendData.shuffleElement);
    if (shuffleElement) {
      shuffleElement.addEventListener("click", function (event) {
        event.preventDefault();
        stopGameTimer(); // Stop timer before destroying the game
        if (gameInstance) {
          gameInstance.destroy(true);
          gameInstance = null;
        }
        updateWordData();
        $(checkBoxElement).prop("checked", false);
      });
    }
  } else {
    console.log("::enterd outside of conditional block");
  }

  // Helper function to format seconds as hh:mm:ss
  function formatTime(seconds) {
    const hrs = Math.floor(seconds / 3600);
    const mins = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;
    // Format with leading zeros if necessary
    const hrsStr = hrs > 0 ? hrs + ":" : "";
    const minsStr = (hrs > 0 && mins < 10 ? "0" : "") + mins;
    const secsStr = (secs < 10 ? "0" : "") + secs;
    return hrsStr + minsStr + ":" + secsStr;
  }
});
