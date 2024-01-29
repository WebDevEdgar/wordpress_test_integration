<?php
/**
 * Plugin Name: Random Facts
 * Plugin URI:  https://github.com/WebDevEdgar/wordpress_test_integration
 * Description: Shows random facts aboit cats and dogs in the footer of page
 * Version:     1.0
 * Author:      Edgar Khachaturov
 * Author uri:  https://www.linkedin.com/in/webedgar/
 * Text Domain: r-facts
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

if (!defined('ABSPATH')){
    die();
}

require_once('vendor/autoload.php');
require_once('RandomFactsCat.php');
class RandomFacts
{
    private static string $dogURL = 'https://dog-api.kinduff.com/api/facts';
    public function hooks(){
        add_action('wp_footer', [$this, 'showData']);
        add_action('wp_enqueue_scripts', function(){
            wp_enqueue_style('randomfacts', plugin_dir_url( __FILE__ ).'/assets/randomFacts.css');
        });
    }

    /**
     * @return void
     * @throws Exception
     * Description: The main method for showing the element
     */
    public function showData() : void{
        $choose = wp_rand(0,1);
        $client = new Client(['verify'=>false]);
        if ($choose){
            $data = $this->getDogData($client);
        }else{
            $randomFactsCat = new RandomFactsCat();
            $randomFactsCat->force($client);
            return;
        }
        if ($data['status'] !== 'success' || !isset($data['data']['text'])){
            return;
        }
        echo $this->getTemplate($data['data']);
    }

    /**
     * @param Client $client
     * @return string[]
     * Description: wrapper for all checks
     */
    protected function getDogData(Client $client) : array{
        try {
            $request = $client->request('get', self::$dogURL);
            $bodyArray = $this->handleResponse($request);
            $parser = $this->parseJson($bodyArray);
            if ($parser){
                return ['status'=>'success', 'data' => $parser];
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

$RandomFacts = new RandomFacts();
$RandomFacts->hooks();