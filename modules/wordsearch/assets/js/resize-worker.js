// resizeWorker.js

// Listen for messages from the main thread
self.onmessage = function (e) {
  const { gridSize, newWidth, newHeight } = e.data;
  // Compute heavy values
  const cellSize = Math.min(newWidth, newHeight) / gridSize;
  const cellHalfSize = cellSize * 0.5;
  let fontSize = Math.floor(cellSize * 0.5);
  if (cellSize < 85) {
    fontSize = cellSize * 0.4;
  }

  // Precompute positions for each cell
  const positions = [];
  for (let row = 0; row < gridSize; row++) {
    const rowPositions = [];
    for (let col = 0; col < gridSize; col++) {
      const x = col * cellSize + cellHalfSize;
      const y = row * cellSize + cellHalfSize;
      rowPositions.push({ x, y });
    }
    positions.push(rowPositions);
  }

  // Send back computed values
  self.postMessage({
    cellSize,
    cellHalfSize,
    fontSize,
    positions,
  });
};
