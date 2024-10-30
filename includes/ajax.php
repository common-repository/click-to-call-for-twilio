<?php
use Twilio\Rest\Client;

if (!class_exists('twt_Ajax')) {
    class twt_Ajax {
        public function __construct() {

            add_action('wp_ajax_make_the_call_guest', array($this, 'make_the_call_callback'));
            add_action('wp_ajax_nopriv_make_the_call_guest', array($this, 'make_the_call_callback'));
        }

        public function make_the_call_callback() {
            global $wpdb;
            $customerNumber = sanitize_text_field(preg_replace('/[^0-9+]/', '', $_POST['user']));
            $agentNumber = sanitize_text_field(preg_replace('/[^0-9+]/', '', $_POST['agent']));
            $welcome = sanitize_text_field($_POST['welcome']);

            if(isset($_POST['security']) && wp_verify_nonce($_POST['security'], 'twt_nonce_action') && $customerNumber && $agentNumber) {
                    $configs = array(
                        'TWT_ACCOUNT_SID' => get_option('twt_setting_account_sid'),
                        'TWT_AUTH_TOKEN' => get_option('twt_setting_auth_token'),
                        'TWT_NUMBER' => get_option('twt_setting_number')
                    );
                    //get the welcome message
                    if( $welcome){
                        $welcomemessage = '&welcome='. urlencode($welcome);
                    }else{
                        $welcomemessage = '';
                    }
                    // Set URL for outbound call   
                    $url = TWT_PLUGIN_URL . 'lib/outbound.php?agentNumber=' . $agentNumber.$welcomemessage;
                    $client = new Client($configs['TWT_ACCOUNT_SID'], $configs['TWT_AUTH_TOKEN']);
                    try {
                    $client->account->calls->create(  
                        $customerNumber,
                        $configs['TWT_NUMBER'],
                        array(
                            "url" => $url
                        )
                    );
                echo "Done"; 
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            wp_die();
        }
    }
    $ajax = new twt_Ajax();
}
