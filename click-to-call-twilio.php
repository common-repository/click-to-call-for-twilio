<?php
/*
  Plugin Name: Click-to-Call for Twilio
  Description: The ultimate Twilio plugin that allows you to integrate click-to-call options into your existing WordPress theme.
  Version: 1.0.0
  Author: daswebagency
  Author URI: https://twilio.dasweb.ca
  License: GPLv2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
define('TWT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TWT_PLUGIN_PATH', plugin_dir_path(__FILE__));
require_once ('lib/Twilio/autoload.php');

if (!class_exists('TWT')) {
    class TWT {
        public function __construct() {
            add_action('admin_menu', array($this, 'twt_settings_page'));
            require_once ('includes/shortcodes.php');
            require_once ('includes/ajax.php');
            add_action('wp_enqueue_scripts', array($this, 'twt_script'));
            add_action('admin_enqueue_scripts', array($this, 'twt_settings_style'));
        }
        public function twt_settings_style() {
            wp_enqueue_style('twt-settings-style', TWT_PLUGIN_URL . 'css/admin.css');
            wp_enqueue_script('twt-setting-script', TWT_PLUGIN_URL . 'js/admin.js');
        }
        public function twt_script() {
            ?>
            <script>
                var ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
                var countries = <?php if(get_option('twt_setting_countries')){ echo json_encode(preg_split('/\n|\r\n?/', get_option('twt_setting_countries'))); } else {echo json_encode(array('us', 'ca'));} ?>;
            </script>
            <?php
            wp_enqueue_style('twt-css', TWT_PLUGIN_URL . 'css/frontend.css');
            wp_enqueue_style('twt-phone-css', TWT_PLUGIN_URL . 'css/intlTelInput.css');
            wp_enqueue_script('twt-phone', TWT_PLUGIN_URL . 'js/intlTelInput.min.js', array('jquery'));
            wp_enqueue_script('twt-util', TWT_PLUGIN_URL . 'js/utils.js');
            wp_enqueue_script('twt-script', TWT_PLUGIN_URL . 'js/twt-script.js');
        }
        public function twt_settings_page() {
            add_menu_page('Click2Call Twilio', 'Click2Call Twilio', 'manage_options', 'twt', array($this, 'twt_settings_page_func'), 'dashicons-phone');
        }

        public function twt_settings_page_func() {
            ?>
            <div class="twt_settings" style="margin-top: 20px;">
                <div class="welcome-panel">
                    <div class="welcome-panel-content" >
                        <div class="welcome-panel-column-container" >
                            <div class="welcome-panel-column">
                                <h1>Click-to-Call for Twilio</h1>
                                <h4>by <a href="https://twilio.dasweb.ca" target="_blank">Dasweb</a></h4>
                                <p>Click-to-Call for Twilio allows you to implement click-to-call, SMS/MMS and Fax Outbound options for your WordPress website using simple and intuitive shortcodes.</p>
                                <p>Read the full plugin <a href="https://twilio.dasweb.ca/documentation?s=pluginpage" target="_blank">Documentation</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                <br>

                <form method="post" class="form-table">
                <?php
                if( isset( $_POST['setting_twt_nonce'] ) && wp_verify_nonce( $_POST['setting_twt_nonce'], 'twt_form_nonce') ) {    
                    if( !($_POST['setting_twt_account_sid']) || !($_POST['setting_twt_auth_token'])) {
                        echo  '<div class="error notice is-dismissible"><p>';
                        _e( 'You cannot leave Account SID or AUTH Token empty!', 'twt-plugin' ) ;
                        echo '</p> </div>';
                    } elseif (!($_POST['setting_twt_number'])) {
                        echo  '<div class="error notice is-dismissible"><p>';
                        _e( 'You cannot leave the Twilio Voice Number field empty!', 'twt-plugin' );
                        echo '</p> </div>';
                    } else {
                        update_option('twt_setting_number', sanitize_text_field(preg_replace('/[^0-9+]/', '', $_POST['setting_twt_number'])));
                        update_option('twt_setting_welcome', sanitize_text_field($_POST['setting_twt_welcome']));
                        update_option('twt_setting_account_sid', sanitize_text_field($_POST['setting_twt_account_sid']));
                        update_option('twt_setting_auth_token', sanitize_text_field($_POST['setting_twt_auth_token']));
                        update_option('twt_setting_countries', sanitize_textarea_field($_POST['setting_twt_countries']));
                        echo  '<div class="updated settings-error notice is-dismissible"><p>';
                        _e( 'Settings updated successfully.', 'twt-plugin' );
                        echo '</p> </div>';
                    }
                 }
                ?>
                <ul class="twt-tab-bar nav-tab-wrapper  wp-clearfix">
                    <li class="twt-tab-active"><a href="#tabs-1"><?php _e( 'General Settings', 'twt-plugin' ); ?></a></li>
                    <li><a href="#tabs-2"><?php _e( 'Click-to-Call', 'twt-plugin' ); ?></a></li>
                    <li><a href="#tabs-3"><?php _e( 'SMS/MMS', 'twt-plugin' ); ?></a></li>
                    <li><a href="#tabs-4"><?php _e( 'Fax', 'twt-plugin' ); ?></a></li>
                    <li><a href="#tabs-5"><?php _e( 'Shortcodes', 'twt-plugin' ); ?></a></li>
                </ul>
                <div class="twt-tab-panel" id="tabs-1">
                    <h3><?php _e( 'General Settings', 'twt-plugin' ); ?></h3>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th><label for="setting_twt_account_sid"><?php _e( 'Twilio Account SID', 'twt-plugin' ); ?></label></th>
                                <td>
                                    <input value="<?php echo esc_attr(get_option('twt_setting_account_sid')); ?>" name="setting_twt_account_sid" type="text" class="regular-text"/>
                                    <p class="description"><?php _e( 'You can get your Account SID from:', 'twt-plugin' ); ?> <a target="_blank" href='https://www.twilio.com/console'>https://www.twilio.com/console</a></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="setting_twt_auth_token"><?php _e( 'Twilio AUTH Token', 'twt-plugin' ); ?></label></th>
                                <td>
                                    <input value="<?php echo esc_attr(get_option('twt_setting_auth_token')); ?>" name="setting_twt_auth_token" type="password" class="regular-text"/>
                                    <p class="description"><?php _e( 'You can get your Account Auth Token from:', 'twt-plugin' ); ?> <a target="_blank" href='https://www.twilio.com/console'>https://www.twilio.com/console</a></p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th><label for="setting_twt_auth_token"><?php _e( 'Country List', 'twt-plugin' ); ?></label></th>
                                <td>
                                    <textarea name="setting_twt_countries" rows="15" cols="5"><?php echo esc_attr(get_option('twt_setting_countries')); ?></textarea>
                                    <p class="description"><?php _e( 'Enter the 2 letters country code for the list of countries that appear in the phone input field.<br><strong>Example:</strong> us -> for United States. Complete list of country codes here: <a href="https://twilio.dasweb.ca/country-list-codes/" target="_blank">Country List Codes</a><br> 
                                    <strong>Please Note:</strong> This is is only to display the country flags in the select dropdown.<br>To completely block certain countries from innitiating the calls please change the settings inside <a href="https://www.twilio.com/console/voice/calls/geo-permissions/low-risk" target="_blank">Twilio Dashboard</a>', 'twt-plugin' ); ?> </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="twt-tab-panel" id="tabs-2" style="display: none;">
                    <h3><?php _e( 'Click-to-Call Settings', 'twt-plugin' ); ?></h3>
                    <table>
                        <tbody>
                            <tr>
                                <th><label for="setting_twt_number"><?php _e( 'Twilio Voice Number', 'twt-plugin' ); ?></label></th>
                                <td>
                                    <input value="<?php echo esc_attr(get_option('twt_setting_number')); ?>" name="setting_twt_number" type="text" class="regular-text"/>
                                    <p class="description"><?php _e( 'Twilio Voice capable Number (ie. +14695572832)', 'twt-plugin' ); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="setting_twt_welcome"><?php _e( 'Welcome Message', 'twt-plugin' ); ?></label></th>
                                <td>
                                    <input value="<?php echo esc_attr(get_option('twt_setting_welcome')); ?>" name="setting_twt_welcome" type="text" class="regular-text"/>
                                    <p class="description"><?php _e( 'Welcome message to be played when connecting visitor to your endpoint number. If left blank nothing will be played.', 'twt-plugin' ); ?></p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="twt-tab-panel" id="tabs-3" style="display: none;">
                    <h3><?php _e( 'SMS/MMS Settings', 'twt-plugin' ); ?></h3>
                    <table>
                        <body>
                            <tr>
                                <td>
                                    <p>Available in the Pro version</p>
                                    <a href="https://twilio.dasweb.ca/shop/twilio-wordpress-tools-pro/" target="_blank">Pro Version</a>
                                </td>
                            </tr>
                        </body>
                    </table>
                </div>
                <div class="twt-tab-panel" id="tabs-4" style="display: none;">
                    <h3><?php _e( 'Fax Settings', 'twt-plugin' ); ?></h3>
                    <table>
                        <tbody>
                            <tr>
                                <td>
                                    <p>Available in the Pro version</p>
                                    <a href="https://twilio.dasweb.ca/shop/twilio-wordpress-tools-pro/" target="_blank">Pro Version</a>
                                </td>
                            </tr> 
                        </tbody>
                    </table> 
                </div>
                <div class="twt-tab-panel" id="tabs-5" style="display: none;">
                    <h3><?php _e( 'Shortcode Examples', 'twt-plugin' ); ?></h3>
                    <table class="update-nag">
                        <tbody>
                            <tr>
                                <td><b><?php _e( 'ShortCode for Click-to-Call:', 'twt-plugin' ); ?> </b> [twtcall label='Click To Call' number='<?php 
                                    if(get_option('twt_setting_number') ){
                                        echo get_option('twt_setting_number'); 
                                    }else{
                                        echo '+15556667777';
                                    }
                                    ?>']</td>
                            </tr>
                        </tbody>
                    </table>
                <br>
                </div>
                    <p class="submit">
                        <?php $twt_add_meta_nonce = wp_create_nonce( 'twt_form_nonce' ); ?>
                        <input type="hidden" name="setting_twt_nonce" value="<?php echo $twt_add_meta_nonce; ?>">
                        <input type="submit" value="<?php _e( 'Save Changes:', 'twt-plugin' ); ?>" class="button button-primary">
                    </p>
                <hr/>
                </form>  
            </div>
            <div class="clear"></div>
            <?php
        }
    }
    $twilio = new TWT();
}
