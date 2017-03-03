<?php

class Cache
{
    private $cache_dir = "cache";
    private $cache_file = "question_cache.json";
    private $cache_size_max = 10485760; // 10 MiB
    private $cache_entry_duration = '+1 day';

    private function path_to_cache_file() {
        return "$this->cache_dir/$this->cache_file";
    }

    private $cache_contents_json = null;

    /**
     * Create the file if it didn't already exist
     */
    private function ready_file()
    {

        if (!is_dir($this->cache_dir)) {
            error_log("Cache directory did not exist, creating.");
            mkdir($this->cache_dir);
        } else if (!is_readable($this->cache_dir)) {
            error_log("Cache dir exists but is not readable");
            return False;
        }

        if (!is_file($this->path_to_cache_file())) {
            touch($this->path_to_cache_file());
            error_log("Cache file did not exist, creating.");
            // init empty json since the file didn't already exist
            file_put_contents($this->path_to_cache_file(), json_encode(array()));
        } else if (!is_readable($this->path_to_cache_file()) ||
                !is_writable($this->path_to_cache_file())) {
            error_log("Cache file exists but is not r/w");
            return False;
        } else {
            if (filesize($this->path_to_cache_file()) > $this->cache_size_max) {
                error_log("Cache size exceeded max, wiping cache");
                // cache size too big, just wipe the whole thing rather than cleaning
                file_put_contents($this->path_to_cache_file(), json_encode(array()));
            }
            if (null === json_decode(file_get_contents($this->path_to_cache_file()))) {
                // something was weird with the cache file, just reset it
                error_log("Cache file contents corrupted, wiping cache");
                file_put_contents($this->path_to_cache_file(), json_encode(array()));
            }
        }

        return True;
    }

    public function __construct()
    {
        $this->ready_file();
        $this->cache_read();
        //error_log("cache contents at object creation:\n" . print_r($this->cache_contents_json, True));
    }

    public function cache_set($request, $response)
    {
        if ($this->cache_contents_json === null) {
            error_log("bad cache contents, try intializing first");
            return False;
        }

        $cache_entry_time = time();

        // simple cache entry struct consisting of response and time
        $cache_entry = array(
            "entry_time" => $cache_entry_time,
            "response" => $response
        );

        // add a new entry or update the entry if it already existed
        $this->cache_contents_json[$request] = $cache_entry;
        $this->cache_write();
    }

    public function cache_read()
    {
        // as array
        $this->cache_contents_json = json_decode(file_get_contents($this->path_to_cache_file()), true);
    }

    public function cache_write()
    {
        file_put_contents(
            $this->path_to_cache_file(),
            json_encode($this->cache_contents_json)
        );
    }

    public function request_in_cache_fresh($request)
    {
        if (!$this->request_in_cache($request)) {
            return false;
        }
        $entry = $this->cache_contents_json[$request];
        error_log('Time cached:     ' . $entry['entry_time']);
        error_log('Expiration time: ' . strtotime($this->cache_entry_duration, $entry['entry_time']));
        error_log('Current time:    ' . time());
        if (strtotime($this->cache_entry_duration, $entry['entry_time']) < time()) {
            error_log('Request in cache but expired.');
            return false;
        }
        return true;
    }

    // TODO cache freshness (i.e. expires after N days)
    private function request_in_cache($request)
    {
        /*
        foreach ( array_keys($this->cache_contents_json) as &$k) {
            error_log("$k");
        }
         */
        return array_key_exists($request, $this->cache_contents_json);
    }

    public function cache_get($request)
    {
        if ($this->request_in_cache($request)) {
            return $this->cache_contents_json[$request]["response"];
        }
        return null;
    }

    public function cache_wipe()
    {
        if ($this->ready_file()) {
            file_put_contents($this->path_to_cache_file(), json_encode(array()));
        }
    }

}

