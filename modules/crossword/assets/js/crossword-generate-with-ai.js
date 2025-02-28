jQuery(document).ready(function ($) {

console.log("is adimi is ",wpQuizPlugin.isAdmin );
/**
 * Replaces placeholders in a string using an object of key-value pairs.
 *
 * @param {string} template - The string template containing placeholders (e.g., [key]).
 * @param {object} variables - An object with key-value pairs for replacement.
 * @returns {string} - The template with placeholders replaced by actual values.
 */
function replacePlaceholders(template, variables) {
    Object.keys(variables).forEach((key) => {
        const regex = new RegExp('\\[' + key + '\\]', 'g'); // Global replacement for all instances of [key]
        template = template.replace(regex, variables[key]);
    });
    return template;
}

/**
 * Generates the complete prompt content by replacing placeholders in context,
 * generation, and return format prompts.
 *
 * @param {number} number - The number of words to generate.
 * @param {string} topic - The topic for the crossword.
 * @param {number} age - The age group of the user.
 * @param {string} language - The language for the crossword.
 * @returns {string} - The final combined prompt content.
 */
function generatePrompt(number, topic, age, language) {
    // Retrieve localized default prompts
    const contextPromptTemplate     = wpQuizPlugin.defaultContextPrompt;
    const generationPromptTemplate  = wpQuizPlugin.defaultGenerationPrompt;
    const returnFormatPrompt        = wpQuizPlugin.defaultReturnFormatPrompt;

    // 1) Get selected categories using the updated selectors
    const selectedCategories = getSelectedCategories();

    // 2) Replace [existing_words] in context prompt
    const existingWords = getCrosswordWordsList();
    const contextPrompt = replacePlaceholders(contextPromptTemplate, {
        existing_words: existingWords.join(', '),
    });

    // 3) Replace placeholders in generation prompt, including the new [categories]
    const generationPrompt = replacePlaceholders(generationPromptTemplate, {
        number: number,
        topic: topic,
        age: age,
        language: language,
        categories: selectedCategories,
    });

    // 4) Combine prompts into the final prompt
    return `${generationPrompt} ${contextPrompt} ${returnFormatPrompt}`;
}

    
function getCrosswordWordsList() {
    let wordsList = [];

    $('.crossword-word-clue').each(function () {
        const index = $(this).data('index');
        const word = $(`input[name="crossword_words[${index}][word]"]`).val();

        if (word) {
            wordsList.push(word.toUpperCase());
        }
    });

    return wordsList;
}
    
    
function appendGeneratedContent(generatedContent) {
    const $container = $("#crossword-words-clues-container");
    const template = $("#crossword-word-clue-template").html();
    const existingEntries = $container.find(".crossword-word-clue").length;

    generatedContent.forEach((item, index) => {
        const newIndex = existingEntries + index;
        
        // Preserve existing unique ID or generate a new one
        const uniqueId = item.uniqueId ? item.uniqueId : `cw_${Date.now()}_${Math.floor(Math.random() * 1000)}`;

        console.log(`Generated Unique ID for word: ${item.word} -> ${uniqueId}`); // Debugging

        let entryHtml = template
            .replace(/{{index}}/g, newIndex)
            .replace(/{{number}}/g, newIndex + 2)
            .replace('value=""', `value="${item.word}"`)  // Sets word
            .replace('value=""', `value="${item.clue}"`)  // Sets clue
            .replace('value=""', `value="${uniqueId}"`); // Sets uniqueId


        // Convert HTML string to a jQuery object
        let $entry = $(entryHtml);
        
        // Add unique ID as a data attribute to identify this record
        $entry.attr("data-unique-id", uniqueId);
        $entry.find("input[name^='crossword_words']").each(function () {
            let nameAttr = $(this).attr("name");
            if (nameAttr.includes("[word]")) {
                $(this).attr("name", `crossword_words[${newIndex}][word]`).attr("data-unique-id", uniqueId);
            } else if (nameAttr.includes("[clue]")) {
                $(this).attr("name", `crossword_words[${newIndex}][clue]`).attr("data-unique-id", uniqueId);
            }
        });

        // Append to container
        $container.append($entry);
    });
}

/**
 * Returns a string of selected categories joined by " > ".
 *
 * This function grabs the text of the selected options from the new dropdowns.
 */
function getSelectedCategories() {
    let selectedCategories = [];

    // Grab the selected text from each dropdown using the updated IDs.
    let selectedSchool   = $('#selected_school_crossword').find(':selected').text().trim();
    let selectedClass    = $('#selected_class_crossowrd').find(':selected').text().trim();
    let selectedSubject  = $('#selected_subject_crossword').find(':selected').text().trim();

    // Validation: category must not be empty or only hyphens.
    const isValidCategory = (category) => {
        return category.length > 0 && !category.match(/^-{3,}$/);
    };

    if (isValidCategory(selectedSchool)) selectedCategories.push(selectedSchool);
    if (isValidCategory(selectedClass)) selectedCategories.push(selectedClass);
    if (isValidCategory(selectedSubject)) selectedCategories.push(selectedSubject);

    // Return categories joined with a separator.
    return selectedCategories.join(' > ');
}

    

    // Unique ID for the button
    const generateButtonId = '#generate-ai-button';

    // Click event handler for the Generate with AI button
    $(generateButtonId).on('click', function () {
        const topic = $('#ai-topic').val().trim();
        const age = $('#ai-age').val().trim();
        const number = $('#ai-questions').val().trim();
        const language = $('#ai-language').val().trim();
        const maxNumberOfWords = parseInt(wpQuizPlugin.maxNumberOfWords);
        // Check if the number is between 1 and 10
        if (number < 1 || number > maxNumberOfWords) {
            Swal.fire(wpQuizPlugin.strings.errorTitle, `${wpQuizPlugin.strings.numberError} ${wpQuizPlugin.maxNumberOfWords}` , 'warning');
            return;
        }

        console.log(generatePrompt(number,topic,age,language));
        // Prepare data for OpenAI API request
        const data = {
            model: wpQuizPlugin.model,
            messages: [{
                role: 'user',
                content: generatePrompt(number,topic,age,language)
            }],
            max_tokens: parseInt(wpQuizPlugin.maxTokens),
            temperature: parseFloat(wpQuizPlugin.temperature)
        };

        // Function to send the request with retry logic
        function sendRequest(retryCount, count) {
            if (count <= 0) return;

            $.ajax({
                url: 'https://api.openai.com/v1/chat/completions',
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + wpQuizPlugin.apiKey,
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify(data),
                beforeSend: function () {
                    $('.kw-loading').show();
                    console.log('Sending request to OpenAI...', data);
                 if(wpQuizPlugin.isAdmin){
                    showAdminPrompt(data.messages[0].content)
                 }
                    $(generateButtonId).text(wpQuizPlugin.generatingText).prop('disabled', true);
                },
                success: function (response) {
                    console.log('Received response:', response);
                    try {
                        const generatedContentString = response.choices[0].message.content.trim();
                        console.log("generated content String", generatedContentString);
                        const generatedContent = JSON.parse(generatedContentString);
                        console.log('Generated content:', generatedContent);
                        appendGeneratedContent(generatedContent);
                        $('#shuffle-button').click();
                        // Display or process the generated content as needed
                        // For now, we'll log it to the console
                        postStatus = $('#original_post_status').val();
                        postId =  $('#post_ID').val();

                        $.fn.updatePostAsDraft(postId, postStatus);
                        crossword.updateHiddenFields();
                        saveCrosswordAjaxNew();

                        $('.kw-loading').hide();
                        Swal.fire(wpQuizPlugin.strings.successTitle,wpQuizPlugin.strings.successMessage, 'success');
                        $.fn.highlightPublishButton();

                    } catch (error) {
                        console.error('Error parsing response:', error);
                        Swal.fire(wpQuizPlugin.strings.errorTitle, wpQuizPlugin.strings.errorMessage, 'error');
                    }
                    $(generateButtonId).text(wpQuizPlugin.generateWithAiText).prop('disabled', false);
                },
                error: function (xhr, status, error) {
                    console.error('API request error:', xhr.responseText);
                    if (retryCount > 0) {
                        console.log(`Retrying request... Attempts left: ${retryCount}`);
                        setTimeout(() => sendRequest(retryCount - 1, count), 2000); // Retry after 2 seconds
                    } else {
                        let errorMsg = 'Failed to generate question. ';
                        errorMsg += xhr.responseJSON?.error?.message || 'Error: ' + error;
                        Swal.fire(wpQuizPlugin.strings.errorTitle, errorMsg, 'error');
                        $(generateButtonId).text(wpQuizPlugin.generateWithAiText).prop('disabled', false);
                    }
                }
            });
        }

        // Start the request with a retry count of 3
        sendRequest(3, number);
    });


    function saveCrosswordAjaxNew() {
        // 1. Update hidden fields so the latest grid data is saved
        if (typeof crossword !== 'undefined' && typeof crossword.updateHiddenFields === 'function') {
            crossword.updateHiddenFields();
        }
    
        console.log("in method");
        // 2. Get the crossword grid data from the hidden input field
        var crossword_data = $('#crossword-data').val();
        
        

        console.log("crossword_Datat", crossword_data);
    
        // 3. Build the clue/word data from the clues container
        var clue_word_data = [];
        $('#crossword-words-clues-container .crossword-word-clue').each(function() {
            var $clueDiv = $(this);
            var word = $clueDiv.find('input[name*="[word]"]').val();
            var clue = $clueDiv.find('input[name*="[clue]"]').val();
            var uniqueId = $clueDiv.find('input[name*="[uniqueId]"]').val();
            var image = $clueDiv.find('input.crossword-image-url').val() || "";
    
            if (word && $.trim(word) !== "") {
                clue_word_data.push({
                    uniqueId: uniqueId,
                    word: word,
                    clue: clue,
                    image: image
                });
            }
        });
    
        console.log("clure data", clue_word_data);
        // 4. Send the data via AJAX
        $.ajax({
            url: crosswordScriptVar.ajaxUrl, // Use localized URL
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'save_crossword_ajax_new', // New AJAX action
                security: crosswordScriptVar.nonce, // Include nonce from localized script
                post_id: $('#post_ID').val(),
                crossword_data: crossword_data, // Correct key name now
                clue_word_data: clue_word_data
            },
            success: function(response) {
                if (response.success) {
                    console.log("Crossword saved successfully via AJAX.");
                } else {
                    console.error("Error saving crossword:", response.data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", errorThrown);
            }
        });
    }
});