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

  // Get visual clues/images
  const visualClues = window.finalEntries || [];
  const hasImages = visualClues.some((entry) => entry && entry.imageUrl !== "");

  // Count valid images
  const validImages = visualClues.filter(
    (entry) =>
      entry && entry.wordText && entry.imageUrl && entry.imageUrl.trim() !== ""
  );
  const imageCount = validImages.length;

  // Calculate image dimensions based on total count
  let imageWidth, imageHeight;
  const mainColumnImages = []; // Images to display in the right column
  const bottomRowImages = []; // Images to display in the bottom row

  if (imageCount <= 6) {
    // Standard size for 6 or fewer images
    imageWidth = 140;
    imageHeight = Math.min(80, 400 / Math.min(imageCount, 6));
    mainColumnImages.push(...validImages);
  } else {
    // Smaller size for more than 6 images
    imageWidth = 140;
    imageHeight = Math.min(60, 400 / 6);

    // First 6 images go in the right column
    mainColumnImages.push(...validImages.slice(0, 6));

    // Remaining images go in the bottom row
    bottomRowImages.push(...validImages.slice(6));
  }

  // Calculate dimensions based on canvas and grid content
  const canvasWidth = gameInstance.canvas?.width || 600;
  const baseCellSize = Math.min(35, Math.floor(canvasWidth / gridSize));
  const puzzleWidth = gridSize * baseCellSize;
  const puzzleHeight = gridSize * baseCellSize;

  // Calculate word list dimensions
  const wordsPerRow = 5; // Number of words per row in the top section
  const wordBoxWidth = 100;
  const wordBoxHeight = 25;
  const wordBoxGap = 10;
  const wordsRowsNeeded = Math.ceil(words.length / wordsPerRow);
  const wordsAreaHeight = wordsRowsNeeded * (wordBoxHeight + wordBoxGap) + 50; // +50 for header

  // Visual clues container dimensions
  const cluesWidth = hasImages ? 180 : 0;
  const cluesGap = hasImages ? 20 : 0;

  // Overall layout dimensions
  const margin = 60;
  const headerHeight = 120;
  const footerHeight = 30;
  const gap = 40;
  const pageHeightPadding = 200;

  // Calculate bottom row height if needed
  let bottomRowHeight = 0;
  if (bottomRowImages.length > 0) {
    // Calculate height needed for bottom row of images
    bottomRowHeight = imageHeight + 40; // Image height plus padding
  }

  // New layout dimensions
  const contentWidth = puzzleWidth + (hasImages ? cluesGap + cluesWidth : 0);
  const contentHeight = puzzleHeight;
  const neededWidth = Math.max(
    margin + contentWidth + margin,
    margin + (words.length * (wordBoxWidth + wordBoxGap)) / 2 + margin
  );
  const neededHeight =
    margin +
    headerHeight +
    gap +
    wordsAreaHeight +
    gap +
    contentHeight +
    (bottomRowHeight > 0 ? gap + bottomRowHeight : 0) +
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

  // --- WORD LIST (now at the top) ---
  const wordsStartY = headerHeight + gap;
  pdf.setFontSize(16);
  pdf.setTextColor(60, 60, 60);
  pdf.text("Find These Words:", margin, wordsStartY + 20);

  // Create word boxes in multiple rows
  pdf.setFontSize(12);
  for (let i = 0; i < words.length; i++) {
    const row = Math.floor(i / wordsPerRow);
    const col = i % wordsPerRow;

    const boxX = margin + col * (wordBoxWidth + wordBoxGap);
    const boxY = wordsStartY + 40 + row * (wordBoxHeight + wordBoxGap);

    pdf.setFillColor(100, 100, 100);
    pdf.setDrawColor(100, 100, 100);
    pdf.roundedRect(boxX, boxY, wordBoxWidth, wordBoxHeight, 3, 3, "FD");

    pdf.setTextColor(255, 255, 255);
    const centerX = boxX + wordBoxWidth / 2;
    const centerY = boxY + wordBoxHeight / 2;
    pdf.text(words[i].toUpperCase(), centerX, centerY, {
      align: "center",
      baseline: "middle",
    });
  }

  // --- PUZZLE GRID (now below word list) ---
  const puzzleX = margin;
  const puzzleY = wordsStartY + wordsAreaHeight + gap;
  const cellSize = baseCellSize;

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

  // --- VISUAL CLUES (right column) - No background, aligned with grid top ---
  if (hasImages && mainColumnImages.length > 0) {
    const cluesX = puzzleX + puzzleWidth + cluesGap;
    const cluesY = puzzleY; // Grid Y position

    try {
      // Start position for images - aligned exactly with the top of the grid
      let currentImageY = cluesY; // Removed the +10 padding to align exactly with grid
      const imageGap = 15; // Increased gap between images

      // Add images to the right column
      for (const entry of mainColumnImages) {
        if (
          entry &&
          entry.wordText &&
          entry.imageUrl &&
          entry.imageUrl.trim() !== ""
        ) {
          // Create subtle border for each image - no background fill
          pdf.setDrawColor(210, 210, 230);
          pdf.setLineWidth(0.5);
          pdf.roundedRect(
            cluesX + 5,
            currentImageY,
            imageWidth + 10,
            imageHeight + 10,
            3,
            3,
            "D"
          );

          // Try to find the image element from the DOM
          const img = document.querySelector(
            `[data-word="${entry.wordText.toLowerCase()}"] img`
          );
          if (img && img.complete && img.naturalHeight !== 0) {
            try {
              // Add image
              pdf.addImage(
                img,
                "JPEG",
                cluesX + 10,
                currentImageY + 5,
                imageWidth,
                imageHeight
              );

              currentImageY += imageHeight + imageGap;
            } catch (imgErr) {
              console.error("Error adding specific image:", imgErr);
            }
          }
        }
      }
    } catch (err) {
      console.error("Error processing main column images:", err);
    }
  }

  // --- BOTTOM ROW IMAGES - No background, aligned with grid left edge ---
  if (hasImages && bottomRowImages.length > 0) {
    // Calculate starting position below the grid
    const bottomY = puzzleY + puzzleHeight + gap;

    try {
      // Increased gap between bottom row images
      const bottomImageGap = 25; // Larger gap for bottom row images

      // Calculate image width based on how many need to fit in the row
      const imagesPerRow = bottomRowImages.length;
      const maxRowWidth = neededWidth - 2 * margin;
      const actualImageWidth = Math.min(
        imageWidth,
        (maxRowWidth - (imagesPerRow - 1) * bottomImageGap) / imagesPerRow
      );

      // Add images to the bottom row
      for (let i = 0; i < bottomRowImages.length; i++) {
        const entry = bottomRowImages[i];
        if (
          entry &&
          entry.wordText &&
          entry.imageUrl &&
          entry.imageUrl.trim() !== ""
        ) {
          // Calculate position in the row with increased gap
          // Start exactly from the grid's left edge (puzzleX)
          const xPos = puzzleX + i * (actualImageWidth + bottomImageGap);

          // Create subtle border for each image - no background fill
          pdf.setDrawColor(210, 210, 230);
          pdf.setLineWidth(0.5);
          pdf.roundedRect(
            xPos,
            bottomY + 5,
            actualImageWidth + 10,
            imageHeight + 10,
            3,
            3,
            "D"
          );

          // Try to find the image element from the DOM
          const img = document.querySelector(
            `[data-word="${entry.wordText.toLowerCase()}"] img`
          );
          if (img && img.complete && img.naturalHeight !== 0) {
            try {
              // Add image
              pdf.addImage(
                img,
                "JPEG",
                xPos + 5,
                bottomY + 10,
                actualImageWidth,
                imageHeight
              );
            } catch (imgErr) {
              console.error("Error adding bottom row image:", imgErr);
            }
          }
        }
      }
    } catch (err) {
      console.error("Error processing bottom row images:", err);
    }
  }

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
