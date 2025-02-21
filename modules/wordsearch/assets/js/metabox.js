jQuery(document).ready(function ($) {
  // Cache selectors for performance
  var $wordsContainer = $("#wordsearch-words-container");
  var template = $("#wordsearch-word-template").html();

  // Function to generate a unique ID (using current timestamp and a random number)
  function generateUniqueId() {
    return "ws_" + Date.now() + "_" + Math.floor(Math.random() * 1000);
  }

  function updateHiddenField() {
    $("#wordEntriesData").val(JSON.stringify(wordEntries));
  }

  // Function to add a new word entry using the template
  // Global array to hold all word entry objects
  var rawData = $("#wordEntriesData").val().trim();
  var wordEntries = [];
  try {
    // Only parse if the string starts with '[' or '{'
    if (rawData && (rawData.charAt(0) === "[" || rawData.charAt(0) === "{")) {
      wordEntries = JSON.parse(rawData);
    } else {
      wordEntries = [];
    }
  } catch (e) {
    console.error("JSON parse error, defaulting to empty array:", e);
    wordEntries = [];
  }

  var entryCount = 0;
  var id = 0;

  // Function to add a new word entry using the template
  function addNewWordEntry() {
    // Get the current number of word entries in the container
    var index = $wordsContainer.children(".wordsearch-word").length;
    console.log("index:", index);

    // Increment the global entry count
    entryCount++;
    console.log("entryCount after increment:", entryCount);

    // Set the display number for the new entry
    var number = entryCount;
    console.log("number:", number);

    // Get the word text from the input that has a class based on the current id
    var wordText = jQuery(".word-input-" + id).val();
    console.log("wordText:", wordText);

    // Get the image URL from the preview element with a class based on the current id
    var imageUrl = jQuery(".wordsearch-image-preview-" + id)
      .children()
      .attr("src");
    console.log("imageUrl:", imageUrl);

    // Generate a new unique ID for this entry
    var uniqueId = generateUniqueId();
    console.log("Generated uniqueId:", uniqueId);

    // Update the id variable with the new uniqueId
    id = uniqueId;
    console.log("Updated id:", id);

    // Reassign number using the updated entryCount (optional, as number is already set)
    var number = entryCount;
    console.log("Reassigned number:", number);

    // Create a new object for this word entry
    var newEntry = {
      id: uniqueId,
      wordText: wordText || "",
      imageUrl: imageUrl || "",
    };
    console.log("newEntry object:", newEntry);

    // If either wordText or imageUrl exists, add the entry to the global array
    if (newEntry.wordText || newEntry.imageUrl) {
      wordEntries.push(newEntry);
      console.log("Updated wordEntries array:", wordEntries);
    } else {
      console.log("newEntry has no valid data, not adding to wordEntries");
    }

    // Replace placeholders in the template with actual values
    var newEntryHtml = template
      .replace(/{{index}}/g, index)
      .replace(/{{uniqueId}}/g, uniqueId)
      .replace(/{{number}}/g, number);
    // console.log("Generated newEntryHtml:", newEntryHtml);

    // Append the new entry HTML to the container
    $wordsContainer.append(newEntryHtml);
    console.log("Appended newEntryHtml to $wordsContainer");
    updateHiddenField();
  }

  // Handler for "Add Word" button click
  $("#add-wordsearch-button").on("click", function (e) {
    e.preventDefault();
    addNewWordEntry();
  });

  // Update array when text changes.
  $("#wordsearch-words-container").on("change", ".word-input", function () {
    var $wordDiv = $(this).closest(".wordsearch-word");
    var uniqueId = $wordDiv.data("unique-id");
    var entry = wordEntries.find(function (item) {
      return item.id === uniqueId;
    });
    if (entry) {
      entry.wordText = $(this).val();
      updateHiddenField();
    }
  });

  // Remove an entry.
  $("#wordsearch-words-container").on("click", ".remove-word", function () {
    if (confirm("Are you sure you want to remove this word?")) {
      var $wordDiv = $(this).closest(".wordsearch-word");
      var uniqueId = $wordDiv.data("unique-id");
      $wordDiv.remove();
      wordEntries = wordEntries.filter(function (item) {
        return item.id !== uniqueId;
      });
      updateHiddenField();
    }
  });

  // Handler for "Clear List" button click
  $("#clear-wordsearch-list-button").on("click", function (e) {
    e.preventDefault();
    if (confirm("Are you sure you want to clear the list?")) {
      $wordsContainer.empty();
      wordEntries = [];
    }
  });
  function handleImageUpload(
    buttonSelector,
    itemSelector,
    inputSelector,
    previewSelector
  ) {
    jQuery(document).on("click", buttonSelector, function (e) {
      e.preventDefault();
      const button = jQuery(this);
      const item = button.closest(itemSelector);
      const uniqueId = item.data("uniqueId");

      console.log("id is", uniqueId);
      const customUploader = wp
        .media({
          title: "Select Image",
          button: { text: "Use this image" },
          multiple: false, // Single image upload
        })
        .on("select", function () {
          const attachment = customUploader
            .state()
            .get("selection")
            .first()
            .toJSON();
          const imageUrl = attachment.url;
          item.find(".wordsearch-image-url").val(imageUrl);

          // Set the image URL in the hidden input field
          const inputField = item.find(inputSelector);
          if (inputField.length === 0) {
            item.append(
              `<input type="hidden" class="searchword-image-url" name="crossword_words[${item.data(
                "index"
              )}][image]" value="${imageUrl}">`
            );
          } else {
            inputField.val(imageUrl);
          }

          // Display the uploaded image in the word section
          const preview = item.find(previewSelector);
          const imageHtml = `<img src="${imageUrl}" style="max-width: 70px; max-height: 70px; border-radius: 5%; padding-left: 10px;" />`;
          if (preview.length === 0) {
            button.before(
              `<div class="wordsearch-image-preview wordsearch-image-preview-${uniqueId}">${imageHtml}</div>`
            );
          } else {
            preview.html(imageHtml);
          }
          var entry = wordEntries.find(function (item) {
            return item.id === uniqueId;
          });
          if (entry) {
            entry.imageUrl = imageUrl;
            updateHiddenField();
          }
        })
        .open();
    });
  }

  // Apply the image upload function for crossword items
  handleImageUpload(
    ".upload-word-image-btn",
    ".add-word-container",
    ".wordsearch-image-url",
    ".wordsearch-image-preview"
  );

  // Delegate removal of word entries to dynamically added elements
  $wordsContainer.on("click", ".remove-word", function (e) {
    e.preventDefault();
    if (confirm("Are you sure you want to remove this word?")) {
      $(this).closest(".wordsearch-word").remove();
      // Optionally, update word numbers after removal
      $wordsContainer.children(".wordsearch-word").each(function (index) {
        $(this).attr("data-index", index);
        $(this)
          .find(".word-number")
          .text(index + 1 + ".");
        // Also update the name attributes if needed
      });
    }
  });

  // (Optional) You can also add image upload handlers here if required.
});
