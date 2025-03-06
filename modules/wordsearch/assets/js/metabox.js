jQuery(document).ready(function ($) {
  // Cache selectors for performance
  var $wordsContainer = $("#wordsearch-words-container");
  var template = $("#wordsearch-word-template").html();
  // Debounce timer variable (placed in an appropriate scope)
  let debounceTimer;
  var savedEntries = entries;

  function mergeSavedEntriesIntoCookie(savedEntries, cookieEntries) {
    // Create a map for cookieEntries keyed by id.
    const map = new Map();
    cookieEntries.forEach((entry) => {
      if (entry.id) {
        map.set(entry.id, entry);
      }
    });

    // Create a Set of saved entry ids for quick lookup.
    const savedIds = new Set(savedEntries.filter((e) => e.id).map((e) => e.id));

    // if (savedEntries.length > cookieEntries.length) {
    // Add any missing savedEntries to the map.
    savedEntries.forEach((entry) => {
      if (entry.id && !map.has(entry.id)) {
        map.set(entry.id, entry);
      }
    });
    // } else if (savedEntries.length < cookieEntries.length) {
    //   // Remove cookie entries that are not in savedEntries.
    //   for (const key of map.keys()) {
    //     if (!savedIds.has(key)) {
    //       map.delete(key);
    //     }
    //   }
    // } else {
    //   return;
    // }

    return Array.from(map.values());
  }

  function syncSavedEntriesWithCookie(savedEntries) {
    // Retrieve cookie data for wordsearch_entries
    if (savedEntries.length === 0) {
      console.log("No saved entries to sync with cookie.");
      setCookie("wordsearch_entries", "", -1);
      return;
    }
    const cookieDataStr = getCookie("wordsearch_entries");
    let cookieEntries = cookieDataStr ? JSON.parse(cookieDataStr) : [];

    // Merge saved entries with the cookie data
    const mergedEntries = mergeSavedEntriesIntoCookie(
      savedEntries,
      cookieEntries
    );

    // Update the cookie if there's a difference
    if (JSON.stringify(mergedEntries) !== JSON.stringify(cookieEntries)) {
      setCookie("wordsearch_entries", JSON.stringify(mergedEntries), 1);
      console.log("Cookie updated with missing saved entries:", mergedEntries);
      return mergedEntries;
    } else {
      console.log("Cookie entries are already up-to-date.");
      return cookieEntries;
    }
  }

  const updatedCookieEntries = syncSavedEntriesWithCookie(savedEntries);

  // Helper functions to set and get cookies
  function setCookie(name, value, days) {
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
  }

  function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(";");
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) === " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }

  // Global array to hold all word entry objects
  var wordEntries = [];

  // On page load, try to get stored data from cookie "wordsearch_entries"
  var rawData = getCookie("wordsearch_entries");

  if (rawData) {
    rawData = rawData.trim();
    try {
      // Only parse if the string starts with '[' or '{'
      if (rawData.charAt(0) === "[" || rawData.charAt(0) === "{") {
        wordEntries = JSON.parse(rawData);
      } else {
        wordEntries = [];
      }
    } catch (e) {
      console.error("JSON parse error, defaulting to empty array:", e);
      wordEntries = [];
    }
  } else {
    wordEntries = [];
  }

  var id = 0;

  // Function to generate a unique ID (using current timestamp and a random number)
  function generateUniqueId() {
    return "ws_" + Date.now() + "_" + Math.floor(Math.random() * 1000);
  }

  // Instead of updating a hidden field, update the cookie with the current wordEntries array
  function updateCookie() {
    // Check if there is at least one entry with a non-empty wordText
    if (
      !wordEntries ||
      !wordEntries.length ||
      !wordEntries.some(function (entry) {
        return entry.wordText && entry.wordText.trim().length > 0;
      })
    ) {
      // No valid entries: clear the cookie.
      setCookie("wordsearch_entries", "", 1);
      return;
    }
    // Otherwise, update the cookie with the current wordEntries.
    setCookie("wordsearch_entries", JSON.stringify(wordEntries), 1); // expires in 1 day
  }

  // Function to add a new word entry using the template
  function addNewWordEntry() {
    // Get the current number of word entries in the container
    var index = $wordsContainer.children(".add-word-container").length;

    // Increment the global entry count
    entryNumber++;

    // Set the display number for the new entry
    var number = entryNumber;

    // Generate a new unique ID for this entry
    var uniqueId = generateUniqueId();

    // Update the id variable with the new uniqueId
    id = uniqueId;

    // Reassign number using the updated entryCount (optional, as number is already set)
    number = entryNumber;

    // Replace placeholders in the template with actual values
    var newEntryHtml = template
      .replace(/{{index}}/g, index)
      .replace(/{{uniqueId}}/g, uniqueId)
      .replace(/{{number}}/g, number);

    // Append the new entry HTML to the container
    $wordsContainer.append(newEntryHtml);
    console.log("Appended newEntryHtml to $wordsContainer");
  }

  // Handler for "Add Word" button click
  $("#add-wordsearch-button").on("click", function (e) {
    e.preventDefault();
    addNewWordEntry();
  });

  // Listen for click events on the save image button
  $(document).on("click", ".save-image-btn", function () {
    // Get the unique id from the closest container
    const uniqueId = $(this).closest(".add-word-container").data("unique-id");
    // Call the save function to update the imageUrl for this entry
    saveEntryImage(uniqueId);
  });

  function saveEntryImage(uniqueId) {
    // Find the image preview element by its unique class name
    const $imagePreview = $(".wordsearch-image-preview-" + uniqueId);
    // Get the image URL from its child (assumes the image element is an <img>)
    const imageUrl = $imagePreview.children("img").attr("src") || "";

    // Look for the entry in the wordEntries array
    let entry = wordEntries.find((item) => item.id === uniqueId);

    if (entry) {
      // Update the imageUrl property
      entry.imageUrl = imageUrl;
    } else {
      // Optionally, if no entry exists yet, create one with an empty wordText
      wordEntries.push({
        id: uniqueId,
        wordText: "",
        imageUrl: imageUrl,
      });
    }

    // Update the cookie after saving
    updateCookie();
  }

  // Update array when text changes.
  // Debounce timer variable (ensure it's in an appropriate scope)

  // Listen to input events on any element with the class "word-input"
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
      updateCookie();
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
        updateCookie();
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
        };
        wordEntries.push(newEntry);
      }

      // Update the cookie with the current wordEntries array
      updateCookie();
    }, 1000); // 1 second debounce delay
  });

  // Remove an entry.
  $wordsContainer.on("click", ".remove-word", function () {
    if (confirm("Are you sure you want to remove this word?")) {
      var $wordDiv = $(this).closest(".add-word-container");
      var uniqueId = $wordDiv.data("unique-id");
      // Remove the element from the DOM.
      $wordDiv.remove();
      $wordsContainer.children(".add-word-container").each(function (index) {
        $(this).attr("data-index", index);
        $(this)
          .find(".word-number")
          .text(index + 1 + ".");
        // Also update the name attributes if needed
      });

      // Filter the array to remove the object with the matching id.
      wordEntries = wordEntries.filter(function (item) {
        return item.id !== uniqueId;
      });

      // Update the cookie with the new array.
      if (wordEntries.length === 0) {
        console.log("No more entries, deleting cookie.");
        // Delete the cookie by setting it with an expired date.
        setCookie("wordsearch_entries", "", -1);
      } else {
        updateCookie();
      }
    }
  });
  // Delegate removal of word entries to dynamically added elements
  // $wordsContainer.on("click", ".remove-word", function (e) {
  //   e.preventDefault();
  //   if (confirm("Are you sure you want to remove this word?")) {
  //     $(this).closest(".wordsearch-word").remove();
  //     // Optionally, update word numbers after removal
  //     $wordsContainer.children(".wordsearch-word").each(function (index) {
  //       $(this).attr("data-index", index);
  //       $(this)
  //         .find(".word-number")
  //         .text(index + 1 + ".");
  //       // Also update the name attributes if needed
  //     });
  //   }
  // });

  // Handler for "Clear List" button click
  $("#clear-wordsearch-list-button").on("click", function (e) {
    e.preventDefault();
    if (confirm("Are you sure you want to clear the list?")) {
      $wordsContainer.empty();
      wordEntries = [];
      // Delete the cookie by setting it with an expired date.
      setCookie("wordsearch_entries", "", -1);
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
          } else {
            wordEntries.push({
              id: uniqueId,
              wordText: "",
              imageUrl: imageUrl,
            });
          }
          updateCookie();
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

  // (Optional) You can also add image upload handlers here if required.
});
