jQuery(document).ready(function ($) {
  // Extract words from the localized previewData.
  // if (previewData) {
  //   var words = previewData.words.map(function (entry) {
  //     return entry.wordText.trim().toLowerCase();
  //   });
  // }

  // Define grid options for the preview (you may choose a different gap or other styling).
  var options = {
    gridSize: 10,
    cellSize: 40,
    gap: 10,
  };

  // Initialize the preview grid in the container "#preview-puzzle" using the common function.

  // When the refresh button is clicked, reinitialize the grid.
  // $("#refresh-preview").on("click", function (e) {
  //   e.preventDefault();
  //   var entriesJSON = $("#wordEntriesData").val();
  //   var wordEntries = [];
  //   try {
  //     wordEntries = JSON.parse(entriesJSON);
  //   } catch (e) {
  //     console.error("Error parsing word entries:", e);
  //   }
  //   var updatedWords = wordEntries.map(function (entry) {
  //     return entry.wordText.trim().toLowerCase();
  //   });
  //   // Reinitialize the preview grid with updated words.
  //   window.previewGame = initializeWordSearchGrid(
  //     "#preview-puzzle",
  //     options,
  //     updatedWords
  //   );
  // });
});
