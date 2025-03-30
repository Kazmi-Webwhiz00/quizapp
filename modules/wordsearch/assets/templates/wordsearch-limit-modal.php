<?php
$default_entry_limit_popup_label = __("Entry Limit Reached", 'wp-quiz-plugin');
$default_entry_limit__body_text = __('You cannot add more than 15 entries to the word search.', 'wp-quiz-plugin');
$entry_limit__popup_title = get_option('kw_wordsearch_entry_limit_popup_title', $default_entry_limit_popup_label);
$entry_limit__popup_body_text = get_option('kw_wordsearch_entry_limit_popup_body_text', $default_entry_limit__body_text);

?>

<div id="wordLimitModal" class="word-limit-modal" style="display: none;">
  <div class="word-limit-modal-content">
    <div class="word-limit-modal-header">
    <h3><?php echo esc_html($entry_limit__popup_title); ?></h3>
    </div>
    <div class="word-limit-modal-body">
    <p><?php echo esc_html($entry_limit__popup_body_text); ?></p>
    </div>
    <div class="word-limit-modal-footer">
      <button id="wordLimitOkButton" class="word-limit-ok-button">OK</button>
    </div>
  </div>
</div>