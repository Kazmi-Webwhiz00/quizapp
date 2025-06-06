<?php

function enqueue_quiz_styles() {
    // Enqueue the CSS file
    wp_enqueue_style(
        'quiz-preset-2-style', // Handle name for the stylesheet
        plugin_dir_url(__FILE__) . 'quiz-preset-2.css', // Path to the CSS file
        array(), // Dependencies (if any), can leave empty
        '1.0.0'  // Version number
    );
        wp_enqueue_style(
        'quiz-dynamic-style', // Handle name for the stylesheet
        plugin_dir_url(__FILE__) . 'quiz-dynamic-css.php', // Path to the CSS file
        array(), // Dependencies (if any), can leave empty
        '1.0.0'  // Version number
    );
}
// Hook the function to the appropriate action
add_action('wp_enqueue_scripts', 'enqueue_quiz_styles');

function wp_quiz_render_ui($quiz_id, $questions, $background_color, $button_background_color, $button_text_color, $progress_bar_color, $progress_bar_background_color, $question_font_family, $question_font_color, $question_font_size, $answer_font_family, $answer_font_color, $answer_font_size) {
    $question_image_width = get_post_meta($quiz_id, 'question_image_width', true);
    $question_image_height = get_post_meta($quiz_id, 'question_image_height', true);
    $answer_image_width = get_post_meta($quiz_id, 'answer_image_width', true);
    $answer_image_height = get_post_meta($quiz_id, 'answer_image_height', true);

    ob_start();?>
        <div id="pf_quiz-container" data-quiz-id="<?php echo $quiz_id; ?>" data-total-questions="<?php echo count($questions); ?>" style="background-color: <?php echo $background_color; ?> !important;">
            <div class="pf_quiz-header">
                <p><strong><?php echo '' ?></strong></p>
                <div class="pf_progress-bar" style="width: 100%; background-color: <?php echo $progress_bar_background_color; ?> !important; height: 4px !important; margin-bottom: 15px !important; display: none !important;">
                    <div class="pf_progress" style="width: 0%; height: 4px; background-color: <?php echo $progress_bar_color; ?> ;"></div>
                </div>
            </div>
            <div id="pf_question-container" class="pf_question-card"></div>
            <div class="pf_quiz-footer">
                <button id="pf_back-question-btn" class="pf_button pf_button-secondary" style="display: none !important; width: 50%  !important  ; background-color: <?php echo $button_background_color; ?> !important ; color: <?php echo $button_text_color; ?>  !important ;"><?php echo __('Back', 'wp-quiz-plugin'); ?></button>
                <button id="pf_next-question-btn" class="pf_button pf_button-primary" style="width: 50%  !important ; background-color: <?php echo $button_background_color; ?>  !important ; color: <?php echo $button_text_color; ?> !important ;">Next</button>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                const quizContainer = $('#pf_quiz-container');
                const quizId = quizContainer.data('quiz-id');
                const totalQuestions = quizContainer.data('total-questions');
                const questions = <?php echo json_encode($questions); ?>;
                let questionIndex = 0;
                let selectedAnswer = null, answerSubmitted = false;
                const quiz_ajax_obj = {
                ajax_url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>'
                };
                
                const styles = {
                    backgroundColor: '<?php echo $background_color; ?>',
                    buttonBackgroundColor: '<?php echo $button_background_color; ?>',
                    buttonTextColor: '<?php echo $button_text_color; ?>',
                                        
                    questionfontFamily: '<?php echo $question_font_family; ?>',
                    questionfontColor: '<?php echo $question_font_color; ?>',
                    questionfontSize: '<?php echo $question_font_size; ?>',
                    ansfontFamily: '<?php echo $answer_font_family; ?>',
                    ansfontColor: '<?php echo $answer_font_color; ?>',
                    ansfontSize: '<?php echo $answer_font_size; ?>'
                };

                loadQuestion();

                function promptForUserName() {
                    const userNameHTML = `
                    <div class="pf_user-name-prompt" style="background-color: ${styles.backgroundColor} !important; padding: 10px !important; border-radius: 5px !important; width: 350px; max-width: 400px; margin: 0 auto;">
                        <label for="pf_user-name-input"><?php echo esc_attr__('Please enter your name:', 'wp-quiz-plugin'); ?></label>
                        <input type="text" id="pf_user-name-input" name="user-name" placeholder="<?php echo esc_attr__('Your name here', 'wp-quiz-plugin'); ?>" style="margin: 10px 0 !important;" autocomplete="off">
                        <button id="pf_submit-name-btn" class="pf_button pf_button-primary" style="background-color: ${styles.buttonBackgroundColor} !important; color: ${styles.buttonTextColor} !important;"><?php echo esc_attr__('Submit', 'wp-quiz-plugin'); ?></button>
                    </div>
                    `;
                    $('#pf_quiz-container').html(userNameHTML);
                        $('#pf_submit-name-btn').on('click', function() {
                        const userName = $('#pf_user-name-input').val().trim();
                        if (userName) {
                        sessionStorage.setItem('userName', userName);
                        displayReportCard();
                        } else {
                            alert("Please enter your name.");
                        }
            });
            }

                function loadQuestion() {
                    const question = questions[questionIndex];
                    let questionHTML = `
                        <div class="pf_question-header">
                            <h3 style="font-family: ${styles.questionfontFamily}; color: ${styles.questionfontColor}; font-size: ${styles.questionfontSize}">${question.Title}</h3>
                            ${question.TitleImage ? `<img src="${question.TitleImage}" class="pf_question-image" alt="Question Image" style="<?php echo get_image_style($quiz_id)?>" >` : ''}
                        </div>
                        <div class="pf_answers-container">
                            ${generateAnswerOptions(question.Answer, question.QuestionType)}
                        </div>
                    `;
                    $('#pf_question-container').html(questionHTML);
                    resetAnswerState();
                    loadStoredAnswer(questionIndex, question.QuestionType);
                    updateProgress();
                    updateButtons();
                }

               function generateAnswerOptions(answerData, questionType) {
                    const answers = JSON.parse(answerData);
                    let answerHTML = '';

                    if (questionType === 'MCQ' || questionType === 'T/F') {
                        answers.forEach((answer, index) => {
                            answerHTML += `
                                <div class="pf_answer-option" data-correct="${answer.correct}" style="flex: 1 0 45%  !important ; max-width: 48%  !important ; margin: 1%  !important ; display: flex  !important ; flex-direction: column  !important ; align-items: center  !important ; justify-content: center  !important ; text-align: center  !important ; border: 1px solid #ccc  !important ; padding: 15px  !important ; box-sizing: border-box  !important ;">
                                    ${answer.image ? `<img src="${answer.image}" class="pf_answer-image" alt="Answer Image" style="<?php echo get_image_style($quiz_id, "answer")?>" >` : ''}
                                    <label for="pf_answer-${index}" style="display: block  !important ; font-family: ${styles.ansfontFamily}  !important ; color: ${styles.ansfontColor}  !important ; font-size: ${styles.ansfontSize}  !important ">${answer.text}</label>
                                </div>
                            `;
                        });
                    } else if (questionType === 'Text') {
                        answerHTML = '<textarea class="pf_answer-textarea" placeholder="Type your answer here..."></textarea>';
                    }

                    // Return the flex container with correct width and wrapping behavior
                    return `
                        <div class="pf_answers-container" style="display: flex  !important ; flex-wrap: wrap  !important ; justify-content: space-between  !important ; width: 100%  !important ; flex-direction: row  !important ;">
                            ${answerHTML}
                        </div>
                    `;
                }


                function resetAnswerState() {
                    selectedAnswer = null;
                    answerSubmitted = false;
                }

                function updateButtons() {
                    const $backButton = $('#pf_back-question-btn');
                    const $nextButton = $('#pf_next-question-btn');
                    $backButton.toggle(questionIndex > 0);
                    $nextButton.prop('disabled', true).css('width', $backButton.is(':visible') ? '50%' : '100%');
                }

                $(document).on('click', '.pf_answer-option', function() {
                    if (answerSubmitted) return;
                    $('.pf_answer-option').removeClass('selected incorrect correct');
                    $(this).addClass('selected');
                    selectedAnswer = $(this);
                    $('#pf_next-question-btn').prop('disabled', false);
                    const answerIndex = selectedAnswer.index();
                    const answerValue = selectedAnswer.find('label').text();
                    storeAnswer(questionIndex, answerIndex, answerValue, 'selected');
                });

                $(document).on('input', '.pf_answer-textarea', function() {
                    if (answerSubmitted) return;
                    storeAnswer(questionIndex, null, $(this).val(), 'selected');
                    $('#pf_next-question-btn').prop('disabled', false);
                });

                $('#pf_next-question-btn').on('click', function() {
                    const question = questions[questionIndex];
                    if (!answerSubmitted) {
                        if (question.QuestionType === 'Text') {
                            handleTextAnswer(question);
                        } else {
                            handleMCQAnswer();
                        }
                        $('#pf_next-question-btn').text(questionIndex < totalQuestions - 1 ? '<?php echo __('Next Question', 'wp-quiz-plugin'); ?>' : '<?php echo __('Submit Quiz', 'wp-quiz-plugin'); ?>');
                        answerSubmitted = true;
                    } else {
                       if (questionIndex < totalQuestions - 1) {
                           loadQuestion(++questionIndex);
                       } else {
                           // Before displaying the report card, ensure we have a user name.
                           const userName = sessionStorage.getItem('userName');
                           if (!userName) {
                               promptForUserName();
                           } else {
                               displayReportCard();
                           }
                       }
                   }
                });

                $('#pf_back-question-btn').on('click', function() {
                    if (questionIndex > 0) {
                        loadQuestion(--questionIndex);
                    }
                });

                function handleTextAnswer(question) {
                    const userAnswer = $('.pf_answer-textarea').val().trim().toLowerCase();
                    const correctTextAnswer = JSON.parse(question.Answer)[0].text.toLowerCase();
                    if (userAnswer === correctTextAnswer) {
                        markCorrectTextAnswer(userAnswer);
                    } else {
                        markIncorrectTextAnswer(userAnswer, correctTextAnswer);
                    }
                    $('.pf_answer-textarea').prop('disabled', true);
                }

                function markCorrectTextAnswer(userAnswer) {
                    isAnswerCorrect = true;
                    storeAnswer(questionIndex, null, userAnswer, 'correct');
                    showAnswerMessage(
                                        "<?php echo esc_js(__('Correct answer!', 'wp-quiz-plugin')); ?>",
                                        '#4CAF50',
                                        '#e8f5e9'
                                    );
                    $('.pf_answer-textarea').css('border-color', '#4CAF50');
                }

                function markIncorrectTextAnswer(userAnswer, correctTextAnswer) {
                    isAnswerCorrect = false;
                    storeAnswer(questionIndex, null, userAnswer, 'incorrect');
                    const message = "<?php echo esc_js(__('Incorrect answer. The correct answer is:', 'wp-quiz-plugin')); ?>";
                            showAnswerMessage(
                                message + 
                                ` "<strong>${correctTextAnswer}</strong>"`, 
                                '#f44336', 
                                '#ffebee'
                            );

                    $('.pf_answer-textarea').css('border-color', '#f44336');
                }

                function handleMCQAnswer() {
                    if (!selectedAnswer) return;
                    const correctAnswer = $('.pf_answer-option[data-correct="1"]');
                    if (selectedAnswer.data('correct') == 1) {
                        selectedAnswer.removeClass('selected').addClass('correct');
                        storeAnswer(questionIndex, selectedAnswer.index(), selectedAnswer.find('label').text(), 'correct');
                    } else {
                        selectedAnswer.removeClass('selected').addClass('incorrect').css('border-color', '#f44336');
                        correctAnswer.addClass('correct');
                        storeAnswer(questionIndex, selectedAnswer.index(), selectedAnswer.find('label').text(), 'incorrect');
                    }
                }

                function showAnswerMessage(message, textColor, bgColor) {
                    $('.pf_correct-answer-message').remove();
                    $('.pf_answer-textarea').after(`<p class="pf_correct-answer-message" style="color: ${textColor}  !important ; background-color: ${bgColor}  !important ; padding: 10px  !important ; border-radius: 5px  !important ; margin-top: 10px  !important ;">${message}</p>`);
                }

                function storeAnswer(questionIndex, answerIndex, answerValue, state) {
                    sessionStorage.setItem('quiz_answer_' + questionIndex, JSON.stringify({
                        answerIndex: answerIndex,
                        answerValue: answerValue,
                        state: state
                    }));
                }

                function loadStoredAnswer(questionIndex, questionType) {
                    const storedData = JSON.parse(sessionStorage.getItem('quiz_answer_' + questionIndex));
                    const $nextButton = $('#pf_next-question-btn');
                    if (storedData) {
                        if (['MCQ', 'T/F'].includes(questionType)) {
                            const answerOption = $('.pf_answer-option').eq(storedData.answerIndex);
                            handleStoredMCQAnswer(storedData, answerOption, questionIndex);
                        } else if (questionType === 'Text') {
                            handleStoredTextAnswer(storedData, questionIndex);
                        }
                         // Ensure the button is not just programmatically enabled, but also visually active
                        setTimeout(function() {
                            $nextButton.prop('disabled', false) // Remove the disabled property
                                .removeClass('disabled')       // Remove any CSS class that may cause it to appear disabled
                                .css({
                                    'pointer-events': 'auto',  // Ensure the button is clickable
                                    'opacity': '1'             // Restore full opacity (remove any graying-out effect)
                                });
                        }, 0);

                    } else {
                        $nextButton.text('<?php echo esc_attr__('Check Answer', 'wp-quiz-plugin'); ?>');
                    }

                    
                    
                }

                function handleStoredMCQAnswer(storedData, answerOption, questionIndex) {
                    if (storedData.state === 'correct') {
                        answerOption.addClass('correct');
                    } else if (storedData.state === 'incorrect') {
                        answerOption.addClass('incorrect').css('border-color', '#f44336');
                        $('.pf_answer-option[data-correct="1"]').addClass('correct');
                    }
                    answerOption.off('click');
                    $('#pf_next-question-btn').prop('disabled', false).text(questionIndex < totalQuestions - 1 ? '<?php echo __('Next Question', 'wp-quiz-plugin'); ?>' : '<?php echo __('Submit Quiz', 'wp-quiz-plugin'); ?>');
                    answerSubmitted = true;
                }

                function handleStoredTextAnswer(storedData, questionIndex) {
                    $('.pf_answer-textarea').val(storedData.answerValue).prop('disabled', true);
                    if (storedData.state === 'correct') {
                        $('.pf_answer-textarea').css('border-color', '#4CAF50');
                    } else if (storedData.state === 'incorrect') {
                        $('.pf_answer-textarea').css('border-color', '#f44336');
                        $('.pf_answer-textarea').after(`<p class="pf_correct-answer-message" style="color: #f44336  !important ; background-color: #ffebee  !important ; padding: 10px  !important ; border-radius: 5px  !important ; margin-top: 10px  !important ;">
                                    <?php echo esc_js(__('Incorrect answer. The correct answer is:', 'wp-quiz-plugin')); ?> 
                                    "<strong>${JSON.parse(questions[questionIndex].Answer)[0].text}</strong>"
                                </p>`);
                    }
                    $('#pf_next-question-btn').prop('disabled', false).text(questionIndex < totalQuestions - 1 ? '<?php echo __('Next Question', 'wp-quiz-plugin'); ?>' : '<?php echo __('Submit Quiz', 'wp-quiz-plugin'); ?>');
                    answerSubmitted = true;
                }

                function updateProgress() {
                    const progressPercentage = Math.round(((questionIndex + 1) / totalQuestions) * 100); // Calculate percentage
                    $('.pf_progress').css('width', progressPercentage + '%'); // Update progress bar width
                    $('.pf_quiz-header p strong').text(`${progressPercentage}%`); // Update header to show percentage
                }


                function displayReportCard() {
                    let correctCount = 0;
                    let incorrectCount = 0;
                    const userName = sessionStorage.getItem('userName') || 'Unknown Student';
                    let answersData = [];
                    for (let i = 0; i < totalQuestions; i++) {
                        const storedData = JSON.parse(sessionStorage.getItem('quiz_answer_' + i));
                        const questionData = questions[i];
                        answersData.push({
                            question: questionData.Title,
                            correctAnswer: JSON.parse(questionData.Answer).find(ans => ans.correct == 1).text,
                            userAnswer: storedData.answerValue,
                            isCorrect: storedData.state === 'correct'
                        });
                        storedData.state === 'correct' ? correctCount++ : incorrectCount++;
                    }

                    const scorePercentage = ((correctCount / totalQuestions) * 100).toFixed(2);
                    const quizData = {
                        userName,
                        quizId,
                        score: scorePercentage,
                        answersData
                    };

                    $.ajax({
                        url: quiz_ajax_obj.ajax_url,
                        method: 'POST',
                        data: {
                            action: 'store_quiz_submission',
                            quiz_data: JSON.stringify(quizData)
                        }
                    });

                    const reportCardHTML = `
                        <div class="pf_report-card">
                            <h2><?php echo __('Quiz Report Card', 'wp-quiz-plugin'); ?></h2>
                            <p><?php echo __('Student Name:', 'wp-quiz-plugin'); ?> <strong>${userName}</strong></p>
                            <p><?php echo __('Total Questions:', 'wp-quiz-plugin'); ?> <strong>${totalQuestions}</strong></p>
                            <p><?php echo __('Correct Answers:', 'wp-quiz-plugin'); ?> <strong>${correctCount}</strong></p>
                            <p><?php echo __('Incorrect Answers:', 'wp-quiz-plugin'); ?> <strong>${incorrectCount}</strong></p>
                            <p><?php echo __('Your Score:', 'wp-quiz-plugin'); ?> <strong>${scorePercentage}%</strong></p>
                            <div class="pf_score-bar"><div class="pf_score-fill" style="width: ${scorePercentage}%  !important "></div></div>
                            <button id="pf_retake-quiz-btn" class="pf_button pf_button-primary"><?php echo __('Retake Quiz', 'wp-quiz-plugin'); ?></button>
                            <button id="pf_download-result-pdf-btn" class="pf_button pf_button-secondary"><?php echo __('Download Result as PDF', 'wp-quiz-plugin'); ?></button>
                        </div>
                    `;
                    $('#pf_quiz-container').html(reportCardHTML);

                    $('#pf_retake-quiz-btn').on('click', function() {
                        sessionStorage.clear();
                        location.reload();
                    });

                    $('#pf_download-result-pdf-btn').on('click', function() {
                        const quizData = {
                            userName, totalQuestions, correctCount, incorrectCount, scorePercentage, answersData
                        };

                        $.ajax({
                            url: quiz_ajax_obj.ajax_url,
                            method: 'POST',
                            xhrFields: { responseType: 'blob' },
                            data: {
                                action: 'generate_pdf',
                                quiz_data: JSON.stringify(quizData)
                            },
                            success: function(response) {
                                const blob = new Blob([response], { type: 'application/pdf' });
                                const link = document.createElement('a');
                                link.href = window.URL.createObjectURL(blob);
                                link.download = 'podsumowanie_quizu.pdf';
                                link.click();
                            }
                        });
                    });
                }
            });
        </script>
    <?php
return ob_get_clean();
}

?>