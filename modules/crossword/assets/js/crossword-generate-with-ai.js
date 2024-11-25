jQuery(document).ready(function ($) {


/**
 * Generates the prompt content for the OpenAI API request.
 * 
 * @param {number} number - The number of words to generate.
 * @param {string} topic - The topic for the crossword.
 * @param {number} age - The age group of the user.
 * @param {string} language - The language for the crossword.
 * @returns {string} - The complete prompt content.
 */
function generatePrompt(number, topic, age, language) {
    // Retrieve existing words in the crossword to avoid duplicates
    const existingWords = getCrosswordWordsList();
    
    // Context prompt to avoid duplicate words
    const context = existingWords.length > 0 
        ? `Avoid using the following words: ${existingWords.join(', ')}.`
        : '';

    // Main prompt to generate crossword words and clues
    const generationPrompt = `
    Generate a crossword with ${number} words on the topic "${topic}" suitable for users aged ${age}. 
    The crossword should be created in the "${language}" language.`;

    // Specify the response format explicitly
    const returnFormatPrompt = `
    Provide the output in the following JSON array format, with no additional text:
    
[
{ "word": "exampleWord1", "clue": "Example clue for word 1" },
{ "word": "exampleWord2", "clue": "Example clue for word 2" },
...
]`;

    // Combine all parts into the final prompt
    return `${generationPrompt} ${context} ${returnFormatPrompt}`;
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