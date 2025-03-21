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

jQuery(document).ready(function ($) {
  // Global variables
  window.localizedEntries = [];
  window.wordData = [];
  window.gameInstance = null;
  window.previousFinalEntriesStr = JSON.stringify([]);
  window.finalEntries = [];
  window.letterTexts = [];
  window.cookieEntries = [];
  let adminEntries = [];
  window.previousGridSize = 0;
  window.gameTimerID = null;
  window.elapsedTime = 0;
  window.customStyles = [];
  window.foundWords = [];
  window.showAnswers = false;
  window.checkBoxElement = null;
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
    adminEntries = cookieData.map((entry) => entry);
    console.log("::frontendData6", adminEntries);
  } else {
    adminEntries = [];
  }

  window.customStyles = window.isAdmin
    ? frontendData["gridStyles"]
    : wordSearchData["gridStyles"];

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

  // If we have entries, initialize the puzzle right away
  if (window.finalEntries.length > 0) {
    console.log("::finalEntries", window.finalEntries);
    updateFinalEntries(window.finalEntries); // This calls updateWordData -> which calls waitForWordSearch
    waitForWordSearch(window.wordData);
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

  window.finalEntries = mergeEntries(window.localizedEntries, adminEntries);

  // Determine context first

  // Initialize the puzzle with whatever we have
  if (window.finalEntries.length > 0) {
    console.log("::finalEntries", window.finalEntries);
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

  // Event listener for word entries updates
  $(document).on("wordsearchEntriesUpdated", function (event, updatedEntries) {
    console.log("::updatedEntries", updatedEntries, adminEntries);
    // Avoid unnecessary console logs in production
    // Use a more efficient change detection approach
    let localizedEntriesChanged = false;
    let cookieEntriesChanged = false;

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
              updateGridBasedOnWords(merged, scene, scene.letterTexts);
            } else {
              // Use requestAnimationFrame instead of setTimeout for better performance
              requestAnimationFrame(() => {
                if (
                  scene &&
                  scene.letterTexts &&
                  scene.letterTexts.length > 0
                ) {
                  updateGridBasedOnWords(merged, scene, scene.letterTexts);
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
  } else {
    console.log("::entered outside of conditional block");
  }
});
