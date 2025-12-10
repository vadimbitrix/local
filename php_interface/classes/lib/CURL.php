<?php

/**
 *
 * Пример использования CURL
 *
 * $curlHelper = new CURL();
 * $response = $curlHelper->makeRequest('http://example.com', 'POST', ['field' => 'value']);
 *
 */

namespace Vadim24\Lib;

use \Exception;

class CURL {
    private $ch;

    public function __construct() {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30); // Set a timeout
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function makeRequest($url, $method = 'GET', $data = []) {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_REFERER, $url);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($method === 'POST') {
            curl_setopt($this->ch, CURLOPT_POST, true);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($this->ch);

        if (curl_errno($this->ch)) {
            throw new Exception(curl_error($this->ch));
        }

        return $result;
    }
}
