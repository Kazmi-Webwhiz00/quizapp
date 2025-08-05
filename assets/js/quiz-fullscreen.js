jQuery(function ($) {
  const container = document.getElementById("pf_quiz-container");
  const fsBtn = container.querySelector(".fullscreen-toggle");

  function requestFS(elem) {
    return (
      elem.requestFullscreen?.() ||
      elem.webkitRequestFullscreen?.() ||
      elem.mozRequestFullScreen?.() ||
      elem.msRequestFullscreen?.()
    );
  }
  function exitFS() {
    return (
      document.exitFullscreen?.() ||
      document.webkitExitFullscreen?.() ||
      document.mozCancelFullScreen?.() ||
      document.msExitFullscreen?.()
    );
  }

  fsBtn.addEventListener("click", () => {
    // if *any* element is in fullscreen, exit; otherwise enter fullscreen on our container
    if (document.fullscreenElement === container) {
      exitFS();
    } else {
      requestFS(container).catch((err) => {
        console.error("Could not enter fullscreen:", err);
      });
    }
  });

  // Optional: listen to the API’s event if you need to do anything on change
  document.addEventListener("fullscreenchange", () => {
    if (document.fullscreenElement === container) {
      //   alert("Entering fullscreen mode: " + container);
      container.classList.add("fullscreen");
    } else {
      container.classList.remove("fullscreen");
    }
    // you could toggle a class / change tooltip, etc.
    console.log("Fullscreen now?", !!document.fullscreenElement);
  });
});
