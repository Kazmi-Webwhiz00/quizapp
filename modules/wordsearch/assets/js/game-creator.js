import {
  getDynamicCanvasSize,
  resizeGame,
  debouncedResize,
  computeEffectiveGridSize,
} from "./grid-manager.js";
import {
  createCanvasLayers,
  getCellFromPoint,
  getXYFromCell,
  getCellsInLine,
  getStringFromCells,
} from "./utils.js";
import {
  highlightWord,
  animateMatch,
  updateWordListUI,
  autoSolvePuzzle,
  startGameTimer,
  showLoadingIndicator,
  updateWordData,
} from "./game-mechanics.js";

export function createWordSearchGame({
  containerId = "game-container",
  containerWidth = "100%",
  containerMaxWidth = "800px",
  puzzleOptions = {},
  canvasParentId = "game-container",
  onGameReady: localOnGameReady,
}) {
  let dynamicCanvas, persistentCanvas;
  const container = document.getElementById(
    containerId ? containerId : "game-container"
  );

  // console.log("::effectiveGridSize", effectiveGridSize);
  function updateGridSize(
    newSize,
    scene,
    letterTexts,
    gridMatrix,
    forceUpdate = false
  ) {
    // console.log("::Matrix", gridMatrix, scene, window.gridMatrix);
    // This function is already simple so no major changes needed
    // but we can add a check to avoid unnecessary updates
    const { width: newWidth, height: newHeight } = getDynamicCanvasSize(
      containerId,
      newSize,
      newSize < 10 ? 800 : 10000
    );

    const cellSize = Math.min(newWidth, newHeight) / newSize;
    window.cellSize = cellSize;

    console.log("::dynamicCanvas", dynamicCanvas.width);
    dynamicCanvas.width = newWidth;
    dynamicCanvas.height = newHeight;
    persistentCanvas.width = newWidth;
    persistentCanvas.height = newHeight;

    console.log("::newWidth6", letterTexts);

    if (newSize !== window.previousGridSize || forceUpdate) {
      window.previousGridSize = newSize;
      resizeGame(
        newWidth,
        newHeight,
        scene,
        letterTexts,
        newSize,
        window.gridMatrix
      );
    }
  }

  function initializeGame(newWidth) {
    if (window.gameInstance) return;
    console.time("::createWordSearchGame");
    console.log("::Starting createWordSearchGame");
    // Set the container's width dynamically
    console.time("::computeEffectiveGridSize");
    let effectiveGridSize = computeEffectiveGridSize(window.wordData);

    console.timeEnd("::computeEffectiveGridSize");
    console.log("::Effective grid size:", effectiveGridSize);

    const defaultPuzzleOpts = {
      gridSize: effectiveGridSize,
      directions: ["W", "N", "WN", "EN"],
      orientations: ["horizontal", "vertical", "diagonal"],
      words: window.wordData,
      preferOverlap: true,
      fillBlanks: true,
      debug: false,
    };
    // Merge user puzzle options with defaults

    let mergedPuzzleOptions = { ...defaultPuzzleOpts, ...puzzleOptions };
    mergedPuzzleOptions.words = window.wordData;
    mergedPuzzleOptions.gridSize = effectiveGridSize;
    // let canvasWidth = container.clientWidth;
    // window.canvasWidth = canvasWidth;
    // console.log("::canvasWidth6", canvasWidth);
    if (container) {
      // container.style.width = containerWidth;
      container.style.maxWidth =
        effectiveGridSize < 10 ? containerMaxWidth : "1000px";
    } else {
      console.error(`Container with id ${containerId} not found.`);
    }

    // Create the puzzle
    const tempDiv = document.createElement("div");
    // Make sure this div stays detached
    console.time("::WordSearchInstance");
    const ws = new WordSearch(tempDiv, mergedPuzzleOptions);
    console.timeEnd("::WordSearchInstance");

    // const gridMatrix = ws.matrix;
    // Store the gridMatrix globally for access across modules

    // Explicitly discard the tempDiv (though JS garbage collection would handle this)
    tempDiv.remove();
    const gridSize = mergedPuzzleOptions.gridSize;
    const wordList = mergedPuzzleOptions.words;
    // Calculate dynamic canvas size
    console.time("::getDynamicCanvasSize");
    // Calculate the dynamic canvas size

    const { width: dynamicWidth, height: dynamicHeight } = getDynamicCanvasSize(
      containerId,
      gridSize,
      effectiveGridSize < 10 ? 800 : 1000
    );
    console.timeEnd("::getDynamicCanvasSize");
    console.log(
      "::Dynamic canvas dimensions:",
      containerId,
      gridSize,
      effectiveGridSize < 10 ? 800 : 1000
    );

    // Internal states
    let isDrawing = false;
    let startPoint = null;
    let foundWordsCount = 0;

    // PHASER CONFIG
    const config = {
      type: Phaser.WEBGL,
      parent: containerId,
      width: dynamicWidth,
      height: dynamicHeight,
      backgroundColor: "#f5e9d1",
      antialias: true,
      transparent: false,
      powerPreference: "default",
      resolution: 1,
      scene: {
        preload,
        create,
        update,
      },
      // scale: {
      //   mode: Phaser.Scale.RESIZE, // Auto-adjust to parent container
      //   autoCenter: Phaser.Scale.CENTER_BOTH,
      // },
      render: {
        pixelArt: false,
        antialias: true,
        roundPixels: false,
        clearBeforeRender: true,
      },
    };

    console.time("::PhaserGameInitialization");
    // Initialize Phaser Game
    const phaserGame = new Phaser.Game(config);
    window.gameInstance = phaserGame;
    console.timeEnd("::PhaserGameInitialization");
    console.log("::Phaser game created");

    function preload() {
      // Load assets as needed
      this.load.audio(
        "wordFound",
        window.isAdmin
          ? frontendData.url + "assets/audio/word-matched.mp3"
          : wordSearchData.url + "assets/audio/word-matched.mp3"
      );
      this.load.audio(
        "letterHover",
        window.isAdmin
          ? frontendData.url + "assets/audio/hover.mp3"
          : wordSearchData.url + "assets/audio/hover.mp3"
      );
    }

    function create() {
      console.log("Scene create() called");
      const scene = this;
      window.scene = scene;
      scene.lineGraphics = this.add.graphics();
      scene.highlightGraphics = this.add.graphics();
      window.letterTexts = [];
      scene.letterTexts = window.letterTexts;
      scene.gridMatrix = window.gridMatrix;
      console.log("::matrix7", scene.gridMatrix); // Store gridMatrix in scene for reference

      if (this.cache.audio.exists("wordFound")) {
        if (this.wordFoundSound) {
          this.wordFoundSound.destroy(); // Destroy any existing instance
        }
        this.wordFoundSound = this.sound.add("wordFound");
      } else {
        console.error("Sound file is missing from the cache!");
      }

      // Create the dynamic & persistent canvases inside the container
      console.time("::createCanvasLayers");
      const { persistentCanvas: pCanvas, dynamicCanvas: dCanvas } =
        createCanvasLayers({
          game: this.game,
          parentId: canvasParentId,
        });
      persistentCanvas = pCanvas;
      dynamicCanvas = dCanvas;
      console.timeEnd("::createCanvasLayers");
      dynamicCanvas.width = dynamicWidth;
      dynamicCanvas.height = dynamicHeight;
      persistentCanvas.width = dynamicWidth;
      persistentCanvas.height = dynamicHeight;

      // Calculate cellSize from dynamic dimensions
      const cellSize = Math.min(dynamicWidth, dynamicHeight) / gridSize;
      window.letterTexts = [];

      console.time("::gridMatrix");
      ws.getMatrix().then((gridMatrix) => {
        // Place code that depends on gridMatrix here.
        window.gridMatrix = gridMatrix;
        scene.gridMatrix = gridMatrix;

        // Trigger a custom event once the gridMatrix is ready:
        console.time("::updateGridSize");
        // console.log("::canvasWidth1", canvasWidth);

        updateGridSize(
          mergedPuzzleOptions.gridSize,
          scene,
          window.letterTexts,
          window.gridMatrix,
          true
        );
        console.timeEnd("::updateGridSize");
        console.timeEnd("::gridMatrix");
      });

      if (!window.isAdmin) {
        startGameTimer();
      } else {
        let gameHeaderElements = document.querySelectorAll(".game-header");
        gameHeaderElements.forEach((el) => {
          el.style.justifyContent = "center";
        });
      }

      // POINTER EVENTS
      scene.input.on("pointerdown", (pointer) => {
        scene.lineGraphics.clear();
        const dynamicCtx = dynamicCanvas.getContext("2d");
        dynamicCtx.clearRect(0, 0, dynamicCanvas.width, dynamicCanvas.height);
        // let newSize = computeEffectiveGridSize(window.wordData);
        // const { width: newWidth, height: newHeight } = getDynamicCanvasSize(
        //   (containerId = "game-container"),
        //   newSize,
        //   newSize < 10 ? 800 : 10000
        // );
        // const cellSize = Math.min(newWidth, newHeight) / newSize;

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
            window.cellSize,
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

        // let newSize = computeEffectiveGridSize(window.wordData);
        // const { width: newWidth, height: newHeight } = getDynamicCanvasSize(
        //   (containerId = "game-container"),
        //   newSize,
        //   newSize < 10 ? 800 : 10000
        // );
        // const cellSize = Math.min(newWidth, newHeight) / newSize;

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
        dynamicCtx.strokeStyle = "rgba(184, 134, 11, 0.6)";
        dynamicCtx.lineWidth = window.cellSize * 0.8;
        dynamicCtx.lineCap = "round";
        dynamicCtx.stroke();
      });

      scene.input.on("pointerup", () => {
        if (!startPoint) return;
        isDrawing = false;

        const dynamicCtx = dynamicCanvas.getContext("2d");
        dynamicCtx.clearRect(0, 0, dynamicCanvas.width, dynamicCanvas.height);

        // let newSize = computeEffectiveGridSize(window.wordData);
        // const { width: newWidth, height: newHeight } = getDynamicCanvasSize(
        //   (containerId = "game-container"),
        //   newSize,
        //   newSize < 10 ? 800 : 10000
        // );
        // const cellSize = Math.min(newWidth, newHeight) / newSize;

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

        const startCell = getCellFromPoint(
          startPoint,
          window.cellSize,
          gridSize
        );
        const endCell = getCellFromPoint(endPoint, window.cellSize, gridSize);
        const selectedCells = getCellsInLine(
          startCell.row,
          startCell.col,
          endCell.row,
          endCell.col
        );
        const guessedWord = getStringFromCells(
          selectedCells,
          window.gridMatrix
        );

        console.log("::guessedWord", guessedWord);

        const isMatch = window.wordData.includes(guessedWord);
        if (isMatch) {
          highlightWord(
            scene,
            selectedCells,
            persistentCanvas,
            window.cellSize,
            wordList,
            guessedWord,
            gridSize
          );
          animateMatch(
            scene,
            selectedCells,
            window.letterTexts,
            window.cellSize
          );

          foundWordsCount++;
          window.foundWords.push(guessedWord);
          updateWordListUI(guessedWord, foundWordsCount, window.wordData);
        }

        startPoint = null;
      });

      // Optional callback after setup
      if (typeof localOnGameReady === "function") {
        localOnGameReady(scene);
      }

      if (typeof frontendData !== "undefined") {
        var elements = document.getElementsByClassName(
          frontendData.checkBoxElement
        );

        function checkboxChangeHandler(e) {
          window.showAnswers = e.target.checked; // Equivalent to $(this).is(":checked")
          if (window.showAnswers) {
            autoSolvePuzzle(
              scene,
              window.gridMatrix,
              window.wordData,
              persistentCanvas,
              window.cellSize,
              gridSize,
              window.showAnswers
            );
          } else {
            updateWordData();
          }
        }

        // If there is at least one element, handle it
        if (elements.length > 0) {
          window.checkBoxElement = elements; // This is a collection of DOM elements

          // Remove any existing "change" event listener and add the new one
          Array.from(window.checkBoxElement).forEach(function (element) {
            element.removeEventListener("change", checkboxChangeHandler);
            element.addEventListener("change", checkboxChangeHandler);
          });
        }
      } else {
        console.log("::entered outside of conditional block");
      }
    }

    // Modified updateGridSize function
    function localOnGameReady(scene) {
      scene.letterTexts = window.letterTexts;
      // Now that the scene is ready, register your resize listener with debouncing
      // window.addEventListener("resize", handleResize);
    }

    function update() {
      // Any real-time updates or animations
    }
    console.timeEnd("::createWordSearchGame");
    return phaserGame;
  }
  const resizeObserver = new ResizeObserver((entries) => {
    for (let entry of entries) {
      const newWidth = entry.contentRect.width;
      window.newWidth = newWidth;
      let effectiveGridSize = computeEffectiveGridSize(window.wordData);

      // Only initialize once; afterwards, call your resize logic
      if (newWidth > 0) {
        if (!window.gameInstance && window.wordData) {
          initializeGame(newWidth);
        } else {
          // The game is already created, so just resize instead of recreating
          if (
            window.gameInstance.scene &&
            window.gameInstance.scene.scenes[0]
          ) {
            const scene = window.gameInstance.scene.scenes[0];
            // Force update true => triggers resizeGame in updateGridSize
            showLoadingIndicator();
            updateGridSize(
              effectiveGridSize,
              scene,
              scene.letterTexts,
              window.gridMatrix,
              true
            );
          }
        }
      }
    }
  });
  resizeObserver.observe(container);
}
