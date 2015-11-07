<?php

$sites = array('stackoverflow', 'ux', 'unix', 'askubuntu', 'softwarerecs', 'programmers');
$url_form = "https://api.stackexchange.com/2.2/questions?order=desc&sort=creation&site=";
$site_rand_index = array_rand($sites);
$site_rand = $sites[$site_rand_index];

$url = $url_form . $site_rand;

$http_request = shell_exec("curl --compressed -s \"$url\"");
//print_r($http_request);
$request_as_array = json_decode($http_request, true);
$request_items = $request_as_array['items'];

$question_rand_index = array_rand($request_items);
$question_rand = $request_items[$question_rand_index];
$question_rand_title = $question_rand['title'];

print_r("Title: $question_rand_title\n");
print_r("Site (SPOILERS): $site_rand\n");

