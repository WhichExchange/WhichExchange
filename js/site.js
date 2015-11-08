
var currentQuestion;
var correctButton;
var failures = 0;

$(function () {
    loadQuestion();
    $('.next-question').click(function() {
        $('.question-container').fadeOut(null, loadQuestion);
    });
});

function loadQuestion() {
    $('.skittles').fadeOut();
    $.ajax(
        'question_get.php',
        {
            method: 'GET',
            success: function(data) {
                failures = 0;

                console.log(data);

                currentQuestion = JSON.parse(data);

                $('p#quota-info').text('Quota limit remaining: ' + currentQuestion.quota);

                $('.question h4').text(currentQuestion.q.title);
                for (var i = 0; i < 4; i++) {
                    var button = $('.btn' + i);
                    button.stop().animate({ backgroundColor: '#FFFFFF' }, 'fast', 'linear');
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
    $('.skittles').fadeIn();
    $('a.view-on-se').attr('href', currentQuestion.q.link);
    console.log('link: ' + currentQuestion.q.link);
    var text = $(this).text();
    if (text === currentQuestion.site) {
        $(this).animate({ backgroundColor: '#82FFAC' }, 'fast', 'linear');
    } else {
        $(this).animate({ backgroundColor: '#FF9999' }, 'fast', 'linear');
        correctButton.animate({ backgroundColor: '#82FFAC' }, 'fast', 'linear')
    }
}