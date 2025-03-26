import { updateWordData } from "./game-mechanics.js";
import { batchDomUpdates } from "./utils.js";

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

// window.cookieEntries = cookieData.map((entry) => entry);

// Utility to save cookieEntries back to cookies/local storage
function updateCookieEntries(entries) {
  // Update the cookie with the combined entries (expires in 1 day)
  setCookie("wordsearch_entries", JSON.stringify(entries), 1);
}

// Renders the word list with eye toggle feature
export function renderWordList(wordData) {
  // Initialize cookieEntries
  var existingCookie = getCookie("wordsearch_entries");
  let cookieData = JSON.parse(existingCookie || "[]");
  const listContainer = document.getElementById("wordList");
  if (!listContainer) return;

  listContainer.innerHTML = "";

  // If wordSearchData is defined, filter out words that are hidden in window.finalEntries.
  if (typeof wordSearchData !== "undefined") {
    const wordPanel = document.getElementsByClassName("word-panel");
    if (!wordPanel) return;

    // Make sure finalEntries is defined and is an array.
    if (window.finalEntries && Array.isArray(window.finalEntries)) {
      // Check if every entry in finalEntries has hidden = true.
      const allHidden = window.finalEntries.every(
        (entry) => entry.hidden === true
      );
      // If all are hidden, hide the panel; otherwise, show it.
      wordPanel[0].style.display = allHidden ? "none" : "flex";
    }

    wordData = wordData.filter((word) => {
      const formattedWord = word.toLowerCase();
      if (window.finalEntries && Array.isArray(window.finalEntries)) {
        const entry = window.finalEntries.find(
          (item) => item.wordText.toLowerCase() === formattedWord
        );
        // If a matching entry exists and hidden is true, filter out this word.
        if (entry && entry.hidden === true) {
          return false;
        }
      }
      return true;
    });
  }

  if (wordData.length > 0) {
    const operations = [];

    wordData.forEach((word) => {
      operations.push((fragment) => {
        const formattedWord = word.length > 0 ? word.toLowerCase() : "";
        const li = document.createElement("li");
        li.id = `word-${formattedWord}`;

        const wordSpan = document.createElement("span");
        wordSpan.textContent = formattedWord;
        wordSpan.classList.add("word-text");

        const eyeIconContainer = document.createElement("span");
        eyeIconContainer.classList.add("eye-icon-container");

        // Create SVG icon
        const eyeIcon = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "svg"
        );
        eyeIcon.setAttribute("xmlns", "http://www.w3.org/2000/svg");
        eyeIcon.setAttribute("width", "24");
        eyeIcon.setAttribute("height", "24");
        eyeIcon.setAttribute("viewBox", "0 0 24 24");
        eyeIcon.setAttribute("fill", "none");
        eyeIcon.setAttribute("stroke", "currentColor");
        eyeIcon.setAttribute("stroke-width", "2");
        eyeIcon.setAttribute("stroke-linecap", "round");
        eyeIcon.setAttribute("stroke-linejoin", "round");
        eyeIcon.classList.add("eye-icon");

        const eyePath = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "path"
        );
        eyePath.setAttribute(
          "d",
          "M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
        );

        const eyeCircle = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "circle"
        );
        eyeCircle.setAttribute("cx", "12");
        eyeCircle.setAttribute("cy", "12");
        eyeCircle.setAttribute("r", "3");

        eyeIcon.appendChild(eyePath);
        eyeIcon.appendChild(eyeCircle);
        eyeIconContainer.appendChild(eyeIcon);

        // Determine the initial hidden state from window.finalEntries.
        let isHidden = false;
        if (window.finalEntries && Array.isArray(window.finalEntries)) {
          const matchingEntry = window.finalEntries.find(
            (item) => item.wordText.toLowerCase() === formattedWord
          );
          if (matchingEntry && matchingEntry.hidden === true) {
            isHidden = true;
          }
        }
        // Store state locally on this element.
        li.dataset.hidden = isHidden; // stored as string "true" or "false"

        // Apply initial CSS classes based on the hidden state.
        if (isHidden) {
          li.classList.add("hidden-word");
          eyeIconContainer.classList.add("eye-closed");
        }

        // Click event: toggle the hidden state for this specific word.
        eyeIconContainer.addEventListener("click", (e) => {
          e.stopPropagation();

          // Toggle the state stored on this list item.
          const currentHidden = li.dataset.hidden === "true";
          const newHidden = !currentHidden;
          li.dataset.hidden = newHidden;

          // Immediately update UI classes for this word.
          li.classList.toggle("hidden-word", newHidden);
          eyeIconContainer.classList.toggle("eye-closed", newHidden);

          // Update only the matching cookieData entry.
          cookieData = cookieData.map((entry) => {
            if (entry.wordText.toLowerCase() === formattedWord) {
              return {
                ...entry,
                hidden: newHidden,
              };
            }
            return entry;
          });
          updateCookieEntries(cookieData);

          // Update the corresponding entry in window.finalEntries.
          if (window.finalEntries && Array.isArray(window.finalEntries)) {
            const index = window.finalEntries.findIndex(
              (entry) => entry.wordText.toLowerCase() === formattedWord
            );
            if (index !== -1) {
              window.finalEntries[index].hidden = newHidden;
            }
          }
        });

        li.appendChild(wordSpan);
        if (typeof wordSearchData === "undefined") {
          li.appendChild(eyeIconContainer);
        }
        fragment.appendChild(li);
      });
    });

    batchDomUpdates(operations, listContainer);
  }
}

