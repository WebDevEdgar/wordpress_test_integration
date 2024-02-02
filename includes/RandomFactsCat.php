<?php

use GuzzleHttp\Exception\GuzzleException;
require_once 'RandomFactsDog.php';

class RandomFactsCat extends RandomFactsDog
{
    private static string $URL = 'https://cat-fact.herokuapp.com/facts';

    public function __construct($client)
    {
        RandomFactsDog::__construct($client);
        $this->client = $client;
    }

    /**
     * @return string[]
     */
    public function force() : array{
        try {
            $request = $this->client->request('get', self::$URL);
            $bodyArray = $this->handleResponse($request);
            $data = $this->parseJson($bodyArray);
            if (isset($data['text'], $data['created_at'])){
                echo $this->getTemplate($data);
                return ['status'=>'success'];
            }
        }catch (GuzzleException){
            return ['status' => 'fail'];
        }
        return ['status' => 'fail'];
    }

    /**
     * @param $data
     * @return string
     */
    private function getTemplate($data) : string{
        $html = '<blockquote id="randomFacts">';
        $html .= '<strong><time datetime="'.$data['created_at'].'">'.$data['created_at'].'</time></strong>';
        $html .= "<p>".$data['text']."</p>";
        $html .= '</blockquote>';
        return $html;
    }

    /**
     * @param $bodyArray
     * @return array
     */
    private function parseJson($bodyArray) : array{
        $clearDatas = array_filter($bodyArray, function($item){
            return isset($item['status']['verified'])
                && $item['status']['verified']
                && $item['createdAt']
                && $item['text']
                && !$item['deleted'];
        });
        if ($clearDatas){
            $randomItem = wp_rand(0,count($clearDatas) - 1);
            $time = strtotime($clearDatas[$randomItem]['createdAt']);
            return [
                'created_at'    => date('Y-m-d', $time),
                'text'    => $clearDatas[$randomItem]['text'],
            ];
        }
        return [];
    }
}