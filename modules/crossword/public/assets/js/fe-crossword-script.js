jQuery(document).ready(function ($) {
  let words = [];
  let currentWord = null; // Keep track of the current word
  let gridData = null; // Move gridData to a higher scope
  let wordList = [];
  let foundWords = [];

  function populateCrosswordFromData(container) {
    let crosswordData = $("#crossword-data").val();
    if (!crosswordData) {
      console.error("No crossword data found in #crossword-data");
      return;
    }

    let data;
    try {
      data = JSON.parse(crosswordData);
    } catch (e) {
      console.error("Invalid JSON data in #crossword-data");
      return;
    }

    gridData = data.grid; // Assign to the global gridData variable

    // Extract words from gridData
    words = extractWordsFromGrid(gridData, data);
    console.log("Words array:", words); // Log the words array

    // Build a mapping from cell positions to words
    let cellToWordsMap = {};
    words.forEach((wordObj) => {
      wordObj.cells.forEach((cell) => {
        let key = cell.x + "," + cell.y;
        if (!cellToWordsMap[key]) {
          cellToWordsMap[key] = [];
        }
        cellToWordsMap[key].push(wordObj);
      });
    });

    const table = $('<table class="crossword-table"></table>');

    // Add CSS classes (no inline CSS)
    if (!$("#crossword-styles").length) {
      $("head").append(`
                <style id="crossword-styles">
                    .filled-cell {
                        background-color: ${cross_ajax_obj.filledCellColor};
                        border: 1px solid ${cross_ajax_obj.cellBorderColor} !important;
                    }

                    .filled-cell input{
                        color:  ${cross_ajax_obj.cellFontColor} !important;
                    }
                    .correct-cell input{
                        background-color: ${cross_ajax_obj.correctedCellColor} !important;
                    }
                    .wrong-cell input {
                        background-color: ${cross_ajax_obj.wrongCellColor} !important;
                    }
                    .highlighted-cell {
                        background-color: ${cross_ajax_obj.highlightColor} !important;
                    }
                    .highlighted-clue {
                        background-color: ${cross_ajax_obj.highlightColor};
                    }
                    #clues-container-fe ul li {
                        font-size: ${cross_ajax_obj.fontSize};
                        font-family: ${cross_ajax_obj.fontFamily};
                        color: ${cross_ajax_obj.fontColor};
                    }
                    .clue-image {
                        height: ${cross_ajax_obj.clueImageHeight} !important;
                        width: ${cross_ajax_obj.clueImageWidth} !important;
                    }
                    .clue-number{
                        color:  ${cross_ajax_obj.clueFontColor} !important;
                    }
                </style>
            `);
    }

    for (let y = 0; y < gridData.length; y++) {
      const tableRow = $("<tr></tr>");
      for (let x = 0; x < gridData[y].length; x++) {
        const cell = gridData[y][x];
        const tableCell = $("<td></td>");
        if (cell && cell.letter) {
          tableCell.addClass("filled-cell");
          if (cell.clueNumber) {
            cell.clueNumber = parseInt(cell.clueNumber); // Ensure clueNumber is an integer
            tableCell.append(
              `<span class="clue-number">${cell.clueNumber}</span>`
            );
          }
          const input = $(
            '<input type="text" maxlength="1" class="letter-input">'
          );
          input.data("x", x);
          input.data("y", y);
          input.on("keydown", handleNavigation); // Arrow key and Backspace handling
          input.on("input", handleInput); // Overwrite existing letters
          input.on("focus", handleFocus); // Highlight word and clue

          // Store the words that this cell is part of
          let key = x + "," + y;
          if (cellToWordsMap[key]) {
            input.data("words", cellToWordsMap[key]);
          }

          tableCell.append(input);
        } else {
          tableCell.addClass("empty-cell");
        }
        tableRow.append(tableCell);
      }
      table.append(tableRow);
    }

    $(container).empty().append(table);

    // Render clues
    renderClues(data.clues);

    // Attach validation logic
    $("#validate-crossword").on("click", function () {
      Swal.fire({
        title: cross_ajax_obj.strings.confirmRevealTitle,
        text: cross_ajax_obj.strings.confirmRevealText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: cross_ajax_obj.strings.confirmRevealYes,
        cancelButtonText: cross_ajax_obj.strings.confirmRevealNo,
        confirmButtonColor: cross_ajax_obj.strings.failureButtonColor,
        cancelButtonColor: cross_ajax_obj.strings.successButtonColor,
      }).then((result) => {
        if (result.isConfirmed) {
          validateCrossword();
        }
      });
    });

    $("#check-words").on("change", function () {
      if ($(this).is(":checked")) {
        enableLiveValidation();
      } else {
        disableLiveValidation();
      }
    });
  }

  function extractWordsFromGrid(gridData, data) {
    wordList = []; // Reset the wordList
    foundWords = []; // Reset foundWords
    let words = [];
    let gridHeight = gridData.length;
    let gridWidth = gridData[0].length;
    let clueNumberToClueTextMap = {};

    // Build a map of clue numbers to clue texts
    if (data.clues && data.clues.across) {
      data.clues.across.forEach((clueObj) => {
        clueNumberToClueTextMap["A" + clueObj.clueNumber] = clueObj.clueText;
      });
    }
    if (data.clues && data.clues.down) {
      data.clues.down.forEach((clueObj) => {
        clueNumberToClueTextMap["D" + clueObj.clueNumber] = clueObj.clueText;
      });
    }

    for (let y = 0; y < gridHeight; y++) {
      for (let x = 0; x < gridWidth; x++) {
        let cell = gridData[y][x];
        if (cell && cell.letter) {
          if (cell.clueNumber) {
            cell.clueNumber = parseInt(cell.clueNumber); // Ensure clueNumber is an integer
          }
          // Check for ACROSS word starting here
          let isStartOfAcross =
            (x === 0 || !gridData[y][x - 1] || !gridData[y][x - 1].letter) &&
            x + 1 < gridWidth &&
            gridData[y][x + 1] &&
            gridData[y][x + 1].letter;
          if (isStartOfAcross) {
            let wordObj = {
              clueNumber: cell.clueNumber,
              direction: "across",
              cells: [],
              clueText: clueNumberToClueTextMap["A" + cell.clueNumber] || "",
              x: x,
              y: y,
            };
            let i = x;
            while (i < gridWidth && gridData[y][i] && gridData[y][i].letter) {
              wordObj.cells.push({ x: i, y: y, letter: gridData[y][i].letter });
              i++;
            }
            words.push(wordObj);
          }

          // Check for DOWN word starting here
          let isStartOfDown =
            (y === 0 || !gridData[y - 1][x] || !gridData[y - 1][x].letter) &&
            y + 1 < gridHeight &&
            gridData[y + 1][x] &&
            gridData[y + 1][x].letter;
          if (isStartOfDown) {
            let wordObj = {
              clueNumber: cell.clueNumber,
              direction: "down",
              cells: [],
              clueText: clueNumberToClueTextMap["D" + cell.clueNumber] || "",
              x: x,
              y: y,
            };
            let i = y;
            while (i < gridHeight && gridData[i][x] && gridData[i][x].letter) {
              wordObj.cells.push({ x: x, y: i, letter: gridData[i][x].letter });
              i++;
            }
            words.push(wordObj);
          }
        }
      }
    }

    // After processing all words, create the wordList
    words.forEach((wordObj) => {
      const letters = wordObj.cells.map((cell) => cell.letter).join("");
      wordList.push({
        word: letters,
        clueNumber: wordObj.clueNumber,
        direction: wordObj.direction,
        found: false,
      });
    });

    return words;
  }

  // Add this function to check if a word has been completed
  function checkWordCompletion() {
    // Go through each word in our words array
    words.forEach((wordObj) => {
      // Get the letters the user has entered for this word
      const userLetters = wordObj.cells
        .map((cell) => {
          const x = cell.x;
          const y = cell.y;
          const input = $(".letter-input").filter(function () {
            return $(this).data("x") === x && $(this).data("y") === y;
          });
          return input.val().toUpperCase();
        })
        .join("");

      // Get the correct letters for this word
      const correctLetters = wordObj.cells
        .map((cell) => cell.letter.toUpperCase())
        .join("");

      // Check if the word is found and not already in foundWords
      if (userLetters === correctLetters && correctLetters !== "") {
        const wordKey = `${wordObj.clueNumber}-${wordObj.direction}`;
        if (!foundWords.includes(wordKey)) {
          foundWords.push(wordKey);

          // Highlight the completed word in a special way if you want
          wordObj.cells.forEach((cell) => {
            let cellInput = $(".letter-input").filter(function () {
              return (
                $(this).data("x") === cell.x && $(this).data("y") === cell.y
              );
            });
            cellInput.closest("td").addClass("correct-cell");
          });

          // Check if all words are found
          checkAllWordsFound();
        }
      }
    });
  }

  // Function to check if all words are found
  function checkAllWordsFound() {
    // If the number of found words equals the number of words in our puzzle
    if (foundWords.length === words.length) {
      // Show a success message with a callback for when OK is clicked
      Swal.fire({
        title: cross_ajax_obj.successPopup.title,
        text: cross_ajax_obj.successPopup.bodyText,
        icon: "success",
        confirmButtonText: cross_ajax_obj.successPopup.buttonText,
        confirmButtonColor: cross_ajax_obj.successPopup.buttonColor,
        customClass: {
          confirmButton: "custom-success-button", // Custom class for text color
        },
      }).then((result) => {
        // When the OK button is clicked
        if (result.isConfirmed) {
          // Reset the puzzle
          resetCrossword();
        }
      });
    }
  }
  // Add a style element for the button text color
  const styleElement = document.createElement("style");
  styleElement.textContent = `
  .custom-success-button {
    color: ${cross_ajax_obj.successPopup.buttonTextColor} !important;
  }
`;
  document.head.appendChild(styleElement);

  // Add this function to handle the reset
  function resetCrossword() {
    // Clear all inputs
    $(".letter-input").val("");

    // Remove all highlighting and status classes
    $(".letter-input")
      .closest("td")
      .removeClass("highlighted-cell correct-cell wrong-cell");
    $("#clues-container-fe li").removeClass("highlighted-clue");

    // Reset tracking arrays
    foundWords = [];
    currentWord = null;

    // You might also want to reset any other game state variables

    // If you have any "found-word" class added to clues, remove those too
    $(".found-word").removeClass("found-word");
  }

  function handleInput(e) {
    const input = $(e.target);

    // Overwrite existing letter with the new input
    let value = input.val().slice(-1).toUpperCase();
    input.val(value);

    // Remove 'correct-cell' class when user changes input
    input.closest("td").removeClass("correct-cell");

    // Remove both correct-cell and wrong-cell classes when user changes input
    input.closest("td").removeClass("correct-cell wrong-cell");

    // Live validation
    if ($("#check-words").is(":checked")) {
      validateCell(input);
    }

    // Move to next cell in the current word
    if (currentWord) {
      // Find the index of the current cell in the word
      const x = input.data("x");
      const y = input.data("y");
      const currentIndex = currentWord.cells.findIndex(
        (cell) => cell.x === x && cell.y === y
      );
      if (currentIndex >= 0 && currentIndex < currentWord.cells.length - 1) {
        // Move to next cell
        const nextCell = currentWord.cells[currentIndex + 1];
        navigateToCell($(".crossword-table"), nextCell.x, nextCell.y);
      }
    }
    // Check if any words have been completed
    checkWordCompletion();
  }

  function handleNavigation(e) {
    const input = $(e.target);
    const x = input.data("x");
    const y = input.data("y");
    const table = $(".crossword-table");

    // If a letter key is pressed, overwrite the existing value
    if (e.key.length === 1 && e.key.match(/^[a-zA-Z]$/)) {
      e.preventDefault(); // Prevent default input
      input.val(e.key.toUpperCase());

      // Remove 'correct-cell' class when user changes input
      input.closest("td").removeClass("correct-cell");

      // Live validation
      if ($("#check-words").is(":checked")) {
        validateCell(input);
      }

      // Move to next cell in the current word
      if (currentWord) {
        const currentIndex = currentWord.cells.findIndex(
          (cell) => cell.x === x && cell.y === y
        );
        if (currentIndex >= 0 && currentIndex < currentWord.cells.length - 1) {
          const nextCell = currentWord.cells[currentIndex + 1];
          navigateToCell(table, nextCell.x, nextCell.y);
        }
      }
      // Check if any words have been completed
      checkWordCompletion();
    } else {
      switch (e.key) {
        case "ArrowUp":
          navigateToCell(table, x, y - 1, true);
          break;
        case "ArrowDown":
          navigateToCell(table, x, y + 1, true);
          break;
        case "ArrowLeft":
          navigateToCell(table, x - 1, y, true);
          break;
        case "ArrowRight":
          navigateToCell(table, x + 1, y, true);
          break;
        case "Backspace":
          // Clear the current cell
          input.val("").closest("td").removeClass("correct-cell wrong-cell");
          e.preventDefault(); // Prevent default backspace behavior

          // Move to previous cell in the current word
          if (currentWord) {
            const currentIndex = currentWord.cells.findIndex(
              (cell) => cell.x === x && cell.y === y
            );
            if (currentIndex > 0) {
              const prevCell = currentWord.cells[currentIndex - 1];
              navigateToCell(table, prevCell.x, prevCell.y);
            }
          }
          break;
      }
    }
  }

  function handleFocus(e) {
    const input = $(e.target);

    // Remove previous highlights
    $(".letter-input").closest("td").removeClass("highlighted-cell");
    $("#clues-container-fe li").removeClass("highlighted-clue");

    const wordsAtCell = input.data("words");
    if (wordsAtCell && wordsAtCell.length > 0) {
      // If currentWord is among them, keep it
      if (currentWord && wordsAtCell.includes(currentWord)) {
        // Do nothing
      } else {
        currentWord = wordsAtCell[0];
      }

      // Highlight the cells of the current word
      currentWord.cells.forEach((cell) => {
        let cellInput = $(".letter-input").filter(function () {
          return $(this).data("x") === cell.x && $(this).data("y") === cell.y;
        });
        cellInput.closest("td").addClass("highlighted-cell");
      });

      // Highlight the clue
      let clueSelector = `#clues-container-fe li[data-clue-number="${currentWord.clueNumber}"][data-direction="${currentWord.direction}"]`;
      $(clueSelector).addClass("highlighted-clue");
    } else {
      currentWord = null;
    }
  }

  function navigateToCell(table, x, y, fromArrowKey = false) {
    const targetInput = table.find(`.letter-input`).filter(function () {
      return $(this).data("x") === x && $(this).data("y") === y;
    });

    if (targetInput.length) {
      targetInput.focus();

      // Update currentWord if navigated via arrow keys
      if (fromArrowKey) {
        const wordsAtCell = targetInput.data("words");
        if (wordsAtCell && wordsAtCell.length > 0) {
          currentWord = wordsAtCell[0]; // You may implement logic to choose the word
        } else {
          currentWord = null;
        }
      }
    }
  }

  function renderClues(cluesData) {
    const acrossClues = $("#across-clues").empty();
    const downClues = $("#down-clues").empty();

    if (cluesData && cluesData.across) {
      cluesData.across.forEach((clueObj) => {
        const clueItem = $("<li></li>");
        clueItem.attr("data-clue-number", clueObj.clueNumber);
        clueItem.attr("data-direction", "across");
        clueItem.append(
          `<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`
        );
        if (clueObj.clueImage) {
          clueItem.append(
            `<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`
          );
        }
        clueItem.on("click", function () {
          // Remove previous highlights
          $(".letter-input").closest("td").removeClass("highlighted-cell");
          $("#clues-container-fe li").removeClass("highlighted-clue");

          // Highlight the clue
          $(this).addClass("highlighted-clue");

          // Find the word
          const clueNumber = parseInt($(this).data("clue-number"));
          const direction = $(this).data("direction");
          console.log(
            "Clicked clueNumber:",
            clueNumber,
            "direction:",
            direction
          );

          const wordObj = words.find(
            (word) =>
              word.clueNumber === clueNumber && word.direction === direction
          );

          if (wordObj) {
            console.log("word found:", wordObj);
            currentWord = wordObj; // Update currentWord

            // Highlight the word
            wordObj.cells.forEach((cell) => {
              let cellInput = $(".letter-input").filter(function () {
                return (
                  $(this).data("x") === cell.x && $(this).data("y") === cell.y
                );
              });
              cellInput.closest("td").addClass("highlighted-cell");
            });

            // Focus on the first cell of the word
            let firstCell = wordObj.cells[0];
            navigateToCell($(".crossword-table"), firstCell.x, firstCell.y);
          } else {
            console.log("word not found");
          }
        });
        acrossClues.append(clueItem);
      });
    }

    if (cluesData && cluesData.down) {
      cluesData.down.forEach((clueObj) => {
        const clueItem = $("<li></li>");
        clueItem.attr("data-clue-number", clueObj.clueNumber);
        clueItem.attr("data-direction", "down");
        clueItem.append(
          `<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`
        );
        if (clueObj.clueImage) {
          clueItem.append(
            `<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`
          );
        }
        clueItem.on("click", function () {
          // Remove previous highlights
          $(".letter-input").closest("td").removeClass("highlighted-cell");
          $("#clues-container-fe li").removeClass("highlighted-clue");

          // Highlight the clue
          $(this).addClass("highlighted-clue");

          // Find the word
          const clueNumber = parseInt($(this).data("clue-number"));
          const direction = $(this).data("direction");
          console.log(
            "Clicked clueNumber:",
            clueNumber,
            "direction:",
            direction
          );

          const wordObj = words.find(
            (word) =>
              word.clueNumber === clueNumber && word.direction === direction
          );

          if (wordObj) {
            console.log("word found:", wordObj);
            currentWord = wordObj; // Update currentWord

            // Highlight the word
            wordObj.cells.forEach((cell) => {
              let cellInput = $(".letter-input").filter(function () {
                return (
                  $(this).data("x") === cell.x && $(this).data("y") === cell.y
                );
              });
              cellInput.closest("td").addClass("highlighted-cell");
            });

            // Focus on the first cell of the word
            let firstCell = wordObj.cells[0];
            navigateToCell($(".crossword-table"), firstCell.x, firstCell.y);
          } else {
            console.log("word not found");
          }
        });
        downClues.append(clueItem);
      });
    }
  }

  function validateCrossword() {
    const inputs = $(".letter-input");
    let allCorrect = true;

    // Reset styles
    $(".letter-input").closest("td").removeClass("correct-cell");

    inputs.each(function () {
      const input = $(this);
      const x = input.data("x");
      const y = input.data("y");
      const userAnswer = input.val().toUpperCase();
      const correctAnswer = gridData[y][x]?.letter?.toUpperCase() || "";

      input.val(correctAnswer);
      if (userAnswer === correctAnswer) {
        input.closest("td").addClass("correct-cell");
      } else {
        input.closest("td").addClass("wrong-cell");
        allCorrect = false;
      }
    });
    // Add this at the end of the validateCrossword function
    if (allCorrect) {
      Swal.fire({
        title: "Congratulations!",
        text: "You have successfully solved the crossword puzzle!",
        icon: "success",
        confirmButtonText: "OK",
      });
    }
  }

  function validateCell(input) {
    const x = input.data("x");
    const y = input.data("y");
    const userAnswer = input.val().toUpperCase();
    const correctAnswer = gridData[y][x]?.letter?.toUpperCase() || "";

    if (userAnswer === correctAnswer && userAnswer !== "") {
      input.closest("td").removeClass("wrong-cell"); // Remove wrong-cell class
      input.closest("td").addClass("correct-cell");
    } else if (userAnswer !== "" && userAnswer !== correctAnswer) {
      input.closest("td").removeClass("correct-cell");
      input.closest("td").addClass("wrong-cell"); // Add this line to apply wrong-cell class
    } else {
      input.closest("td").removeClass("correct-cell");
      input.closest("td").removeClass("wrong-cell"); // Remove both classes if empty
    }
  }
  function enableLiveValidation() {
    $(".letter-input").each(function () {
      validateCell($(this));
    });
  }

  function disableLiveValidation() {
    $(".letter-input").closest("td").removeClass("correct-cell wrong-cell");
  }

  $("#kw-reset-crossword").on("click", function () {
    populateCrosswordFromData("#crossword-grid");
  });

  // Initialize the crossword grid
  populateCrosswordFromData("#crossword-grid");
});
