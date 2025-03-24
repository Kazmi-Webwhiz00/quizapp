export function createCanvasLayers({ game, parentId = "game-container" }) {
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

export function getXYFromCell(row, col, cellSize, gridSize) {
  const container = document.getElementById("game-container");
  const computedStyle = window.getComputedStyle(container);
  const paddingLeft = parseInt(computedStyle.paddingLeft, 10) || 0;
  const paddingTop = parseInt(computedStyle.paddingTop, 10) || 0;
  const xPadding =
    col === 0 ? paddingLeft / 2 : col === gridSize - 1 ? -(paddingLeft / 2) : 0;
  const yPadding =
    row === 0 ? paddingTop / 2 : row === gridSize - 1 ? -(paddingTop / 2) : 0;

  return {
    x: col * cellSize + cellSize * 0.5 + xPadding,
    y: row * cellSize + cellSize * 0.5 + yPadding,
  };
}

export function getCellFromPoint(point, cellSize, gridSize) {
  let col = Math.floor(point.x / cellSize);
  let row = Math.floor(point.y / cellSize);
  row = Math.max(0, Math.min(row, gridSize - 1));
  col = Math.max(0, Math.min(col, gridSize - 1));
  return { row, col };
}

export function getStringFromCells(cells, gridMatrix) {
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

export function getCellsInLine(r1, c1, r2, c2) {
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

export function getRandomTransparentColor() {
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

export function throttle(func, limit) {
  let inThrottle;
  return function (...args) {
    if (!inThrottle) {
      func.apply(this, args);
      inThrottle = true;
      setTimeout(() => (inThrottle = false), limit);
    }
  };
}

export function batchDomUpdates(operations, container) {
  // Use DocumentFragment for batch updates
  const fragment = document.createDocumentFragment();

  operations.forEach((op) => {
    // Each operation adds elements to the fragment
    op(fragment);
  });

  // Apply all changes at once
  container.appendChild(fragment);
}

export function checkWordInDirection(
  matrix,
  row,
  col,
  word,
  deltaRow,
  deltaCol
) {
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
    if (matrix[curRow][curCol].letter.toUpperCase() !== word[i].toUpperCase()) {
      return null;
    }
    matchedCells.push({ row: curRow, col: curCol });
  }

  // If we got here, the entire word matched
  return matchedCells;
}

export function createWordPlacementWorker() {
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

export function setupWordPlacementWorker() {
  const worker = createWordPlacementWorker();

  worker.onmessage = function (e) {
    const placements = e.data;
    // Use the placements to update the grid
  };

  return worker;
}

function extractGameColors() {
  // Initialize with safe default colors
  const colors = {
    header: "#2c3752",
    accent: "#D6A651",
    cellEven: "#ecd8b3",
    cellOdd: "#f5e9d1",
    cellBorder: "#d6a651",
    text: "#5c4012",
  };

  try {
    const gameInstance = window.gameInstance;
    if (gameInstance) {
      // Try to get color scheme from direct properties
      if (gameInstance.colorScheme) {
        Object.assign(colors, gameInstance.colorScheme);
      }

      // Additional extraction from scene objects if needed
      // (simplified for safety)
    }
  } catch (e) {
    console.warn("Error extracting game colors:", e);
    // Fall back to defaults if anything goes wrong
  }

  return colors;
}

/**
 * Converts game color formats (0xRRGGBB) to standard hex (#RRGGBB)
 */

/**
 * Converts hex color to RGB object for jsPDF
 */
function hexToRgbArray(hex) {
  const defaultColor = [0, 0, 0];
  try {
    if (!hex || typeof hex !== "string") return defaultColor;
    hex = hex.replace(/^#/, "");
    if (hex.length === 3) {
      hex = hex
        .split("")
        .map((ch) => ch + ch)
        .join("");
    }
    if (hex.length !== 6) return defaultColor;
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return [isNaN(r) ? 0 : r, isNaN(g) ? 0 : g, isNaN(b) ? 0 : b];
  } catch (e) {
    console.warn("Error converting color:", e);
    return defaultColor;
  }
}

function getTextureFillColor(texture) {
  const canvas = texture.getSourceImage();
  const ctx = canvas.getContext("2d");
  const pixelData = ctx.getImageData(0, 0, 1, 1).data;
  return [pixelData[0], pixelData[1], pixelData[2]];
}

export function downloadWordSearchAsPDF() {
  // Ensure jsPDF is loaded
  if (typeof window.jspdf === "undefined") {
    const jspdfScript = document.createElement("script");
    jspdfScript.src = window.isAdmin
      ? frontendData.url + "assets/library/jspdf.umd.min.js"
      : wordSearchData.url + "assets/library/jspdf.umd.min.js";
    document.head.appendChild(jspdfScript);
    jspdfScript.onload = downloadWordSearchAsPDF;
    return;
  }

  // Get game instance and grid data
  const gameInstance = window.gameInstance;
  if (!gameInstance) {
    console.error("Game instance not found");
    return;
  }
  const gridMatrix = gameInstance.gridMatrix || window.gridMatrix || [];
  if (!gridMatrix.length) {
    console.error("Grid matrix data not found");
    return;
  }

  // Retrieve scene and dynamic game colors
  const scene = gameInstance.scene.scenes[0];
  const gameColors = extractGameColors();
  const gridSize = gridMatrix.length;

  // Extract cell textures and their fill colors
  const evenTexture = scene.textures.get("evenCell");
  const oddTexture = scene.textures.get("oddCell");
  const evenRgb = getTextureFillColor(evenTexture);
  const oddRgb = getTextureFillColor(oddTexture);
  const borderRgb = hexToRgbArray(gameColors.cellBorder);
  const textRgb = hexToRgbArray(gameColors.text);

  // Retrieve word list
  const words = gameInstance.words || window.wordData || getWordsList();

  // Calculate dimensions based on canvas and grid content
  const canvasWidth = gameInstance.canvas?.width || 600;
  const baseCellSize = Math.min(35, Math.floor(canvasWidth / gridSize));
  const puzzleWidth = gridSize * baseCellSize;
  const puzzleHeight = gridSize * baseCellSize;
  const wordBlockHeight = 30;
  const totalWordListHeight = words.length * wordBlockHeight + 60;
  const margin = 60,
    headerHeight = 120,
    footerHeight = 30,
    gap = 40,
    pageHeightPadding = 200;
  const avgCharWidth = 8;
  const maxWordLength = words.reduce(
    (max, word) => Math.max(max, word.length),
    0
  );
  const wordListBoxWidth = Math.max(150, maxWordLength * avgCharWidth + 40);
  const contentHeight = Math.max(puzzleHeight, totalWordListHeight);
  const neededWidth = margin + puzzleWidth + gap + wordListBoxWidth + margin;
  const neededHeight =
    margin +
    headerHeight +
    contentHeight +
    footerHeight +
    margin +
    pageHeightPadding;

  // Create PDF with custom dimensions
  const pdf = new jspdf.jsPDF("portrait", "pt", [neededWidth, neededHeight]);

  // --- HEADER ---
  pdf.setFillColor(...hexToRgbArray(gameColors.header));
  pdf.rect(0, 0, neededWidth, headerHeight, "F");
  pdf.setFontSize(28);
  pdf.setTextColor(255, 255, 255);
  const titleText = "WORD SEARCH PUZZLE";
  const titleWidth = pdf.getTextWidth(titleText);
  pdf.text(titleText, (neededWidth - titleWidth) / 2, headerHeight / 2 + 10);
  pdf.setDrawColor(...hexToRgbArray(gameColors.accent));
  pdf.setLineWidth(2);
  pdf.line(margin, headerHeight + 10, neededWidth - margin, headerHeight + 10);

  // --- PUZZLE GRID ---
  const puzzleX = margin,
    puzzleY = headerHeight + margin,
    cellSize = baseCellSize;
  pdf.setFillColor(230, 230, 230);
  pdf.roundedRect(
    puzzleX - 5,
    puzzleY - 5,
    puzzleWidth + 10,
    puzzleHeight + 10,
    3,
    3,
    "F"
  );

  for (let row = 0; row < gridSize; row++) {
    for (let col = 0; col < gridSize; col++) {
      const isEvenCell = (row + col) % 2 === 0;
      const cellColor = isEvenCell ? evenRgb : oddRgb;
      pdf.setFillColor(...cellColor);
      pdf.rect(
        puzzleX + col * cellSize,
        puzzleY + row * cellSize,
        cellSize,
        cellSize,
        "F"
      );
    }
  }

  // Draw grid lines
  pdf.setDrawColor(...borderRgb);
  pdf.setLineWidth(0.5);
  for (let i = 0; i <= gridSize; i++) {
    pdf.line(
      puzzleX + i * cellSize,
      puzzleY,
      puzzleX + i * cellSize,
      puzzleY + puzzleHeight
    );
    pdf.line(
      puzzleX,
      puzzleY + i * cellSize,
      puzzleX + puzzleWidth,
      puzzleY + i * cellSize
    );
  }

  // Add letters to grid
  pdf.setFontSize(cellSize * 0.6);
  pdf.setTextColor(...textRgb);
  for (let row = 0; row < gridSize; row++) {
    for (let col = 0; col < gridSize; col++) {
      const cell = gridMatrix[row] && gridMatrix[row][col];
      if (cell) {
        const letter = cell.letter || "";
        const textWidth = pdf.getTextWidth(letter);
        const textX = puzzleX + col * cellSize + (cellSize - textWidth) / 2;
        const textY = puzzleY + row * cellSize + cellSize * 0.7;
        pdf.text(letter, textX, textY);
      }
    }
  }

  // --- WORD LIST ---
  const wordListX = puzzleX + puzzleWidth + gap,
    wordListY = puzzleY;
  pdf.rect(wordListX, wordListY, wordListBoxWidth, 40, "F");
  pdf.setFontSize(16);
  pdf.setTextColor(255, 255, 255);
  pdf.text("Find These Words:", wordListX + 10, wordListY + 25);
  let wordY = wordListY + 60;
  pdf.setFontSize(12);
  words.forEach((word) => {
    pdf.setFillColor(100, 100, 100);
    pdf.setDrawColor(100, 100, 100);
    pdf.roundedRect(wordListX, wordY - 10, wordListBoxWidth, 25, 3, 3, "FD");
    const centerX = wordListX + wordListBoxWidth / 2;
    const centerY = wordY - 10 + 25 / 2;
    pdf.text(word.toUpperCase(), centerX, centerY, {
      align: "center",
      baseline: "middle",
    });
    wordY += wordBlockHeight;
  });

  // --- FOOTER ---
  const today = new Date();
  pdf.setFontSize(8);
  pdf.setTextColor(100, 100, 100);
  pdf.text(
    `Generated on ${today.toLocaleDateString()} at ${today.toLocaleTimeString()}`,
    margin,
    neededHeight - 20
  );

  pdf.save("word-search-puzzle.pdf");
}
/**
 * Example fallback for retrieving words
 */
function getWordsList() {
  const gameScene = window.gameInstance.scene.scenes[0];

  // If you have a global window.wordData, use it
  if (window.wordData && window.wordData.length) {
    return window.wordData;
  }

  // Try different possible locations in the game scene
  if (gameScene.wordList) {
    return gameScene.wordList;
  }
  if (gameScene.words) {
    return gameScene.words;
  }
  if (gameScene.foundWords && gameScene.foundWords.targets) {
    return gameScene.foundWords.targets;
  }
  if (window.wordSearchData && window.wordSearchData.words) {
    return window.wordSearchData.words;
  }

  // Default fallback
  return [
    "DRAMA",
    "COMEDY",
    "HORROR",
    "FANTASY",
    "ADVENTURE",
    "ANIMATION",
    "THRILLER",
    "ROMANCE",
    "DOCUMENTARY",
  ];
}
