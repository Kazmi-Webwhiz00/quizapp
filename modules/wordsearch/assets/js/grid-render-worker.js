// grid-render-worker.js
self.onmessage = function (e) {
  const { operation, data } = e.data;

  switch (operation) {
    case "calculateGridPositions":
      const positions = calculateGridPositions(data);
      self.postMessage({ type: "positions", result: positions });
      break;

    case "prepareLetterStyles":
      const styles = prepareLetterStyles(data);
      self.postMessage({ type: "styles", result: styles });
      break;

    case "optimizeGridRendering":
      const renderingPlan = optimizeGridRendering(data);
      self.postMessage({ type: "renderingPlan", result: renderingPlan });
      break;
  }
};

// Calculate positions for all grid cells efficiently
function calculateGridPositions({
  gridSize,
  cellSize,
  offsetX = 0,
  offsetY = 0,
}) {
  const positions = new Array(gridSize);
  const cellHalfSize = cellSize * 0.5;

  for (let row = 0; row < gridSize; row++) {
    positions[row] = new Array(gridSize);

    for (let col = 0; col < gridSize; col++) {
      positions[row][col] = {
        x: offsetX + col * cellSize + cellHalfSize,
        y: offsetY + row * cellSize + cellHalfSize,
        row: row,
        col: col,
      };
    }
  }

  return positions;
}

// Prepare text styles for all letters in the grid
function prepareLetterStyles({ gridSize, cellSize, gridMatrix }) {
  const styles = new Array(gridSize);
  const fontSize = Math.floor(cellSize * 0.5);

  // Create base style
  const baseStyle = {
    fontFamily: "Georgia, serif",
    fontSize: fontSize,
    color: "#473214",
    fontWeight: "bold",
    stroke: "#ffffff",
    strokeThickness: 0.5,
    shadowOffsetX: 1,
    shadowOffsetY: 1,
    shadowColor: "rgba(0,0,0,0.08)",
    shadowBlur: 1,
    origin: 0.5,
  };

  // Create style for each letter
  for (let row = 0; row < gridSize; row++) {
    styles[row] = new Array(gridSize);

    for (let col = 0; col < gridSize; col++) {
      const letter =
        gridMatrix && gridMatrix[row] && gridMatrix[row][col]
          ? gridMatrix[row][col].letter
          : "";

      styles[row][col] = {
        ...baseStyle,
        text: letter,
      };
    }
  }

  return styles;
}

// Optimize rendering by determining most efficient update sequence
function optimizeGridRendering({
  gridSize,
  cellSize,
  gridMatrix,
  previousMatrix,
}) {
  // Determine which cells need updates
  const updates = [];
  const isNew = !previousMatrix || previousMatrix.length === 0;

  // If it's a completely new grid, render in chunks
  if (isNew) {
    // Create chunked rendering plan - break into rows for progressive rendering
    const chunks = [];
    const CHUNK_SIZE = 4; // 4 rows at a time

    for (let i = 0; i < gridSize; i += CHUNK_SIZE) {
      const rowsToRender = Math.min(CHUNK_SIZE, gridSize - i);
      chunks.push({
        startRow: i,
        endRow: i + rowsToRender - 1,
        delay: i * 10, // Slight delay between chunks for smoother rendering
      });
    }

    return {
      type: "full",
      chunks,
    };
  } else {
    // For updates, find only cells that changed
    for (let row = 0; row < gridSize; row++) {
      for (let col = 0; col < gridSize; col++) {
        const oldLetter =
          previousMatrix && previousMatrix[row] && previousMatrix[row][col]
            ? previousMatrix[row][col].letter
            : null;
        const newLetter =
          gridMatrix && gridMatrix[row] && gridMatrix[row][col]
            ? gridMatrix[row][col].letter
            : null;

        if (oldLetter !== newLetter) {
          updates.push({ row, col });
        }
      }
    }

    return {
      type: "update",
      updates,
    };
  }
}
