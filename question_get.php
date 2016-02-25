<?php
require_once 'Question.php'; //fugg
$q = new Question();
$sites = json_decode(file_get_contents('sites.json'));
$q->addSites($sites->sites);
$q->sendQuestion();
echo htmlspecialchars_decode(json_encode($q), ENT_QUOTES);
