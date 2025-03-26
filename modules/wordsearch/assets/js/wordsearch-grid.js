jQuery(document).ready(function ($) {
  let localizedEntries = [];
  let wordData = [];
  let gameInstance = null;
  let previousFinalEntriesStr = JSON.stringify([]);
  let finalEntries = [];
  let letterTexts = [];
  let cookieEntries = [];
  let previousGridSize = 0;
  // Global variable to hold the timer ID.
  // window.wordsearchGridTimerId = null;
  // Global timer ID for grid updates (or game clock)
  let gameTimerID = null;
  // Global variable to store elapsed time (in seconds, for example)
  let elapsedTime = 0;
  let customStyles = [];

  if (typeof wordSearchData !== "undefined") {
    localizedEntries = JSON.parse(wordSearchData.entries || "[]");
  }

  if (typeof frontendData !== "undefined") {
    cookieEntries = JSON.parse(frontendData.entries || "[]");
  } else {
    cookieEntries = [];
  }
  customStyles =
    typeof wordSearchData !== "undefined"
      ? wordSearchData["gridStyles"]
      : frontendData["gridStyles"];

  const rawData =
    typeof frontendData !== "undefined" ? frontendData.entries : [];
  if (rawData) {
    try {
      const parsed = JSON.parse(rawData);
      if (Array.isArray(parsed) && parsed.length > 0) {
        finalEntries = parsed;
      }
    } catch (e) {
      console.log("Error parsing initial wordsearch_entries cookie:", e);
    }
  }

  // 2) If we have entries, initialize the puzzle right away
  if (finalEntries.length > 0) {
    updateFinalEntries(finalEntries); // This calls updateWordData -> which calls waitForWordSearch
    waitForWordSearch(wordData);
  }

  if (!gameInstance) {
    gameInstance = createWordSearchGame({
      containerId: "game-container",
      puzzleOptions: {
        // start with an empty word list
        // You can include other default puzzle options as needed.
      },
      onGameReady: function (scene) {
        console.log("Default empty game instance created.");
      },
    });
  }

  finalEntries = mergeEntries(localizedEntries, cookieEntries);

  // 2) Merge them for the first time
  // finalEntries = mergeEntries(localizedEntries, cookieEntries);

  // Determine context first
  const isAdmin = typeof wordSearchData !== "undefined";

  // 3) Initialize the puzzle with whatever we have
  if (finalEntries.length > 0) {
    updateWordData(); // This will create the game
  } else {
    console.log("No initial finalEntries. Waiting for new entries...");
  }

  /************************************************************
   * 2) Clear Cookie on "Add New WordSearch" Page
   ************************************************************/
  // if (window.location.href.indexOf("post-new.php") !== -1) {
  //   clearCookie("wordsearch_entries");
  //   console.log("Cleared cookie: wordsearch_entries");
  // }

  $(document).on("wordsearchEntriesUpdated", function (event, updatedEntries) {
    // Avoid unnecessary console logs in production

    // Use a more efficient change detection approach
    let localizedEntriesChanged = false;
    let cookieEntriesChanged = false;

    // Check if localized entries have changed
    if (typeof wordSearchData !== "undefined" && wordSearchData.entries) {
      try {
        const newLocalizedEntries = JSON.parse(wordSearchData.entries);
        // Use a simple length comparison first (faster)
        if (newLocalizedEntries.length !== localizedEntries.length) {
          localizedEntries = newLocalizedEntries;
          localizedEntriesChanged = true;
        } else {
          // Only do deep comparison if lengths match
          if (
            JSON.stringify(newLocalizedEntries) !==
            JSON.stringify(localizedEntries)
          ) {
            localizedEntries = newLocalizedEntries;
            localizedEntriesChanged = true;
          }
        }
      } catch (e) {
        console.error("Error parsing localized data:", e);
      }
    }

    // Process cookie entries
    try {
      const newCookieEntries = updatedEntries.data ? updatedEntries.data : [];
      // Use a simple length comparison first
      if (newCookieEntries.length !== cookieEntries.length) {
        cookieEntries = newCookieEntries;
        cookieEntriesChanged = true;
      } else if (newCookieEntries.length > 0) {
        // Only do deeper comparison if necessary
        if (
          JSON.stringify(newCookieEntries) !== JSON.stringify(cookieEntries)
        ) {
          cookieEntries = newCookieEntries;
          cookieEntriesChanged = true;
        }
      }
    } catch (e) {
      console.error("Error parsing cookie data:", e);
    }

    // Only process if something actually changed
    if (localizedEntriesChanged || cookieEntriesChanged) {
      const merged = mergeEntries(localizedEntries, cookieEntries);

      // Skip stringification if we can directly detect changes
      if (merged.length === 0) {
        if (finalEntries.length !== 0) {
          finalEntries = [];
          updateFinalEntries([]);
        }
      } else {
        // Check if we need to update
        const needsUpdate =
          finalEntries.length !== merged.length ||
          JSON.stringify(merged) !== previousFinalEntriesStr;

        if (needsUpdate) {
          previousFinalEntriesStr = JSON.stringify(merged);
          updateFinalEntries(merged);

          // Optimize game instance updates
          if (gameInstance) {
            const scene = gameInstance.scene.scenes[0];
            if (scene && scene.letterTexts && scene.letterTexts.length > 0) {
              updateGridBasedOnWords(merged, scene, scene.letterTexts);
            } else {
              // Use requestAnimationFrame instead of setTimeout for better performance
              requestAnimationFrame(() => {
                if (
                  scene &&
                  scene.letterTexts &&
                  scene.letterTexts.length > 0
                ) {
                  updateGridBasedOnWords(merged, scene, scene.letterTexts);
                }
              });
            }
          }
        }
      }
    }
  });

  /**
   * Merges two arrays of objects by unique 'id'.
   */
  // Merge by unique 'id'
  function updateFinalEntries(newEntries) {
    finalEntries = newEntries;

    updateWordData(); // Your custom function that regenerates the word list, etc.
  }

  function mergeEntries(arrayA, arrayB) {
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
    // Create word data more efficiently
    const newWordData =
      finalEntries.length > 0 ? new Array(finalEntries.length) : [];

    // Use a standard for loop instead of map for better performance
    if (finalEntries.length > 0) {
      for (let i = 0; i < finalEntries.length; i++) {
        newWordData[i] = finalEntries[i].wordText.toUpperCase();
      }
    }

    // Only update if there's a change
    if (JSON.stringify(wordData) !== JSON.stringify(newWordData)) {
      wordData = newWordData;

      // Update the word list in the UI
      renderWordList(wordData);

      // Cache DOM reference
      const containerEl = document.getElementById("game-container");

      // Only destroy if necessary
      if (gameInstance) {
        gameInstance.destroy(true);
        gameInstance = null;
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
      if (wordData.length > 0) {
        // Calculate needed grid size
        const estimatedGridSize = computeEffectiveGridSize(wordData);

        // Validate words against this grid size
        const validationResult = validateWords(wordData, estimatedGridSize);

        if (!validationResult.valid) {
          // Handle problematic words by filtering them out
          const filteredWordData = handleWordOverflow(
            wordData,
            validationResult
          );

          // Only continue if we have remaining words
          if (filteredWordData.length > 0) {
            // Use requestIdleCallback if available, otherwise fallback to setTimeout
            if (window.requestIdleCallback) {
              requestIdleCallback(() => waitForWordSearch(filteredWordData));
            } else {
              setTimeout(() => waitForWordSearch(filteredWordData), 0);
            }
          }
        } else {
          // All words are valid, proceed normally
          if (window.requestIdleCallback) {
            requestIdleCallback(() => waitForWordSearch(wordData));
          } else {
            setTimeout(() => waitForWordSearch(wordData), 0);
          }
        }
      }
    }
  }

  function validateWords(wordData, gridSize) {
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

  /**
   * Handle word overflow errors gracefully
   * @param {Array} wordData - Original word data
   * @param {Object} validationResult - Result from validateWords function
   * @return {Array} - Filtered word data with long words removed
   */
  function handleWordOverflow(wordData, validationResult) {
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

  function waitForWordSearch(wordData, retries = 5) {
    // Quick validation
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
      const effectiveGridSize = computeEffectiveGridSize(wordData);

      // Safety check - ensure grid is big enough
      if (effectiveGridSize < longestLength) {
        console.error(
          `Grid size ${effectiveGridSize} is too small for word "${longestWord}" (length ${longestLength})`
        );
        // Force grid to be at least as big as longest word + 2
        effectiveGridSize = longestLength + 2;
      }

      // previousGridSize = effectiveGridSize;

      // Clean up old game instance
      if (gameInstance) {
        gameInstance.destroy(true);
        gameInstance = null;
      }

      // Create game with optimized settings
      gameInstance = createWordSearchGame({
        containerId: "game-container",
        puzzleOptions: {
          gridSize: effectiveGridSize,
          words: wordData,
          debug: false,
          orientations: ["horizontal", "vertical", "diagonal"],
          // Addition of performance-optimizing parameters
          maxAttempts: 20, // Limit placement attempts per word
          preferOverlap: true, // Prefer overlapping letters for denser puzzles
          fillBlanks: true, // Fill in remaining cells after word placement
          maxGridGrowth: 5, // Allow grid to grow if needed, but limit it
        },
      });
    } catch (error) {
      console.error("Error creating word search:", error);
    }
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
    const defaultPuzzleOpts = {
      directions: ["W", "N", "WN", "EN"],
      orientations: ["horizontal", "vertical", "diagonal"],
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
    // Make sure this div stays detached
    const ws = new WordSearch(tempDiv, mergedPuzzleOptions);
    const gridMatrix = ws.matrix;
    // Explicitly discard the tempDiv (though JS garbage collection would handle this)
    tempDiv.remove();
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
    // let cellSize;
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
        updateGridSize(
          previousGridSize,
          scene,
          scene.letterTexts,
          gridMatrix,
          true
        );
      }
    }

    function preload() {
      // Load assets as needed
      this.load.audio(
        "wordFound",
        frontendData.url + "assets/audio/word-matched.mp3"
      );
      this.load.audio(
        "letterHover",
        frontendData.url + "assets/audio/hover.mp3"
      );
    }

    function create() {
      this.lineGraphics = this.add.graphics();
      this.highlightGraphics = this.add.graphics();
      const scene = this;
      letterTexts = [];
      scene.letterTexts = letterTexts;
      // const fontColor = customStyles ? customStyles["fontColor"] : "#000";
      // const fontFamily = customStyles ? customStyles["fontFamily"] : "Roboto";
      // Update grid size for the first time (using mergedPuzzleOptions.gridSize)
      // destroyAllTextObjects(scene);

      // destroyAllTextObjects(scene);

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
      // const fontSize = Math.floor(cellSize * 0.5);
      letterTexts = [];

      // Render grid letters
      // for (let row = 0; row < gridSize; row++) {
      //   if (!letterTexts[row]) {
      //     letterTexts[row] = [];
      //   }
      //   for (let col = 0; col < gridSize; col++) {
      //     if (letterTexts[row][col]) {
      //       letterTexts[row][col].destroy();
      //     }
      //     const { letter } = gridMatrix[row][col];
      //     const x = col * cellSize + cellSize * 0.7;
      //     const y = row * cellSize + cellSize * 0.7;
      //     const textObj = scene.add
      //       .text(x, y, letter, finalStyle)
      //       .setOrigin(0.5);
      //     // Ensure no stroke/shadow if previously set
      //     textObj.setStroke(fontColor, 0); // stroke color + thickness=0
      //     textObj.setShadow(0, 0, fontColor, 0, false, false); // Disable any shadow
      //     textObj.setColor(fontColor); // Re-apply solid color
      //     textObj.setDepth(10);
      //     textObj.setData("row", row);
      //     textObj.setData("col", col);
      //     letterTexts[row][col] = textObj;
      //   }
      // }

      updateGridSize(
        mergedPuzzleOptions.gridSize,
        scene,
        letterTexts,
        gridMatrix,
        true
      ); // This calls resizeGame internally

      // Start a timer only if it's not already running
      startGameTimer();

      // POINTER EVENTS
      scene.input.on("pointerdown", (pointer) => {
        scene.lineGraphics.clear();
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
        // Clear previous line
        scene.lineGraphics.clear();
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
        // dynamicCtx.beginPath();
        // dynamicCtx.moveTo(startPoint.x, startPoint.y);
        // dynamicCtx.lineTo(currentX, currentY);
        // dynamicCtx.strokeStyle = "rgba(184, 134, 11, 0.6)";
        // dynamicCtx.lineWidth = cellSize * 0.8;
        // dynamicCtx.lineCap = "round";
        // dynamicCtx.stroke();
        scene.lineGraphics.lineStyle(cellSize * 0.8, 0xb8860b, 0.6);
        scene.lineGraphics.beginPath();
        scene.lineGraphics.moveTo(startPoint.x, startPoint.y);
        scene.lineGraphics.lineTo(pointer.x, pointer.y);
        scene.lineGraphics.strokePath();
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
        // window.startWordsearchGridTimer();
      }

      if (typeof frontendData !== "undefined") {
        checkBoxElement = document.getElementsByClassName(
          frontendData.checkBoxElement
        );
        if (checkBoxElement) {
          $(checkBoxElement).off("change"); // Remove old handlers
          $(checkBoxElement).on("change", function () {
            showAnswers = $(this).is(":checked");
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

    // (Optional) If you have a dedicated updateGridSize function that calls resizeGame:
    // Modified updateGridSize function
    function updateGridSize(
      newSize,
      scene,
      letterTexts,
      gridMatrix,
      forceUpdate = false
    ) {
      // This function is already simple so no major changes needed
      // but we can add a check to avoid unnecessary updates
      if (newSize !== previousGridSize || forceUpdate) {
        previousGridSize = newSize;
        resizeGame(
          scene.sys.game.config.width,
          scene.sys.game.config.height,
          scene,
          letterTexts,
          newSize,
          gridMatrix
        );
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
                animateMatch(scene, matchedCells, letterTexts, cellSize);

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
      // Now that the scene is ready, register your resize listener with debouncing
      window.addEventListener(
        "resize",
        debouncedResize(() => handleResize(), 250)
      );
    }

    function update() {
      // Any real-time updates or animations
    }
    return phaserGame;
  }

  // Modified updateGridBasedOnWords function
  function updateGridBasedOnWords(newWordList, scene, letterTexts) {
    // Use the optimized grid renderer
    updateGridRenderer({
      newWordList,
      scene,
      letterTexts,
      gridMatrix,
    });
  }

  function getDynamicCanvasSize(containerId, gridSize, maxCanvasSize = 800) {
    const container = document.getElementById(containerId);
    if (!container) {
      console.error(`Container with id ${containerId} not found.`);
      // Fallback to a default size if container is missing
      return { width: 600, height: 600 };
    }
    // Use the container width to determine a base cell size
    const containerWidth = container.clientWidth || maxCanvasSize;

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

  //   // Reset the letterTexts array to ensure clean slate
  //   if (typeof letterTexts !== "undefined") {
  //     letterTexts = [];
  //   }
  // }

  function resizeGame(
    newWidth,
    newHeight,
    scene,
    letterTexts = [],
    gridSize,
    gridMatrix
  ) {
    // Cache calculations and avoid repeated work
    const gameCanvas = scene.sys.game.canvas;
    gameCanvas.width = newWidth;
    gameCanvas.height = newHeight;

    // Remove children more efficiently
    scene.children.each((child) => {
      child.destroy();
    });

    // Pre-calculate shared values
    const cellSize = Math.min(newWidth, newHeight) / gridSize;
    const fontSize = Math.floor(cellSize * 0.5);
    const cellHalfSize = cellSize * 0.5;

    // Create style object once
    // UPDATED: Modified colors to maintain the gold theme but with better contrast
    const fontColor = "#473214";
    const fontFamily = "Georgia, serif";
    const finalStyle = {
      fontFamily: "Georgia, serif",
      fontSize: `${fontSize}px`,
      color: "#473214",
      fontWeight: "bold",
      stroke: "#ffffff",
      strokeThickness: 0.5,
      shadow: {
        offsetX: 1,
        offsetY: 1,
        color: "rgba(0,0,0,0.08)",
        blur: 1,
        stroke: false,
        fill: true,
      },
    };

    // Create backgrounds more efficiently
    const backgroundGradient = scene.add.graphics();
    backgroundGradient.fillStyle(0xf5e9d1, 1);
    backgroundGradient.fillGradientStyle(
      0xf5d992,
      0xe6ba6c,
      0xdca745,
      0xc89836,
      1,
      1,
      1,
      1
    );
    backgroundGradient.fillRect(0, 0, newWidth, newHeight);

    // Draw grid pattern more efficiently
    const gridPattern = scene.add.graphics();
    gridPattern.lineStyle(1, 0xd6a651, 0.2);

    // Use a single path for all horizontal lines
    gridPattern.beginPath();
    for (let i = 0; i <= gridSize; i++) {
      gridPattern.moveTo(0, i * cellSize);
      gridPattern.lineTo(newWidth, i * cellSize);
    }
    gridPattern.strokePath();

    // Use a single path for all vertical lines
    gridPattern.beginPath();
    for (let i = 0; i <= gridSize; i++) {
      gridPattern.moveTo(i * cellSize, 0);
      gridPattern.lineTo(i * cellSize, newHeight);
    }
    gridPattern.strokePath();

    // Batch cell backgrounds
    const cellBackgrounds = scene.add.graphics();
    for (let row = 0; row < gridSize; row++) {
      for (let col = 0; col < gridSize; col++) {
        const isEvenCell = (row + col) % 2 === 0;
        cellBackgrounds.fillStyle(isEvenCell ? 0xecd8b3 : 0xf5e9d1, 1);
        cellBackgrounds.fillRect(
          col * cellSize,
          row * cellSize,
          cellSize,
          cellSize
        );
      }
    }

    // Load sound effects only once
    if (!scene.sound.get("letterHover")) {
      scene.load.audio("letterHover", "assets/audio/hover.mp3");
      scene.load.once("complete", function () {
        console.log("Sound loaded successfully");
      });
      scene.load.start();
    }

    // Create letter objects more efficiently
    for (let row = 0; row < gridSize; row++) {
      if (!letterTexts[row]) {
        letterTexts[row] = [];
      }

      for (let col = 0; col < gridSize; col++) {
        // Clean up old objects
        if (letterTexts[row][col]) {
          letterTexts[row][col].destroy();
        }

        const x = col * cellSize + cellHalfSize;
        const y = row * cellSize + cellHalfSize;

        // Create circle highlights in batches
        scene.add.circle(x, y, cellSize * 0.4, 0xffffff, 0.1);

        // Create letter text
        const letterObj = scene.add
          .text(x, y, gridMatrix[row][col].letter, finalStyle)
          .setOrigin(0.5);

        // Add interactive behavior
        letterObj.setInteractive();

        // Use event emitter pattern for better performance
        letterObj.on("pointerover", function () {
          this.setScale(1.1);
          scene.sound.play("letterHover", { volume: 0.5 });
          this.setStroke("#FF9900", 2.5);
          this.setShadow(2, 2, "#ffd800", 6, true, true);
          this.setColor("#8B4513");
        });

        letterObj.on("pointerout", function () {
          this.setScale(1.0);
          this.setStroke("#b8860b", 0.7);
          this.setShadow(1, 1, "#ffd800", 2, false, true);
          this.setColor("#473214");
        });

        // Store data
        letterTexts[row][col] = letterObj;
        letterObj.setData("row", row);
        letterObj.setData("col", col);
      }
    }
  }

  function startGameTimer() {
    if (!gameTimerID) {
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
    const persistentCtx = persistentCanvas.getContext("2d", {
      willReadFrequently: true,
      alpha: true,
    });
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
    const dynamicCtx = dynamicCanvas.getContext("2d", {
      willReadFrequently: true,
      alpha: true,
    });
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
    // const higlightedCellTextColor = customStyles
    //   ? customStyles["higlightedCellTextColor"]
    //   : "#fff";
    const higlightedCellTextColor = "#FFFFFF";
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
    // Enhanced color palette - warm, vibrant colors that complement the gold theme
    const colorPalette = [
      "hsla(28, 90%, 50%, 0.4)", // Orange
      "hsla(350, 90%, 55%, 0.4)", // Red
      "hsla(190, 90%, 50%, 0.4)", // Teal
      "hsla(120, 70%, 45%, 0.4)", // Green
      "hsla(270, 80%, 55%, 0.4)", // Purple
    ];

    return colorPalette[Math.floor(Math.random() * colorPalette.length)];
  }

  // Modified renderWordList function
  function renderWordList(wordData) {
    const listContainer = document.getElementById("wordList");
    if (!listContainer) return;

    listContainer.innerHTML = ""; // Clear any existing content

    if (wordData.length > 0) {
      // Create operations array for batch updates
      const operations = [];

      wordData.forEach((word) => {
        operations.push((fragment) => {
          const formattedWord = word.length > 0 ? word.toLowerCase() : "";
          const li = document.createElement("li");
          li.id = `word-${formattedWord}`;
          li.textContent = formattedWord;
          fragment.appendChild(li);
        });
      });

      // Apply all DOM updates at once
      batchDomUpdates(operations, listContainer);
    }
  }

  // --- NEW: Strikes out the found word in the #wordList
  function updateWordListUI(foundWord, foundWordsCount, wordList) {
    foundWord = foundWord ? foundWord.toLowerCase() : "";
    const foundItem = document.getElementById(`word-${foundWord}`);
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

  // This function should be added to improve grid size computation
  function computeEffectiveGridSize(wordData) {
    // Exit early if no words
    if (!wordData || wordData.length === 0) {
      return 0;
    }

    // Find the longest word length
    let maxLength = 0;
    for (let i = 0; i < wordData.length; i++) {
      const wordLength = wordData[i].length;
      maxLength = Math.max(maxLength, wordLength);
    }

    // Basic grid size calculation - ensure minimum size is at least maxLength
    const baseSize = Math.max(
      maxLength,
      Math.ceil(Math.sqrt(wordData.length * 15))
    );

    // Add a buffer to ensure enough space for word placement (especially for diagonal words)
    // Diagonal words need more space - at minimum, the longest word should fit diagonally
    const diagonalBuffer = Math.ceil(maxLength * 0.4); // Add 40% buffer for diagonal placement
    let gridSize = baseSize + diagonalBuffer;

    // Ensure the grid is at least 2 cells larger than the longest word
    // This gives the algorithm space to place words and generate a proper puzzle
    gridSize = Math.max(gridSize, maxLength + 2);

    // Cap the grid size to prevent performance issues, but ensure it's still big enough
    const maxGridSize = Math.min(
      30,
      Math.max(maxLength + 2, wordData.length * 2)
    );
    gridSize = Math.min(gridSize, maxGridSize);

    return gridSize;
  }

  // Throttle expensive operations
  function throttle(func, limit) {
    let inThrottle;
    return function () {
      const args = arguments;
      const context = this;
      if (!inThrottle) {
        func.apply(context, args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  }

  // Debounce window resize events
  const debouncedResize = function (callback, delay = 250) {
    let timeoutId;
    return function (...args) {
      if (timeoutId) {
        clearTimeout(timeoutId);
      }
      timeoutId = setTimeout(() => {
        callback.apply(this, args);
        timeoutId = null;
      }, delay);
    };
  };

  // Use a web worker for heavy computations
  function createWordPlacementWorker() {
    const workerCode = (self.onmessage = function (e) {
      const { words, gridSize, orientations } = e.data;
      // Compute word placements
      const placements = computePlacements(words, gridSize, orientations);
      self.postMessage(placements);
    });

    function computePlacements(words, gridSize, orientations) {
      // Implementation of word placement algorithm
      // This would be similar to what the WordSearch library does
      // but optimized for background processing
      return []; // Return computed placements
    }
    const blob = new Blob([workerCode], { type: "application/javascript" });
    return new Worker(URL.createObjectURL(blob));
  }

  // Example usage of the worker
  function setupWordPlacementWorker() {
    const worker = createWordPlacementWorker();

    worker.onmessage = function (e) {
      const placements = e.data;
      // Use the placements to update the grid
    };

    return worker;
  }

  // Optimize rendering with requestAnimationFrame
  // Optimized grid update using requestAnimationFrame
  function updateGridRenderer(gridData) {
    if (window.gridUpdateScheduled) {
      return;
    }

    window.gridUpdateScheduled = true;

    requestAnimationFrame(() => {
      const { newWordList, scene, letterTexts, gridMatrix } = gridData;
      const newGridSize = computeEffectiveGridSize(newWordList);

      // Check if grid size really changed to avoid unnecessary redraws
      if (newGridSize !== previousGridSize) {
        previousGridSize = newGridSize;
        // Update your puzzle options if needed
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

      window.gridUpdateScheduled = false;
    });
  }

  // Batch DOM updates for better performance
  function batchDomUpdates(operations, container) {
    // Use DocumentFragment for batch updates
    const fragment = document.createDocumentFragment();

    operations.forEach((op) => {
      // Each operation adds elements to the fragment
      op(fragment);
    });

    // Apply all changes at once
    container.appendChild(fragment);
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
