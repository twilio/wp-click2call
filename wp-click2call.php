<?php
/*
 Plugin Name: Wordpress Click2Call
 Plugin URI: http://twilio.com
 Description: Allows theme developers to add click2call support to any post/page
 Version: 1.0
 Author: Adam Ballai
 Author URI: http://twilio.com
*/

if (!function_exists('add_action')) {
    $wp_root = '../../..';
    if (file_exists($wp_root.'/wp-load.php')) {
        require_once($wp_root.'/wp-load.php');
    } else {
        require_once($wp_root.'/wp-config.php');
    }
}
require_once 'twilio.php';
    
if(!class_exists('Click2call')) {

define('WP_CLICK2CALL_VERSION', '1.0');

class Click2call {
    static $options = array('wpc2c_twilio_sid' => 'Twilio Account Sid',
                            'wpc2c_twilio_token' => 'Twilio Token',
                            'wpc2c_caller_id' => 'Caller ID',
                            'wpc2c_primary_phone' => 'Primary Phone To Call');
    function init() {
        global $wpdb;

        register_activation_hook(__FILE__, array('Click2call',
                                                 'create_settings'));

        add_action('admin_menu', array('Click2call',
                                       'admin_menu'));

        add_action('wp_head', array('Click2call',
                                    'head_scripts'));
    }

    function dial($number) {
        $twilio = new TwilioRestClient(get_option('wpc2c_twilio_sid'),
                                       get_option('wpc2c_twilio_token'),
                                       'https://api.twilio.com/2008-08-01');
        
        $connecting_url = plugins_url('wp-click2call/wp-click2call.php?connect_to='.urlencode(get_option('wpc2c_primary_phone')));
        
        $response = $twilio->request("Accounts/".get_option('wpc2c_twilio_sid')."/Calls",
                                     'POST',
                                     array( "Caller" => get_option('wpc2c_caller_id'),
                                            "Called" => $number,
                                            "Url" => $connecting_url,
                                            )
                                     );

        $data = array('error' => false, 'message' => '');
        if($response->IsError) {
            $data['error'] = true;
            $data['message'] = $response->ErrorMessage;
        }

        echo json_encode($data);
    }

    function connect($number)
    {
        $twilio = new Response();
        $twilio->addDial($number);
        $twilio->Respond();
    }

    function admin_menu() {
        
        add_menu_page('Click2call Options',
                      'Click2call',
                      8,
                      __FILE__,
                      array('Click2call',
                            'options'));
    }

    function head_scripts() {
        wp_enqueue_script('wp-click2call', plugins_url('wp-click2call/click2call.js'), array('jquery', 'swfobject'), '1.0', true);
        wp_localize_script('wp-click2call', 'click2callL10n', array(
                                                                    'plugin_url' => plugins_url('wp-click2call'),
                                                                    ));
        wp_print_scripts('wp-click2call');
    }

    function options() {
        $message = '';
        if(!empty($_POST['submit'])) {
            self::update_settings($_POST);
            $message = "Updated";
        }
        echo '<h1>Click2Call</h1';
        echo '<div class="wrap">';
        echo $message;
        echo '<form name="c2c-options" action="" method="post">';
        foreach(self::$options as $option => $title) {
            $value = get_option($option, '');
            echo '<label for="'.htmlspecialchars($option).'">';
            echo htmlspecialchars($title).'<input id="'.htmlspecialchars($option).'" type="text" name="'.htmlspecialchars($option).'" value="'.htmlspecialchars($value).'" /></label> <br />';
        }
        echo '<input type="submit" name="submit" value="Save" />';
        echo '</form>';
        echo '</div>';
    }
    
    function create_settings() {
        add_option('wpc2c_twilio_sid', '',
                   'Twilio Account Sid',
                   'yes');
        
        add_option('wpc2c_twilio_token', '',
                   'Twilio Account Token',
                   'yes');
        
        add_option('wpc2c_primary_phone', '',
                   'Primary phone number to call',
                   'yes');

        add_option('wpc2c_caller_id', '',
                   'What to show up on your friends phone',
                   'yes');

    }

    function update_settings($settings) {
        foreach(self::$options as $option => $title) {
            update_option($option, $settings[$option]);
        }
    }
    
}

/* Wordpres Tag for click2call */
function wp_c2c() {
    $c2c_id = "C2C".uniqid();
    echo '<div id="'.$c2c_id.'" class="click2call"></div>';
}

function wp_c2c_main() {
    
    if(!empty($_REQUEST['caller']))
    {
        Click2call::dial($_REQUEST['caller']);
        exit;
    }
    
    if(!empty($_REQUEST['connect_to']))
    {
        Click2call::connect($_REQUEST['connect_to']);
        exit;
    }
    
    return Click2call::init();
}

wp_c2c_main();
} // end class check