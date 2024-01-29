<?php
use GuzzleHttp\Exception\GuzzleException;

class RandomFactsCat extends RandomFacts
{
    private static string $URL = 'https://cat-fact.herokuapp.com/facts';

    /**
     * @param $client
     * @return string[]
     */
    public function force($client) : array{
        try {
            $request = $client->request('get', self::$URL);
            $data = $this->parseJson(
                $this->handleResponse($request)
            );
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
        $html .= '<strong><time>'.$data['created_at'].'</time></strong>';
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