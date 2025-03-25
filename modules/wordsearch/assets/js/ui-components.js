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

        // Create word text span
        const wordSpan = document.createElement("span");
        wordSpan.textContent = formattedWord;
        wordSpan.classList.add("word-text");

        // Create eye icon container
        const eyeIconContainer = document.createElement("span");
        eyeIconContainer.classList.add("eye-icon-container");

        // Create eye icon (using SVG for scalability)
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

        // Create eye path
        const eyePath = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "path"
        );
        eyePath.setAttribute(
          "d",
          "M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
        );

        // Create eye circle
        const eyeCircle = document.createElementNS(
          "http://www.w3.org/2000/svg",
          "circle"
        );
        eyeCircle.setAttribute("cx", "12");
        eyeCircle.setAttribute("cy", "12");
        eyeCircle.setAttribute("r", "3");

        // Assemble eye icon
        eyeIcon.appendChild(eyePath);
        eyeIcon.appendChild(eyeCircle);
        eyeIconContainer.appendChild(eyeIcon);

        // Add click event to toggle word visibility
        eyeIconContainer.addEventListener("click", (e) => {
          e.stopPropagation(); // Prevent event bubbling

          // Toggle visibility
          li.classList.toggle("hidden-word");
          eyeIconContainer.classList.toggle("eye-closed");
        });

        // Append elements to list item
        li.appendChild(wordSpan);
        li.appendChild(eyeIconContainer);

        fragment.appendChild(li);
      });
    });

    // Apply all DOM updates at once
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
