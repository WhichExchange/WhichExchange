
var currentQuestion;
var correctButton;
var failures = 0;
var chrisIsTheWeirdOne = false;

$(function () {
    loadQuestion();
    $('.next-question').click(function() {
        for (var i = 0; i < 4; i++) {
            var button = $('.btn' + i);
            button.animate({ backgroundColor: '#FFFFFF' }, 'fast');
        }
        $('.question-container').fadeOut('normal', loadQuestion);
    });

    $('.settings').popover({
        content: generateSettingsPopover(),
        html: true,
        placement: 'right',
        trigger: 'focus'
    }).focus(function () {
        $('#out').select().one('mouseup', function (e) {
             e.preventDefault();
         });
    });
    $('[data-toggle="popover"]').popover();

    $('.view-on-se').click(function() {
        $(this).blur();
    })
});

function loadQuestion() {
    chrisIsTheWeirdOne = false;
    $('.skittles').hide();
    $.ajax(
        'question_get.php',
        {
            method: 'GET',
            success: function(data) {

                failures = 0;

                currentQuestion = JSON.parse(data);

                if (currentQuestion.last_question_cached == true) {
                    // If cache was used, don't report info about quota limit.
                    $('p#quota-info').hide().text('');
                } else {
                    if (currentQuestion.quota < 1) {
                        $('p#quota-info').show().text('Question could not be retrieved. API quota limit exceeded.');
                    }
                    /*
                    if (currentQuestion.has_key == false && currentQuestion.quota > 1) {
                        $('p#quota-info').text('Question could not be retrieved. API quota limit exceeded.');
                        //Quota limit remaining: ' + currentQuestion.quota + ' (NO KEY PROVIDED)');
                    } else {
                        $('p#quota-info').text('Quota limit remaining: ' + currentQuestion.quota);
                    }
                    */
                }


                $('.question h4').text(currentQuestion.q.title);

                for (var i = 0; i < 4; i++) {
                    var button = $('.btn' + i);
                    button.stop().css("background-color","#FFFFFF");
                    button
                        .text(currentQuestion.question_choices[i])
                        .click(questionClicked);

                    if (currentQuestion.question_choices[i] === currentQuestion.site) {
                        correctButton = button;
                    }
                }

                $('.question-container').fadeIn(800);
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

function generateSettingsPopover() {
    return '<div id="timewindowtextdiv">Time Window:</div>' +
        '<a href="javascript:void(0)" onclick="allTime()" class="btn btn-default btn-time btn-alltime">All Time</a>' +
        '<a href="javascript:void(0)" onclick="sixMonths()" class="btn btn-default btn-time btn-six">6 Months</a>' +
        '<a href="javascript:void(0)" onclick="oneMonth()" class="btn btn-default btn-time btn-one">1 Month</a>';
}

function questionClicked() {
    if (!chrisIsTheWeirdOne) {
        chrisIsTheWeirdOne = true;
        $(this).blur();
        $('.skittles').fadeIn();
        $('a.view-on-se').attr('href', currentQuestion.q.link);
        var text = $(this).text();
        if (text === currentQuestion.site) {
            $(this).stop().animate({ backgroundColor: '#82FFAC' }, 'fast', 'linear');
        } else {
            $(this).stop().animate({ backgroundColor: '#FF9999' }, 'fast', 'linear');
            correctButton.stop().animate({ backgroundColor: '#82FFAC' }, 'fast', 'linear')
        }
    }
}

function oneMonth() {
    setTimeInterval('oneMonth');
}

function sixMonths() {
    setTimeInterval('sixMonths');
}

function allTime() {
    setTimeInterval('allTime');
}

function setTimeInterval(timeInterval) {
    $.ajax(
        'time.php',
        {
            method: 'POST',
            data: {
                time: timeInterval
            },
            success: function() {
                $('.question-container').fadeOut('normal', loadQuestion);
            }

        }
    );
}
