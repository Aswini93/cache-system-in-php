<?php
/* 
@dated 19-02-2023
@desc Created a class that retrieves data from a remote REST API and caches the results.
*/

class RemoteAPI {

    private $url;
    private $cache_file;
    private $cache_time;
    private $cache_data;

    //constructor method that takes the URL of the REST API as an argument.
    public function __construct($url, $cache_time = 300) {
        $this->url = $url;
        $this->cache_file = 'cache'. '/remote_api_cache_' . md5($url);
        $this->cache_time = $cache_time;
        $this->cache_data = false;
    }
    //public method called "getData" that retrieves the data from the API
    public function getData() {
        /* 
            1. The "getData" method should first check if the data is already cached. If it is, return the cached data. If not,retrieve the data from the API, cache it, and return it.
            2. The cached data should be stored for a configurable for 5 minutes before being invalidated and re-fetched from the API.
        */
        if ($this->cache_data && (time() - filemtime($this->cache_file)) < $this->cache_time) {
            return $this->cache_data;
        }

        // Retrieve data from API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // Handle errors
        if (!$response) {
            throw new Exception('Failed to retrieve data from API');
        }
        $data = json_decode($response, true);
        if (!$data) {
            throw new Exception('Invalid data returned from API');
        }

        // Cache data
        file_put_contents($this->cache_file, $response);
        $this->cache_data = $data;

        return $data;
    }

}
/* Used sample json from https://dummyjson.com */
$api = new RemoteAPI('https://dummyjson.com/products');
$data = $api->getData();
print_r($data);
?>