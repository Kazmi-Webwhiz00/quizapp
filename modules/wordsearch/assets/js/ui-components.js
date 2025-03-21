import { updateWordData } from "./game-mechanics.js";
import { batchDomUpdates } from "./utils.js";

export function renderWordList(wordData) {
  const listContainer = document.getElementById("wordList");
  if (!listContainer) return;

  listContainer.innerHTML = ""; // Clear any existing content

  if (wordData.length > 0) {
    // Create operations array for batch updates
    const operations = [];
    wordData.forEach((word) => {
      operations.push((fragment) => {
        const formattedWord = word.length > 0 ? word.toLowerCase() : "";
        const li = document.createElement("li");
        li.id = `word-${formattedWord}`;
        li.textContent = formattedWord;
        fragment.appendChild(li);
      });
    });

    // Apply all DOM updates at once
    batchDomUpdates(operations, listContainer);
  }
}

export function showCompletionMessage() {
  const modal = document.getElementById("completionModal");
  if (modal) {
    modal.style.opacity = 0;
    modal.style.display = "block";
    // Animate the opacity for a smooth fade-in.
    setTimeout(() => {
      modal.style.opacity = 1;
    }, 10);

    // Add a click handler to the close button.
    const closeBtn = document.getElementById("closeModal");
    if (closeBtn) {
      closeBtn.onclick = (event) => {
        event.preventDefault();
        // Fade out the modal
        modal.style.opacity = 0;
        setTimeout(() => {
          if (window.gameInstance) {
            window.gameInstance.destroy(true);
            window.gameInstance = null;
          }

          if (typeof updateWordData === "function") {
            updateWordData();
          }

          modal.style.display = "none";

          Array.from(window.checkBoxElement).forEach(function (el) {
            el.checked = false;
          });

          window.elapsedTime = 0;
          window.gameTimerID = null;
        }, 300);
      };
    }
  } else {
    console.error("Completion modal not found in the DOM!");
  }
}
