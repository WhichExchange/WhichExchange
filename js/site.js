
var currentQuestion;
var correctButton;
var failures = 0;

$(function () {
    loadQuestion();
    $('.next-question a').click(function() {
        $('.question-container').fadeOut(null, loadQuestion);
    });
});

function loadQuestion() {
    $('.next-question a').hide();
    $.ajax(
        'question_get.php',
        {
            method: 'GET',
            success: function(data) {
                failures = 0;

                currentQuestion = JSON.parse(data);

                $('p#quota-info').text('Quota limit remaining: ' + currentQuestion.quota);

                $('.question h4').text(currentQuestion.q.title);
                for (var i = 0; i < 4; i++) {
                    var button = $('.btn' + i);
                    button.animate({ backgroundColor: '#FFFFFF' }, 'fast', 'linear');
                    button
                        .text(currentQuestion.question_choices[i])
                        .click(questionClicked);

                    if (currentQuestion.question_choices[i] === currentQuestion.site) {
                        correctButton = button;
                    }
                }

                $('.question-container').fadeIn();
            },
            error: function() {
                // try again
                failures++;
                if (failures > 10) {
                    // something is terribly wrong, just let it fail
                    return
                }
                setTimeout(loadQuestion, 0);
            }
        }
    );
}

function questionClicked() {
    $('.next-question a').show();
    var text = $(this).text();
    if (text === currentQuestion.site) {
        $(this).animate({ backgroundColor: '#82FFAC' }, 'fast', 'linear');
    } else {
        $(this).animate({ backgroundColor: '#FF9999' }, 'fast', 'linear');
        correctButton.animate({ backgroundColor: '#82FFAC' }, 'fast', 'linear')
    }
}