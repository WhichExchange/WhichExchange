<?php

require_once 'Keys.php';

class Question
{
    private $question_sent = false;
    private $sites = array();
    private $url_form = "https://api.stackexchange.com/2.2/questions?";
    public $sort = 'creation';
    public $order = 'desc';
    public $site = null;
    public $q = null;
    public $question_choices = null;

    //@Override
    public function __construct()
    {
        
    }

    public function add_site($site)
    {
        if ($this->question_sent === true) {
            throw new \Exception('Question already sent; sites may not be edited');
        }
        if (!is_string($site)) {
            throw new \InvalidArgumentException();
        }
        array_push($this->sites, $site);
    }

    public function clear_sites()
    {
        if ($this->question_sent === true) {
            throw new \Exception('Question already sent; sites may not be edited');
        }
        $this->sites = array();
    }

    public function add_sites($site_list)
    {
        if ($this->question_sent === true) {
            throw new \Exception('Question already sent; sites may not be edited');
        }
        if (!is_array($site_list)) {
            throw new \InvalidArgumentException();
        }
        foreach($site_list as $site) {
            $this->add_site($site);
        }
    }

    public function send_question()
    {
        $sort = $this->sort;
        $order = $this->order;
        $key = Keys::$stackOverflowKey;
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

        $url = $this->url_form . "order=$order&sort=$sort&site=$rand_site&key=$key";
        // @TODO file_get_contents($uri);
        $http_request = shell_exec("curl --compressed -s \"$url\"");
        $request_as_array = json_decode($http_request, true);
        $request_items = $request_as_array['items'];

        $question_rand_index = array_rand($request_items);
        $question_rand = $request_items[$question_rand_index];
        $this->q = $question_rand;

        $this->get_question_choices();
    }

    public function get_question_choices()
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

