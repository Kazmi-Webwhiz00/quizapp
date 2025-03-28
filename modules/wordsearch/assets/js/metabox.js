jQuery(document).ready(function ($) {
  // Cache selectors for performance.
  var wordsContainer = $("#wordsearch-words-container");
  var template = $("#wordsearch-word-template").html();
  // Global array to hold all word entry objects.
  var wordEntries = [];
  // Debounce timer variable.
  let debounceTimer;
  let totalEntries = 0;

  // Assume a global variable "entries" exists from the database.
  var savedEntries = entries; // entries from the database
  const savedEvent = new CustomEvent("entriesUpdated", {
    detail: savedEntries,
  });
  document.dispatchEvent(savedEvent);

  // On page load, if savedEntries exists, assign it to wordEntries and trigger update.
  if (savedEntries && savedEntries.length > 0) {
    wordEntries = savedEntries;
    $(document).trigger("wordsearchEntriesUpdated", { data: wordEntries });
  }

  var id = 0;

  // Function to generate a unique ID (using current timestamp and a random number).
  function generateUniqueId() {
    return "ws_" + Date.now() + "_" + Math.floor(Math.random() * 1000);
  }

  // Instead of updating a cookie, we trigger the event with the current wordEntries array.
  function updateEntries() {
    // Proceed only if every entry has a valid non-empty wordText.
    const allEntriesValid = wordEntries.every(
      (entry) => entry.wordText && entry.wordText.trim().length > 0
    );
    if (!allEntriesValid) {
      return;
    }

    // Filter out entries that don't have a valid wordText (ignore those that only have imageUrl)
    var validWordEntries = wordEntries.filter(function (entry) {
      return entry.wordText && entry.wordText.trim().length > 0;
    });

    // If no valid entries exist, clear the cookie and trigger the event with an empty array.
    if (!validWordEntries.length) {
      // Create a new event with an empty array.
      const emptyEvent = new CustomEvent("entriesUpdated", {
        detail: [],
      });
      document.dispatchEvent(emptyEvent);
      // $(document).trigger("wordsearchEntriesUpdated", { data: [] });
      return;
    }

    const updateEvent = new CustomEvent("entriesUpdated", {
      detail: wordEntries,
    });
    document.dispatchEvent(updateEvent);

    $(document).trigger("wordsearchEntriesUpdated", { data: wordEntries });
  }

  // When data comes in from a "wordsearchEntriesAdded" event, compare with wordEntries.
  $(document).on("wordsearchEntriesAdded", function (event, entriesData) {
    entriesData.data.forEach(function (entry) {
      if (entry.id && !wordEntries.some((e) => e.id === entry.id)) {
        wordEntries.push(entry);
      }
    });

    const updateEvent = new CustomEvent("entriesUpdated", {
      detail: wordEntries,
    });
    document.dispatchEvent(updateEvent);

    $(document).trigger("wordsearchEntriesUpdated", { data: wordEntries });
  });

  // Function to add a new word entry using the template and update wordEntries.
  function addNewWordEntry() {
    var index = wordsContainer.children(".add-word-container").length;
    var number = index + 1;
    var uniqueId = generateUniqueId();
    id = uniqueId;
    // Replace placeholders in the template with actual values.
    var newEntryHtml = template
      .replace(/{{index}}/g, index)
      .replace(/{{uniqueId}}/g, uniqueId)
      .replace(/{{number}}/g, number);
    wordsContainer.append(newEntryHtml);

    // Add a new (empty) entry to the global wordEntries array.
    wordEntries.push({
      id: uniqueId,
      wordText: "",
      imageUrl: "",
      hidden: false,
    });

    // Trigger the event with the updated entries.
    updateEntries();
  }

  // Handler for "Add Word" button click.
  $("#add-wordsearch-button").on("click", function (e) {
    e.preventDefault();
    totalEntries = totalEntries + 1;
    if (totalEntries > 15) {
      window.showWordLimitModal();
      return;
    }
    addNewWordEntry();
  });

  // Listen for click events on the save image button.
  $(document).on("click", ".save-image-btn", function () {
    const uniqueId = $(this).closest(".add-word-container").data("unique-id");
    saveEntryImage(uniqueId);
  });

  function saveEntryImage(uniqueId) {
    const $imagePreview = $(".wordsearch-image-preview-" + uniqueId);
    const imageUrl = $imagePreview.children("img").attr("src") || "";
    let entry = wordEntries.find((item) => item.id === uniqueId);
    if (entry) {
      entry.imageUrl = imageUrl;
    } else {
      wordEntries.push({
        id: uniqueId,
        wordText: "",
        imageUrl: imageUrl,
        hidden: false,
      });
    }
    updateEntries();
  }

  // Listen to input events on any element with the class "word-input".
  $(document).on("input", ".word-input", function () {
    const that = this;
    clearTimeout(debounceTimer);

    // Get the current input value immediately.
    const inputValue = $(that).val() || "";

    // If the input is empty, update immediately and return.
    if (inputValue.length === 0) {
      // Find the closest container with the class "add-word-container"
      const $wordDiv = $(that).closest(".add-word-container");
      // Retrieve the unique id from the container's data attribute
      const uniqueId = $wordDiv.data("unique-id");

      // Remove the existing entry from wordEntries if it exists.
      const index = wordEntries.findIndex((item) => item.id === uniqueId);
      if (index !== -1) {
        wordEntries.splice(index, 1);
      }
      // Update the cookie with the current wordEntries array (which may now be empty)
      updateEntries();
      return; // Exit immediately.
    }

    // Otherwise, proceed with debounced update.
    debounceTimer = setTimeout(function () {
      // Ensure that we're getting the input value as a string
      let inputValue = $(that).val() || "";

      if (inputValue.length > 15) {
        // Create a jQuery UI dialog to notify the user.
        $(
          '<div title="Character Length Error">Characters cannot be more than 15.</div>'
        ).dialog({
          modal: true,
          buttons: {
            OK: function () {
              $(this).dialog("close");
            },
          },
          close: function () {
            // Optionally, trim the input to 15 characters when the dialog closes.
            $(that).val(inputValue.substring(0, 15));
            $(this).remove();
          },
        });

        // Since the input is invalid, clear the inputValue.
        inputValue = "";

        // Remove the corresponding entry from wordEntries.
        const $wordDiv = $(that).closest(".add-word-container");
        const uniqueId = $wordDiv.data("unique-id");
        const index = wordEntries.findIndex((item) => item.id === uniqueId);
        if (index !== -1) {
          wordEntries.splice(index, 1);
        }

        // Update the cookie to remove the invalid entry.
        updateEntries();
        return; // Exit to avoid further processing.
      }

      // Find the closest container with the class "add-word-container"
      const $wordDiv = $(that).closest(".add-word-container");
      // Retrieve the unique id from the container's data attribute
      const uniqueId = $wordDiv.data("unique-id");

      // Look for an existing entry in the global wordEntries array
      let entry = wordEntries.find((item) => item.id === uniqueId);

      // If the entry exists, update it; if not, create a new one
      if (entry) {
        entry.wordText = inputValue;
      } else {
        const newEntry = {
          id: uniqueId,
          wordText:
            inputValue.length > 15 ? inputValue.substring(0, 15) : inputValue,
          imageUrl: "", // Default value; update as needed
          hidden: false,
        };
        wordEntries.push(newEntry);
      }

      // Update the cookie with the current wordEntries array
      updateEntries();
    }, 1000); // 1 second debounce delay
  });

  // Remove an entry when the remove button is clicked.
  wordsContainer.on("click", ".remove-word", function () {
    var $wordDiv = $(this).closest(".add-word-container");
    var uniqueId = $wordDiv.data("unique-id");

    // Remove the element from the DOM.
    $wordDiv.remove();

    // Update the indices of remaining elements.
    wordsContainer.children(".add-word-container").each(function (index) {
      $(this).attr("data-index", index);
      $(this)
        .find(".word-number")
        .text(index + 1 + ".");
    });

    entryNumber = wordsContainer.children(".add-word-container").length;
    // Filter the array to remove the object with the matching id.
    wordEntries = wordEntries.filter(function (item) {
      return item.id !== uniqueId;
    });

    // Trigger the update event with the new array.
    updateEntries();
  });

  // Handler for "Clear List" button click.
  $("#clear-wordsearch-list-button").on("click", function (e) {
    e.preventDefault();
    if (confirm("Are you sure you want to clear the list?")) {
      wordsContainer.empty();
      wordEntries = [];
      const event = new CustomEvent("entriesUpdated", {
        detail: [],
      });
      document.dispatchEvent(event);
      $(document).trigger("wordsearchEntriesUpdated", { data: [] });
    }
  });

  // Function to handle image upload.
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

      const customUploader = wp
        .media({
          title: "Select Image",
          button: { text: "Use this image" },
          multiple: false,
        })
        .on("select", function () {
          const attachment = customUploader
            .state()
            .get("selection")
            .first()
            .toJSON();
          const imageUrl = attachment.url;
          item.find(".wordsearch-image-url").val(imageUrl);

          const inputField = item.find(inputSelector);
          if (inputField.length === 0) {
            item.append(
              `<input type="hidden" class="wordsearch-image-url" name="wordsearch_words[${item.data(
                "index"
              )}][image]" value="${imageUrl}">`
            );
          } else {
            inputField.val(imageUrl);
          }

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
          } else {
            wordEntries.push({
              id: uniqueId,
              wordText: "",
              imageUrl: imageUrl,
              hidden: false,
            });
          }
          updateEntries();
        })
        .open();
    });
  }

  // Apply the image upload handler.
  handleImageUpload(
    ".upload-word-image-btn",
    ".add-word-container",
    ".wordsearch-image-url",
    ".wordsearch-image-preview"
  );
});
