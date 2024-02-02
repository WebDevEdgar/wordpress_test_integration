<?php

use GuzzleHttp\Exception\GuzzleException;

class RandomFactsDog
{
    private static string $URL = 'https://dog-api.kinduff.com/api/facts';

    public function __construct($client){
        $this->client = $client;
    }

    /**
     * @return string[]
     * Description: wrapper for all checks
     */
    public function force() : array{
        try {
            $request = $this->client->request('get', self::$URL);
            $bodyArray = $this->handleResponse($request);
            $data = $this->parseJson($bodyArray);
            if ($data && $data['text']){
                    echo $this->getTemplate($data);
            }
        }catch (GuzzleException){
            return ['status' => 'fail'];
        }
        return ['status' => 'fail'];
    }

    /**
     * @param $data
     * @return string
     * Description: html markup
     */
    private function getTemplate($data) : string{
        $html = '<blockquote id="randomFacts">';
        $html .= '<p>'.$data['text'].'</p>';
        $html .= '</blockquote>';
        return $html;
    }

    /**
     * @param $response
     * @return array|string[]
     * Description: responsible for all requests
     */
    protected function handleResponse($response) : array{
        try {
            if ($response->getStatusCode() === 200) {
                $body = $response->getBody();
                return json_decode($body, true);
            }
        }catch (\JsonException){
            return ['status'=>'fail'];
        }
        return ['status'=>'fail'];
    }

    /**
     * @param array $bodyArray
     * @return array
     * Description: parses the array from response
     */
    private function parseJson(array $bodyArray) : array{
        if (isset($bodyArray['facts'])){
            return [
                'text'    => $bodyArray['facts'][0],
            ];
        }
        return [];
    }
}