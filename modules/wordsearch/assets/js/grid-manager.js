import {
  // showLoadingIndicator,
  hideLoadingIndicator,
} from "./game-mechanics.js";

export function getDynamicCanvasSize(
  containerId = "game-container",
  gridSize,
  maxCanvasSize = 800
) {
  const container = document.getElementById(containerId);
  if (!container) {
    console.error(`Container with id ${containerId} not found.`);
    return { width: 600, height: 600 };
  }
  // Use the container width to determine a base cell size
  let containerWidth = window.newWidth || maxCanvasSize;

  // Example: keep the puzzle square by using containerWidth alone
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

// Helper function to wrap the worker logic in a promise.
function runResizeWorker({ gridSize, newWidth, newHeight }) {
  return new Promise((resolve, reject) => {
    const worker = new Worker(
      window.isAdmin
        ? frontendData.url + "assets/js/resize-worker.js"
        : wordSearchData.url + "assets/js/resize-worker.js"
    );
    worker.onmessage = (e) => {
      resolve(e.data);
      worker.terminate();
    };
    worker.onerror = (err) => {
      reject(err);
      worker.terminate();
    };
    worker.postMessage({ gridSize, newWidth, newHeight });
  });
}

export async function resizeGame(
  newWidth,
  newHeight,
  scene,
  letterTexts = [],
  gridSize,
  gridMatrix
) {
  // This is the asynchronous line that waits for the worker response:
  const { cellSize, cellHalfSize, fontSize, positions } = await runResizeWorker(
    { gridSize, newWidth, newHeight }
  );

  // === Begin Main-Thread Rendering Code ===

  // Cache calculations and avoid repeated work
  const gameCanvas = scene.sys.game.canvas;
  gameCanvas.width = newWidth;
  gameCanvas.height = newHeight;

  // 1) Let Phaser know the new dimensions:
  scene.scale.resize(newWidth, newHeight);
  scene.cameras.main.setScroll(0, 0);
  // scene.cameras.main.setZoom(1);

  // 3. Destroy old objects to free memory.
  if (scene.gridContainer) {
    scene.gridContainer.destroy();
  } else {
    scene.children.each((child) => child.destroy());
  }

  // Reset letter texts array to prevent issues with old references
  scene.letterTexts = null;
  window.letterTexts = null;

  // 2) Reset the camera to top-left so it doesn't scroll upward.

  // Optionally center the camera if you prefer:
  // scene.cameras.main.centerOn(0, 0);

  // Container-Based Rendering: Main container for better batching
  scene.gridContainer = scene.add.container(0, 0);

  // Optimized Text Rendering: Enhanced style with better WebGL settings

  const textStyle = {
    fontFamily: window.customStyles["fontFamily"] || "rgb(236,216,179)",
    fontSize: `${fontSize}px`,
    color: window.customStyles["fontColor"],
    fontWeight: "bold",
    stroke: "#ffffff",
    strokeThickness: fontSize > 20 ? 1 : 0.5,
    shadow: {
      offsetX: 1,
      offsetY: 1,
      color: "rgba(0,0,0,0.08)",
      blur: fontSize > 20 ? 2 : 1,
      stroke: false,
      fill: true,
    },
    resolution: 2,
    // resolution: Math.max(1, Math.floor(cellSize / 40)),
    padding: { x: 1, y: 1 }, // Prevent text clipping
  };

  // Texture Atlases: Create background texture only once
  if (!scene.textures.exists("backgroundGradient")) {
    const bgGraphics = scene.make.graphics({ x: 0, y: 0, add: false });
    bgGraphics.fillStyle(0xf5e9d1, 1);
    bgGraphics.fillGradientStyle(
      0xf5d992,
      0xe6ba6c,
      0xdca745,
      0xc89836,
      1,
      1,
      1,
      1
    );
    bgGraphics.fillRect(0, 0, 100, 100);
    bgGraphics.generateTexture("backgroundGradient", 100, 100);
    bgGraphics.destroy();
  }

  // Reduced Graphics Operations: Use texture instead of direct drawing
  const backgroundSprite = scene.add.tileSprite(
    0,
    0,
    newWidth,
    newHeight,
    "backgroundGradient"
  );
  backgroundSprite.setOrigin(0, 0);
  scene.gridContainer.add(backgroundSprite);

  // Texture Atlases: Create grid pattern texture
  if (!scene.textures.exists("gridPattern")) {
    const gridGraphics = scene.make.graphics({ x: 0, y: 0, add: false });
    gridGraphics.lineStyle(1, 0xd6a651, 0.2);
    gridGraphics.strokeRect(0, 0, cellSize, cellSize);
    gridGraphics.generateTexture("gridPattern", cellSize, cellSize);
    gridGraphics.destroy();
  }

  // Reduced Graphics Operations: Use tileSprite for grid
  const gridSprite = scene.add.tileSprite(
    0,
    0,
    newWidth,
    newHeight,
    "gridPattern"
  );
  gridSprite.setOrigin(0, 0);
  scene.gridContainer.add(gridSprite);

  // Create cell textures once - recreate if cell size changed
  const cellTextureKey = `cell_${cellSize.toFixed(2)}`;
  if (!scene.textures.exists(cellTextureKey + "_even")) {
    // Remove old cell textures if they exist
    if (scene.textures.exists("evenCell")) {
      scene.textures.remove("evenCell");
      scene.textures.remove("oddCell");
    }

    // Load sound effects - ADD THIS SECTION
    if (!scene.sound.get("letterHover")) {
      scene.load.audio("letterHover", "assets/audio/hover.mp3");
      scene.load.once("complete", function () {});
      scene.load.start();
    }

    const evenColor = window.customStyles["evenCellBgColor"];
    const oddColor = window.customStyles["oddCellBgColor"];

    const evenGraphics = scene.make.graphics({ x: 0, y: 0, add: false });
    evenGraphics.fillStyle(evenColor, 1);
    evenGraphics.fillRect(0, 0, cellSize, cellSize);
    evenGraphics.generateTexture("evenCell", cellSize, cellSize);
    evenGraphics.destroy();

    const oddGraphics = scene.make.graphics({ x: 0, y: 0, add: false });
    oddGraphics.fillStyle(oddColor, 1);
    oddGraphics.fillRect(0, 0, cellSize, cellSize);
    oddGraphics.generateTexture("oddCell", cellSize, cellSize);
    oddGraphics.destroy();
  }

  // Batched Rendering: Container for cell backgrounds
  const cellsContainer = scene.add.container(0, 0);
  scene.gridContainer.add(cellsContainer);

  // Reset sprite pools when resizing
  scene.cellPool = {
    even: [],
    odd: [],
  };
  scene.circlePool = [];
  scene.letterPool = new Map();

  // Single-Pass Grid Generation: Create all cells in one pass
  for (let row = 0; row < gridSize; row++) {
    for (let col = 0; col < gridSize; col++) {
      const isEvenCell = (row + col) % 2 === 0;
      const textureKey = isEvenCell ? "evenCell" : "oddCell";

      // Create new sprite
      const cellSprite = scene.add.sprite(
        col * cellSize,
        row * cellSize,
        textureKey
      );
      cellSprite.setOrigin(0, 0); // Top-left origin
      cellsContainer.add(cellSprite);
    }
  }

  // Create highlight circle texture with proper size
  const circleTextureKey = `highlightCircle_${cellSize.toFixed(2)}`;
  if (!scene.textures.exists(circleTextureKey)) {
    // Remove old circle texture if it exists
    if (scene.textures.exists("highlightCircle")) {
      scene.textures.remove("highlightCircle");
    }

    const circleGraphics = scene.make.graphics({ x: 0, y: 0, add: false });
    circleGraphics.fillStyle(0xffffff, 0.1);
    circleGraphics.fillCircle(cellSize * 0.4, cellSize * 0.4, cellSize * 0.4);
    circleGraphics.generateTexture(
      "highlightCircle",
      cellSize * 0.8,
      cellSize * 0.8
    );
    circleGraphics.destroy();
  }

  // Load sound effects only once
  if (!scene.sound.get("letterHover")) {
    scene.load.audio("letterHover", "assets/audio/hover.mp3");
    scene.load.once("complete", function () {});
    scene.load.start();
  }

  // Create letter textures for common letters (Texture Atlas)
  if (gridSize > 10) {
    const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    for (let i = 0; i < alphabet.length; i++) {
      const letter = alphabet[i];
      const letterTextureKey = `letter_${letter}_${fontSize.toFixed(0)}`;

      // Remove old letter texture if size changed
      if (
        scene.textures.exists(`letter_${letter}`) &&
        !scene.textures.exists(letterTextureKey)
      ) {
        scene.textures.remove(`letter_${letter}`);
      }

      if (!scene.textures.exists(letterTextureKey)) {
        createLetterTexture(scene, letter, textStyle, letterTextureKey);
      }
    }
  }

  // 8. Create letter and highlight containers.
  const circlesContainer = scene.add.container(0, 0);
  const lettersContainer = scene.add.container(0, 0);
  scene.gridContainer.add(circlesContainer);
  scene.gridContainer.add(lettersContainer);

  // Create letter objects more efficiently with fixed positioning
  window.letterTexts = createLetterTextsFixedPositioning(
    scene,
    gridSize,
    positions,
    gridMatrix,
    textStyle
  );

  // Save reference
  scene.letterTexts = window.letterTexts;

  if (scene.letterTexts) {
    const container = document.getElementById("game-container");
    if (typeof hideLoadingIndicator === "function") {
      hideLoadingIndicator();
      container.style.pointerEvents = "auto";
    }
  }

  // Finalize any other cleanup (if necessary)

  // Debug log
}

// Helper function to create letter textures
function createLetterTexture(scene, letter, style, textureKey) {
  const tempText = scene.add.text(0, 0, letter, style);
  const textWidth = tempText.width;
  const textHeight = tempText.height;

  const rt = scene.add.renderTexture(0, 0, textWidth + 4, textHeight + 4);
  rt.draw(tempText, 2, 2);

  // Use the version-specific texture key if provided
  rt.saveTexture(textureKey || `letter_${letter}`);

  // Clean up
  rt.destroy();
  tempText.destroy();
}

// Fixed function for letter creation with better positioning
function createLetterTextsFixedPositioning(
  scene,
  gridSize,
  positions,
  gridMatrix,
  textStyle
) {
  const letterTexts = [];

  // Container-Based Rendering: Separate containers for organization
  const circlesContainer = scene.add.container(0, 0);
  const lettersContainer = scene.add.container(0, 0);

  scene.gridContainer.add(circlesContainer);
  scene.gridContainer.add(lettersContainer);

  // Single-Pass Grid Generation: Process everything in one efficient loop
  for (let row = 0; row < gridSize; row++) {
    letterTexts[row] = [];

    for (let col = 0; col < gridSize; col++) {
      // Calculate positions consistently
      // const x = col * cellSize + cellHalfSize;
      // const y = row * cellSize + cellHalfSize;

      // Use computed positions from the worker.
      const { x, y } = positions[row][col];

      // Get letter from grid matrix with safety checks
      let letter = "";
      if (gridMatrix && gridMatrix[row] && gridMatrix[row][col]) {
        letter = gridMatrix[row][col].letter || "";
      }

      // Create highlight circle
      const circleSprite = scene.add.sprite(x, y, "highlightCircle");
      circleSprite.setVisible(false); // Initially hidden
      circlesContainer.add(circleSprite);

      // Create letter text with optimizations
      // Letter Hover Shadow Effect Fix

      if (letter) {
        let letterObj;

        // Use texture-based approach for all letters
        const letterTextureKey = `letter_${letter}_${textStyle.fontSize.replace(
          "px",
          ""
        )}`;
        const fallbackKey = `letter_${letter}`;

        // Create texture if doesn't exist
        if (
          !scene.textures.exists(letterTextureKey) &&
          !scene.textures.exists(fallbackKey)
        ) {
          createLetterTexture(scene, letter, textStyle, fallbackKey);
        }

        // Use the appropriate texture
        const textureToUse = scene.textures.exists(letterTextureKey)
          ? letterTextureKey
          : fallbackKey;

        // Create letter sprite with correct positioning
        letterObj = scene.add.sprite(x, y, textureToUse);
        letterObj.setOrigin(0.5, 0.5);

        // Create a glow sprite that will serve as our shadow effect
        const glowSprite = scene.add.sprite(x, y, textureToUse);
        glowSprite.setOrigin(0.5, 0.5);
        glowSprite.setTint(0xff5f00); // Vibrant orange-coral instead of 0xffd700
        glowSprite.setScale(1.2);
        glowSprite.setAlpha(0);
        glowSprite.setBlendMode(Phaser.BlendModes.ADD); // Add blend mode for glow effect
        glowSprite.setDepth(letterObj.depth - 1); // Ensure it's behind the letter

        // Add to container for batch rendering (add glow first so it's behind)
        lettersContainer.add(glowSprite);
        lettersContainer.add(letterObj);

        // Add text-specific data for compatibility
        letterObj.setText = function (newText) {
          // Handle text change if needed
          if (newText !== letter) {
            const newTextureKey = `letter_${newText}_${textStyle.fontSize.replace(
              "px",
              ""
            )}`;
            const newFallbackKey = `letter_${newText}`;
            const textureToUse = scene.textures.exists(newTextureKey)
              ? newTextureKey
              : newFallbackKey;

            if (scene.textures.exists(textureToUse)) {
              this.setTexture(textureToUse);
              glowSprite.setTexture(textureToUse); // Update glow sprite texture too
            } else {
              // Create the texture if it doesn't exist
              createLetterTexture(scene, newText, textStyle, newFallbackKey);
              this.setTexture(newFallbackKey);
              glowSprite.setTexture(newFallbackKey); // Update glow sprite texture too
            }
          }
        };

        // Store reference to the glow sprite
        letterObj.glowSprite = glowSprite;

        // Add required methods for compatibility
        letterObj.setColor = function (color) {
          // Convert color to tint
          const colorNum = Phaser.Display.Color.HexStringToColor(color).color;
          this.setTint(colorNum);
          return this;
        };

        letterObj.setStroke = function (color, width) {
          // Just record it for compatibility
          this._strokeColor = color;
          this._strokeWidth = width;
          return this;
        };

        // Implement setShadow to actually apply visual effects
        letterObj.setShadow = function (
          offsetX,
          offsetY,
          color,
          blur,
          shadowStroke,
          shadowFill
        ) {
          if (shadowFill) {
            // Use our glow sprite to create the shadow effect
            const glowSprite = this.glowSprite;
            if (glowSprite) {
              if (color) {
                const colorNum =
                  Phaser.Display.Color.HexStringToColor(color).color;
                glowSprite.setTint(colorNum);
              }

              // Set position offset if provided
              glowSprite.x = this.x + (offsetX || 0);
              glowSprite.y = this.y + (offsetY || 0);

              // Set blur (implemented as scale - more blur = bigger glow)
              const blurAmount = blur || 0;
              const scaleMultiplier = 1 + blurAmount * 0.02;
              glowSprite.setScale(
                this.scaleX * scaleMultiplier,
                this.scaleY * scaleMultiplier
              );

              // Make the glow visible
              glowSprite.setAlpha(0.7);
            }
          } else {
            // Hide the glow if shadowFill is false
            if (this.glowSprite) {
              this.glowSprite.setAlpha(0);
            }
          }
          return this;
        };

        // Add interactive behavior
        letterObj.setInteractive();

        // Use optimized event handlers
        letterObj.on("pointerover", function () {
          this.setScale(1.1);
          // Update glow sprite scale to match
          if (this.glowSprite) {
            this.glowSprite.setScale(1.1 * 1.2); // Keep the glow slightly larger
            this.glowSprite.x = this.x;
            this.glowSprite.y = this.y;
          }

          // Play sound effect on hover
          window.customStyles["toggleGridLettersSound"] &&
            scene.sound.play("letterHover", { volume: 0.5 });

          // Apply gold shadow effect by showing the glow sprite with gold tint
          this.setShadow(2, 2, "#FF9900", 8, true, true);

          // Change letter color
          if (this.setColor) this.setColor("#8B4513");

          // Find and show the corresponding circle
          circleSprite.setVisible(true);
        });

        letterObj.on("pointerout", function () {
          this.setScale(1.0);
          // Hide the shadow by setting shadowFill to false
          this.setShadow(1, 1, "#FF9900", 0, false, true);

          // Reset letter color
          if (this.setColor) this.setColor("#473214");

          // Hide the circle
          circleSprite.setVisible(false);
        });

        // Store data
        letterTexts[row][col] = letterObj;
        letterObj.setData("row", row);
        letterObj.setData("col", col);
        letterObj.setData("circleSprite", circleSprite);

        // Add to container for batch rendering
        lettersContainer.add(letterObj);
      }
    }
  }

  return letterTexts;
}

// export function resizeGame(
//   newWidth,
//   newHeight,
//   scene,
//   letterTexts = [],
//   gridSize,
//   gridMatrix
// ) {
//   const gameCanvas = scene.sys.game.canvas;
//   gameCanvas.width = newWidth;
//   gameCanvas.height = newHeight;
//   scene.children.removeAll();
//   const cellSize = Math.min(newWidth, newHeight) / gridSize;
//   const fontSize = Math.floor(cellSize * 0.5);
//   // const fontColor = customStyles ? customStyles["fontColor"] : "#000";
//   // const fontFamily = customStyles ? customStyles["fontFamily"] : "Roboto";

//   // UPDATED: Modified colors to maintain the gold theme but with better contrast
//   const fontColor = "#473214";
//   const fontFamily = "Georgia, serif";

//   const finalStyle = {
//     fontFamily: "Georgia, serif",
//     fontSize: `${fontSize}px`,
//     color: "#5c4012",
//     fontWeight: "bold",
//     stroke: "#ffffff",
//     strokeThickness: 0.5,
//     shadow: {
//       offsetX: 1,
//       offsetY: 1,
//       color: "rgba(0,0,0,0.08)",
//       blur: 1,
//       stroke: false,
//       fill: true,
//     },
//   };

//   // NEW: Add subtle gradient background
//   const backgroundGradient = scene.add.graphics();
//   backgroundGradient.fillStyle(0xf5e9d1, 1);
//   backgroundGradient.fillGradientStyle(
//     0xf5d992,
//     0xe6ba6c,
//     0xdca745,
//     0xc89836,
//     1,
//     1,
//     1,
//     1
//   );
//   backgroundGradient.fillRect(0, 0, newWidth, newHeight);

//   // NEW: Add subtle grid pattern
//   const gridPattern = scene.add.graphics();
//   gridPattern.lineStyle(1, 0xd6a651, 0.2);

//   // Draw grid lines
//   for (let i = 0; i <= gridSize; i++) {
//     // Horizontal lines
//     gridPattern.moveTo(0, i * cellSize);
//     gridPattern.lineTo(newWidth, i * cellSize);

//     // Vertical lines
//     gridPattern.moveTo(i * cellSize, 0);
//     gridPattern.lineTo(i * cellSize, newHeight);
//   }

//   // NEW: Add cell backgrounds with alternating patterns
//   const cellBackgrounds = scene.add.graphics();
//   for (let row = 0; row < gridSize; row++) {
//     for (let col = 0; col < gridSize; col++) {
//       // Create subtle checkerboard pattern
//       const isEvenCell = (row + col) % 2 === 0;
//       cellBackgrounds.fillStyle(isEvenCell ? 0xecd8b3 : 0xf5e9d1, 1);

//       cellBackgrounds.fillRect(
//         col * cellSize,
//         row * cellSize,
//         cellSize,
//         cellSize
//       );
//     }
//   }

//   // Load sound effects - ADD THIS SECTION
//   if (!scene.sound.get("letterHover")) {
//     scene.load.audio("letterHover", "assets/audio/hover.mp3");
//     scene.load.once("complete", function () {
//     });
//     scene.load.start();
//   }

//   for (let row = 0; row < gridSize; row++) {
//     // Initialize each row if not already defined
//     if (!letterTexts[row]) {
//       letterTexts[row] = [];
//     }
//     for (let col = 0; col < gridSize; col++) {
//       if (letterTexts[row][col]) {
//         letterTexts[row][col].destroy();
//       }
//       // NEW: Add subtle circular highlights behind letters
//       scene.add.circle(
//         col * cellSize + cellSize * 0.5,
//         row * cellSize + cellSize * 0.5,
//         cellSize * 0.4,
//         0xffffff,
//         0.1
//       );

//       const x = col * cellSize + cellSize * 0.5;
//       const y = row * cellSize + cellSize * 0.5;
//       const letterObj = scene.add
//         .text(x, y, gridMatrix[row][col].letter, finalStyle)
//         .setOrigin(0.5);
//       // Add interactive hover effect
//       // Add interactive hover effect
//       letterObj.setInteractive();
//       letterObj.on("pointerover", function () {
//         this.setScale(1.1);
//         // Play sound effect on hover - ADD THIS LINE
//         scene.sound.play("letterHover", { volume: 0.5 });
//         // Add glow effect - ADD THIS
//         this.setStroke("#FF9900", 2.5);
//         this.setShadow(2, 2, "#ffd700", 6, true, true);
//         this.setColor("#8B4513");
//       });
//       letterObj.on("pointerout", function () {
//         this.setScale(1.0);
//         this.setStroke("#b8860b", 0.7);
//         this.setShadow(1, 1, "#ffd700", 2, false, true);
//         this.setColor("#473214");
//       });
//       // Store necessary data
//       letterTexts[row][col] = letterObj;
//       // letterObj.setData("layer", "second");
//       letterObj.setData("row", row);
//       letterObj.setData("col", col);

//       if (letterTexts[row][col]) {
//         if (typeof hideLoadingIndicator === "function") {
//           hideLoadingIndicator();
//         }
//       }
//     }
//   }
// }

export function computeEffectiveGridSize(wordData) {
  // Exit early if no words
  if (!wordData || wordData.length === 0) {
    return 0;
  }

  // Find the longest word length
  let maxLength = 0;
  for (let i = 0; i < wordData.length; i++) {
    const wordLength = wordData[i].length;
    if (wordLength > maxLength) {
      maxLength = wordLength;
    }
  }

  /*
    1) Base size is the maximum of:
       - the longest word length,
       - a smaller multiplier of wordData.length (for multi-word puzzles).
    2) A modest diagonal buffer helps with diagonal placements.
       - e.g. 2 cells or up to maxLength * 0.25, whichever is smaller.
    3) Finally, clamp to a max of 30 to avoid very large puzzles.
  */

  // Base size: keep it simpler (use 6 instead of 15 in the sqrt factor).
  const baseSize = Math.max(
    maxLength,
    Math.ceil(Math.sqrt(wordData.length * 6))
  );

  // For a small diagonal buffer, use either 2 or up to 25% of the longest word.
  const diagonalBuffer = Math.min(2, Math.ceil(maxLength * 0.25));

  // Proposed grid size
  let gridSize = baseSize + diagonalBuffer;

  // Ensure at least longestWord+2 (so a single long word can fit diagonally)
  gridSize = Math.max(gridSize, maxLength + 2);

  // Cap to 30 (or you can pick a different number if desired)
  gridSize = Math.min(gridSize, 30);
  return gridSize;
}

export function debouncedResize(func, wait) {
  let timeout;
  return function (...args) {
    const context = this;
    clearTimeout(timeout);
    timeout = setTimeout(() => {
      func.apply(context, args);
    }, wait);
  };
}
