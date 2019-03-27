<?php

namespace AuthenticationServer\Service;

class HTTPClient
{
    /**
     * @param $uri
     * @param array $headers
     * @return mixed
     */
    public function get($uri, $headers = [])
    {
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (count($headers) > 0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        return curl_exec($curl);
    }

    /**
     * @param $uri
     * @param null $data
     * @param array $headers
     * @return mixed
     */
    public function post($uri, $data = null, $headers = [])
    {
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);

        if (count($headers) > 0) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        if (!is_null($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        return curl_exec($curl);
    }
}
