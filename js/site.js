
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
        placement: 'left',
        trigger: 'focus'
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

                if (currentQuestion.has_key == false) {
                    $('p#quota-info').text('Quota limit remaining: ' + currentQuestion.quota + ' (NO KEY PROVIDED)');
                } else {
                    $('p#quota-info').text('Quota limit remaining: ' + currentQuestion.quota);
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
    return '<div>Time window:</div><br/>' +
        '<a href="javascript:void(0)" onclick="oneMonth()" class="btn btn-default btn-time btn-one">1 month</a><br/>' +
        '<a href="javascript:void(0)" onclick="sixMonths()" class="btn btn-default btn-time btn-six">6 Months</a><br/>' +
        '<a href="javascript:void(0)" onclick="allTime()" class="btn btn-default btn-time btn-alltime">All Time</a>';
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
