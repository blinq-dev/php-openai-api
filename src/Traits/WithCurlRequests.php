<?php

namespace Blinq\LLM\Traits;

trait WithCurlRequests
{
    /**
     * Sends a JSON request to a specified URL.
     *
     * @param string $method The HTTP method to use.
     * @param string $url The URL to send the request to.
     * @param array $jsonData The JSON data to send in the body of the request.
     * @param array $headers Optional additional headers to send with the request.
     * @return array|null The parsed JSON response, or null on failure.
     */
    protected function sendJsonRequest(string $method, string $url, array $jsonData, array $headers = []) {
        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($jsonData),
            CURLOPT_HTTPHEADER => $headers,
        ];

        if ($jsonData === []) {
            unset($curlOptions[CURLOPT_POSTFIELDS]);
        } else {
            $curlOptions[CURLOPT_HTTPHEADER][] = 'Content-Type: application/json';

            // Accept
            $curlOptions[CURLOPT_HTTPHEADER][] = 'Accept: application/json';
        }

        if ($jsonData['stream'] ?? false) {
            $curlOptions[CURLOPT_WRITEFUNCTION] = [$this, 'handleStream'];
        }

        $curl = curl_init();

        curl_setopt_array($curl, $curlOptions);
        $body = curl_exec($curl);
        curl_close($curl);

        try {
            $bodyParsed = json_decode($body, true);
        } catch (\Throwable $th) {
            $bodyParsed = null;
        }

        return $bodyParsed;
    }
}
