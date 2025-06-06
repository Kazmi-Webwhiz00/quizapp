jQuery(document).ready(function ($) {
  // Declare gridSize and grid at a higher scope so they are accessible in all functions
  let gridSize;
  let grid;
  // Global (or module-scoped) image store keyed by index
  var imageStore = {};

  function generateCrosswordGrid(container) {
    // Fetch words, clues, and clue images from the input fields
    let wordsData = [];
    $(".crossword-word-clue").each(function () {
      const index = $(this).data("index");
      const uniqueId = $(this).attr("data-unique-id") || "";
      const word = $(`input[name="crossword_words[${index}][word]"]`).val();
      const clue = $(`input[name="crossword_words[${index}][clue]"]`).val();
      // Get the image from the hidden input first...
      let imageFromInput = $(
        `input[name="crossword_words[${index}][image]"]`
      ).val();
      let image = imageFromInput || imageStore[index] || "";

      if (!imageFromInput && image) {
        $(`input[name="crossword_words[${index}][image]"]`).val(image);
      }
      if (word || image) {
        // Optionally include the image even if word is empty
        wordsData.push({
          uniqueId: uniqueId,
          word: word ? word.toUpperCase() : "", // Ensure word is uppercase if present
          clue: clue || "",
          image: image || "",
          clueNumber: null, // Initialize clueNumber
        });
        window.crosswordClueData = wordsData;
      }
    });

    if (wordsData.length === 0) {
      $(container)
        .empty()
        .append(`<p>${crosswordLabels.emptyCrosswordMessage}</p>`);
      $("#clues-container").empty();
      return;
    }

    // Sort words by length in descending order
    wordsData.sort((a, b) => b.word.length - a.word.length);

    // Calculate dynamic grid size based on words
    const longestWordLength = wordsData[0].word.length;
    const totalLetters = wordsData.reduce(
      (sum, wordObj) => sum + wordObj.word.length,
      0
    );
    gridSize = Math.max(
      longestWordLength + 5,
      Math.ceil(Math.sqrt(totalLetters)) + 5
    );
    gridSize = Math.min(gridSize, 100); // Set a maximum grid size to prevent excessive size

    // Initialize grid
    grid = [];
    for (let i = 0; i < gridSize; i++) {
      grid[i] = new Array(gridSize).fill(null);
    }

    // Initialize placed words array
    let placedWords = [];

    // Directions
    const ACROSS = "across";
    const DOWN = "down";

    // Function to check if a word can be placed at the given position and direction
    function canPlaceWord(word, x, y, direction) {
      if (direction === ACROSS) {
        if (x < 0 || x + word.length > gridSize || y < 0 || y >= gridSize)
          return false;

        // Check the cells before and after the word in x direction
        if (x > 0 && grid[y][x - 1]) return false;
        if (x + word.length < gridSize && grid[y][x + word.length])
          return false;

        for (let i = 0; i < word.length; i++) {
          const cell = grid[y][x + i];

          // Check adjacent cells above and below
          if (cell) {
            if (cell.letter !== word[i]) {
              return false;
            }
          } else {
            // Check for adjacent cells above and below
            if (y > 0 && grid[y - 1][x + i]) return false;
            if (y < gridSize - 1 && grid[y + 1][x + i]) return false;
          }
        }
      } else if (direction === DOWN) {
        if (y < 0 || y + word.length > gridSize || x < 0 || x >= gridSize)
          return false;

        // Check the cells before and after the word in y direction
        if (y > 0 && grid[y - 1][x]) return false;
        if (y + word.length < gridSize && grid[y + word.length][x])
          return false;

        for (let i = 0; i < word.length; i++) {
          const cell = grid[y + i][x];

          // Check adjacent cells left and right
          if (cell) {
            if (cell.letter !== word[i]) {
              return false;
            }
          } else {
            // Check for adjacent cells left and right
            if (x > 0 && grid[y + i][x - 1]) return false;
            if (x < gridSize - 1 && grid[y + i][x + 1]) return false;
          }
        }
      }
      return true;
    }

    // Function to place a word on the grid
    function placeWord(wordObj, x, y, direction) {
      const { word } = wordObj;
      wordObj.x = x;
      wordObj.y = y;
      wordObj.direction = direction;
      placedWords.push(wordObj);

      if (direction === ACROSS) {
        for (let i = 0; i < word.length; i++) {
          if (!grid[y][x + i]) {
            grid[y][x + i] = {
              letter: word[i],
              across: wordObj,
              down: null,
              clueNumber: null,
            };
          } else {
            grid[y][x + i].across = wordObj;
          }
        }
      } else if (direction === DOWN) {
        for (let i = 0; i < word.length; i++) {
          if (!grid[y + i][x]) {
            grid[y + i][x] = {
              letter: word[i],
              across: null,
              down: wordObj,
              clueNumber: null,
            };
          } else {
            grid[y + i][x].down = wordObj;
          }
        }
      }
    }

    // Function to remove a word from the grid
    function removeWord(wordObj) {
      const { word, x, y, direction } = wordObj;
      placedWords = placedWords.filter((w) => w !== wordObj);

      if (direction === ACROSS) {
        for (let i = 0; i < word.length; i++) {
          const cell = grid[y][x + i];
          if (cell) {
            if (cell.across === wordObj) {
              if (cell.down === null) {
                grid[y][x + i] = null;
              } else {
                cell.across = null;
              }
            }
          }
        }
      } else if (direction === DOWN) {
        for (let i = 0; i < word.length; i++) {
          const cell = grid[y + i][x];
          if (cell) {
            if (cell.down === wordObj) {
              if (cell.across === null) {
                grid[y + i][x] = null;
              } else {
                cell.down = null;
              }
            }
          }
        }
      }
    }

    // Function to find possible positions to place a word
    function findPositions(word) {
      let positions = [];

      if (placedWords.length === 0) {
        // Place the first word in the center
        const x = Math.floor((gridSize - word.length) / 2);
        const y = Math.floor(gridSize / 2);
        // Check if the word can be placed horizontally
        if (canPlaceWord(word, x, y, ACROSS)) {
          positions.push({ x, y, direction: ACROSS });
        }
        // Check if the word can be placed vertically
        if (canPlaceWord(word, x, y, DOWN)) {
          positions.push({ x, y, direction: DOWN });
        }
      } else {
        for (let placedWord of placedWords) {
          for (let i = 0; i < word.length; i++) {
            for (let j = 0; j < placedWord.word.length; j++) {
              if (word[i] === placedWord.word[j]) {
                let x, y, direction;

                if (placedWord.direction === ACROSS) {
                  // Try placing word vertically
                  direction = DOWN;
                  x = placedWord.x + j;
                  y = placedWord.y - i;
                } else {
                  // Try placing word horizontally
                  direction = ACROSS;
                  x = placedWord.x - i;
                  y = placedWord.y + j;
                }

                if (canPlaceWord(word, x, y, direction)) {
                  positions.push({ x, y, direction });
                }
              }
            }
          }
        }
      }
      return positions;
    }

    // Recursive function to try placing words
    function placeWords(index) {
      if (index >= wordsData.length) {
        return true; // All words placed
      }

      const wordObj = wordsData[index];
      const positions = findPositions(wordObj.word);

      // Shuffle positions to add randomness
      positions.sort(() => Math.random() - 0.5);

      for (let pos of positions) {
        placeWord(wordObj, pos.x, pos.y, pos.direction);
        if (placeWords(index + 1)) {
          return true;
        }
        removeWord(wordObj);
      }

      return false; // Unable to place word at any position
    }

    // Start placing words
    const success = placeWords(0);

    // If not all words could be placed, collect unplaced words
    let unplacedWords = [];
    if (!success) {
      unplacedWords = wordsData.slice(placedWords.length).map((w) => w.word);
    }

    // If unplaced words exist, display a message
    if (unplacedWords.length > 0) {
      const unplacedList = unplacedWords.join(", ");
      const message = crosswordLabels.errorMessageText + " " + unplacedList;
      $("#error-message").text(message).show();
    } else {
      $("#error-message").hide();
    }

    // Assign clue numbers after all words have been placed
    assignClueNumbers(placedWords);

    // Render the grid in the container
    const table = $('<table class="crossword-table"></table>');
    for (let y = 0; y < gridSize; y++) {
      const tableRow = $("<tr></tr>");
      for (let x = 0; x < gridSize; x++) {
        const cell = grid[y][x];
        const tableCell = $("<td></td>");
        if (cell) {
          tableCell
            .addClass("filled-cell")
            .css("background-color", crosswordLabels.filledCellColor);
          if (cell.clueNumber && !tableCell.find(".clue-number").length) {
            tableCell.append(
              `<span class="clue-number">${cell.clueNumber}</span>`
            );
          }
          tableCell.append(`<span class="letter">${cell.letter}</span>`);
        } else {
          tableCell.addClass("empty-cell");
        }
        tableRow.append(tableCell);
      }
      table.append(tableRow);
    }

    $(container).empty().append(table);

    // Display clues with optional images
    const acrossClues = $("<ul></ul>");
    const downClues = $("<ul></ul>");
    placedWords.forEach((wordObj) => {
      // Check if either clue or image is present
      if (wordObj.clue || wordObj.image) {
        const clueItem = $(`<li data-unique-id="${wordObj.uniqueId}"></li>`);
        // Always display clue number when either a clue or image exists
        clueItem.append(`<strong>${wordObj.clueNumber}.</strong> `);
        // Append clue text only if it exists
        if (wordObj.clue) {
          clueItem.append(wordObj.clue);
        }
        // Append image if it exists
        if (wordObj.image) {
          clueItem.append(
            `<br><img src="${wordObj.image}" alt="Clue image" class="clue-image">`
          );
        }
        if (wordObj.direction === ACROSS) {
          acrossClues.append(clueItem);
        } else {
          downClues.append(clueItem);
        }
      }
    });

    $("#clues-container").empty();
    // if (wordsData.length >= 12) {
    //   document
    //     .getElementsByClassName("preview-container")[0]
    //     .classList.add("column-layout");

    //   document.getElementById("clues-container").classList.add("clues-portion");
    // }
    // Create container for "Across" clues with heading and append the list into it
    const acrossContainer = $(`
  <div class="acrossClue">
    <h3>${crosswordLabels.acrossLabel}</h3>
  </div>
`);
    acrossContainer.append(acrossClues);

    // Create container for "Down" clues with heading and append the list into it
    const downContainer = $(`
  <div class="downClue">
    <h3>${crosswordLabels.downLabel}</h3>
  </div>
`);
    downContainer.append(downClues);

    // Append the dynamically created containers into the main container
    $("#clues-container").append(acrossContainer).append(downContainer);
  }

  function assignClueNumbers(placedWords) {
    // Reset clue numbers for all cells and words
    for (let y = 0; y < gridSize; y++) {
      for (let x = 0; x < gridSize; x++) {
        const cell = grid[y][x];
        if (cell) {
          cell.clueNumber = null;
        }
      }
    }
    placedWords.forEach((wordObj) => {
      wordObj.clueNumber = null;
    });

    let clueNumber = 1;

    // Assign clue numbers to each word and their starting cells
    placedWords.forEach((wordObj) => {
      const { x, y, direction } = wordObj;
      const cell = grid[y][x];

      if (cell) {
        if (cell.clueNumber) {
          // If the cell already has a clue number, use it
          wordObj.clueNumber = cell.clueNumber;
        } else {
          // Assign a new clue number
          cell.clueNumber = clueNumber;
          wordObj.clueNumber = clueNumber;
          clueNumber++;
        }
      }
    });
  }

  // Function to show/hide crossword answers based on the checkbox state
  function toggleAnswers() {
    const showAnswers = $("#toggle-answers").is(":checked");
    $(".letter").css("color", showAnswers ? "#000" : "transparent");
  }

  // Update grid when inputs change
  $(document).on("input", ".crossword-word-clue input", function () {
    generateCrosswordGrid("#crossword-grid");
    toggleAnswers();
  });

  $(document).on("imageUploaded", function (event, data) {
    imageStore[data.index] = data.imageUrl;
    generateCrosswordGrid("#crossword-grid", data.imageUrl);
  });

  // Update grid when the checkbox is toggled
  $("#toggle-answers").on("change", toggleAnswers);

  // Shuffle button functionality
  $("#shuffle-button").on("click", function () {
    generateCrosswordGrid("#crossword-grid");
    toggleAnswers();
  });

  // Initial grid generation
  crossword.populateCrosswordFromData("#crossword-grid");
  toggleAnswers();
});
