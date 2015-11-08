<?php
require_once 'Question.php'; //fugg
$q = new Question();
$sites = array(
    'stackoverflow',
    'ux',
    'unix',
    'askubuntu',
    'softwarerecs',
    'programmers',
    'serverfault',
    'superuser'
);
$q->addSites($sites);
$q->sendQuestion();
echo htmlspecialchars_decode(json_encode($q), ENT_QUOTES);
