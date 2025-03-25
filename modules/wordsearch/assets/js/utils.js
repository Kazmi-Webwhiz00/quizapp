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

  // 1) Obtain puzzle data, etc.
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
  const scene = gameInstance.scene.scenes[0];
  const gameColors = extractGameColors();
  const gridSize = gridMatrix.length;

  // Cell textures
  const evenTexture = scene.textures.get("evenCell");
  const oddTexture = scene.textures.get("oddCell");
  const evenRgb = getTextureFillColor(evenTexture);
  const oddRgb = getTextureFillColor(oddTexture);
  const borderRgb = hexToRgbArray(gameColors.cellBorder);
  const textRgb = hexToRgbArray(gameColors.text);

  // Word list
  const words = gameInstance.words || window.wordData || getWordsList();
  const wordCount = words.length;

  // Images
  const visualClues = window.finalEntries || [];
  const validImages = visualClues.filter(
    (entry) =>
      entry && entry.wordText && entry.imageUrl && entry.imageUrl.trim() !== ""
  );
  const imageCount = validImages.length;
  const hasImages = imageCount > 0;

  // ----------------------------
  // 2) PDF: Use A4 (portrait)
  // ----------------------------
  const pdf = new jspdf.jsPDF("portrait", "pt", "A4");
  const pageWidth = pdf.internal.pageSize.getWidth(); // ~595 pt
  const pageHeight = pdf.internal.pageSize.getHeight(); // ~842 pt

  // Margins
  const marginLeft = 50;
  const marginRight = 50;
  const marginTop = 60;
  const marginBottom = 30;
  const contentWidth = pageWidth - marginLeft - marginRight;

  // ----------------------------
  // 3) Decide puzzle cell size
  //    based on word count, etc.
  // ----------------------------
  // This is just a heuristic. You can refine as desired:
  let baseCellSize;
  if (wordCount <= 10) {
    baseCellSize = 40; // larger cells if few words
  } else if (wordCount <= 20) {
    baseCellSize = 35;
  } else if (wordCount <= 30) {
    baseCellSize = 30;
  } else {
    baseCellSize = 25; // smaller if many words
  }
  // If gridSize is large (e.g. 20×20), you might reduce further:
  if (gridSize > 15) {
    baseCellSize = Math.min(baseCellSize, 25); // or some other logic
  }

  // Compute puzzle width/height from cell size:
  let puzzleWidth = gridSize * baseCellSize;
  let puzzleHeight = gridSize * baseCellSize;

  // ----------------------------
  // 4) Word list layout
  // ----------------------------
  const wordsPerRow = 5;
  const wordBoxWidth = 90;
  const wordBoxHeight = 22;
  const wordBoxGap = 10;
  const wordsRowsNeeded = Math.ceil(wordCount / wordsPerRow);
  const wordsAreaHeight = wordsRowsNeeded * (wordBoxHeight + wordBoxGap) + 40;

  // ----------------------------
  // 5) Images layout
  // ----------------------------
  // We want to keep images noticeable, so let’s define a “typical” maximum size
  // if few images, otherwise smaller. Then we’ll handle final scaling.
  let maxImgWidth = imageCount > 10 ? 100 : 140;
  let maxImgHeight = imageCount > 10 ? 80 : 100;
  let imagesPerColumn = imageCount > 10 ? 7 : 5; // arbitrary
  const imagesGapY = 10; // vertical gap
  const imagesGapX = 15; // horizontal gap between columns

  // Distribute images into columns
  let imageColumns = [];
  if (imageCount > 0) {
    const columns = Math.ceil(imageCount / imagesPerColumn);
    for (let i = 0; i < columns; i++) {
      const start = i * imagesPerColumn;
      const end = Math.min(start + imagesPerColumn, imageCount);
      imageColumns.push(validImages.slice(start, end));
    }
  }

  // Compute total images listing width
  const imagesListingWidth =
    imageColumns.length > 0
      ? imageColumns.length * maxImgWidth +
        (imageColumns.length - 1) * imagesGapX
      : 0;

  // Gap between puzzle and images
  const puzzleImagesGap = hasImages ? 20 : 0;

  // Combine puzzle + images widths
  let combinedWidth = puzzleWidth + puzzleImagesGap + imagesListingWidth;

  // ----------------------------
  // 6) If puzzle+images exceed content width, scale them
  // ----------------------------
  if (combinedWidth > contentWidth) {
    const scale = contentWidth / combinedWidth;

    // If scaling would make cells too small, adjust strategy
    const minCellSizeThreshold = 20; // Minimum acceptable cell size
    if (baseCellSize * scale < minCellSizeThreshold) {
      // Prioritize cell size, compress images more aggressively
      const cellPreservationScale = minCellSizeThreshold / baseCellSize;
      puzzleWidth *= cellPreservationScale;
      puzzleHeight *= cellPreservationScale;

      // Compress images more to make room
      maxImgWidth *= scale / cellPreservationScale;
      maxImgHeight *= scale / cellPreservationScale;
    } else {
      // Normal proportional scaling
      puzzleWidth *= scale;
      puzzleHeight *= scale;
      maxImgWidth *= scale;
      maxImgHeight *= scale;
    }
  }

  // Now puzzleWidth, puzzleHeight, maxImgWidth, maxImgHeight are final

  // ----------------------------
  // 7) Draw Header
  // ----------------------------
  const headerHeight = 80;
  pdf.setFillColor(...hexToRgbArray(gameColors.header));
  pdf.rect(0, 0, pageWidth, headerHeight, "F");
  pdf.setFontSize(22);
  pdf.setTextColor(255, 255, 255);
  const titleText = "WORD SEARCH PUZZLE";
  const titleW = pdf.getTextWidth(titleText);
  pdf.text(titleText, (pageWidth - titleW) / 2, headerHeight / 2 + 8);
  pdf.setDrawColor(...hexToRgbArray(gameColors.accent));
  pdf.setLineWidth(2);
  pdf.line(
    marginLeft,
    headerHeight + 10,
    pageWidth - marginRight,
    headerHeight + 10
  );

  // ----------------------------
  // 8) Word List
  // ----------------------------
  let currentY = marginTop + headerHeight;
  pdf.setFontSize(14);
  pdf.setTextColor(60, 60, 60);
  pdf.text("Find These Words:", marginLeft, currentY + 20);
  currentY += 40;
  pdf.setFontSize(11);
  words.forEach((word, i) => {
    const row = Math.floor(i / wordsPerRow);
    const col = i % wordsPerRow;
    const boxX = marginLeft + col * (wordBoxWidth + wordBoxGap);
    const boxY = currentY + row * (wordBoxHeight + wordBoxGap);
    pdf.setFillColor(100, 100, 100);
    pdf.setDrawColor(100, 100, 100);
    pdf.roundedRect(boxX, boxY, wordBoxWidth, wordBoxHeight, 3, 3, "FD");
    pdf.setTextColor(255, 255, 255);
    const cx = boxX + wordBoxWidth / 2;
    const cy = boxY + wordBoxHeight / 2;
    pdf.text(word.toUpperCase(), cx, cy, {
      align: "center",
      baseline: "middle",
    });
  });
  currentY += wordsRowsNeeded * (wordBoxHeight + wordBoxGap) + 30;

  // ----------------------------
  // 9) Puzzle
  // ----------------------------
  const puzzleX = marginLeft;
  const puzzleY = Math.max(
    currentY,
    marginTop + headerHeight + wordsAreaHeight + 40
  );
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
  const cellW = puzzleWidth / gridSize;
  const cellH = puzzleHeight / gridSize;
  for (let r = 0; r < gridSize; r++) {
    for (let c = 0; c < gridSize; c++) {
      const isEven = (r + c) % 2 === 0;
      pdf.setFillColor(...(isEven ? evenRgb : oddRgb));
      pdf.rect(puzzleX + c * cellW, puzzleY + r * cellH, cellW, cellH, "F");
    }
  }
  // Grid lines
  pdf.setDrawColor(...borderRgb);
  pdf.setLineWidth(0.5);
  for (let i = 0; i <= gridSize; i++) {
    // vertical lines
    pdf.line(
      puzzleX + i * cellW,
      puzzleY,
      puzzleX + i * cellW,
      puzzleY + puzzleHeight
    );
    // horizontal
    pdf.line(
      puzzleX,
      puzzleY + i * cellH,
      puzzleX + puzzleWidth,
      puzzleY + i * cellH
    );
  }
  // Letters
  pdf.setFontSize(cellH * 0.6);
  pdf.setTextColor(...textRgb);
  for (let r = 0; r < gridSize; r++) {
    for (let c = 0; c < gridSize; c++) {
      const letter = gridMatrix[r][c]?.letter || "";
      if (letter) {
        const txtW = pdf.getTextWidth(letter);
        const tx = puzzleX + c * cellW + (cellW - txtW) / 2;
        const ty = puzzleY + r * cellH + cellH * 0.7;
        pdf.text(letter, tx, ty);
      }
    }
  }

  // ----------------------------
  // 10) Images (to the right)
  // ----------------------------
  if (hasImages && imageColumns.length > 0) {
    const imagesX = puzzleX + puzzleWidth + (hasImages ? puzzleImagesGap : 0);
    const imagesY = puzzleY; // align top with puzzle
    imageColumns.forEach((colImgs, colIndex) => {
      const colX = imagesX + colIndex * (maxImgWidth + imagesGapX);
      let currentColY = imagesY;
      colImgs.forEach((entry) => {
        pdf.setDrawColor(210, 210, 230);
        pdf.setLineWidth(0.5);
        pdf.roundedRect(
          colX,
          currentColY,
          maxImgWidth,
          maxImgHeight,
          3,
          3,
          "D"
        );
        const imgElem = document.querySelector(
          `[data-word="${entry.wordText.toLowerCase()}"] img`
        );
        if (imgElem && imgElem.complete && imgElem.naturalHeight !== 0) {
          try {
            pdf.addImage(
              imgElem,
              "JPEG",
              colX,
              currentColY,
              maxImgWidth,
              maxImgHeight
            );
          } catch (err) {
            console.error("Error adding image", err);
          }
        }
        currentColY += maxImgHeight + imagesGapY;
      });
    });
  }

  // ----------------------------
  // 11) Footer
  // ----------------------------
  pdf.setFontSize(8);
  pdf.setTextColor(100, 100, 100);
  const today = new Date();
  const footerText = `Generated on ${today.toLocaleDateString()} at ${today.toLocaleTimeString()}`;

  // Calculate text width for right-alignment
  const footerTextWidth = pdf.getTextWidth(footerText);

  pdf.text(
    footerText,
    pageWidth - marginRight - footerTextWidth,
    pageHeight - marginBottom
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
