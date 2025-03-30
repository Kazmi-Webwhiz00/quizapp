// Import modules
import { createWordSearchGame } from "./game-creator.js";
import {
  updateWordData,
  mergeEntries,
  updateFinalEntries,
  waitForWordSearch,
  updateGridBasedOnWords,
  stopGameTimer,
} from "./game-mechanics.js";

import { downloadWordSearchAsPDF } from "./utils.js";

jQuery(document).ready(function ($) {
  // Global variables
  window.localizedEntries = [];
  window.wordData = [];
  window.gameInstance = null;
  window.previousFinalEntriesStr = JSON.stringify([]);
  window.finalEntries = [];
  window.totalEntries = 0;
  window.letterTexts = [];
  window.cookieEntries = [];
  let adminEntries = [];
  window.previousGridSize = 0;
  window.gameTimerID = null;
  window.elapsedTime = 0;
  window.customStyles = [];
  window.foundWords = [];
  window.showAnswers = false;
  window.showWords = false;
  window.checkBoxElement = null;
  window.showWordsElement = null;
  window.newGridSize = 0;
  window.isAdmin = typeof wordSearchData === "undefined";

  window.gamerTimerValue = window.isAdmin
    ? frontendData.timerValue
    : wordSearchData.timerValue;

  if (!window.isAdmin) {
    let data = JSON.parse(wordSearchData.entries || "[]");
    window.localizedEntries = data.map((entry) => entry);
  }

  if (window.isAdmin) {
    let cookieData = JSON.parse(frontendData.entries || "[]");
    window.cookieEntries = cookieData.map((entry) => entry);
    adminEntries = cookieData.map((entry) => entry);
  } else {
    adminEntries = [];
  }

  window.customStyles = window.isAdmin
    ? frontendData["gridStyles"]
    : wordSearchData["gridStyles"];

  window.pdfText = window.isAdmin
    ? frontendData["pdfText"]
    : wordSearchData["pdfText"];

  const rawData = window.isAdmin ? frontendData.entries : [];
  if (rawData) {
    try {
      const parsed = JSON.parse(rawData);
      if (Array.isArray(parsed) && parsed.length > 0) {
        window.finalEntries = parsed;
      }
    } catch (e) {
      console.log("Error parsing initial wordsearch_entries cookie:", e);
    }
  }

  window.finalEntries = mergeEntries(window.localizedEntries, adminEntries);

  // If we have entries, initialize the puzzle right away
  if (window.finalEntries.length > 0) {
    updateFinalEntries(window.finalEntries);
    waitForWordSearch(window.wordData);
    // Check for duplicate wordText values (ignoring empty strings).
    const entries = window.finalEntries;
    const wordSet = new Set();
    let duplicateFound = false;
    for (const entry of entries) {
      if (entry.wordText && entry.wordText.trim() !== "") {
        if (wordSet.has(entry.wordText)) {
          duplicateFound = true;
          break;
        }
        wordSet.add(entry.wordText);
      }
    }
    // If any duplicate wordText is found, update the visual clues and return immediately.
    if (duplicateFound) {
      updateVisualClues(entries);
      return;
    }

    if (window.finalEntries) {
      // Add a small delay to ensure DOM is ready after any grid updates
      updateVisualClues(window.finalEntries);
    }
  }

  if (!window.gameInstance) {
    window.gameInstance = createWordSearchGame({
      containerId: "game-container",
      puzzleOptions: {
        // start with an empty word list
        // You can include other default puzzle options as needed.
      },
      onGameReady: function (scene) {
        console.log("Default empty game instance created.");
      },
    });
  }

  // Determine context first

  // Initialize the puzzle with whatever we have
  if (window.finalEntries.length > 0) {
    updateWordData(); // This will create the game
  } else {
    console.log("No initial finalEntries. Waiting for new entries...");
  }

  function showWordLimitModal() {
    // Get the modal by ID instead of className
    const modal = document.getElementById("wordLimitModal");

    if (modal) {
      // Set display first, then animate opacity
      modal.style.display = "flex";
      modal.style.opacity = 0;

      // Animate the opacity for a smooth fade-in
      setTimeout(() => {
        modal.style.opacity = 1;
        // Add the show class to trigger CSS animations
        modal.classList.add("show");
      }, 10);

      // Add a click handler to the OK button
      const okBtn = document.getElementById("wordLimitOkButton");
      if (okBtn) {
        okBtn.onclick = (event) => {
          event.preventDefault();
          // Fade out the modal
          modal.style.opacity = 0;
          modal.classList.remove("show");

          setTimeout(() => {
            modal.style.display = "none";
          }, 300);
        };
      }
    } else {
      console.error("Word limit modal not found in the DOM!");
    }
  }

  window.showWordLimitModal = showWordLimitModal;

  // Function to handle adding and updating visual clues
  function updateVisualClues(entries) {
    // Clear previous clues by emptying the container and ensure it's visible.
    const $container = $(".visual-clues-container");
    const hasImageEntry = entries.some((entry) => entry.imageUrl !== "");
    if (hasImageEntry) {
      $container.empty().css("display", "block");
    }

    // Only proceed if entries exist.
    if (!Array.isArray(entries) || entries.length === 0) return;

    // Sets to track processed words and image URLs.
    const addedWords = new Set();
    const addedImageUrls = new Set();

    for (const entry of entries) {
      // Process only entries with both wordText and imageUrl.
      if (entry.wordText && entry.imageUrl) {
        // If this imageUrl or wordText has already been processed, skip it.
        if (
          addedImageUrls.has(entry.imageUrl) ||
          addedWords.has(entry.wordText)
        ) {
          continue;
        }

        // Record this entry as processed.
        addedWords.add(entry.wordText);
        addedImageUrls.add(entry.imageUrl);

        // Add the visual clue.
        addVisualClue(entry.wordText, entry.imageUrl);
        addedImageUrls.clear();
      }
    }
  }

  // Function to add a single visual clue with random positioning
  function addVisualClue(word, imageUrl) {
    const clueWidth = 60;
    const clueHeight = 60;
    const containerWidth = $(".visual-clues-container").width();
    const containerHeight = $(".visual-clues-container").height();

    // Default dimensions if container not yet sized
    const cWidth = containerWidth || 180;
    const cHeight = containerHeight || 350;

    const maxLeft = cWidth - clueWidth;
    const maxTop = cHeight - clueHeight;

    // Generate random position
    const position = {
      top: Math.floor(Math.random() * maxTop),
      left: Math.floor(Math.random() * maxLeft),
      rotation: Math.floor(Math.random() * 40 - 20), // -20 to +20 degrees
    };

    // Create visual clue element
    const $clue = $("<div></div>")
      .addClass("visual-clue")
      .attr("data-word", word.toLowerCase())
      .css({
        top: position.top + "px",
        left: position.left + "px",
        "--rotation": position.rotation + "deg", // CSS variable for rotation
        transform: "rotate(" + position.rotation + "deg)",
      });

    // Create and append image
    $("<img>").attr("src", imageUrl).attr("alt", word).appendTo($clue);

    // Add to container
    $(".visual-clues-container").append($clue);
  }

  // Event listener for word entries updates
  $(document).on("wordsearchEntriesUpdated", function (event, updatedEntries) {
    // Avoid unnecessary console logs in production
    // Use a more efficient change detection approach
    let localizedEntriesChanged = false;
    let cookieEntriesChanged = false;

    const entries = updatedEntries.data;

    // Check for duplicate wordText values (ignoring empty strings).
    const wordSet = new Set();
    let duplicateFound = false;
    for (const entry of entries) {
      if (entry.wordText && entry.wordText.trim() !== "") {
        if (wordSet.has(entry.wordText)) {
          duplicateFound = true;
          break;
        }
        wordSet.add(entry.wordText);
      }
    }
    // If any duplicate wordText is found, update the visual clues and return immediately.
    if (duplicateFound) {
      updateVisualClues(entries);
      return;
    }

    if (updatedEntries.data) {
      // Add a small delay to ensure DOM is ready after any grid updates
      updateVisualClues(updatedEntries.data);
    }

    // Check if localized entries have changed
    if (!window.isAdmin && wordSearchData.entries) {
      try {
        const newLocalizedEntries = JSON.parse(wordSearchData.entries);
        // Use a simple length comparison first (faster)
        if (newLocalizedEntries.length !== window.localizedEntries.length) {
          window.localizedEntries = newLocalizedEntries.map((entry) => entry);
          localizedEntriesChanged = true;
        } else {
          // Only do deep comparison if lengths match
          if (
            JSON.stringify(newLocalizedEntries) !== JSON.stringify(adminEntries)
          ) {
            window.localizedEntries = newLocalizedEntries.map((entry) => entry);
            localizedEntriesChanged = true;
          }
        }
      } catch (e) {
        console.error("Error parsing localized data:", e);
      }
    }

    // Process cookie entries
    try {
      const newCookieEntries = updatedEntries.data ? updatedEntries.data : [];
      if (typeof frontendData !== "undefined" && frontendData.entries) {
        // Merge new entries with the existing cookie entries.

        if (JSON.stringify(newCookieEntries) !== JSON.stringify(adminEntries)) {
          adminEntries = newCookieEntries.map((entry) => entry);
          cookieEntriesChanged = true;
        }
      } else {
        // Fallback: replace cookie entries if no frontendData.entries exist.
        // if (newCookieEntries.length !== adminEntries.length) {
        // adminEntries = newCookieEntries;
        // cookieEntriesChanged = false;
        // } else if (newCookieEntries.length > 0) {
        //   if (
        //     JSON.stringify(newCookieEntries) !==
        //     JSON.stringify(adminEntries)
        //   ) {
        //     adminEntries = newCookieEntries;
        //     cookieEntriesChanged = true;
        //   }
        // }
      }
    } catch (e) {
      console.error("Error parsing cookie data:", e);
    }

    // Only process if something actually changed
    if (localizedEntriesChanged || cookieEntriesChanged) {
      const merged = mergeEntries(window.localizedEntries, adminEntries);

      // Skip stringification if we can directly detect changes
      if (merged.length === 0) {
        if (window.finalEntries.length !== 0) {
          window.finalEntries = [];
          updateFinalEntries([]);
        }
      } else {
        // Check if we need to update
        const needsUpdate =
          window.finalEntries.length !== merged.length ||
          JSON.stringify(merged) !== window.previousFinalEntriesStr;

        if (needsUpdate) {
          window.previousFinalEntriesStr = JSON.stringify(merged);
          updateFinalEntries(merged);

          // Optimize game instance updates
          if (window.gameInstance) {
            const scene = window.gameInstance.scene.scenes[0];
            if (scene && scene.letterTexts && scene.letterTexts.length > 0) {
              // updateGridBasedOnWords(merged, scene, scene.letterTexts);
            } else {
              // Use requestAnimationFrame instead of setTimeout for better performance
              requestAnimationFrame(() => {
                if (
                  scene &&
                  scene.letterTexts &&
                  scene.letterTexts.length > 0
                ) {
                  // updateGridBasedOnWords(merged, scene, scene.letterTexts);
                }
              });
            }
          }
        }
      }
    }
  });

  // Initialize shuffle button if it exists
  if (window.isAdmin) {
    const shuffleElement = document.getElementById(frontendData.shuffleElement);
    if (shuffleElement) {
      shuffleElement.addEventListener("click", function (event) {
        event.preventDefault();
        stopGameTimer(); // Stop timer before destroying the game
        if (window.gameInstance) {
          window.gameInstance.destroy(true);
          window.gameInstance = null;
        }
        updateWordData();
        $(window.checkBoxElement).prop("checked", false);
      });
    }
  }

  if (window.localizedEntries.length > 0 || window.cookieEntries.length > 0) {
    const downloadElement = document.getElementById(
      typeof frontendData !== "undefined"
        ? frontendData.downloadElement
        : wordSearchData.downloadElement
    );
    if (downloadElement) {
      downloadElement.style.display = "flex";
      downloadElement.addEventListener("click", function (event) {
        downloadElement.style.padding = "10px 20px";
        downloadElement.style.backgroundColor = "#f5d992";
        downloadElement.style.border = "2px solid #c89836";
        downloadElement.style.borderRadius = "4px";
        downloadElement.style.color = "#473214";
        downloadElement.style.fontWeight = "bold";
        downloadElement.style.cursor = "pointer";
        downloadElement.style.fontFamily = "Georgia, serif";
        downloadElement.style.boxShadow = "0 2px 4px rgba(0,0,0,0.2)";
        event.preventDefault();
        // Add hover effect
        downloadElement.onmouseover = function () {
          this.style.backgroundColor = "#e6ba6c";
        };
        downloadElement.onmouseout = function () {
          this.style.backgroundColor = "#f5d992";
        };

        downloadWordSearchAsPDF();
      });
    }
  }

  // Get the initial sound setting from WordPress options
  const soundElement = window.customStyles["toggleSound"];

  // Toggle sound function
  let soundEnabled = true;
  window.soundEnabled = soundEnabled;

  // Set initial UI state
  $("#soundOnIcon").show();
  $("#soundOffIcon").hide();

  // Toggle sound function
  $(`#${soundElement}`).on("click", function (event) {
    console.log(":: Sound toggle button clicked");
    event.preventDefault();

    // Toggle the sound state
    soundEnabled = !soundEnabled;
    window.soundEnabled = soundEnabled;

    // Update the UI based on the new state
    if (soundEnabled) {
      $("#soundOnIcon").show();
      $("#soundOffIcon").hide();
      console.log("Sound is now enabled");
      // Your code for when sound is on
    } else {
      $("#soundOnIcon").hide();
      $("#soundOffIcon").show();
      console.log("Sound is now disabled");
      // Your code for when sound is off
    }
  });
});
