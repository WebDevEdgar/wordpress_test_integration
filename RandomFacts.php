<?php
/**
 * Plugin Name: Random Facts
 * Plugin URI:  https://github.com/WebDevEdgar/wordpress_test_integration
 * Description: Shows random facts aboit cats and dogs in the footer of page
 * Version:     1.1
 * Author:      Edgar Khachaturov
 * Author uri:  https://www.linkedin.com/in/webedgar/
 * Text Domain: r-facts
 */

namespace randomFacts;
use GuzzleHttp\Client;
use RandomFactsCat;
use RandomFactsDog;

if (!defined('ABSPATH')){
    die();
}

require_once('vendor/autoload.php');
class RandomFacts
{
    private static string $URL = 'https://dog-api.kinduff.com/api/facts';
    public function hooks(){
        add_action('wp_footer', [$this, 'showData']);
        add_action('wp_enqueue_scripts', function(){
            wp_enqueue_style('randomfacts', plugin_dir_url( __FILE__ ).'/assets/randomFacts.css');
        });
    }

    /**
     * @return void
     * Description: The main method for showing the element
     */
    public function showData() : void{
        $choose = wp_rand(0,1);
        $client = new Client(['verify'=>false]);
        if ($choose){
            require_once 'includes/RandomFactsDog.php';
            $randomFactsDog = new RandomFactsDog($client);
            $randomFactsDog->force();
            return;
        }
        require_once 'includes/RandomFactsCat.php';
        $randomFactsCat = new RandomFactsCat($client);
        $randomFactsCat->force();
    }
}

$RandomFacts = new RandomFacts();
$RandomFacts->hooks();