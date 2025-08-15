/**
 * Safe meta-box reorder that preserves TinyMCE content, toolbar,
 * AND the media buttons printed by wp_editor().
 */
(function ($) {
  // ---------- Desired order ----------
  function buildOrder() {
    var postType = (window.KW_MetaBox_Order && KW_MetaBox_Order.postType) || "";

    var order = [
      "submitdiv", // Publish
      "postimagediv", // Featured Image
      "my_custom_info_box_id", // My custom image
    ];

    if (postType === "wordsearch") {
      // indexOf('postimagediv') === 1, so splice at 2
      order.splice(2, 0, "wordsearch-timer"); // after Featured Image
    }

    var typePrefix = postType === "quizzes" ? "quiz" : postType;
    order.push(typePrefix + "_seo_text_meta_box");
    order.push(typePrefix + "_visibility_meta_box");

    return order;
  }

  // ---------- Helpers ----------
  var debounceTimer = null;
  var isReordering = false;

  function isHeartbeatOrAutosave(url) {
    url = url || "";
    return /heartbeat|autosave/i.test(url);
  }

  function getCurrentIds($container) {
    return $container
      .find("> .postbox")
      .map(function () {
        return this.id || "";
      })
      .get();
  }

  function captureEditorState($box) {
    // Keep the original DOM (.wp-editor-wrap and media buttons)
    var $ta = $box.find("textarea.wp-editor-area");
    if (!$ta.length) return null;

    var id = $ta.attr("id");
    var content = "";
    var wasVisual = null;

    if (window.tinyMCE && tinyMCE.get(id)) {
      var ed = tinyMCE.get(id);
      wasVisual = !ed.isHidden(); // true if Visual tab active
      content = ed.getContent({ format: "raw" });
      try {
        wp.editor.remove(id);
      } catch (e) {}
    } else {
      // Text tab active; textarea already present
      wasVisual = false;
      content = $ta.val();
      // IMPORTANT: do not remove or recreate the textarea/wrap
    }

    return { id: id, content: content, wasVisual: wasVisual };
  }

  function reinitWpEditor(textareaId, content, wasVisual) {
    if (!window.wp || !wp.editor || !window.tinyMCEPreInit) return;

    // Set the textarea value BEFORE re-init so WP uses it
    var $ta = $("#" + textareaId);
    if ($ta.length && typeof content === "string") {
      $ta.val(content);
    }

    var mceSettings = $.extend(
      true,
      {},
      (tinyMCEPreInit.mceInit || {})[textareaId] || {}
    );
    var qtSettings = $.extend(
      true,
      {},
      (tinyMCEPreInit.qtInit || {})[textareaId] || {}
    );

    // Re-initialize using WP’s server-provided config (keeps toolbar layout)
    wp.editor.initialize(textareaId, mceSettings);

    if (typeof window.quicktags === "function" && qtSettings.id) {
      window.quicktags(qtSettings);
      if (window.QTags && typeof QTags._buttonsInit === "function") {
        QTags._buttonsInit();
      }
    }

    // Restore active tab
    if (wasVisual === false && window.switchEditors) {
      switchEditors.go(textareaId, "html"); // back to Text tab
    }
  }

  function moveBoxIfNeeded($container, id, targetIndex) {
    var $box = $("#" + id);
    if (!$box.length) return;

    var currentIds = getCurrentIds($container);
    var currentIndex = currentIds.indexOf(id);
    if (currentIndex === -1 || currentIndex === targetIndex) return; // already right

    // Capture editor state (but DO NOT rebuild DOM)
    var editorState = captureEditorState($box);

    // Insert before the element that should follow it; else append
    var $siblings = $container.find("> .postbox");
    var $after = $siblings.eq(targetIndex + 1);
    if ($after.length) $after.before($box);
    else $container.append($box);

    // Re-init the editor with original settings (keeps toolbar + media buttons)
    if (editorState && editorState.id) {
      reinitWpEditor(
        editorState.id,
        editorState.content || "",
        editorState.wasVisual
      );
    }
  }

  function reorderSideBoxes() {
    if (isReordering) return;
    isReordering = true;

    var order = buildOrder();
    var $container = $("#side-sortables");
    if (!$container.length) {
      isReordering = false;
      return;
    }

    // Only act on boxes that exist
    var existing = order.filter(function (id) {
      return $("#" + id).length;
    });

    existing.forEach(function (id, i) {
      moveBoxIfNeeded($container, id, i);
    });

    isReordering = false;
  }

  function reorderDebounced() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(reorderSideBoxes, 150);
  }

  // ---------- Wire up ----------
  $(document).ready(reorderSideBoxes);

  // Reorder after meaningful AJAX, but skip heartbeat/autosave
  $(document).ajaxComplete(function (_evt, _xhr, settings) {
    var url = (settings && settings.url) || "";
    if (isHeartbeatOrAutosave(url)) return;
    reorderDebounced();
  });
})(jQuery);
