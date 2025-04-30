jQuery(document).ready(function ($) {
  $(".kw-color-picker").wpColorPicker();
  const tabs = $(".kw-crossword-nav-tab");
  const panes = $(".kw-crossword-tab-pane");

  // Function to activate a tab
  function activateTab(tabKey) {
    tabs.removeClass("kw-crossword-nav-tab-active");
    panes.hide();

    $(`.kw-crossword-nav-tab[data-tab="${tabKey}"]`).addClass(
      "kw-crossword-nav-tab-active"
    );
    $(`#kw-crossword-${tabKey}`).show();
  }

  // Add click event listeners to tabs
  tabs.on("click", function (event) {
    event.preventDefault();
    const tabKey = $(this).data("tab");
    activateTab(tabKey);

    // Update the URL hash without reloading the page
    history.pushState(null, "", `#${tabKey}`);
  });

  $(".kw-reset-button").on("click", function () {
    const $parentSection = $(this).closest(".kw-settings-section");
    // var $imageField = $("#kw_crossword_admin_featured_image");
    // var $preview = $parentSection.find("#kw-image-preview");
    // const defaultUrl = $imageField.data("default-url");

    // Reset all input, textarea, and select fields with 'data-default'
    $parentSection
      .find("input[data-default], textarea[data-default], select[data-default]")
      .each(function () {
        const defaultValue = $(this).data("default");
        $(this).val(defaultValue);

        // If it's a color picker, update the color
        if ($(this).hasClass("wp-color-picker")) {
          $(this).wpColorPicker("color", defaultValue);
        }
      });

    // 2) reset the preview <img>
    // $preview.html(
    //   '<img src="' + defaultUrl + '" style="max-width:200px;height:150px;" />'
    // );
    // $imageField.val(0);
  });

  // Initialize the active tab based on the URL hash
  const activeTab = window.location.hash.substring(1) || "general";
  activateTab(activeTab);

  // Default value for the crossword prompt
  const defaultPrompt = "Generate a crossword puzzle prompt.";

  // Reset button click handler
  $("#kw-reset-default-prompt").on("click", function () {
    $("#kw_crossword_prompt_main").val(defaultPrompt);
  });

  let mediaFrame;
  const $imageField = $("#kw_crossword_admin_featured_image");
  const $preview = $("#kw-image-preview");

  $("#kw_crossword_admin_featured_image_button").on("click", function (e) {
    e.preventDefault();

    if (mediaFrame) {
      mediaFrame.open();
      return;
    }

    mediaFrame = wp.media({
      title: kwCrosswordAdmin.title,
      button: { text: kwCrosswordAdmin.buttonText },
      library: { type: "image" },
      multiple: false,
    });

    mediaFrame.on("select", function () {
      const attachment = mediaFrame.state().get("selection").first().toJSON();

      // ← store the ID, not the URL
      $imageField.val(attachment.id);

      // preview can still use the URL
      $preview.html(
        '<img src="' +
          attachment.sizes.medium.url +
          '" style="max-width:200px;height:150px;" />'
      );
    });

    mediaFrame.open();
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

  // Function to show "Copied to clipboard" message
  function showCopyMessage(messageId) {
    $(messageId).fadeIn().delay(1500).fadeOut();
  }
});
