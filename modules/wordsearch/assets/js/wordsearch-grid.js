jQuery(document).ready(function ($) {
  setTimeout(function () {
    console.log("::wordsearchData", wordsearchData); // Check if wordsearchData exists
    var words = wordsearchData.words;
    console.log("::words", words); // Should print words if they exist
  }, 500);

  // Convert words to an array of word texts (if each entry contains a property named 'wordText')
  // var wordList = words.map(function (entry) {
  //   return entry.wordText;
  // });

  // Array of colors for valid lines (feature 3 & 4).
  const validLineColors = [
    "rgba(244, 67, 54, 0.29)",
    "rgba(255, 255, 0, 0.29)",
    "rgba(127, 17, 224, 0.40)",
    "rgba(153, 153, 153,0.40)",
    "rgba(255, 204, 102,0.60)",
    "rgba(17, 217, 224, 0.4)",
  ];

  /**************************************
   * Insert Example Words
   **************************************/
  const guessWords = ["touch"];
  guessWords.map((word) =>
    WordFindGame.insertWordBefore($("#add-word").parent(), word)
  );

  // Set default secret word.
  $("#secret-word").val("LAETITIA");
  /**************************************
   * Konva.js Drawing Variables & Functions
   **************************************/

  let isDrawing = false;
  let selectedPoints = []; // Will hold [startX, startY, endX, endY]
  let currentLine = null;
  let currentColorIndex = 0;
  let lastValidPoint = null;
  let puzzleOffsetCached = null;
  let pointerMoveScheduled = false;

  /**************************************
   * Puzzle Initialization
   **************************************/

  // Helper function: adjust the Konva canvas to match the puzzle container's inner dimensions.
  function adjustKonvaCanvas() {
    const $puzzle = $("#puzzle");
    const adjustedWidth = $puzzle.innerWidth();
    const adjustedHeight = $puzzle.innerHeight();

    // Set the Konva stage dimensions to match the actual inner content area.
    window.konvaStage.width(adjustedWidth);
    window.konvaStage.height(adjustedHeight);

    // Update the Konva container and canvas dimensions to match the inner content.
    $(".konvajs-content").css({
      width: "100%",
      height: "100%",
    });
    $("canvas").css({
      width: "100%",
      height: "100%",
    });
  }

  // Initializes the Konva overlay. Call this after the puzzle grid is rendered.
  function initKonvaOverlay() {
    // Destroy any existing stage to prevent conflicts.
    if (window.konvaStage && window.konvaStage.destroy) {
      window.konvaStage.destroy();
      window.konvaStage = null;
    }
    $("#konvaLayer").remove();

    // Ensure the puzzle container is relatively positioned.
    $("#puzzle").css({ position: "relative" });
    // Append an overlay container for Konva.
    $("#puzzle").append(
      '<div id="konvaLayer" style="position:absolute; top:0; left:0; width:100%; height:100%; z-index:999;"></div>'
    );

    // Initialize the Konva stage using the current puzzle container dimensions.
    try {
      var stage = new Konva.Stage({
        container: "konvaLayer",
        width: $("#puzzle").width(),
        height: $("#puzzle").height(),
      });
    } catch (error) {
      console.error("Failed to initialize Konva stage:", error);
      return;
    }
    var layer = new Konva.Layer();
    stage.add(layer);
    window.konvaStage = stage;
    window.konvaLayer = layer;
    console.log("Konva stage initialized:", stage);

    // Adjust the Konva canvas dimensions to match the inner content area.
    adjustKonvaCanvas();
  }

  function recreate() {
    // Set the desired grid size (e.g., 10x10 grid)
    var gridSize = 10; // number of cells per row/column
    var cellSize = 40; // intended cell dimensions (width & height) in pixels
    var gap = 30;
    const $puzzle = $("#puzzle");

    // Get actual width and height excluding padding (content-box fix)
    const paddingLeft = parseInt($puzzle.css("padding-left"), 10) || 0;
    const paddingTop = parseInt($puzzle.css("padding-top"), 10) || 0;

    // Calculate container dimensions including the gaps.
    var containerWidth = gridSize * cellSize + (gridSize - 1) * gap;
    var containerHeight = gridSize * cellSize + (gridSize - 1) * gap;

    $("#puzzle")
      .empty()
      .css({
        display: "grid",
        "grid-template-columns": `repeat(${gridSize}, ${cellSize}px)`,
        "grid-gap": gap + "px",
        width: containerWidth + "px",
        height: containerHeight + "px",
        position: "relative",
        padding: "0", // Padding added as desired.
        "box-sizing": "border-box", // Ensure padding is included in the dimensions.
      });

    setTimeout(adjustKonvaCanvas, 0);

    // Update options dynamically using the calculated rows and columns.
    var options = {
      height: gridSize, // number of rows
      width: gridSize, // number of columns
      allowedMissingWords: 0,
      maxGridGrowth: 10,
      maxAttempts: 200,
      fillBlanks: true,
      orientations: ["horizontal", "vertical", "diagonal"],
      preferOverlap: true,
      gridSize: gridSize,
      createCell: function (letter) {
        return $("<div></div>")
          .addClass("puzzleSquare")
          .text(letter)
          .css({
            width: cellSize + "px",
            height: cellSize + "px",
            border: "none",
            "text-align": "center",
            "line-height": cellSize + "px",
            "box-sizing": "border-box",
          });
      },
    };

    // Initialize and render the puzzle grid.
    var game;
    try {
      game = new WordFindGame("#puzzle", options);
      console.log("Game initialized:", game);
    } catch (error) {
      $("#result-message")
        .text("😞 " + error + ", try to specify less ones")
        .css({ color: "red" });
      return;
    }
    wordfind.print(game);
    // Make sure any row wrappers don't interfere with grid layout.
    $(".puzzleRow").css("display", "contents");
    window.game = game;

    // Recalculate the actual cell height and adjust the container height accordingly.
    var actualCellHeight = $(".puzzleSquare").outerHeight(true); // includes margin
    var actualContainerHeight =
      gridSize * actualCellHeight + (gridSize - 1) * gap;
    $("#puzzle").css("height", actualContainerHeight + "px");

    // Initialize the Konva overlay and adjust the canvas.
    initKonvaOverlay();
    adjustKonvaCanvas();
  }

  // Create the puzzle on page load.
  recreate();

  // Update the Konva canvas on window resize.
  $(window).resize(function () {
    adjustKonvaCanvas();
  });

  adjustKonvaCanvas();

  // Check if a formed word is valid.
  function isValidGuessWord(word) {
    // (Make sure your valid words list and letter extraction use the same case.)
    return guessWords.includes(word.toLowerCase());
  }

  // Returns the center of a grid cell relative to #puzzle.
  function getCellCenter(cell) {
    const $cell = $(cell);
    const $puzzle = $("#puzzle");
    if (!$cell.length || !$puzzle.length) return [0, 0];

    const cellOffset = $cell.offset();
    const containerOffset = $puzzle.offset();
    const centerX =
      cellOffset.left - containerOffset.left + $cell.outerWidth() / 2;
    const centerY =
      cellOffset.top - containerOffset.top + $cell.outerHeight() / 2;
    return [centerX, centerY];
  }

  // Given a starting point and a current point, "snap" the current point
  // to be perfectly horizontal, vertical, or diagonal relative to the start.
  function snapTo45(start, current) {
    const dx = current[0] - start[0];
    const dy = current[1] - start[1];
    const distance = Math.sqrt(dx * dx + dy * dy);
    if (distance === 0) return start.slice();

    // Calculate the angle in radians.
    let angle = Math.atan2(dy, dx);
    // Snap angle to nearest multiple of 45° (pi/4 radians).
    const snappedAngle = Math.round(angle / (Math.PI / 4)) * (Math.PI / 4);
    const newX = start[0] + distance * Math.cos(snappedAngle);
    const newY = start[1] + distance * Math.sin(snappedAngle);
    return [newX, newY];
  }
  // Compute intermediate cell centers along a straight line.
  // We assume the grid is uniform and use cellSize (from strokeWidth) as our step.
  function getCellsInLine(start, end, cellSize) {
    const cells = [];
    const dx = end[0] - start[0];
    const dy = end[1] - start[1];
    let steps = 0,
      stepX = 0,
      stepY = 0;

    if (Math.abs(dx) < 1) {
      // Vertical.
      steps = Math.abs(Math.round(dy / cellSize));
      stepX = 0;
      stepY = dy > 0 ? cellSize : -cellSize;
    } else if (Math.abs(dy) < 1) {
      // Horizontal.
      steps = Math.abs(Math.round(dx / cellSize));
      stepX = dx > 0 ? cellSize : -cellSize;
      stepY = 0;
    } else {
      // For diagonal or other angles, use the distance.
      const distance = Math.sqrt(dx * dx + dy * dy);
      steps = Math.max(1, Math.round(distance / cellSize));
      stepX = dx / steps;
      stepY = dy / steps;
    }

    for (let i = 0; i <= steps; i++) {
      const x = start[0] + stepX * i;
      const y = start[1] + stepY * i;
      cells.push([x, y]);
    }
    return cells;
  }

  // ------------------------------
  // Pointer Event Handlers
  // ------------------------------

  function pointerDownHandler(e) {
    // Get the starting cell from the pointer event.
    const cell = document.elementFromPoint(e.clientX, e.clientY);

    // Only start drawing if the pointer is on a valid puzzle cell.
    if (!cell || !cell.classList.contains("puzzleSquare")) {
      return;
    }

    isDrawing = true;
    selectedPoints = []; // Reset for new drawing

    // Get the center of the cell relative to #puzzle.
    const center = getCellCenter(cell);
    selectedPoints = [center[0], center[1]]; // Save as the start point
    lastValidPoint = center;

    //Cache container offset for use during pointer move.
    const $puzzle = $("#puzzle");
    puzzleOffsetCached = $puzzle.offset();

    // Approximate cell size for later interpolation.
    const $cell = $(cell);
    const cellWidth = $cell.outerWidth();
    const cellHeight = $cell.outerHeight();
    const strokeWidthValue = Math.max(cellWidth, cellHeight);

    // Create a new Konva.Line with two identical points ([start, start]).
    currentLine = new Konva.Line({
      points: selectedPoints.concat(selectedPoints),
      stroke: validLineColors[0],
      strokeWidth: strokeWidthValue,
      lineCap: "round",
      lineJoin: "round",
      tension: 1,
      draggable: false, // Controlled via pointer events
      listening: false, // Disable internal events on the line if not needed
    });

    // Ensure the line is rendered on top.
    currentLine.moveToTop();

    window.konvaLayer.add(currentLine);
    window.konvaLayer.draw();
  }

  function pointerMoveHandler(e) {
    if (!isDrawing || !currentLine) return;

    // Throttle updates with requestAnimationFrame.
    if (pointerMoveScheduled) return;
    pointerMoveScheduled = true;

    // Compute pointer coordinates relative to #puzzle.
    requestAnimationFrame(() => {
      // Use cached offset for calculations.
      const currentRelativeX = e.pageX - puzzleOffsetCached.left;
      const currentRelativeY = e.pageY - puzzleOffsetCached.top;
      const startPoint = [selectedPoints[0], selectedPoints[1]];

      // Snap to the nearest 45° direction.
      const snappedPoint = snapTo45(startPoint, [
        currentRelativeX,
        currentRelativeY,
      ]);

      const $puzzle = $("#puzzle");

      // Only update if pointer is within the container ,Update the line with start and snapped endpoint.
      if (
        currentRelativeX >= 0 &&
        currentRelativeY >= 0 &&
        currentRelativeX <= $puzzle.outerWidth() &&
        currentRelativeY <= $puzzle.outerHeight()
      ) {
        lastValidPoint = snappedPoint;
        selectedPoints = [
          startPoint[0],
          startPoint[1],
          snappedPoint[0],
          snappedPoint[1],
        ];
        currentLine.points(selectedPoints);
      }

      // currentLine.points(selectedPoints);
      // Ensure the line stays on top during dragging.
      // Removed redundant moveToTop() call here.
      window.konvaLayer.batchDraw();
      pointerMoveScheduled = false;
    });
  }

  function pointerUpHandler(e) {
    // If drawing never started (e.g., pointerDown didn't fire on a valid cell),
    // then do nothing.
    if (!isDrawing || !currentLine) return;

    isDrawing = false;
    // Use the last valid endpoint if available; this ensures that if you dragged out and came back,
    // the endpoint used for computing the word is a valid, in-bound value.
    const finalEndpoint = lastValidPoint
      ? lastValidPoint
      : [selectedPoints[2], selectedPoints[3]];

    // Cancel the line if pointerUp occurs outside the puzzle container.
    const $puzzle = $("#puzzle");
    const puzzleOffset = $puzzle.offset();
    const puzzleWidth = $puzzle.outerWidth();
    const puzzleHeight = $puzzle.outerHeight();
    if (
      e.pageX < puzzleOffset.left ||
      e.pageX > puzzleOffset.left + puzzleWidth ||
      e.pageY < puzzleOffset.top ||
      e.pageY > puzzleOffset.top + puzzleHeight
    ) {
      console.log("Pointer up outside container, cancelling line.");
      currentLine.destroy();
      window.konvaLayer.draw();
      currentLine = null;
      return;
    }

    // Define the start and end points.
    const startPoint = [selectedPoints[0], selectedPoints[1]];
    const endPoint = finalEndpoint;

    // Use strokeWidth as an approximation of cell size.
    const cellSize = currentLine.strokeWidth();

    // Compute all intermediate cell centers along the line.
    const cellCenters = getCellsInLine(startPoint, endPoint, cellSize);

    // Convert container-relative coordinates to viewport coordinates.
    // const puzzleOffset = $("#puzzle").offset();
    const scrollX = window.scrollX;
    const scrollY = window.scrollY;

    // Build the guessed word by iterating through cell centers.
    let matchedWord = "";
    let lastElement = null;
    cellCenters.forEach((center) => {
      const pageX = center[0] + puzzleOffset.left - scrollX;
      const pageY = center[1] + puzzleOffset.top - scrollY;
      const element = document.elementFromPoint(pageX, pageY);

      if (element && element.classList.contains("puzzleSquare")) {
        // Only add if this DOM element isn't the same as the previous one.
        if (element !== lastElement) {
          matchedWord += element.textContent.trim();
          lastElement = element;
        }
      }
    });

    console.log("Guessed word:", matchedWord);

    // Only keep the line if there is a non-empty matched word and it is valid.
    if (matchedWord.trim().length > 0 && isValidGuessWord(matchedWord)) {
      const newColor =
        validLineColors[++currentColorIndex % validLineColors.length];
      currentLine.stroke(newColor);
    } else {
      // currentLine.stroke("");
      currentLine.destroy();
    }

    window.konvaLayer.draw();
    currentLine = null;
  }

  // Attach pointer event listeners to the underlying #puzzle container.
  $("#puzzle").on("pointerdown", pointerDownHandler);
  $("#puzzle").on("pointermove", pointerMoveHandler);
  $("#puzzle").on("pointerup", pointerUpHandler);

  $(document).on("pointerup", function (e) {
    if (!isDrawing || !currentLine) return;

    const $puzzle = $("#puzzle");
    const puzzleOffset = $puzzle.offset();
    const puzzleWidth = $puzzle.outerWidth();
    const puzzleHeight = $puzzle.outerHeight();

    if (
      e.pageX < puzzleOffset.left ||
      e.pageX > puzzleOffset.left + puzzleWidth ||
      e.pageY < puzzleOffset.top ||
      e.pageY > puzzleOffset.top + puzzleHeight
    ) {
      console.log("Pointer up outside container, cancelling line.");
      currentLine.destroy();
      window.konvaLayer.draw();
      currentLine = null;
      isDrawing = false;
      return;
    }
  });

  /**************************************
   * Additional Event Listeners
   **************************************/
  $("#extra-letters").change((evt) => {
    $("#secret-word").prop(
      "disabled",
      !evt.target.value.startsWith("secret-word")
    );
  });
  $("#add-word").click(() =>
    WordFindGame.insertWordBefore($("#add-word").parent())
  );
  $("#create-grid").click(recreate);
  $("#solve").click(() => {
    if (window.game) {
      window.game.solve();
    }
  });
});
