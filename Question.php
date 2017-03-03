<?php

require_once 'Cache.php';

class Question
{
    private $question_sent = false;
    private $sites = array();
    private $url_form = "https://api.stackexchange.com/2.2/questions";
    public $has_key = false;
    public $sort = 'votes';
    public $order = 'desc';
    public $site = null;
    public $q = null;
    public $question_choices = null;
    public $quota = null;
    public $last_question_cached = false;

    public $timeIntervals;

    //@Override
    public function __construct()
    {
        date_default_timezone_set('UTC');
        $this->timeIntervals = array(
            'oneMonth' => strtotime('-30 days'),
            'sixMonths' => strtotime('-180 days'),
            'allTime' => 0
        );
    }

    public function addSite($site)
    {
        if ($this->question_sent === true) {
            throw new \Exception('Question already sent; sites may not be edited');
        }
        if (!is_string($site)) {
            throw new \InvalidArgumentException();
        }
        array_push($this->sites, $site);
    }

    public function clearSites()
    {
        if ($this->question_sent === true) {
            throw new \Exception('Question already sent; sites may not be edited');
        }
        $this->sites = array();
    }

    public function addSites($site_list)
    {
        if ($this->question_sent === true) {
            throw new \Exception('Question already sent; sites may not be edited');
        }
        if (!is_array($site_list)) {
            throw new \InvalidArgumentException();
        }
        foreach ($site_list as $site) {
            $this->addSite($site);
        }
    }

    public function sendQuestion()
    {
        $sort = $this->sort;
        $order = $this->order;
        $this->last_question_cached = false;

        session_start();
        if (isset($_SESSION['time'])) {
            $fromDate = $this->timeIntervals[$_SESSION['time']];
        } else {
            $fromDate = $this->timeIntervals['allTime'];
        }

        if ($this->question_sent === true) {
            throw new \Exception('already sent question');
        }
        if (count($this->sites) < 4) {
            throw new \Exception('Need 4 or more sites to draw from');
        }
        if (!is_string($sort)) {
            throw new \Exception("bad sort");
        }
        if (!is_string($order)) {
            throw new \Exception("bad order");
        }
        $this->question_sent = true;

        $rand_site_index = array_rand($this->sites);
        $rand_site = $this->sites[$rand_site_index];
        $this->site = $rand_site;

        // Notably, does not include key (yet)
        $url_get_parameters_string = "?order=$order&sort=$sort&site=$rand_site&fromDate=$fromDate";

        // Cache without (before) the key is considered/needed
        $cache = new Cache();
        $in_cache = $cache->request_in_cache_fresh($url_get_parameters_string);
        error_log("Request '$url_get_parameters_string' is in cache: " . ($in_cache?"True":"False"));

        if ($in_cache) {
            $request_as_array = $cache->cache_get($url_get_parameters_string);
            $request_items = $request_as_array['items'];
            // so the 'no key' message doesn't show up
            $this->has_key = true;
            $this->last_question_cached = true;
            
        } else {
            // Request not in cache.
            
            $key_get_param = "";

            // use key if available
            if (is_file('keys.json')) {
                $contents = json_decode(file_get_contents('keys.json'));
                if ($contents && array_key_exists('stackexchange', $contents)) {
                    $key = $contents->stackexchange;
                    $key_get_param = "&key=$key";
                    $this->has_key = true;
                }
            }

            if (!$this->has_key) {
                error_log("No key provided. API quota will be limited.");
            }

            $url = $this->url_form . $url_get_parameters_string . $key_get_param;

            // @TODO file_get_contents($uri);
            $http_request = shell_exec("curl --compressed -s \"$url\"");
            $request_as_array = json_decode($http_request, true);
            $request_items = $request_as_array['items'];
            $this->quota = $request_as_array['quota_remaining'];

            // If the quota is lower than you expect, double check that your API key is being used correctly
            error_log("API quota remaining: " . $this->quota);

            $cache->cache_set($url_get_parameters_string, $request_as_array);
        }

        $attempts = 0;
        do {
            $question_rand_index = array_rand($request_items);
            $question_rand = $request_items[$question_rand_index];
            $attempts++;

            $question_rand['title'] = htmlspecialchars_decode($question_rand['title']);

        } while (strpos('"', $question_rand['title']) !== false && $attempts < 50);

        $this->q = $question_rand;

        $this->getQuestionChoices();

    }

    public function getQuestionChoices()
    {
        if ($this->question_sent !== true) {
            throw new \Exception('Question not sent yet');
        }
        if (count($this->sites) < 4) {
            throw new \Exception('not enough sites');
        }
        $rand_indices = array();
        while (count($rand_indices) < 4) {
            $ind = array_rand($this->sites);
            if (in_array($ind, $rand_indices)) {
                continue;
            }
            array_push($rand_indices, $ind);
        }
        for ($i = 0; $i<count($rand_indices); $i++) {
            $rand_indices[$i] = $this->sites[$rand_indices[$i]];
        }
        // Confirm that the answer is actually there (lmao)
        if (!in_array($this->site, $rand_indices)) {
            $put_index = array_rand($rand_indices);
            $rand_indices[$put_index] = $this->site;
        }
        $this->question_choices = $rand_indices;

    }
}

