jQuery(document).ready(function ($) {
  window.crossword = window.crossword || {};
  crossword.getGridData = function () {
    let gridData = [];
    $(".crossword-table tr").each(function (rowIndex) {
      let rowData = [];
      $(this)
        .find("td")
        .each(function (colIndex) {
          const cell = $(this);
          const letter = cell.find(".letter").text().trim() || "";
          const clueNumber = cell.find(".clue-number").text().trim() || "";
          rowData.push({
            letter: letter,
            clueNumber: clueNumber,
          });
        });
      gridData.push(rowData);
    });
    return gridData.length ? gridData : ""; // Return empty string if no data
  };

  crossword.getCluesData = function () {
    let acrossClues = [];
    $(`#clues-container h3:contains(${crosswordLabels.acrossLabel})`)
      .next("ul")
      .find("li")
      .each(function () {
        const clueUniqueId = $(this).attr("data-unique-id") || ""; // Retrieve unique ID
        const clueNumber =
          $(this).find("strong").text().replace(".", "").trim() || "";
        const clueText =
          $(this)
            .contents()
            .filter(function () {
              return this.nodeType === 3; // Node.TEXT_NODE
            })
            .text()
            .trim() || "";
        const clueImage = $(this).find("img").attr("src") || "";
        acrossClues.push({
          clueUniqueId: clueUniqueId,
          clueNumber: clueNumber,
          clueText: clueText,
          clueImage: clueImage,
        });
      });

    let downClues = [];
    $(`#clues-container h3:contains(${crosswordLabels.downLabel})`)
      .next("ul")
      .find("li")
      .each(function () {
        const clueUniqueId = $(this).attr("data-unique-id") || ""; // Retrieve unique ID
        const clueNumber =
          $(this).find("strong").text().replace(".", "").trim() || "";
        const clueText =
          $(this)
            .contents()
            .filter(function () {
              return this.nodeType === 3; // Node.TEXT_NODE
            })
            .text()
            .trim() || "";
        const clueImage = $(this).find("img").attr("src") || "";
        downClues.push({
          clueUniqueId: clueUniqueId,
          clueNumber: clueNumber,
          clueText: clueText,
          clueImage: clueImage,
        });
      });

    return {
      across: acrossClues.length ? acrossClues : "",
      down: downClues.length ? downClues : "",
    };
  };

  // Function to update hidden fields
  crossword.updateHiddenFields = function () {
    const crosswordData = {
      grid: crossword.getGridData() || "",
      clues: crossword.getCluesData() || { across: "", down: "" },
    };
    // Check if crosswordData is empty
    const isEmptyCrosswordData =
      (crosswordData.grid === "" || crosswordData.grid.length === 0) &&
      (crosswordData.clues.across === "" ||
        crosswordData.clues.across.length === 0) &&
      (crosswordData.clues.down === "" ||
        crosswordData.clues.down.length === 0);

    // Set value of #crossword-data based on emptiness
    if (isEmptyCrosswordData) {
      $("#crossword-data").val(""); // Set to empty string if crosswordData is empty
    } else {
      const crosswordDataJson = JSON.stringify(crosswordData);
      console.log(crosswordDataJson); // For debugging purposes
      $("#crossword-data").val(crosswordDataJson);
    }
  };

  crossword.populateCrosswordFromData = function (container) {
    // Read data from the hidden field
    let crosswordData = $("#crossword-data").val();
    if (!crosswordData) {
      console.error("No crossword data found in #crossword-data");
      return;
    }

    // Parse the JSON data
    let data;
    try {
      data = JSON.parse(crosswordData);
    } catch (e) {
      console.error("Invalid JSON data in #crossword-data");
      return;
    }

    let gridData = data.grid;
    let cluesData = data.clues;

    // Render the grid
    const table = $('<table class="crossword-table"></table>');
    for (let y = 0; y < gridData.length; y++) {
      const tableRow = $("<tr></tr>");
      for (let x = 0; x < gridData[y].length; x++) {
        const cell = gridData[y][x];
        const tableCell = $("<td></td>");
        if (cell && cell.letter) {
          tableCell
            .addClass("filled-cell")
            .css("background-color", crosswordLabels.filledCellColor);
          if (cell.clueNumber) {
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

    // Append the grid to the container
    $(container).empty().append(table);

    // Render the clues
    const acrossClues = $("<ul></ul>");
    const downClues = $("<ul></ul>");

    cluesData.across.forEach((clueObj) => {
      console.log("obje", JSON.stringify(clueObj));
      if (
        clueObj.clueNumber &&
        clueObj.clueNumber !== "null" &&
        (clueObj.clueText || clueObj.clueImage)
      ) {
        const clueItem = $(
          `<li data-unique-id="${clueObj.clueUniqueId}"></li>`
        );
        // Only append clue text if it exists
        if (clueObj.clueText) {
          clueItem.append(
            `<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`
          );
        } else {
          // Still show the clue number even if clue text is empty
          clueItem.append(`<strong>${clueObj.clueNumber}.</strong>`);
        }
        // Append the image if it exists
        if (clueObj.clueImage) {
          clueItem.append(
            `<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`
          );
        }
        acrossClues.append(clueItem);
      }
    });

    cluesData.down.forEach((clueObj) => {
      if (
        clueObj.clueNumber &&
        clueObj.clueNumber !== "null" &&
        (clueObj.clueText || clueObj.clueImage)
      ) {
        const clueItem = $(
          `<li data-unique-id="${clueObj.clueUniqueId}"></li>`
        );
        // Append clue number, and clue text if available
        if (clueObj.clueText) {
          clueItem.append(
            `<strong>${clueObj.clueNumber}.</strong> ${clueObj.clueText}`
          );
        } else {
          clueItem.append(`<strong>${clueObj.clueNumber}.</strong>`);
        }
        // Append the image if it exists
        if (clueObj.clueImage) {
          clueItem.append(
            `<br><img src="${clueObj.clueImage}" alt="Clue image" class="clue-image">`
          );
        }
        downClues.append(clueItem);
      }
    });

    // Append the clues to the clues container
    $("#clues-container").empty();
    $("#clues-container").append(`<h3>${crosswordLabels.acrossLabel}</h3>`);
    $("#clues-container").append(acrossClues);
    $("#clues-container").append(`<h3>${crosswordLabels.downLabel}</h3>`);
    $("#clues-container").append(downClues);

    // Apply the show/hide answers functionality
    toggleAnswers();
  };

  function toggleAnswers() {
    const showAnswers = $("#toggle-answers").is(":checked");
    $(".letter").css("color", showAnswers ? "#000" : "transparent");
  }
});
