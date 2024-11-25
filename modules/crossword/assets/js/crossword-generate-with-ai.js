jQuery(document).ready(function ($) {


/**
 * Replaces placeholders in a string using an object of key-value pairs.
 *
 * @param {string} template - The string template containing placeholders (e.g., [key]).
 * @param {object} variables - An object with key-value pairs for replacement.
 * @returns {string} - The template with placeholders replaced by actual values.
 */
function replacePlaceholders(template, variables) {
    Object.keys(variables).forEach((key) => {
        const placeholder = `[${key}]`; // Match the placeholder format [key]
        template = template.replace(placeholder, variables[key]);
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
    const contextPromptTemplate = wpQuizPlugin.defaultContextPrompt;
    const generationPromptTemplate = wpQuizPlugin.defaultGenerationPrompt;
    const returnFormatPrompt = wpQuizPlugin.defaultReturnFormatPrompt;

    // Replace [existing_words] in context prompt
    const existingWords = getCrosswordWordsList();
    const contextPrompt = replacePlaceholders(contextPromptTemplate, {
        existing_words: existingWords.join(', '),
    });

    // Replace [number], [topic], [age], and [language] in generation prompt
    const generationPrompt = replacePlaceholders(generationPromptTemplate, {
        number: number,
        topic: topic,
        age: age,
        language: language,
    });

    // Combine prompts into the final prompt
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
            const entryHtml = template
                .replace(/{{index}}/g, newIndex)
                .replace(/{{number}}/g, newIndex + 1)
                .replace('value=""', `value="${item.word}"`)  // Set word
                .replace('value=""', `value="${item.clue}"`); // Set clue
    
            $container.append(entryHtml);
        });
    }

    // Unique ID for the button
    const generateButtonId = '#generate-ai-button';

    // Click event handler for the Generate with AI button
    $(generateButtonId).on('click', function () {
        const topic = $('#ai-topic').val().trim();
        const age = $('#ai-age').val().trim();
        const number = $('#ai-questions').val().trim();
        const language = $('#ai-language').val().trim();

        // Check if the number is between 1 and 10
        if (number < 1 || number > 10) {
            Swal.fire('Error', 'The number must be between 1 and 10.', 'warning');
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
                    console.log('Sending request to OpenAI...', data);
                    $(generateButtonId).text(wpQuizPlugin.generatingText).prop('disabled', true);
                },
                success: function (response) {
                    console.log('Received response:', response);
                    try {
                        const generatedContentString = response.choices[0].message.content.trim();
                        const generatedContent = JSON.parse(generatedContentString);
                        console.log('Generated content:', generatedContent);
                        appendGeneratedContent(generatedContent);
                        $('#shuffle-button').click();
                        // Display or process the generated content as needed
                        // For now, we'll log it to the console
                        Swal.fire('Generated Content', generatedContent, 'success');

                    } catch (error) {
                        console.error('Error parsing response:', error);
                        Swal.fire('Error', 'Could not parse the response. Ensure the AI response follows the expected format.', 'error');
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
                        Swal.fire('Error', errorMsg, 'error');
                        $(generateButtonId).text(wpQuizPlugin.generateWithAiText).prop('disabled', false);
                    }
                }
            });
        }

        // Start the request with a retry count of 3
        sendRequest(3, number);
    });
});