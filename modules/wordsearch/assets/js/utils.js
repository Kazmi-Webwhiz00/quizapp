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

// Original color palette defined as a constant.
const originalColorPalette = [
  "rgba(255, 153, 0, 0.4)", // Orange
  "rgba(255, 51, 51, 0.4)", // Red
  "rgba(0, 204, 204, 0.4)", // Teal
  "rgba(0, 153, 0, 0.4)", // Green
  "rgba(153, 102, 255, 0.4)", // Purple
  "rgba(102, 204, 255, 0.4)", // Light Blue
  "rgba(204, 102, 255, 0.4)", // Violet
  "rgba(255, 102, 102, 0.4)", // Soft Red
  "rgba(255, 204, 0, 0.4)", // Golden Yellow
  "rgba(102, 255, 102, 0.4)", // Light Green
];

// Maintain a persistent copy that will be mutated.
let availableColorPalette = [...originalColorPalette];

export function getRandomTransparentColor() {
  // If all colors have been used, reset the available colors.
  if (availableColorPalette.length === 0) {
    availableColorPalette = [...originalColorPalette];
  }

  // Randomly select an index from the available palette.
  const randomIndex = Math.floor(Math.random() * availableColorPalette.length);

  // Remove and return the chosen color (ensuring uniqueness until depletion).
  const [uniqueColor] = availableColorPalette.splice(randomIndex, 1);
  return uniqueColor;
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
  const { validImages, imageCount, hasImages } = getImagesData();

  // 7) Get images layout parameters
  let { maxImgWidth, maxImgHeight, imagesGapY, imagesGapX, puzzleImagesGap } =
    getImagesLayout(imageCount, hasImages);

  // Create PDF and get page dimensions
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

  // 8) Calculate puzzle dimensions
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
    40
  );

  // 10) Calculate leftover vertical space (after header and word list)
  const headerBuffer = 40;
  const leftoverHeight = contentHeight - (wordsAreaHeight + headerBuffer);

  // 11) Decide layout mode:
  // Use side-by-side layout if 12 or fewer images; if more than 12 images, use below-grid mode.
  const MAX_COLUMN_IMAGES = 12;
  const useBelowGrid = hasImages && imageCount > MAX_COLUMN_IMAGES;
  let placeImagesBelow = false;
  let scaledData = {};
  let imageColumnsSet = null;

  if (!useBelowGrid) {
    // --- SIDE-BY-SIDE LAYOUT ---
    // Determine dynamic images per column based on available vertical space.
    let dynamicImagesPerColumn = Math.floor(
      contentHeight / (maxImgHeight + imagesGapY)
    );
    if (dynamicImagesPerColumn < 1) dynamicImagesPerColumn = 1;
    const imagesPerColumn = dynamicImagesPerColumn;
    let imageColumns = [];
    for (let i = 0; i < Math.ceil(imageCount / imagesPerColumn); i++) {
      const start = i * imagesPerColumn;
      const end = Math.min(start + imagesPerColumn, imageCount);
      imageColumns.push(validImages.slice(start, end));
    }
    imageColumnsSet = imageColumns;

    scaledData = fitPuzzleAndImagesOnPage({
      puzzleWidth,
      puzzleHeight,
      baseCellSize,
      gridSize,
      puzzleImagesGap,
      maxImgWidth,
      maxImgHeight,
      imageColumns: imageColumns,
      imagesGapX,
      imagesGapY,
      contentWidth,
      leftoverHeight,
    });

    // Update dimensions using side-by-side scaling.
    puzzleWidth = scaledData.puzzleWidth;
    puzzleHeight = scaledData.puzzleHeight;
    maxImgWidth = scaledData.maxImgWidth;
    maxImgHeight = scaledData.maxImgHeight;
    puzzleImagesGap = scaledData.puzzleImagesGap;
    imagesGapX = scaledData.imagesGapX;
    imagesGapY = scaledData.imagesGapY;
    placeImagesBelow = scaledData.placeImagesBelow; // should be false
  } else {
    // --- BELOW-THE-GRID LAYOUT ---
    // Distribute images into exactly 3 columns with 6 images each.
    // If there are more than 18 images, only the first 18 are used for this page.
    imageColumnsSet = distributeImagesBelowGrid(validImages, 6, 3);
    // Fit puzzle and images below the grid so that images span the full content width.
    scaledData = fitPuzzleAndImagesBelow({
      puzzleWidth,
      puzzleHeight,
      maxImgWidth,
      maxImgHeight,
      imagesGapX,
      imagesGapY,
      gapBelowPuzzle: 20, // vertical gap between grid and images
      imageColumns: imageColumnsSet,
      contentWidth,
      leftoverHeight,
    });
    // Update dimensions using below-grid scaling.
    puzzleWidth = scaledData.puzzleWidth;
    puzzleHeight = scaledData.puzzleHeight;
    maxImgWidth = scaledData.maxImgWidth;
    maxImgHeight = scaledData.maxImgHeight;
    imagesGapX = scaledData.imagesGapX;
    imagesGapY = scaledData.imagesGapY;
    var gapBelowPuzzle = scaledData.gapBelowPuzzle; // vertical gap between grid and images
    placeImagesBelow = scaledData.placeImagesBelow; // should be true
  }

  // 12) Load font (and logo) then continue with drawing everything.
  loadFontAndContinue(pdf);

  // ----------------------- Helper Functions -----------------------------

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
    return { validImages, imageCount, hasImages };
  }

  function createPDF() {
    return new jspdf.jsPDF("portrait", "pt", "A4");
  }

  function getPageDimensions(pdf) {
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const marginLeft = 50,
      marginRight = 50,
      marginTop = 60,
      marginBottom = 30;
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
    if (wordCount <= 10) baseCellSize = 40;
    else if (wordCount <= 20) baseCellSize = 35;
    else if (wordCount <= 30) baseCellSize = 30;
    else baseCellSize = 25;
    if (gridSize > 15) baseCellSize = Math.min(baseCellSize, 25);
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
    let currentRowWidth = 0,
      currentRowMaxHeight = 0;
    words.forEach((word) => {
      const text = word.toUpperCase();
      const { w: textWidth, h: textHeight } = pdf.getTextDimensions(text);
      const boxWidth = textWidth + horizontalPadding * 2;
      const boxHeight = textHeight + verticalPadding * 2;
      if (currentRowWidth + boxWidth > availableWidth) {
        rows.push(currentRowMaxHeight);
        currentRowWidth = boxWidth + wordBoxGap;
        currentRowMaxHeight = boxHeight;
      } else {
        currentRowWidth += boxWidth + wordBoxGap;
        currentRowMaxHeight = Math.max(currentRowMaxHeight, boxHeight);
      }
    });
    if (currentRowWidth > 0) rows.push(currentRowMaxHeight);
    const totalGap = (rows.length - 1) * wordBoxGap;
    const totalRowsHeight = rows.reduce((sum, h) => sum + h, 0);
    return totalRowsHeight + totalGap + topPadding;
  }

  function getImagesLayout(imageCount, hasImages) {
    let maxImgWidth = imageCount > 10 ? 100 : 140;
    let maxImgHeight = imageCount > 10 ? 80 : 100;
    const imagesGapY = 15,
      imagesGapX = 15;
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
    leftoverHeight,
    minCellSizeThreshold = 20,
  }) {
    // SIDE-BY-SIDE LAYOUT
    const numColumns = imageColumns.length;
    const imagesListingWidth = numColumns
      ? numColumns * maxImgWidth + (numColumns - 1) * imagesGapX
      : 0;
    const boundingWidth =
      puzzleWidth +
      (imagesListingWidth ? puzzleImagesGap : 0) +
      imagesListingWidth;
    let scale = boundingWidth > contentWidth ? contentWidth / boundingWidth : 1;
    scale = Math.min(scale, 1);
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
      placeImagesBelow: false,
    };
  }

  let imagesX = marginLeft;

  function fitPuzzleAndImagesBelow({
    puzzleWidth,
    puzzleHeight,
    maxImgWidth,
    maxImgHeight,
    imagesGapX,
    imagesGapY,
    gapBelowPuzzle,
    imageColumns,
    contentWidth,
    leftoverHeight,
  }) {
    // First, scale puzzle horizontally if needed.
    let scale = 1;
    if (puzzleWidth > contentWidth) {
      scale = contentWidth / puzzleWidth;
    }
    puzzleWidth *= scale;
    puzzleHeight *= scale;

    // For images below, span the full available width.
    const imagesSideMargin = 0;
    const availableImagesWidth = contentWidth - imagesSideMargin * 2;
    const numColumns = imageColumns.length; // Should be 3.
    if (numColumns > 1) {
      maxImgWidth =
        (availableImagesWidth - (numColumns - 1) * imagesGapX) / numColumns;
    } else {
      maxImgWidth = Math.min(availableImagesWidth, maxImgWidth);
    }

    // Calculate images listing height.
    let imagesListingHeight = calculateImagesListingHeight(
      imageColumns,
      maxImgHeight,
      imagesGapY
    );

    // Total required height = puzzle + gap + images.
    let totalRequiredHeight =
      puzzleHeight + gapBelowPuzzle + imagesListingHeight;
    if (totalRequiredHeight > leftoverHeight) {
      const verticalScale = leftoverHeight / totalRequiredHeight;
      puzzleWidth *= verticalScale;
      puzzleHeight *= verticalScale;
      maxImgWidth *= verticalScale;
      maxImgHeight *= verticalScale;
      gapBelowPuzzle *= verticalScale;
      imagesGapY *= verticalScale;
    }
    return {
      puzzleWidth,
      puzzleHeight,
      maxImgWidth,
      maxImgHeight,
      gapBelowPuzzle,
      imagesGapX,
      imagesGapY,
      placeImagesBelow: true,
    };
  }

  function loadFontAndContinue(pdf) {
    const fontUrl = window.isAdmin
      ? frontendData.url + "assets/fonts/NotoSans-Regular.ttf"
      : wordSearchData.url + "assets/fonts/NotoSans-Regular.ttf";
    const logoUrl = window.isAdmin
      ? frontendData.url + "assets/images/LOGO-Edu.png"
      : wordSearchData.url + "assets/images/LOGO-Edu.png";
    Promise.all([loadResourceAsBase64(fontUrl), loadResourceAsBase64(logoUrl)])
      .then(([fontBase64, logoBase64]) => {
        pdf.addFileToVFS("NotoSans-Regular.ttf", fontBase64.split(",")[1]);
        pdf.addFont("NotoSans-Regular.ttf", "NotoSans", "normal");
        pdf.setFont("NotoSans", "normal");

        // Draw header.
        const headerHeight = drawHeader(
          pdf,
          pageWidth,
          marginRight,
          marginLeft,
          gameColors,
          logoBase64
        );
        // Introduce shift-up to move word list, grid, images upward
        const shiftUp = 20; // Adjust this value as needed
        let currentY = marginTop + headerHeight - shiftUp;
        // Draw word list.
        currentY = drawWordList(
          pdf,
          words,
          wordBoxGap,
          horizontalPadding,
          verticalPadding,
          marginLeft,
          currentY
        );

        // Position puzzle.
        let puzzleX;
        if (!placeImagesBelow) {
          puzzleX = marginLeft;
        } else {
          puzzleX = marginLeft + (contentWidth - puzzleWidth) / 2;
        }
        const puzzleY = currentY;
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

        let imagesY;
        if (!placeImagesBelow) {
          imagesX = puzzleX + puzzleWidth + puzzleImagesGap;
          imagesY = puzzleY;
          drawImages(
            pdf,
            hasImages,
            imageColumnsSet,
            imagesX,
            imagesY,
            maxImgWidth,
            maxImgHeight,
            imagesGapX,
            imagesGapY,
            placeImagesBelow
          );
        } else {
          imagesX = marginLeft; // imagesSideMargin.
          imagesY = puzzleY + puzzleHeight + scaledData.gapBelowPuzzle;
          drawImagesBelowGrid(
            pdf,
            imageColumnsSet,
            imagesX,
            imagesY,
            maxImgHeight,
            imagesGapX,
            imagesGapY,
            contentWidth
          );
        }

        pdf.save("wykreslanka.pdf");
        if (typeof onComplete === "function") {
          onComplete();
        }
      })
      .catch((error) => console.error("Error loading font:", error));
  }

  function drawHeader(
    pdf,
    pageWidth,
    marginRight,
    marginLeft,
    gameColors,
    logoBase64
  ) {
    const headerHeight = 80;
    const maxImageWidth = pageWidth * 0.15;
    const maxImageHeight = headerHeight * 0.75;
    const props = pdf.getImageProperties(logoBase64);
    const aspectRatio = props.width / props.height;
    let displayWidth = maxImageWidth;
    let displayHeight = displayWidth / aspectRatio;
    if (displayHeight > maxImageHeight) {
      displayHeight = maxImageHeight;
      displayWidth = displayHeight * aspectRatio;
    }
    const imageX = pageWidth - marginRight - displayWidth;
    const imageY = headerHeight - displayHeight;
    pdf.addImage(
      logoBase64,
      "PNG",
      imageX,
      imageY,
      displayWidth,
      displayHeight
    );

    pdf.setFont("NotoSans", "normal");
    let fontSize = 22;
    pdf.setFontSize(fontSize);
    pdf.setTextColor(0, 0, 0);
    const titleText = window.pdfText?.postTitle || "Word Search Puzzle";
    const titleAreaWidth = pageWidth - marginRight - marginLeft - maxImageWidth;
    const titleLines = pdf.splitTextToSize(titleText, titleAreaWidth);
    const lineHeightFactor = pdf.getLineHeightFactor();
    let lineHeight = fontSize * lineHeightFactor;
    let totalBlockHeight = lineHeight * titleLines.length;
    const availableHeight = headerHeight - 10;
    if (totalBlockHeight > availableHeight) {
      const scaleFactor = availableHeight / totalBlockHeight;
      fontSize = fontSize * scaleFactor;
      pdf.setFontSize(fontSize);
      lineHeight = fontSize * lineHeightFactor;
      totalBlockHeight = lineHeight * titleLines.length;
    }
    const headerCenterY = headerHeight / 2;
    const topOfTextY = headerCenterY - totalBlockHeight / 2;
    let textStartY = topOfTextY + lineHeight;
    const centerX = marginLeft + marginRight / 2 + titleAreaWidth / 2;
    pdf.text(titleLines, centerX, textStartY, {
      align: "center",
      baseline: "alphabetic",
      lineHeightFactor,
    });
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
    words.forEach((word) => {
      const text = word.toUpperCase();
      const { w: textWidth, h: textHeight } = pdf.getTextDimensions(text);
      const boxWidth = textWidth + horizontalPadding * 2;
      const boxHeight = textHeight + verticalPadding * 2;
      if (xPos + boxWidth > marginLeft + availableWidth) {
        xPos = marginLeft;
        yPos += currentRowMaxHeight + wordBoxGap;
        currentRowMaxHeight = 0;
      }
      pdf.setLineWidth(0.75);
      pdf.setDrawColor(100, 100, 100);
      pdf.roundedRect(xPos, yPos, boxWidth, boxHeight, 3, 3, "D");
      pdf.setFontSize(11);
      pdf.setTextColor(0, 0, 0);
      const textX = xPos + boxWidth / 2;
      const textY = yPos + boxHeight / 2;
      pdf.text(text, textX, textY, { align: "center", baseline: "middle" });
      xPos += boxWidth + wordBoxGap;
      currentRowMaxHeight = Math.max(currentRowMaxHeight, boxHeight);
    });
    currentY = yPos + currentRowMaxHeight + 30;
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
      pdf.line(
        puzzleX + i * cellW,
        puzzleY,
        puzzleX + i * cellW,
        puzzleY + puzzleHeight
      );
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
    startX,
    startY,
    maxImgWidth,
    maxImgHeight,
    imagesGapX,
    imagesGapY,
    placeImagesBelow
  ) {
    if (!hasImages || !imageColumns.length) return;
    if (!placeImagesBelow) {
      // SIDE-BY-SIDE layout
      imageColumns.forEach((colImgs, colIndex) => {
        const colX = imagesX + colIndex * (maxImgWidth + imagesGapX);
        let currentColY = startY;
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
            `[data-word="${entry.wordText?.toUpperCase()}"] img`
          );
          if (imgElem && imgElem.complete && imgElem.naturalHeight !== 0) {
            try {
              const ratio = imgElem.naturalWidth / imgElem.naturalHeight;
              let drawWidth = maxImgWidth;
              let drawHeight = maxImgHeight;
              if (maxImgWidth / maxImgHeight > ratio) {
                drawWidth = maxImgHeight * ratio;
              } else {
                drawHeight = maxImgWidth / ratio;
              }
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
  }

  function drawImagesBelowGrid(
    pdf,
    columns,
    startX,
    startY,
    baseMaxImgHeight,
    imagesGapX,
    imagesGapY,
    contentWidth
  ) {
    // Determine the maximum number of rows (based on the column with the most images)
    const maxRows = Math.max(...columns.map((col) => col.length));
    let currentY = startY;

    // Loop over each row index.
    for (let row = 0; row < maxRows; row++) {
      // Gather images present in the current row.
      const rowImages = [];
      for (let col = 0; col < columns.length; col++) {
        if (columns[col][row]) {
          rowImages.push(columns[col][row]);
        }
      }
      if (rowImages.length === 0) break; // no images in this row, exit loop.

      // For full rows (i.e. rows with images count equal to columns.length), use the standard gap.
      // For the last (incomplete) row:
      // - If it contains exactly one image, no gap is used.
      // - Otherwise, use the provided imagesGapX.
      let gapX = imagesGapX;
      if (row === maxRows - 1 && rowImages.length === 1) {
        gapX = 0;
      }

      // Compute the cell width. The available width is reduced by the total horizontal gaps.
      const totalGaps = (rowImages.length - 1) * gapX;
      const cellWidth = (contentWidth - totalGaps) / rowImages.length;

      let currentX = startX;
      // Process each image in the current row.
      for (let i = 0; i < rowImages.length; i++) {
        const entry = rowImages[i];
        const imgElem = document.querySelector(
          `[data-word="${entry.wordText?.toUpperCase()}"] img`
        );
        if (imgElem && imgElem.complete && imgElem.naturalHeight !== 0) {
          try {
            const ratio = imgElem.naturalWidth / imgElem.naturalHeight;
            const availableWidth = cellWidth;
            const availableHeight = baseMaxImgHeight;
            let drawWidth, drawHeight;

            // Scale the image while preserving its aspect ratio.
            if (availableWidth / availableHeight > ratio) {
              drawWidth = availableHeight * ratio;
              drawHeight = availableHeight;
            } else {
              drawWidth = availableWidth;
              drawHeight = availableWidth / ratio;
            }

            // Center the image within its cell horizontally and vertically.
            const xPos = currentX + (cellWidth - drawWidth) / 2;
            const yPos = currentY + (baseMaxImgHeight - drawHeight) / 2;

            // Draw the border exactly around the image.
            pdf.setDrawColor(210, 210, 230);
            pdf.setLineWidth(0.5);
            pdf.roundedRect(xPos, yPos, drawWidth, drawHeight, 3, 3, "D");

            // Draw the image.
            pdf.addImage(imgElem, "JPEG", xPos, yPos, drawWidth, drawHeight);
          } catch (err) {
            console.error("Error adding image", err);
          }
        }
        // Advance the horizontal position for the next image.
        currentX += cellWidth + gapX;
      }
      // Move down to the next row.
      currentY += baseMaxImgHeight + imagesGapY;
    }
  }

  function loadResourceAsBase64(url) {
    return fetch(url)
      .then((res) => res.blob())
      .then(
        (blob) =>
          new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onloadend = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(blob);
          })
      );
  }

  function cleanLetter(letter) {
    return letter.replace(/[\u0000-\u001F\u007F-\u009F]/g, "");
  }

  function calculateImagesListingHeight(
    imageColumns,
    maxImgHeight,
    imagesGapY
  ) {
    let maxColumnHeight = 0;
    imageColumns.forEach((col) => {
      if (col.length > 0) {
        const colHeight =
          col.length * maxImgHeight + (col.length - 1) * imagesGapY;
        if (colHeight > maxColumnHeight) maxColumnHeight = colHeight;
      }
    });
    return maxColumnHeight;
  }

  function distributeImagesBelowGrid(validImages, maxColumns, maxRows) {
    // Create exactly maxColumns empty arrays.
    const columns = Array.from({ length: maxColumns }, () => []);
    const maxImages = maxColumns * maxRows; // Maximum images to render.
    for (let i = 0; i < validImages.length && i < maxImages; i++) {
      // Round-robin assignment: place each image in one of the maxColumns columns.
      const colIndex = i % maxColumns;
      columns[colIndex].push(validImages[i]);
    }
    return columns;
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
