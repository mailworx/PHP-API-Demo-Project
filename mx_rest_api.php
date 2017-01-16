<?php
    // REST mailworx API helper
    namespace mailworx;
    
class JSON {
    //////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////
    private $request = array();
    private $credentials = array(); // TODO: SET LOGIN VALUES HERE! Set 'Account', 'Username', 'Password', 'Source'.
    private $url;
    private $method;
    private $log = false;
        
    //////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////
        
    // sets the default values for the helper
    function __construct($log = false) {
        $this->log = $log;
        // change the credentials for your API or use the method setCredentials
        $this->reset();
    }
        
    // sets the credentials of the connection
    public function setCredentials($account = '', $username = '', $password = '', $source = '') {
        if ($account != '') {
            $this->request['SecurityContext']['Account'] = $this->credentials['Account'] = $account;
        }
        if ($username != '') {
            $this->request['SecurityContext']['Username'] = $this->credentials['Username'] = $username;
        }
        if ($password != '') {
            $this->request['SecurityContext']['Password'] = $this->credentials['Password'] = $password;
        }
        if ($source != '') {
            $this->request['SecurityContext']['Source'] = $this->credentials['Source'] = $source;
        }
    }
        
    public function setCredentialsByObject($securityContext) {
        if (!is_null($securityContext)) {
            $this->setCredentials($securityContext['Account'], $securityContext['Username'], $securityContext['Password'], $securityContext['Source']);
        }
    }

    // sets the language of the connection
    public function setLanguage($language) {
        if (!isset($language)) {
            return false;
        }
        $this->request['Language'] = strtoupper(trim($language));
        return true;
    }
	
    // sets the method for the next request
    public function setMethod($method) {
        if (!isset($method)) {
            return false;
        }
        // remove useless whitespaces
        $this->method = trim($method);
        return true;
    }
    // check for all needed infos of the request
    private function checkRequestData() {
        // method is needed
        if ($this->method == '') {
            return false;
        }
        // credentials are needed
        if ($this->request['SecurityContext']['Account'] == '' ||
            $this->request['SecurityContext']['Username'] == '' ||
            $this->request['SecurityContext']['Password'] == '' ||
            $this->request['SecurityContext']['Source'] == '') {
            return false;
        }
        return true;
    }
        
    public function getURL() {
        $url = $this->url . "/" . $this->method;
        /*if($this->log === true) {
            $url = str_replace('https://', 'http://', $url);
        }*/
        return $url;
    }
        
    public function getRequestJSON() {
        if ($this->checkRequestData()) {
            return json_encode(array(
                'request' => $this->request
            ));
        }
        return "the request data is not completely configured";
    }
        
    public function getJSON() {
        $start = microtime(true);
        if ($this->checkRequestData()) {
            $json = json_encode(array(
                'request' => $this->request
            ));
                
            $json = str_replace("&lt;", "<", $json);
            $json = str_replace("&gt;", ">", $json);
                
            // set request data
            $ch = curl_init($this->getURL());
                                                                                     
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json))
            );
                                                                                                                                     
            // execute the request
            $result = curl_exec($ch);
                
            if (curl_errno($ch)) {
                $this->log(curl_error($ch));
            }
                
            $this->log($this->getURL());
            $this->log($this->request);
            $this->log(json_encode($this->request));
            $this->log(json_decode($result));
            // reset all settings of the last request
            $this->reset();
            $this->log('Execution time ' . round(microtime(true) - $start, 2) . 's');
            // return result of the last request
            return $result;
        }
        $this->log("the request data is not completely configured");
        return '';
    }
        
    public function getData() {
        return json_decode($this->getJSON());
    }
    public function setProperty($name, $data) {
        $this->request[$name] = $data;
    }
        
    public function getTime($time = '', $gmt = '+0200') {
        // set timezone
        if (!isset($gmt)) {
            $gmt = '+0100';
        }
        if (!is_numeric($time)) {
            $time = time() - 86400; // last day
        }
        $time_format = '/Date(' . date('U', $time)*1000 . $gmt . ')/';
        return $time_format;
    }
        
    private function reset() {
        $this->request = array();
        // sets default values
        $this->request['SecurityContext'] = $this->credentials;
        $this->request['Language'] = 'DE';
        $this->url = 'http://sys.mailworx.info/Services/JSON/ServiceAgent.svc';
        $this->method = '';
    }
    // for logging values of the helper
    public function log($var) {
        if ($this->log === true) {
            echo "<pre>";
            print_r($var);
            echo "</pre>";
        }
    }
}
