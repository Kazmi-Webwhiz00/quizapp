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
  let source = texture.getSourceImage();
  let canvas;

  // Check if the source is already a canvas element
  if (source instanceof HTMLCanvasElement) {
    canvas = source;
  } else {
    // Create an offscreen canvas and draw the image on it
    canvas = document.createElement("canvas");
    canvas.width = source.width;
    canvas.height = source.height;
    const offscreenCtx = canvas.getContext("2d");
    offscreenCtx.drawImage(source, 0, 0);
  }

  const ctx = canvas.getContext("2d");
  const pixelData = ctx.getImageData(0, 0, 1, 1).data;
  return [pixelData[0], pixelData[1], pixelData[2]];
}

export function downloadWordSearchAsPDF(onComplete) {
  // 1) Ensure jsPDF is loaded
  if (typeof window.jspdf === "undefined") {
    loadJsPDF();
    return;
  }

  // 2) Obtain game data
  const { gameInstance, gridMatrix } = getGameData();
  if (!gameInstance || !gridMatrix.length) return;

  // 3) Get scene, colors, and grid size
  const scene = gameInstance.scene.scenes[0];
  const gameColors = extractGameColors();
  const gridSize = gridMatrix.length;

  // 4) Get cell textures and colors
  const { evenRgb, oddRgb, borderRgb, textRgb } = getCellColors(
    scene,
    gameColors
  );

  // 5) Word list
  const { words, wordCount } = getWordList(gameInstance);

  // 6) Images processing
  const { validImages, imageCount, hasImages, imageColumns } = getImagesData();

  // 10) Images layout parameters
  let { maxImgWidth, maxImgHeight, imagesGapY, imagesGapX, puzzleImagesGap } =
    getImagesLayout(imageCount, hasImages, imageColumns);

  // 7) Create PDF and get page dimensions
  const pdf = createPDF();
  const {
    pageWidth,
    pageHeight,
    marginLeft,
    marginRight,
    marginTop,
    marginBottom,
    contentWidth,
    contentHeight,
  } = getPageDimensions(pdf);

  // Compute the maximum number of images that can fit in a column based on the current maxImgHeight.
  let dynamicImagesPerColumn = Math.floor(
    contentHeight / (maxImgHeight + imagesGapY)
  );

  // If there are more than 13 images, cap images per column at 8 and adjust maxImgHeight so images fit.
  if (imageCount > 13) {
    dynamicImagesPerColumn = Math.min(dynamicImagesPerColumn, 8);
    maxImgHeight =
      (contentHeight - (dynamicImagesPerColumn - 1) * imagesGapY) /
      dynamicImagesPerColumn;
  }

  // Ensure at least one image per column.
  const imagesPerColumn =
    dynamicImagesPerColumn > 0 ? dynamicImagesPerColumn : 1;

  // Distribute the valid images into columns.
  for (let i = 0; i < Math.ceil(imageCount / imagesPerColumn); i++) {
    const start = i * imagesPerColumn;
    const end = Math.min(start + imagesPerColumn, imageCount);
    imageColumns.push(validImages.slice(start, end));
  }

  // 8) Decide puzzle cell size and calculate dimensions
  const {
    baseCellSize,
    puzzleWidth: initialPuzzleWidth,
    puzzleHeight: initialPuzzleHeight,
  } = calculatePuzzleDimensions(wordCount, gridSize);
  let puzzleWidth = initialPuzzleWidth;
  let puzzleHeight = initialPuzzleHeight;

  // 9) Word list layout parameters
  const wordBoxGap = 10;
  const horizontalPadding = 6;
  const verticalPadding = 4;

  const wordsAreaHeight = calculateWordsAreaHeight(
    pdf,
    words,
    marginLeft,
    marginRight,
    wordBoxGap,
    horizontalPadding,
    verticalPadding,
    40 // extra top padding for the "Find the Words" label, etc.
  );

  // 11) Figure out how much vertical space is left after the word list + header
  //     so we can scale puzzle+images to fit below that area.
  const headerBuffer = 40; // Some extra buffer below the word list
  // We'll figure out puzzle+images scaling based on leftover vertical space:
  const leftoverHeight = contentHeight - (wordsAreaHeight + headerBuffer);

  // 12) Scale puzzle and images if needed
  ({ puzzleWidth, puzzleHeight, maxImgWidth, maxImgHeight } =
    fitPuzzleAndImagesOnPage({
      puzzleWidth,
      puzzleHeight,
      baseCellSize,
      gridSize,
      puzzleImagesGap,
      maxImgWidth,
      maxImgHeight,
      imageColumns,
      imagesGapX,
      imagesGapY,
      contentWidth,
      leftoverHeight,
    }));

  console.log("::maxImgWidth", maxImgWidth, maxImgHeight);

  // 13) Draw Header (independent of font)
  const headerHeight = drawHeader(
    pdf,
    pageWidth,
    marginRight,
    marginLeft,
    gameColors
  );

  // 14) Now load the font and then continue drawing everything else
  loadFontAndContinue(pdf);

  // ------------------------- Helper Functions -------------------------

  function loadJsPDF() {
    const jspdfScript = document.createElement("script");
    jspdfScript.src = window.isAdmin
      ? frontendData.url + "assets/library/jspdf.umd.min.js"
      : wordSearchData.url + "assets/library/jspdf.umd.min.js";
    document.head.appendChild(jspdfScript);
    jspdfScript.onload = () => downloadWordSearchAsPDF(onComplete);
  }

  function getGameData() {
    const gameInstance = window.gameInstance;
    if (!gameInstance) {
      console.error("Game instance not found");
    }
    const gridMatrix = gameInstance
      ? gameInstance.gridMatrix || window.gridMatrix || []
      : [];
    if (!gridMatrix.length) {
      console.error("Grid matrix data not found");
    }
    return { gameInstance, gridMatrix };
  }

  function getCellColors(scene, gameColors) {
    const cellTextureKey = scene.cellTextureKey;
    const evenTexture = scene.textures.get(cellTextureKey + "_even");
    const oddTexture = scene.textures.get(cellTextureKey + "_odd");
    const evenRgb = getTextureFillColor(evenTexture);
    const oddRgb = getTextureFillColor(oddTexture);
    const borderRgb = hexToRgbArray(gameColors.cellBorder);
    const textRgb = hexToRgbArray(gameColors.text);
    return { evenRgb, oddRgb, borderRgb, textRgb };
  }

  function getWordList(gameInstance) {
    const words = getWordsList() || gameInstance.words || window.wordData || [];
    return { words, wordCount: words.length };
  }

  function getImagesData() {
    const visualClues = window.finalEntries || [];
    const validImages = visualClues.filter(
      (entry) => entry && entry.wordText && entry.imageUrl?.trim() !== ""
    );
    const imageCount = validImages.length;
    const hasImages = imageCount > 0;
    // imageColumns will be calculated later once pdf is initialized
    const imageColumns = [];

    return { validImages, imageCount, hasImages, imageColumns };
  }

  function createPDF() {
    return new jspdf.jsPDF("portrait", "pt", "A4");
  }

  function getPageDimensions(pdf) {
    const pageWidth = pdf.internal.pageSize.getWidth(); // ~595 pt
    const pageHeight = pdf.internal.pageSize.getHeight(); // ~842 pt
    const marginLeft = 50;
    const marginRight = 50;
    const marginTop = 60;
    const marginBottom = 30;
    const contentWidth = pageWidth - marginLeft - marginRight;
    const contentHeight = pageHeight - marginTop - marginBottom;
    return {
      pageWidth,
      pageHeight,
      marginLeft,
      marginRight,
      marginTop,
      marginBottom,
      contentWidth,
      contentHeight,
    };
  }

  function calculatePuzzleDimensions(wordCount, gridSize) {
    let baseCellSize;
    if (wordCount <= 10) {
      baseCellSize = 40;
    } else if (wordCount <= 20) {
      baseCellSize = 35;
    } else if (wordCount <= 30) {
      baseCellSize = 30;
    } else {
      baseCellSize = 25;
    }

    // If the grid is very large, cap the cell size further
    if (gridSize > 15) {
      baseCellSize = Math.min(baseCellSize, 25);
    }

    const puzzleWidth = gridSize * baseCellSize;
    const puzzleHeight = gridSize * baseCellSize;
    return { baseCellSize, puzzleWidth, puzzleHeight };
  }

  function calculateWordsAreaHeight(
    pdf,
    words,
    marginLeft,
    marginRight,
    wordBoxGap,
    horizontalPadding,
    verticalPadding,
    topPadding = 40
  ) {
    const pageWidth = pdf.internal.pageSize.getWidth();
    const availableWidth = pageWidth - marginLeft - marginRight;

    let rows = [];
    let currentRowWidth = 0;
    let currentRowMaxHeight = 0;

    words.forEach((word) => {
      const text = word.toUpperCase();
      const { w: textWidth, h: textHeight } = pdf.getTextDimensions(text);
      // Each box is as large as the text plus padding
      const boxWidth = textWidth + horizontalPadding * 2;
      const boxHeight = textHeight + verticalPadding * 2;

      // If it doesn't fit on the current row, move to next row
      if (currentRowWidth + boxWidth > availableWidth) {
        rows.push(currentRowMaxHeight);
        currentRowWidth = boxWidth + wordBoxGap; // start a new row
        currentRowMaxHeight = boxHeight;
      } else {
        currentRowWidth += boxWidth + wordBoxGap;
        currentRowMaxHeight = Math.max(currentRowMaxHeight, boxHeight);
      }
    });

    // Push the last row if there was at least one box
    if (currentRowWidth > 0) {
      rows.push(currentRowMaxHeight);
    }

    // Sum all row heights plus vertical gaps
    const totalGap = (rows.length - 1) * wordBoxGap;
    const totalRowsHeight = rows.reduce((sum, h) => sum + h, 0);
    return totalRowsHeight + totalGap + topPadding;
  }

  function getImagesLayout(imageCount, hasImages, imageColumns) {
    let maxImgWidth = imageCount > 10 ? 100 : 140;
    let maxImgHeight = imageCount > 10 ? 80 : 100;
    const imagesGapY = 15;
    const imagesGapX = 15;
    // Gap between puzzle and first column of images
    const puzzleImagesGap = hasImages ? 20 : 0;
    return {
      maxImgWidth,
      maxImgHeight,
      imagesGapY,
      imagesGapX,
      puzzleImagesGap,
    };
  }

  function fitPuzzleAndImagesOnPage({
    puzzleWidth,
    puzzleHeight,
    baseCellSize,
    gridSize,
    puzzleImagesGap,
    maxImgWidth,
    maxImgHeight,
    imageColumns,
    imagesGapX,
    imagesGapY,
    contentWidth,
    contentHeight,
    minCellSizeThreshold = 20,
  }) {
    // 1) Calculate total images listing width
    const imagesListingWidth = imageColumns.length
      ? imageColumns.length * maxImgWidth +
        (imageColumns.length - 1) * imagesGapX
      : 0;

    // 2) Combined bounding width = puzzle + gap + images
    const boundingWidth =
      puzzleWidth +
      (imagesListingWidth ? puzzleImagesGap : 0) +
      imagesListingWidth;

    // 3) Determine horizontal scale so boundingWidth doesn’t exceed contentWidth.
    // Also, prevent any upscale by clamping the scale to 1.
    let scale = boundingWidth > contentWidth ? contentWidth / boundingWidth : 1;
    scale = Math.min(scale, 1);

    // 4) Apply uniform scale to puzzle dimensions, image dimensions, and gaps
    puzzleWidth *= scale;
    puzzleHeight *= scale;
    maxImgWidth *= scale;
    maxImgHeight *= scale;
    puzzleImagesGap *= scale;
    imagesGapX *= scale;
    imagesGapY *= scale;

    return {
      puzzleWidth,
      puzzleHeight,
      maxImgWidth,
      maxImgHeight,
      puzzleImagesGap,
      imagesGapX,
      imagesGapY,
    };
  }

  function drawHeader(pdf, pageWidth, marginRight, marginLeft, gameColors) {
    const headerImageUrl = window.isAdmin
      ? frontendData.url + "assets/images/LOGO-Edu.png"
      : wordSearchData.url + "assets/images/LOGO-Edu.png";

    const headerHeight = 80;
    const maxImageWidth = pageWidth * 0.15;
    const maxImageHeight = headerHeight * 0.75;

    const img = new Image();
    img.src = headerImageUrl;

    img.onload = function () {
      const aspectRatio = img.width / img.height;
      let displayWidth = maxImageWidth;
      let displayHeight = displayWidth / aspectRatio;

      if (displayHeight > maxImageHeight) {
        displayHeight = maxImageHeight;
        displayWidth = displayHeight * aspectRatio;
      }

      const imageX = pageWidth - marginRight - displayWidth;
      const imageY = headerHeight - displayHeight;
      pdf.addImage(img, "PNG", imageX, imageY, displayWidth, displayHeight);
    };

    pdf.setFontSize(22);
    pdf.setTextColor(0, 0, 0);
    const titleText = window.pdfText?.postTitle || "Word Search Puzzle";
    const titleW = pdf.getTextWidth(titleText);
    pdf.text(titleText, (pageWidth - titleW) / 2, headerHeight / 2 + 15);

    pdf.setDrawColor(...hexToRgbArray(gameColors.accent));
    pdf.setLineWidth(2);
    pdf.line(
      marginLeft,
      headerHeight + 10,
      pageWidth - marginRight,
      headerHeight + 10
    );

    return headerHeight;
  }

  function drawWordList(
    pdf,
    words,
    wordBoxGap,
    horizontalPadding,
    verticalPadding,
    marginLeft,
    currentY
  ) {
    // (Optional) Title text
    pdf.setFontSize(14);
    pdf.setTextColor(60, 60, 60);
    const findWordsLabel = window.pdfText?.findWordsLabel || "Find the Words:";
    pdf.text(findWordsLabel, marginLeft, currentY + 20);
    currentY += 40;

    const marginRight = 20;
    const pageWidth = pdf.internal.pageSize.getWidth();
    const availableWidth = pageWidth - marginLeft - marginRight;

    let xPos = marginLeft;
    let yPos = currentY;
    let currentRowMaxHeight = 0;

    // Draw each word with dynamic box
    words.forEach((word) => {
      const text = word.toUpperCase();
      const { w: textWidth, h: textHeight } = pdf.getTextDimensions(text);
      const boxWidth = textWidth + horizontalPadding * 2;
      const boxHeight = textHeight + verticalPadding * 2;

      // If we exceed available width, wrap to next row
      if (xPos + boxWidth > marginLeft + availableWidth) {
        xPos = marginLeft;
        yPos += currentRowMaxHeight + wordBoxGap;
        currentRowMaxHeight = 0;
      }

      // Draw the box
      pdf.setLineWidth(0.75);
      pdf.setDrawColor(100, 100, 100);
      pdf.roundedRect(xPos, yPos, boxWidth, boxHeight, 3, 3, "D");

      // Place text in the center
      pdf.setFontSize(11);
      pdf.setTextColor(0, 0, 0);
      const textX = xPos + boxWidth / 2;
      const textY = yPos + boxHeight / 2;
      pdf.text(text, textX, textY, { align: "center", baseline: "middle" });

      // Advance to the right
      xPos += boxWidth + wordBoxGap;
      currentRowMaxHeight = Math.max(currentRowMaxHeight, boxHeight);
    });

    // Update currentY after the final row
    currentY = yPos + currentRowMaxHeight + 30; //
    return currentY;
  }

  function drawPuzzleBackgroundAndGrid(
    pdf,
    puzzleX,
    puzzleY,
    puzzleWidth,
    puzzleHeight,
    gridSize,
    evenRgb,
    oddRgb,
    borderRgb
  ) {
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
      // horizontal lines
      pdf.line(
        puzzleX,
        puzzleY + i * cellH,
        puzzleX + puzzleWidth,
        puzzleY + i * cellH
      );
    }
  }

  function drawPuzzleLetters(
    pdf,
    gridMatrix,
    puzzleX,
    puzzleY,
    puzzleWidth,
    puzzleHeight,
    gridSize,
    textRgb
  ) {
    pdf.setTextColor(...textRgb);
    pdf.setFont("NotoSans", "normal");

    const cellH = puzzleHeight / gridSize;
    pdf.setFontSize(cellH * 0.6);

    for (let r = 0; r < gridSize; r++) {
      for (let c = 0; c < gridSize; c++) {
        let letter = gridMatrix[r][c]?.letter || "";
        letter = cleanLetter(letter);
        if (letter) {
          const cellW = puzzleWidth / gridSize;
          const txtW = pdf.getTextWidth(letter);
          const tx = puzzleX + c * cellW + (cellW - txtW) / 2;
          const ty = puzzleY + r * cellH + cellH * 0.7;
          pdf.text(letter, tx, ty);
        }
      }
    }
  }

  function drawImages(
    pdf,
    hasImages,
    imageColumns,
    puzzleX,
    puzzleWidth,
    puzzleY,
    maxImgWidth,
    maxImgHeight,
    imagesGapX,
    imagesGapY,
    puzzleImagesGap
  ) {
    if (!hasImages || !imageColumns.length) return;
    const imagesX = puzzleX + puzzleWidth + puzzleImagesGap;
    const imagesY = puzzleY;

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

        // Try to find the loaded <img> from the DOM
        const imgElem = document.querySelector(
          `[data-word="${entry.wordText?.toUpperCase()}"] img`
        );
        if (imgElem && imgElem.complete && imgElem.naturalHeight !== 0) {
          try {
            const ratio = imgElem.naturalWidth / imgElem.naturalHeight;
            let drawWidth = maxImgWidth;
            let drawHeight = maxImgHeight;

            // Preserve aspect ratio
            if (maxImgWidth / maxImgHeight > ratio) {
              drawWidth = maxImgHeight * ratio;
            } else {
              drawHeight = maxImgWidth / ratio;
            }

            // Center the image within its container
            const offsetX = colX + (maxImgWidth - drawWidth) / 2;
            const offsetY = currentColY + (maxImgHeight - drawHeight) / 2;

            pdf.addImage(
              imgElem,
              "JPEG",
              offsetX,
              offsetY,
              drawWidth,
              drawHeight
            );
          } catch (err) {
            console.error("Error adding image", err);
          }
        }
        currentColY += maxImgHeight + imagesGapY;
      });
    });
  }

  function loadFontAndContinue(pdf) {
    const fontUrl = window.isAdmin
      ? frontendData.url + "assets/fonts/NotoSans-Regular.ttf"
      : wordSearchData.url + "assets/fonts/NotoSans-Regular.ttf";

    fetch(fontUrl)
      .then((res) => res.blob())
      .then(
        (blob) =>
          new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => {
              const base64String = reader.result.split(",")[1];
              resolve(base64String);
            };
            reader.onerror = reject;
            reader.readAsDataURL(blob);
          })
      )
      .then((fontData) => {
        // Register font
        pdf.addFileToVFS("NotoSans-Regular.ttf", fontData);
        pdf.addFont("NotoSans-Regular.ttf", "NotoSans", "normal");
        pdf.setFont("NotoSans", "normal");

        // a) Draw the word list
        let currentY = marginTop + headerHeight;
        currentY = drawWordList(
          pdf,
          words,
          wordBoxGap,
          horizontalPadding,
          verticalPadding,
          marginLeft,
          currentY
        );

        // b) Position puzzle (below word list)
        const puzzleY = currentY;
        // If we have images, puzzle is left; if not, center puzzle horizontally
        const puzzleX = hasImages
          ? marginLeft
          : marginLeft + (contentWidth - puzzleWidth) / 2;

        // c) Draw puzzle background and grid
        drawPuzzleBackgroundAndGrid(
          pdf,
          puzzleX,
          puzzleY,
          puzzleWidth,
          puzzleHeight,
          gridSize,
          evenRgb,
          oddRgb,
          borderRgb
        );

        // d) Draw puzzle letters
        drawPuzzleLetters(
          pdf,
          gridMatrix,
          puzzleX,
          puzzleY,
          puzzleWidth,
          puzzleHeight,
          gridSize,
          textRgb
        );

        // e) Draw images
        drawImages(
          pdf,
          hasImages,
          imageColumns,
          puzzleX,
          puzzleWidth,
          puzzleY,
          maxImgWidth,
          maxImgHeight,
          imagesGapX,
          imagesGapY,
          puzzleImagesGap
        );

        // f) Save the PDF
        pdf.save("wykreslanka.pdf");
        if (typeof onComplete === "function") {
          onComplete();
        }
      })
      .catch((error) => console.error("Error loading font:", error));
  }

  function cleanLetter(letter) {
    // Remove any weird control characters
    return letter.replace(/[\u0000-\u001F\u007F-\u009F]/g, "");
  }
}

/**
 * Example fallback for retrieving words
 */
function getWordsList() {
  const gameScene = window.gameInstance.scene.scenes[0];
  // If you have a global window.wordData, use it
  if (window.wordData && window.wordData.length && window.finalEntries.length) {
    let wordData = window.wordData;
    // Filter out words that are marked as hidden
    wordData = wordData.filter((word) => {
      const formattedWord = word.toUpperCase();
      if (window.finalEntries && Array.isArray(window.finalEntries)) {
        const entry = window.finalEntries.find(
          (item) => item.wordText.toUpperCase() === formattedWord
        );
        if (entry && entry.hidden === true) {
          return false;
        }
      }
      return true;
    });
    return wordData;
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
