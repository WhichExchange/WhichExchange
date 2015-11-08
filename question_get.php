<?php
require_once 'Question.php'; //fugg
$q = new Question();
$sites = array('stackoverflow', 'ux', 'unix', 'askubuntu', 'softwarerecs', 'programmers');
$q->add_sites($sites);
$q->send_question();
print_r(json_encode($q));
