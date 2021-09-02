<?php
/*
Plugin Name: Fast Google Recapha
Version: 1.0
Author: Ivanochko Andrii
Description: Просте встановлення Recapha3
*/


defined('ABSPATH') or die("Hey! this not allowed");
define( 'FGR_NAME', 'fast_google_recaptcha');
define( 'FGR_CLASS_NAME', 'fastGoogleRecaptcha');
define( 'FGR_FILE', __FILE__);
define( 'FGR_DIR', untrailingslashit( dirname( __FILE__ ) ) );
define( 'FGR_URL', plugins_url().'/'.(FGR_NAME).'/');

class fastGoogleRecaptcha {
    static $debug = true;
    static private $settings = [];
    static $robot;

    static function init(){
        register_activation_hook(FGR_FILE,array(FGR_CLASS_NAME,'activation_hook'));
        require_once FGR_DIR . '/modules/settings_page/settings_page.php';
        add_filter( 'plugin_action_links', array(FGR_CLASS_NAME,'plugin_action_links'), 10, 2 );
        self::set_default_options();
        add_action( 'wp_enqueue_scripts', array(FGR_CLASS_NAME,'front_scripts'));
        if(self::$settings['fgr_form7check'] == 'on'){
            add_filter('wpcf7_before_send_mail', array(FGR_CLASS_NAME,'form7_check_robot'),1,3);
        }
    }

    static function front_scripts(){
        wp_enqueue_script( 'google_api', 'https://www.google.com/recaptcha/api.js?render='.self::$settings['fgr_site_key'], array(), null, true );
        wp_localize_script( 'google_api', 'frg_data',  array(
            'fgr_sitekey' => self::$settings['fgr_site_key'],
        ));
        wp_enqueue_script( 'script_id', FGR_URL.'modules/recaptcha/recaptcha.js', array(), null, true ); 
    }

    static function plugin_action_links($actions, $plugin_file){
        if(false === strpos( $plugin_file, FGR_NAME )) return $actions;
        $settings_link = '<a href="options-general.php?page='.FGR_NAME.'">Настройки</a>'; array_unshift( $actions, $settings_link ); 
        return $actions; 
    }

    static function set_default_options(){
        $settings = array(
            'fgr_site_key' => '',
            'fgr_private_key' => '',
            'fgr_form7check' => '',
        );
        $opts = get_option('recaptcha_settings');
        if(is_array($opts)) $settings = array_merge($settings,$opts);
        self::$settings = $settings;
    }

    static function form7_check_robot($contact_form,&$abort,$object){
            $is_robot = self::is_robot();
            if($is_robot === true){
                $abort = true;//якщо робот то відхилимо відправку форми
                $object->set_response('Подозрительная активность');
                // Для дебага:
                // $object->set_response('Подозрительная активность '.var_export(self::$robot,true));
            }else{
                // Для дебага:
                // $object->set_response('Норм '.var_export(self::$robot,true));
            }
    }

    static function is_robot(){
        self::check_robot();
        if(self::$robot->success == true && self::$robot->score >= 0.5){ return false; }
        else{ return true; };
    }

    static function check_robot(){
        self::$robot = self::returnReCaptcha($_REQUEST['frg_token'],self::$settings['fgr_private_key']);
        if(self::$robot) return self::$robot;
    }

    private static function returnReCaptcha($token, $sicret_key) {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $params = [
            'secret' => $sicret_key, 
            'response' => $token, 
            'remoteip' => $_SERVER['REMOTE_ADDR'], 
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        return json_decode($response);
    }
}

if(class_exists(FGR_CLASS_NAME)){
    (FGR_CLASS_NAME)::init();
}