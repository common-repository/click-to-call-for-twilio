<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('twt_Short_Codes')) {
    class twt_Short_Codes {
        public function __construct() {
            add_shortcode('twtcall', array($this, 'twt_shortcode_call'));
        }
        public function twt_shortcode_call($attr) {
            ob_start();
            if (isset($attr['number']) && get_option('twt_setting_number') && get_option('twt_setting_auth_token') && get_option('twt_setting_account_sid')):
                $agent_phone = esc_attr($attr['number']);
                $button_label = 'Click To Call';
                if (isset($attr['label']))
                    $button_label = esc_attr($attr['label']);
                ?>
                <div class="twt-content twt-call">
                    <input type="text" class="twt_call_number"/>
                    <input type="hidden" class="twt_welcome_message" value="<?php echo esc_attr(get_option('twt_setting_welcome'));?>"/>
                    <?php wp_nonce_field( 'twt_nonce_action', 'twt_nonce_field' ); ?>
                    <button data-agent="<?php echo $agent_phone; ?>" type="button" class="twt_call_button"><?php echo $button_label; ?></button>
                </div>
                <?php
            endif;
            return ob_get_clean();
        }
    }
    $twt_shortcode = new twt_Short_Codes();
}