export function showCompletionMessage() {
  // Ensure SweetAlert2 is available
  if (typeof Swal === "undefined") {
    console.error("SweetAlert2 is not loaded. Please include the library.");
    return;
  }

  const title = window.customStyles["successPopupTitle"];
  const bodyText = window.customStyles["successPopupBodyText"];

  Swal.fire({
    title: `<div class="wordsearch-modal-title-container">
              <span class="wordsearch-modal-title-emoji">🎉</span>
              <span class="wordsearch-modal-title-text">${title}</span>
              <span class="wordsearch-modal-title-emoji">🏆</span>
            </div>`,
    html: `
      <div class="wordsearch-modal-content">
        <p class="wordsearch-modal-description">${bodyText}</p>
        <p class="wordsearch-modal-question">Ready for another challenge?</p>
      </div>
    `,
    icon: "success",
    confirmButtonText: "Play Again",
    showCancelButton: false, // Explicitly remove cancel button
    allowOutsideClick: false,
    allowEscapeKey: false,
    allowEnterKey: false,
    focusConfirm: false,
    backdrop: `
      linear-gradient(135deg, 
        rgba(76, 175, 80, 0.2), 
        rgba(41, 128, 185, 0.2))
    `,
    customClass: {
      title: "wordsearch-modal-title",
      container: "wordsearch-modal-container",
      popup: "wordsearch-modal-popup",
      confirmButton: "wordsearch-modal-confirm-btn",
    },
    showClass: {
      popup: "animate__animated animate__bounceIn",
    },
    hideClass: {
      popup: "animate__animated animate__fadeOut animate__faster",
    },
  }).then((result) => {
    if (result.isConfirmed) {
      // Prevent scrolling/jumping behavior
      // Destroy existing game instance completely
      if (window.gameInstance) {
        window.gameInstance.destroy(true);
        window.gameInstance = null;
      }

      // Reset game state
      window.elapsedTime = 0;
      window.gameTimerID = null;

      const visualClues = document.getElementsByClassName("visual-clue");

      // Loop through the elements
      for (let i = 0; i < visualClues.length; i++) {
        const clue = visualClues[i];
        // Get the data-word attribute and compare (case-insensitive) to guessedWord
        clue.classList.remove("found");
      }

      // Restart the game by calling updateWordData function
      if (typeof updateWordData === "function") {
        updateWordData();
      } else {
        console.error(
          "updateWordData function not found. Unable to restart game."
        );

        // Show error if updateWordData not found
        Swal.fire({
          title: "Oops!",
          text: "Unable to restart the game. Please refresh the page.",
          icon: "error",
          confirmButtonColor: "#3085d6",
        });
      }
    }
  });

  // Add custom styles for the SweetAlert modal
  if (!document.getElementById("wordsearch-swal-styles")) {
    const swalStyles = document.createElement("style");
    swalStyles.id = "wordsearch-swal-styles";
    swalStyles.innerHTML = `
      .wordsearch-modal-popup {
        border-radius: 15px;
        padding: 25px;
        max-width: 500px;
        background: linear-gradient(to bottom right, #ffffff, #f0f0f0);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        border: 2px solid rgba(76, 175, 80, 0.2);
      }
      
      .wordsearch-modal-title-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin-bottom: 10px;
      }
      
      .wordsearch-modal-title-emoji {
        font-size: 36px;
      }
      
      .wordsearch-modal-title {
        font-size: 32px;
        font-weight: 800;
        color: #2c3e50;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
      }
      
      .wordsearch-modal-content {
        text-align: center;
        padding: 15px;
      }
      
      .wordsearch-modal-description {
        font-size: 18px;
        color: #34495e;
        margin-bottom: 15px;
        line-height: 1.5;
      }
      
      .wordsearch-modal-question {
        font-size: 20px;
        font-weight: 600;
        color: #2980b9;
        margin-bottom: 20px;
      }
      
      .wordsearch-modal-confirm-btn {
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 12px 30px;
        border-radius: 50px;
        transition: all 0.3s ease;
      }
      
      .wordsearch-modal-confirm-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      }
      
      .swal2-icon.swal2-success {
        border-color: #4CAF50;
        color: #4CAF50;
      }
    `;
    document.head.appendChild(swalStyles);
  }
}
