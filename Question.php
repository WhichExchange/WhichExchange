<?php

class Question
{
    private $question_sent = false;
    private $sites = array();
    private $url_form = "https://api.stackexchange.com/2.2/questions?";
    public $has_key = false;
    public $sort = 'votes';
    public $order = 'desc';
    public $site = null;
    public $q = null;
    public $question_choices = null;
    public $quota = null;

    public $timeIntervals;

    //@Override
    public function __construct()
    {
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

        session_start();
        if (isset($_SESSION['time'])) {
            $fromDate = $this->timeIntervals[$_SESSION['time']];
        } else {
            $fromDate = $this->timeIntervals['oneMonth'];
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

        $url = $this->url_form . "order=$order&sort=$sort&site=$rand_site&fromDate=$fromDate";

        if (is_file('keys.json')) {
            $contents = json_decode(file_get_contents('keys.json'));
            if ($contents && array_key_exists('stackexchange', $contents)) {
                $key = $contents->stackexchange;
                $url .= "&key=$key";
                $this->has_key = true;
            }
        }

        // @TODO file_get_contents($uri);
        $http_request = shell_exec("curl --compressed -s \"$url\"");
        $request_as_array = json_decode($http_request, true);
        $request_items = $request_as_array['items'];

        $attempts = 0;
        do {
            $question_rand_index = array_rand($request_items);
            $question_rand = $request_items[$question_rand_index];
            $attempts++;

            $question_rand['title'] = htmlspecialchars_decode($question_rand['title']);

        } while (strpos('"', $question_rand['title']) !== false && $attempts < 50);

        $this->q = $question_rand;
        $this->quota = $request_as_array['quota_remaining'];

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
