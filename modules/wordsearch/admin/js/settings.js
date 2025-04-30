jQuery(document).ready(function ($) {
  $(".kw-color-picker").wpColorPicker({
    change: function (event, ui) {
      var hexColor = ui.color.toString();

      // Try to get the color2name function:
      var color2nameFn =
        window.color2name &&
        typeof window.color2name === "object" &&
        typeof window.color2name.default === "function"
          ? window.color2name.default
          : window.color2name;

      if (typeof color2nameFn === "function") {
        var colorName = color2nameFn(hexColor);
        console.log("Selected color:", hexColor, "->", colorName);
      } else {
        console.error(
          "color2name function is not defined properly. Make sure the color-2-name script is loaded correctly."
        );
      }
    },
  });
  const tabs = $(".kw-wordsearch-nav-tab");
  const panes = $(".kw-wordsearch-tab-pane");

  // Function to activate a tab
  function activateTab(tabKey) {
    tabs.removeClass("kw-wordsearch-nav-tab-active");
    panes.hide();
    $(`.kw-wordsearch-nav-tab[data-tab="${tabKey}"]`).addClass(
      "kw-wordsearch-nav-tab-active"
    );
    $(`#kw-wordsearch-${tabKey}`).show();
  }

  // Add click event listeners to tabs
  tabs.on("click", function (event) {
    event.preventDefault();
    const tabKey = $(this).data("tab");
    activateTab(tabKey);

    // Update the URL hash without reloading the page
    history.pushState(null, "", `#${tabKey}`);
  });

  $("#kw_grid_text_sound_setting").on("change", function () {
    // When toggled on, set the value to "1"; if off, set to "0"
    if ($(this).is(":checked")) {
      $(this).attr("value", "1");
      // Optionally update the checked attribute in the DOM as well:
      $(this).attr("checked", "checked");
    } else {
      $(this).attr("value", "0");
      // Remove the checked attribute so that it’s clear in the DOM:
      $(this).removeAttr("checked");
    }
  });

  $(".kw-reset-button").on("click", function () {
    const $parentSection = $(this).closest(".kw-settings-section");

    // Reset all input, textarea, and select fields with 'data-default'
    $parentSection
      .find("input[data-default], textarea[data-default], select[data-default]")
      .each(function () {
        const defaultValue = $(this).data("default");
        $(this).val(defaultValue);

        const $checkbox = $("#kw_grid_text_sound_setting");
        const defaultCheckboxValue = $checkbox.data("default").toString(); // "0" or "1"

        // Set the value attribute to the default value
        $checkbox.attr("value", defaultCheckboxValue);

        // Update the checked state based on the default:
        if (defaultCheckboxValue === "1") {
          $checkbox.prop("checked", true);
          $checkbox.attr("checked", "checked");
        } else {
          $checkbox.prop("checked", false);
          $checkbox.removeAttr("checked");
        }

        // If it's a color picker, update the color
        if ($(this).hasClass("wp-color-picker")) {
          $(this).wpColorPicker("color", defaultValue);
        }
      });
  });

  // Initialize the active tab based on the URL hash
  const activeTab = window.location.hash.substring(1) || "ai";
  activateTab(activeTab);

  // Default value for the wordsearch prompt
  const defaultPrompt = "Generate a wordsearch puzzle prompt.";

  // Reset button click handler for wordsearch prompt
  $("#kw-reset-default-prompt").on("click", function () {
    $("#kw_wordsearch_prompt_main").val(defaultPrompt);
  });
});

jQuery(document).ready(function ($) {
  function openTab(event, tabId) {
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) {
      tabContents[i].style.display = "none";
    }
    const tabs = document.getElementsByClassName("nav-tab");
    for (let i = 0; i < tabs.length; i++) {
      tabs[i].classList.remove("nav-tab-active");
    }
    document.getElementById(tabId).style.display = "block";
    event.currentTarget.classList.add("nav-tab-active");
  }

  // Copy to clipboard function for Quiz Shortcode
  $(document).on("click", "#quiz-copy-button", function () {
    // Find the sibling input field with ID 'quiz-copy-input' within the same '.shortcode-box'
    var $input = $(this).closest(".shortcode-box").find("#quiz-copy-input");
    $input.focus().select();
    if (document.execCommand("copy")) {
      // Show the copy message within the same '.shortcode-box'
      $(this)
        .closest(".shortcode-box")
        .find("#quiz-copy-message")
        .fadeIn(200)
        .delay(1000)
        .fadeOut(200);
    }
  });

  // Copy to clipboard function for Crossword Shortcode
  $(document).on("click", "#crossword-copy-button", function () {
    // Find the sibling input field with ID 'crossword-copy-input' within the same container
    var $input = $(this)
      .closest(".shortcode-box")
      .find("#crossword-copy-input");
    $input.focus().select();
    if (document.execCommand("copy")) {
      // Show the copy message within the same '.shortcode-box'
      $(this)
        .closest(".shortcode-box")
        .find("#crossword-copy-message")
        .fadeIn(200)
        .delay(1000)
        .fadeOut(200);
    }
  });

  // Copy to clipboard function for Wordsearch Shortcode
  $(document).on("click", "#wordsearch-copy-button", function () {
    // Find the sibling input field with ID 'wordsearch-copy-input' within the same container
    var $input = $(this)
      .closest(".shortcode-box")
      .find("#wordsearch-copy-input");
    $input.focus().select();
    if (document.execCommand("copy")) {
      // Show the copy message within the same '.shortcode-box'
      $(this)
        .closest(".shortcode-box")
        .find("#wordsearch-copy-message")
        .fadeIn(200)
        .delay(1000)
        .fadeOut(200);
    }
  });

  let mediaFrame;
  const $imageField = $("#kw_wordsearch_admin_featured_image");
  const $preview = $("#kw-image-preview");

  // ← Open / select
  $("#kw_wordsearch_admin_featured_image_button").on("click", function (e) {
    e.preventDefault();

    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title: kwWordsearchAdmin.title,
      button: { text: kwWordsearchAdmin.buttonText },
      library: { type: "image" },
      multiple: false,
    });

    mediaFrame.on("select", function () {
      const attachment = mediaFrame.state().get("selection").first().toJSON();

      // STORE the ID, not the URL
      $imageField.val(attachment.id);

      // preview with the medium URL
      $preview.html(
        '<img src="' +
          attachment.sizes.medium.url +
          '" style="max-width:200px;height:150px;" />'
      );
    });

    mediaFrame.open();
  });

  // Function to show "Copied to clipboard" message
  function showCopyMessage(messageId) {
    $(messageId).fadeIn().delay(1500).fadeOut();
  }
});
