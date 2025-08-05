jQuery(document).ready(function ($) {
  // Function to handle image upload
  function handleImageUpload(
    buttonSelector,
    itemSelector,
    inputSelector,
    previewSelector
  ) {
    $(document).on("click", buttonSelector, function (e) {
      e.preventDefault();
      const button = $(this);
      const item = button.closest(itemSelector);
      const uniqueId = item.attr("data-unique-id"); // Get unique ID
      const index = item.data("index");

      const customUploader = wp
        .media({
          title: crosswordScriptVar.selectImageTitle,
          button: { text: crosswordScriptVar.useImageText },
          multiple: false, // Single image upload
        })
        .on("select", function () {
          const attachment = customUploader
            .state()
            .get("selection")
            .first()
            .toJSON();
          const imageUrl = attachment.url;

          // Set the image URL in the hidden input field
          const inputField = item.find(inputSelector);
          $(document).trigger("imageUploaded", [
            { index: index, imageUrl: imageUrl },
          ]);
          if (inputField.length === 0) {
            item.append(
              `<input type="hidden" class="crossword-image-url" name="crossword_words[${item.data(
                "index"
              )}][image]" value="${imageUrl}">`
            );
          } else {
            inputField.val(imageUrl);
          }

          // Display the uploaded image in the word clue section
          const preview = item.find(previewSelector);
          const imageHtml = `<img src="${imageUrl}" style="max-width: 70px; max-height: 70px; border-radius: 5%; padding-left: 10px;" />`;
          if (preview.length === 0) {
            button.before(
              `<div class="crossword-image-preview">${imageHtml}</div>`
            );
          } else {
            preview.html(imageHtml);
          }

          // **Find the matching clue item and append the image there**
          const clueItem = $(`li[data-unique-id="${uniqueId}"]`);
          if (clueItem.length > 0) {
            // Remove existing image if already appended
            clueItem.find(".clue-image").remove();
            // Append new image
            clueItem.append(
              `<br><img src="${imageUrl}" alt="Clue image" class="clue-image">`
            );
          }
        })
        .open();
    });
  }

  // Apply the image upload function for crossword items
  handleImageUpload(
    ".upload-crossword-image-btn",
    ".crossword-word-clue",
    ".crossword-image-url",
    ".crossword-image-preview"
  );

  // Initialize the crossword form functionality
  const container = $("#crossword-words-clues-container");
  const addButton = $("#add-word-button");
  const clearButton = $("#clear-list-button");
  const crosswordDataField = $("#crossword-data");
  const template = $("#crossword-word-clue-template").html();

  // Function to render the HTML using the template
  function renderWordClue(index) {
    const newField = template
      .replace(/{{index}}/g, index)
      .replace(/{{number}}/g, index + 1);
    const newElement = $(newField);
    container.append(newElement);

    // Attach event listener for the remove button
    attachRemoveButtonEvent(newElement);

    updateIndices(); // Update indices after adding a new row
  }

  // Function to attach the remove event listener
  function attachRemoveButtonEvent(element) {
    element.find(".remove-word").on("click", function () {
      $(this).closest(".crossword-word-clue").remove();
      updateIndices();
      $("#shuffle-button").click();
    });
  }

  $(document).on("click", ".remove-word", function () {
    $(this).closest(".crossword-word-clue").remove();
    updateIndices();
    $("#shuffle-button").click();
  });

  // Add a new word and clue input pair
  addButton.on("click", function () {
    const index = container.children().length;
    renderWordClue(index);
  });

  // Clear the entire list
  clearButton.on("click", function () {
    container.empty();
    updateIndices();
    $("#shuffle-button").click();
  });

  // Update indices when rows are added or removed
  function updateIndices() {
    container.children(".crossword-word-clue").each(function (index) {
      $(this)
        .find(".word-number")
        .text(index + 1 + ".");
      $(this).attr("data-index", index);
      $(this)
        .find("input, select, textarea")
        .each(function () {
          const name = $(this).attr("name");
          if (name) {
            const newName = name.replace(/\d+/, index);
            $(this).attr("name", newName);
          }
        });
    });
  }

  // Attach event listener for existing items
  container.children(".crossword-word-clue").each(function () {
    attachRemoveButtonEvent($(this));
  });

  $("form").on("submit", function (event) {
    crossword.updateHiddenFields();
  });
});
