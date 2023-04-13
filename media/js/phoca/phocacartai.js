/*
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */


function phEscapeRegExp(string) {
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1");
}

function phReplaceAll(find, replace, string) {
    return string.replace(new RegExp(phEscapeRegExp(find), "g"), replace);
}

function startFullOverlay() {

    const phOverlay = jQuery('<div id="phOverlay"><div id="phLoaderFull"> </div></div>');
    phOverlay.appendTo(document.body);
    
    //document.getElementById("phOverlay").style.display = 'block';
    jQuery("#phOverlay").fadeIn().css("display","block");
}

function stopOverlay() {
	//document.getElementById("phOverlay").style.display = 'none';
    jQuery("#phOverlay").fadeIn().css("display","none");
}

function phAiCreateQuestion() {

    const phParams 	= Joomla.getOptions('phParamsPC');

    let fields      = ['description', 'description_long', 'features', 'metadesc'];
    let aiKeywords  = document.getElementById('jform_aidata_ai_keywords');

    fields.forEach((field) => {
        
        let keyQuestion = 'ai_premade_question_' + field; 
        //let keyQuestionSuffix = 'aiPremadeQuestionSuffix' + pHapitalizeFirstLetter(field); 

        let question = '';
        //let questionSuffix = '';

        if (phParams[keyQuestion]) {
            question            = phParams[keyQuestion];
        }

        /*if (phParams[keyQuestionSuffix]){
            questionSuffix      = phParams[keyQuestionSuffix];
        }*/


        if (question && aiKeywords.value.trim() !== '') {
            question = question + ': ' + aiKeywords.value.trim();
        }

        /*if (question && questionSuffix && questionSuffix.trim() !== '') {
            question = question + '. ' + questionSuffix + '.';
        } else */ if (question) {
            question = question + '.';
        }

        document.getElementById('ai_question_' + field).value = question;


    });



    /*let question    = phParams['aiPremadeQuestionDescription'];
    let questionSuffix    = phParams['aiPremadeQuestionSuffixDescription'];

    let aiKeywords =  document.getElementById ('jform_aidata_ai_keywords');


    if (aiKeywords.value.trim() !== '') {
       question = question + ': ' + aiKeywords.value.trim();
    }

    if (questionSuffix.trim() !== '') {
        question = question + '. ' + questionSuffix + '.';
     } else {
        question = question + '.';
     }

     document.getElementById('ai_question_description').value = question;*/

}

function phAiGenerateAnswer(typeId) {

    

    const paramName = 'ai_parameters_' + typeId;
    const questionId = 'ai_question_' + typeId;
    const targetId = 'ai_content_' + typeId;
    const messageId = 'ai_message_' + typeId;

    const phParams 	    = Joomla.getOptions('phParamsPC');
    const phLang 	    = Joomla.getOptions('phLangPC');
    const apiKey        = phParams['aiApiKey'];
    const model         = phParams['aiModel'];
    const parameters    = phParams[paramName];
    const apiUrl        = 'https://api.openai.com/v1/completions';

    const prompt = document.getElementById(questionId).value;

    // Default params if not set
    let dataParams = {max_tokens: 100, temperature: 0.5};

    if (typeof parameters !== 'undefined' && parameters != '') {
        dataParams = JSON.parse(parameters);
    } 

    const data = {
        prompt: prompt,
        model: model
    };

    Object.assign(data, data, dataParams);

    if (apiKey == '') {
        document.getElementById(messageId).innerHTML = '<div class="ph-msg-error-box">' + phLang['COM_PHOCACART_ERROR_YOU_DID_NOT_PROVIDE_API_KEY'] + '</div>';
        return false;
    }
    startFullOverlay();

    // Make request to OpenAI API
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${apiKey}`
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (typeof data.error !== 'undefined') {
            if (typeof data.error.message !== 'undefined' && data.error.message != '') {
                document.getElementById(messageId).innerHTML = '<div class="ph-msg-error-box">' + data.error.message + '</div>';
            } else {
                console.log(data.error);
            }
            
        }

        if (typeof data.choices[0].text !== 'undefined' && data.choices[0].text != '') {
            let answer = data.choices[0].text;
            answer = answer.trim();
            document.getElementById(targetId).value = answer;
        }
        stopOverlay();
            
    })
    .catch(error => {
        console.log(error);
        //document.getElementById(messageId).innerHTML = '<div class="ph-msg-error-box">' + error + '</div>';
        stopOverlay();
    });
    
    

}

function phAiPasteAnswer(typeId) {

    const phLang 	    = Joomla.getOptions('phLangPC');

    const messageId = 'ai_message_' + typeId;
    const sourceId = 'ai_content_' + typeId;
    const targetId = 'jform_' + typeId;
    let sourceContent = document.getElementById(sourceId).value;

    sourceContent = phReplaceAll("\n", '<br>', sourceContent);

    // Is input field editor form field?
    let inputFieldEditor = 0;
    if (typeof Joomla.editors.instances[targetId] !== 'undefined') {
        inputFieldEditor = 1;
    }

    let currentContent = '';
    if (inputFieldEditor == 1) {
        currentContent = Joomla.editors.instances[targetId].getValue();
    } else {
        currentContent = document.getElementById(targetId).value;
    }
    

    if (currentContent != '' || currentContent == '<p></p>') {
        if (confirm(phLang['COM_PHOCACART_DESTINATION_FIELD_NOT_EMPTY_PRESS_OK_TO_OVERWRITE_CONTENTS_IN_DESTINATION_FIELD']) == true) {

            if (inputFieldEditor == 1) {
                Joomla.editors.instances[targetId].setValue(sourceContent);
            } else {
                document.getElementById(targetId).value = sourceContent;
            }
            document.getElementById(messageId).innerHTML = '<div class="ph-msg-success-box">' + phLang['COM_PHOCACART_SUCCESS_CONTENT_INSERTED'] + '</div>';
          } 
    } else {
        if (inputFieldEditor == 1) {
            Joomla.editors.instances[targetId].setValue(sourceContent);
        } else {
            document.getElementById(targetId).value = sourceContent;
        }
        document.getElementById(messageId).innerHTML = '<div class="ph-msg-success-box">' + phLang['COM_PHOCACART_SUCCESS_CONTENT_INSERTED'] + '</div>';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    
    // Build the question from Ai Keywords parameter at start
    phAiCreateQuestion();

    // Change it when typing
    let aiKeywords =  document.getElementById ('jform_aidata_ai_keywords');
    aiKeywords.addEventListener("keyup", phAiCreateQuestion, false);

    let aiQuestionBox = document.getElementById ('phAiQuestionBox');

    // Add listeners to all buttons - generate action
    let aiQuestionBoxBtnsGenerate = aiQuestionBox.querySelectorAll('.phBtnGenerate');
    aiQuestionBoxBtnsGenerate.forEach(btn => {
        btn.addEventListener('click', event => {
            phAiGenerateAnswer(event.target.dataset.typeid);
        });
     });

     // Add listeners to all buttons - paste action
     let aiQuestionBoxBtnsPaste = aiQuestionBox.querySelectorAll('.phBtnPaste');
     aiQuestionBoxBtnsPaste.forEach(btn => {
        btn.addEventListener('click', event => {
            phAiPasteAnswer( event.target.dataset.typeid);
        });
     });
}, false);
