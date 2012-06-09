<?php

/**
* Inspired by http://gitorious.org/twitter-api-test-suite
*
* @author     Dac Chartrand <dac.chartrand@gmail.com>
* @license    http://www.gnu.org/licenses/gpl-3.0.txt
*/


abstract class RestAbstract
{

    /**
    * @var object cURL handle
    */
    public $curl;

    /**
    * @var string Placeholder for 'GET', 'POST', 'PUT', 'DELETE'
    */
    public $method;

    /**
    * @var string Returned headers
    */
    public $headers;

    /**
    * @var string Returned body
    */
    public $body;

    /**
    * @var bool
    */
    public $debug = false;

    // ------------------------------------------------------------------------
    // Public
    // ------------------------------------------------------------------------

    /**
    * Initialize. This method must be called before all others
    *
    * @param string $url
    * @param string $method 'GET', 'PUT', 'POST', or 'DELETE'
    * @param string|array $data
    */
    public function init($url, $method, $data = null)
    {
        if (isset($this->curl)) {
            curl_close($this->curl);
            unset($this->curl);
        }
        unset($this->method, $this->headers, $this->body, $this->xml, $this->json);

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_URL, $url);

        // Debug
        if ($GLOBALS['CONFIG']['DEBUG']) {
            curl_setopt($this->curl, CURLOPT_VERBOSE, 1);
            $this->debug = true;
        }

        // Method
        if ('GET' == strtoupper($method)) {
            $this->initGET();
        }
        elseif ('POST' == strtoupper($method)) {
            $this->initPOST($data);
        }
        else {
            throw new Exception('Unsuported method. Use GET or POST.');
        }

    }


    /**
    * Set CURLOPT_HTTPHEADER
    *
    * @param string $headers
    */
    public function setHeaders($headers)
    {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
    }


    /**
    * Set Curl SSH hacks
    *
    */
    public function setSSH()
    {

        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);
    }


    /**
    * Set CURLOPT_PORT
    *
    */
    public function setPort($port)
    {
        curl_setopt($this->curl, CURLOPT_PORT, $port);
    }


    /**
    * Perform a cURL session, store returned headers and body, optionaly
    * validate and store a SimpleXML or Json representation of the body
    *
    */
    public function exec()
    {
        $response = curl_exec($this->curl);
        if ($this->debug) {
            echo '--[[ cURL response ' . str_repeat('-', 61) . "\n";
            echo trim($response) . "\n";
            echo str_repeat('-', 76) . "]]--\n";
        }

        $error = curl_error($this->curl);
        if (!empty($error)) {
            // TODO: Throw exception
        }

        // Extract the headers and body
        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $this->headers = substr($response, 0, $header_size);
        $this->body = substr($response, $header_size);

    }


    // ------------------------------------------------------------------------
    // Protected
    // ------------------------------------------------------------------------

    /**
    * Initalize GET
    */
    protected function initGET()
    {
        $this->method = 'GET';
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);

    }


    /**
    * Initalize POST
    *
    * @param string|array $data
    */
    protected function initPOST($data = null)
    {
        print_r($data);

        $this->method = 'POST';
        curl_setopt($this->curl, CURLOPT_POST, true);
        if ($data != null) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
        }

    }


}