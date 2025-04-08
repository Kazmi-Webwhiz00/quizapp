jQuery(document).ready(function ($) {
  console.log("is admin (wordsearch) is ", wordsearchScriptVar.isAdmin);
  const MAX_ENTRIES = 15;

  // Global array to hold all word entry objects (if not defined already)
  var wordEntries = [];

  /**
   * Replaces placeholders in a string using an object of key-value pairs.
   *
   * @param {string} template - The string template containing placeholders (e.g., [key]).
   * @param {object} variables - An object with key-value pairs for replacement.
   * @returns {string} - The template with placeholders replaced by actual values.
   */
  function ws_replacePlaceholders(template, variables) {
    Object.keys(variables).forEach((key) => {
      const regex = new RegExp("\\[" + key + "\\]", "g"); // Global replacement for all instances of [key]
      template = template.replace(regex, variables[key]);
    });
    return template;
  }

  /**
   * Generates the complete prompt content by replacing placeholders in context,
   * generation, and return format prompts for wordsearch.
   *
   * @param {number} number - The number of words to generate.
   * @param {string} topic - The topic for the wordsearch.
   * @param {number} age - The age group of the user.
   * @param {string} language - The language for the wordsearch.
   * @returns {string} - The final combined prompt content.
   */
  function ws_generatePrompt(number, topic, age, language) {
    // Retrieve localized default prompts
    const contextPromptTemplate = wordsearchScriptVar.wsDefaultContextPrompt;
    const generationPromptTemplate =
      wordsearchScriptVar.wsDefaultGenerationPrompt;
    const returnFormatPrompt = wordsearchScriptVar.wsDefaultReturnFormatPrompt;
    const defaultCategory = wordsearchScriptVar.defaultCategory;

    // 1) Get selected categories using updated selectors for wordsearch
    const selectedCategories = ws_getSelectedCategories();

    // 2) (Optional) Retrieve existing words to avoid duplicates.
    // Commented out as it relates to the UI grid:
    const existingWords = ws_getWordsearchWordsList();
    const contextPrompt = ws_replacePlaceholders(contextPromptTemplate, {
      existing_words: existingWords.join(", "),
    });
    // For AI generation, you might send an empty context or a default message.

    // 3) Replace placeholders in generation prompt, including the new [categories]
    const generationPrompt = ws_replacePlaceholders(generationPromptTemplate, {
      number: number,
      topic: topic,
      age: age,
      language: language,
      categories: selectedCategories || defaultCategory,
    });

    // 4) Combine prompts into the final prompt
    return `${generationPrompt} ${contextPrompt} ${returnFormatPrompt}`;
  }

  // Commented out non-AI UI functions for managing word entries

  function ws_getWordsearchWordsList() {
    let wordsList = [];
    $(".add-word-container").each(function () {
      const index = $(this).data("index");
      const wordText = $(
        `input[name="wordsearch_words[${index}][word]"]`
      ).val();
      if (wordText) {
        wordsList.push(wordText.toUpperCase());
      }
    });
    return wordsList;
  }

  function ws_appendGeneratedContent(generatedContent) {
    const $container = $("#wordsearch-words-container");
    const template = $("#wordsearch-word-template").html();
    const existingEntries = $container.find(".add-word-container").length;
    generatedContent.forEach((item, index) => {
      const newIndex = existingEntries + index;
      const uniqueId = item.id
        ? item.id
        : `ws_${Date.now()}_${Math.floor(Math.random() * 1000)}`;
      console.log(
        `Generated Unique ID for word: ${item.wordText} -> ${uniqueId}`
      );
      let entryHtml = template
        .replace(/{{index}}/g, newIndex)
        .replace(/{{number}}/g, newIndex + 1)
        .replace('value=""', `value="${item.wordText}"`)
        // .replace('value=""', `value="${item.clue}"`)
        .replace('value="{{uniqueId}}"', `value=${uniqueId}`);
      let $entry = $(entryHtml);
      $entry.attr("data-unique-id", uniqueId);
      $entry.find("input[name^='wordsearch_words']").each(function () {
        let nameAttr = $(this).attr("name");
        if (nameAttr.includes("[word]")) {
          $(this)
            .attr("name", `wordsearch_words[${newIndex}][word]`)
            .attr("data-unique-id", uniqueId);
        }
        // else if (nameAttr.includes("[clue]")) {
        //   $(this)
        //     .attr("name", `wordsearch_words[${newIndex}][clue]`)
        //     .attr("data-unique-id", uniqueId);
        // }
      });
      $container.append($entry);
    });
  }

  /**
   * Returns a string of selected categories joined by " > " for wordsearch.
   *
   * This function grabs the text of the selected options from the new dropdowns.
   */
  function ws_getSelectedCategories() {
    let selectedCategories = [];
    let selectedSchool = $("#selected_school_wordsearch")
      .find(":selected")
      .text()
      .trim();
    let selectedClass = $("#selected_class_wordsearch")
      .find(":selected")
      .text()
      .trim();
    let selectedSubject = $("#selected_subject_wordsearch")
      .find(":selected")
      .text()
      .trim();

    const isValidCategory = (category) => {
      return category.length > 0 && !category.match(/^-{3,}$/);
    };

    if (isValidCategory(selectedSchool)) {
      selectedCategories.push(selectedSchool);
    }
    if (isValidCategory(selectedClass)) {
      selectedCategories.push(selectedClass);
    }
    if (isValidCategory(selectedSubject)) {
      selectedCategories.push(selectedSubject);
    }

    return selectedCategories.join(" > ");
  }

  // Unique ID for the wordsearch generate button
  const generateButtonId = "#ws-generate-ai-button";

  // Click event handler for the Generate with AI button for wordsearch
  $(generateButtonId).on("click", function () {
    const topic = $("#ws-topic").val().trim();
    const age = $("#ws-age").val().trim();
    const number = $("#ws-words").val().trim();
    const newEntries = parseInt(number);

    const language = $("#ws-language").val().trim();
    const maxNumberOfWords = parseInt(wordsearchScriptVar.wsMaxNumberOfWords);
    if (newEntries < 1 || newEntries > maxNumberOfWords) {
      Swal.fire(
        wordsearchScriptVar.wsStrings.errorTitle,
        `${wordsearchScriptVar.wsStrings.numberError} ${wordsearchScriptVar.wsMaxNumberOfWords}`,
        "warning"
      );
      return;
    }

    // Check if adding new entries would exceed the maximum limit
    if (window.totalEntries + newEntries > MAX_ENTRIES) {
      Swal.fire({
        title: wordsearchScriptVar.entryLimitTitle,
        text: wordsearchScriptVar.entryLimitBodyText,
        icon: "error",
        confirmButtonText: "OK",
        confirmButtonColor: "#3085d6", // Professional blue color
        customClass: {
          container: "my-swal-container",
          popup: "my-swal-popup",
          title: "my-swal-title",
          content: "my-swal-content",
          confirmButton: "my-swal-confirm-btn",
        },
        showClass: {
          popup: "animate__animated animate__fadeIn faster", // Smooth fade-in animation
        },
        hideClass: {
          popup: "animate__animated animate__fadeOut faster", // Smooth fade-out animation
        },
      });

      return;
    }

    // Update the global total entries
    window.totalEntries += newEntries;
    console.log(ws_generatePrompt(number, topic, age, language));
    const data = {
      model: wordsearchScriptVar.wsModel,
      messages: [
        {
          role: "user",
          content: ws_generatePrompt(number, topic, age, language),
        },
      ],
      max_tokens: parseInt(wordsearchScriptVar.wsMaxTokens),
      temperature: parseFloat(wordsearchScriptVar.wsTemperature),
    };

    // Function to send the request with retry logic
    function ws_sendRequest(retryCount, count) {
      if (count <= 0) return;
      $.ajax({
        url: "https://api.openai.com/v1/chat/completions",
        method: "POST",
        headers: {
          Authorization: "Bearer " + wordsearchScriptVar.wsApiKey,
          "Content-Type": "application/json",
        },
        data: JSON.stringify(data),
        beforeSend: function () {
          $(".kw-loading").show();
          console.log("Sending request to OpenAI (wordsearch)...", data);
          if (wordsearchScriptVar.isAdmin) {
            showAdminPrompt(data.messages[0].content);
          }
          $(generateButtonId)
            .text(wordsearchScriptVar.wsGeneratingText)
            .prop("disabled", true);
        },
        success: function (response) {
          console.log("Received response (wordsearch):", response);
          try {
            const generatedContentString =
              response.choices[0].message.content.trim();
            let jsonString = generatedContentString;

            // Remove markdown code block formatting if present
            if (jsonString.startsWith("```")) {
              // Remove the first line (which may be "```json" or just "```")
              jsonString = jsonString.replace(/^```(?:json)?\n?/, "");
              // Remove trailing triple backticks, if any
              jsonString = jsonString.replace(/```$/, "").trim();
            }

            console.log("generated content String", jsonString);
            const generatedContent = JSON.parse(jsonString);
            console.log("Generated content (wordsearch):", generatedContent);

            // Process each generated entry: rename "word" to "wordText"
            generatedContent.forEach(function (item) {
              if (item.word) {
                item.wordText = item.word; // Rename property
                delete item.word; // Remove old property
              }
              // Ensure each entry has a unique id
              if (!item.id) {
                item.id =
                  "ws_" + Date.now() + "_" + Math.floor(Math.random() * 1000);
              }

              if (!item.imageUrl) {
                item.imageUrl = "";
              }

              if (!item.hidden) {
                item.hidden = false;
              }
              // Push the modified entry to the global wordEntries array
              wordEntries.push(item);
            });

            // Update the cookie with the new entries
            // Commented out UI update related to word entry rendering
            ws_appendGeneratedContent(generatedContent);
            // $("#ws-shuffle-button").click();
            // Additional non-AI processing (e.g., saving grid data) is commented out:
            let postStatus = $("#original_post_status").val();
            let postId = $("#post_ID").val();
            $.fn.updatePostAsDraft(postId, postStatus);
            // wordsearch.updateHiddenFields();
            ws_saveWordSearchAjax();
            $(".kw-loading").hide();
            Swal.fire(
              wordsearchScriptVar.wsStrings.successTitle,
              wordsearchScriptVar.wsStrings.successMessage,
              "success"
            );
            $.fn.highlightPublishButton();
          } catch (error) {
            console.error("Error parsing response (wordsearch):", error);
            $(".kw-loading").hide();
            Swal.fire(
              wordsearchScriptVar.wsStrings.errorTitle,
              wordsearchScriptVar.wsStrings.errorMessage,
              "error"
            );
            window.totalEntries -= newEntries; // Decrement total entries on error
          }
          $(generateButtonId)
            .text(wordsearchScriptVar.wsGenerateWithAiText)
            .prop("disabled", false);
        },
        error: function (xhr, status, error) {
          console.error("API request error (wordsearch):", xhr.responseText);
          if (retryCount > 0) {
            console.log(`Retrying request... Attempts left: ${retryCount}`);
            setTimeout(() => ws_sendRequest(retryCount - 1, count), 2000);
          } else {
            let errorMsg = "Failed to generate wordsearch. ";
            errorMsg += xhr.responseJSON?.error?.message || "Error: " + error;
            Swal.fire(
              wordsearchScriptVar.wsStrings.errorTitle,
              errorMsg,
              "error"
            );
            $(generateButtonId)
              .text(wordsearchScriptVar.wsGenerateWithAiText)
              .prop("disabled", false);
          }
        },
      });
    }

    // Start the request with a retry count of 3
    ws_sendRequest(3, number);
  });

  // Comment out non-AI function for saving grid data

  function ws_saveWordSearchAjax() {
    var word_search_data = [];
    $(".add-word-container").each(function () {
      var $wordDiv = $(this);
      var wordText = $wordDiv.find('input[name*="[word]"]').val();
      // var clue = $clueDiv.find('input[name*="[clue]"]').val();
      var uniqueId = $wordDiv.find('input[name*="[uniqueId]"]').val();
      var imageUrl = $wordDiv.find("input.wordsearch-image-url").val() || "";
      if (
        wordText &&
        $.trim(wordText) !== "" &&
        uniqueId &&
        $.trim(uniqueId) !== ""
      ) {
        word_search_data.push({
          id: uniqueId,
          wordText: wordText,
          // clue: clue,
          imageUrl: imageUrl,
          hidden: false,
        });
      }
    });

    const postId = $("#post_ID").val();
    let baseUrl = "";

    // Split the pathname to extract the subdirectory (assumes it's the first segment)
    const pathSegments = window.location.pathname.split("/").filter(Boolean);
    const subdirectory = pathSegments[0] || ""; // "stateduntimed"

    // Build the base URL using the subdirectory

    if (subdirectory !== "wp-admin") {
      baseUrl = `${window.location.origin}/${subdirectory}/wp-admin/post.php?post=${postId}&action=edit`;
    } else {
      baseUrl = `${window.location.origin}/wp-admin/post.php?post=${postId}&action=edit`;
    }

    window.history.replaceState(null, "", baseUrl);

    $.ajax({
      url: wordsearchScriptVar.ajaxUrl,
      type: "POST",
      dataType: "json",
      data: {
        action: "save_wordsearch_ajax",
        security: wordsearchScriptVar.nonce,
        post_id: $("#post_ID").val(),
        // wordsearch_data: word_entries,
        word_search_data: word_search_data,
      },
      success: function (response) {
        if (response.success) {
          console.log("Wordsearch saved successfully via AJAX.", response.data);
          // Trigger a custom event with the updated entries as data
          $(document).trigger("wordsearchEntriesAdded", response.data);
        } else {
          console.error("Error saving wordsearch:", response.data);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX error (wordsearch):", errorThrown);
      },
    });
  }
});
