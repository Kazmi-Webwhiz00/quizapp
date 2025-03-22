// grid-renderer.js
let renderWorker = null;

export function initializeGridRenderer() {
  if (!renderWorker) {
    renderWorker = new Worker("grid-render-worker.js");
  }
  return renderWorker;
}

export function cleanupGridRenderer() {
  if (renderWorker) {
    renderWorker.terminate();
    renderWorker = null;
  }
}

// Use this function to efficiently render the grid with the worker
export function renderGridWithWorker(
  scene,
  gridSize,
  cellSize,
  gridMatrix,
  previousMatrix = null
) {
  return new Promise((resolve, reject) => {
    const worker = initializeGridRenderer();

    // Set up event listeners
    const messageHandler = function (e) {
      const { type, result } = e.data;

      if (type === "renderingPlan") {
        worker.removeEventListener("message", messageHandler);

        // Apply the rendering plan
        applyRenderingPlan(scene, result, cellSize, gridSize, gridMatrix);
        resolve();
      }
    };

    worker.addEventListener("message", messageHandler);

    // Send the request to worker
    worker.postMessage({
      operation: "optimizeGridRendering",
      data: {
        gridSize,
        cellSize,
        gridMatrix,
        previousMatrix,
      },
    });
  });
}

// Function to calculate positions for all grid cells
export function calculateGridPositionsWithWorker(gridSize, cellSize) {
  return new Promise((resolve, reject) => {
    const worker = initializeGridRenderer();

    const messageHandler = function (e) {
      const { type, result } = e.data;
      if (type === "positions") {
        worker.removeEventListener("message", messageHandler);
        resolve(result);
      }
    };

    worker.addEventListener("message", messageHandler);

    worker.postMessage({
      operation: "calculateGridPositions",
      data: {
        gridSize,
        cellSize,
      },
    });
  });
}

// Function to get letter styles
export function prepareLetterStylesWithWorker(gridSize, cellSize, gridMatrix) {
  return new Promise((resolve, reject) => {
    const worker = initializeGridRenderer();

    const messageHandler = function (e) {
      const { type, result } = e.data;
      if (type === "styles") {
        worker.removeEventListener("message", messageHandler);
        resolve(result);
      }
    };

    worker.addEventListener("message", messageHandler);

    worker.postMessage({
      operation: "prepareLetterStyles",
      data: {
        gridSize,
        cellSize,
        gridMatrix,
      },
    });
  });
}

// Apply the rendering plan from the worker
function applyRenderingPlan(
  scene,
  renderingPlan,
  cellSize,
  gridSize,
  gridMatrix
) {
  const letterTexts = window.letterTexts || [];

  // Create styles once
  const fontSize = Math.floor(cellSize * 0.5);
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

  if (renderingPlan.type === "full") {
    // Full rendering in chunks for better performance
    renderingPlan.chunks.forEach((chunk, index) => {
      setTimeout(() => {
        renderGridChunk(
          scene,
          chunk.startRow,
          chunk.endRow,
          gridSize,
          cellSize,
          gridMatrix,
          letterTexts,
          finalStyle
        );
      }, chunk.delay);
    });
  } else {
    // Just update specific cells
    renderingPlan.updates.forEach(({ row, col }) => {
      updateGridCell(
        scene,
        row,
        col,
        cellSize,
        gridMatrix,
        letterTexts,
        finalStyle
      );
    });
  }

  window.letterTexts = letterTexts;
  scene.letterTexts = letterTexts;
}

// Render a chunk of the grid (a few rows at a time)
function renderGridChunk(
  scene,
  startRow,
  endRow,
  gridSize,
  cellSize,
  gridMatrix,
  letterTexts,
  textStyle
) {
  const cellHalfSize = cellSize * 0.5;

  for (let row = startRow; row <= endRow; row++) {
    if (!letterTexts[row]) {
      letterTexts[row] = [];
    }

    for (let col = 0; col < gridSize; col++) {
      updateGridCell(
        scene,
        row,
        col,
        cellSize,
        gridMatrix,
        letterTexts,
        textStyle
      );
    }
  }
}

// Update a single grid cell
function updateGridCell(
  scene,
  row,
  col,
  cellSize,
  gridMatrix,
  letterTexts,
  textStyle
) {
  const cellHalfSize = cellSize * 0.5;
  const x = col * cellSize + cellHalfSize;
  const y = row * cellSize + cellHalfSize;

  // Clean up old objects
  if (letterTexts[row] && letterTexts[row][col]) {
    letterTexts[row][col].destroy();
  }

  // Create letter text
  if (!letterTexts[row]) {
    letterTexts[row] = [];
  }

  // Get letter from matrix
  const letter =
    gridMatrix && gridMatrix[row] && gridMatrix[row][col]
      ? gridMatrix[row][col].letter
      : "";

  // Create text object
  const letterObj = scene.add.text(x, y, letter, textStyle).setOrigin(0.5);

  // Add interactive behavior
  letterObj.setInteractive();

  // Add hover effects
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
  letterObj.setData("row", row);
  letterObj.setData("col", col);
  letterTexts[row][col] = letterObj;
}
