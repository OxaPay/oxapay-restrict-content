<?php
/*
Plugin Name: OxaPay Crypto Payment Gateway For Restrict Content
Version: 1.0.0
Description: OxaPay is a leading crypto payment gateway that enables businesses to accept payments in various cryptocurrencies securely.
Plugin URI: https://app.oxapay.com/signin
Author: OxaPay.com
Author URI: http://oxapay.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('RCP_OxaPay')) {
    class RCP_OxaPay
    {
        public function process_webhooks()
        {}
        public function scripts()
        {}
         public function fields()
        {}

        public function validate_fields()
        {}

        public function supports($item = '')
        {return;}

        public function __construct()
        {
            add_action('init', array($this, 'OxaPay_Verify'));
            add_action('rcp_payments_settings', array($this, 'OxaPay_Setting'));
            add_action('rcp_gateway_OxaPay', array($this, 'OxaPay_Request'));
            add_filter('rcp_payment_gateways', array($this, 'OxaPay_Register'));
        }

        public function OxaPay_Register($gateways)
        {
            global $rcp_options;

            if (version_compare(RCP_PLUGIN_VERSION, '2.1.0', '<')) {
                $gateways['OxaPay'] = isset($rcp_options['oxapay_name']) ? $rcp_options['oxapay_name'] :  __('OxaPay', 'oxapay-crypto-payment-gateway-for-restrict-content');
            } else {
                $gateways['OxaPay'] = array(
                    'label' => isset($rcp_options['oxapay_name']) ? $rcp_options['oxapay_name'] :  __('OxaPay', 'oxapay-crypto-payment-gateway-for-restrict-content'),
                    'admin_label' => isset($rcp_options['oxapay_name']) ? $rcp_options['oxapay_name'] :  __('OxaPay', 'oxapay-crypto-payment-gateway-for-restrict-content'),
                    'class' => 'rcp_oxapay',
                );

            }

            return $gateways;
        }

        public function OxaPay_Setting($rcp_options)
        {
            $style_handle = 'gf-oxapay-custom-css';
            wp_register_style($style_handle, FALSE);
            wp_enqueue_style($style_handle);

            $inline_style =
            "            
                .oxapay-settings {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #e9f4fb;
                    padding: 20px;
                    border: 1px solid #0073aa;
                    border-radius: 8px;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    text-align: left;
                }
        
                .oxapay-settings h3 {
                    color: #0056b3;
                    font-size: 1.5em;
                    margin-bottom: 15px;
                }
        
                .oxapay-settings table {
                    width: 100%;
                    border-spacing: 0 15px;
                }
        
                .oxapay-settings th {
                    text-align: left;
                    font-weight: bold;
                    vertical-align: top;
                    padding-right: 15px;
                    width: 25%;
                    color: #333;
                }
        
                .oxapay-settings td {
                    width: 75%;
                }
        
                .oxapay-settings input {
                    width: 100%;
                    max-width: 400px;
                    padding: 10px;
                    border: 1px solid #ccc;
                    border-radius: 4px;
                    font-size: 14px;
                }
        
                .oxapay-settings .description {
                    font-size: 0.9em;
                    color: #555;
                    margin-top: 5px;
                }
        
                .oxapay-settings hr {
                    border: none;
                    border-top: 1px solid #ddd;
                    margin: 20px 0;
                }
        
                .oxapay-settings .button {
                    background-color: #0073aa;
                    color: white;
                    border: none;
                    padding: 12px 25px;
                    font-size: 16px;
                    font-weight: bold;
                    border-radius: 4px;
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
        
                .oxapay-settings .button:hover {
                    background-color: #005680;
                }
            ";
            wp_add_inline_style($style_handle, $inline_style);
            ?>
            <hr>
            <div class="oxapay-settings">
                <table class="form-table">
                    <?php do_action('RCP_OxaPay_before_settings', $rcp_options); ?>
        
                    <tr valign="top">
                                    <div style="text-align: center; margin-bottom: 20px;">
                        <h1 style="font-size: 26px; font-weight: bold; color: #333; margin-bottom: 10px;">
                        <?php esc_html_e("OxaPay Payment Settings", "oxapay-crypto-payment-gateway-for-restrict-content"); ?>
                        </h1>
                        <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'oxapay.svg'); ?>"
                             alt="OxaPay Logo" 
                             style="width: 100%; max-width: 300px; height: auto; border-radius: 8px; margin-top: 10px;" />
                    </div>
        
        
                    <tr valign="top">
                        <th>
                            <label for="rcp_settings[oxapay_merchant]">
                                <?php esc_html_e('OxaPay Merchant Key', 'oxapay-crypto-payment-gateway-for-restrict-content'); ?>
                            </label>
                        </th>
                        <td>
                            <input id="rcp_settings[oxapay_merchant]" 
                                   name="rcp_settings[oxapay_merchant]"
                                   value="<?php echo isset($rcp_options['oxapay_merchant']) ? esc_attr($rcp_options['oxapay_merchant']) : ''; ?>" />
                            <div class="description">
                                <?php esc_html_e('You can find your OxaPay Merchant Key in the settings of your personal account.', 'oxapay-crypto-payment-gateway-for-restrict-content'); ?>
                            </div>
                        </td>
                    </tr>
        
                    <tr valign="top">
                        <th>
                            <label for="rcp_settings[oxapay_lifetime]">
                                <?php esc_html_e('Invoice Lifetime', 'oxapay-crypto-payment-gateway-for-restrict-content'); ?>
                            </label>
                        </th>
                       <td>
                            <input id="rcp_settings[oxapay_lifetime]" 
                                   name="rcp_settings[oxapay_lifetime]"
                                   type="number"
                                   min="15" 
                                   max="2880" 
                                   value="<?php echo isset($rcp_options['oxapay_lifetime']) ? esc_attr($rcp_options['oxapay_lifetime']) : '60'; ?>" />
                            <div class="description">
                                <?php esc_html_e('Set the expiration time for the payment link in minutes (15-2880).', 'oxapay-crypto-payment-gateway-for-restrict-content'); ?>
                            </div>
                        </td>
                    </tr>
        
                    <tr valign="top">
                        <th>
                            <label for="rcp_settings[oxapay_name]">
                                <?php esc_html_e('Method Title', 'oxapay-crypto-payment-gateway-for-restrict-content'); ?>
                            </label>
                        </th>
                        <td>
                            <input id="rcp_settings[oxapay_name]" 
                                   name="rcp_settings[oxapay_name]"
                                   value="<?php echo esc_attr(isset($rcp_options['oxapay_name']) ? $rcp_options['oxapay_name'] : esc_html('OxaPay', 'oxapay-crypto-payment-gateway-for-restrict-content')); ?>" />
                        </td>
                    </tr>
        
                    <?php do_action('RCP_OxaPay_after_settings', $rcp_options); ?>
                </table>
                <div style="text-align: center; margin-top: 20px;">
                <button 
                    type="submit" 
                    name="gf_oxapay_save" 
                    style="background-color: #0056b3; color: white; border: none; padding: 12px 25px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; transition: background-color 0.3s;">
                    <?php esc_html_e("Save Settings", "oxapay-crypto-payment-gateway-for-restrict-content"); ?>
                </button>
                </div>
            </div>
            <hr>
            <?php
        }
        
        public function OxaPay_Request($subscription_data)
        {
            $new_subscription_id = get_user_meta($subscription_data['user_id'], 'rcp_subscription_level', true);
            if (!empty($new_subscription_id)) {
                update_user_meta($subscription_data['user_id'], 'rcp_subscription_level_new', $new_subscription_id);
            }

            $old_subscription_id = get_user_meta($subscription_data['user_id'], 'rcp_subscription_level_old', true);
            update_user_meta($subscription_data['user_id'], 'rcp_subscription_level', $old_subscription_id);

            global $rcp_options;

            ob_start();
            $query = 'restrict_oxapay';
            $amount = str_replace(',', '', $subscription_data['price']);
            $currency = $subscription_data['currency'];
            do_action('RCP_Before_Sending_to_OxaPay', $subscription_data);
            
            $MerchantID = isset($rcp_options['oxapay_merchant']) ? $rcp_options['oxapay_merchant'] : '';
            $Lifetime = isset($rcp_options['oxapay_lifetime']) ? $rcp_options['oxapay_lifetime'] : '60';
            $Amount = $amount;
            $Email = isset($subscription_data['user_email']) ? $subscription_data['user_email'] : '-';
		    $CallbackURL = trailingslashit(home_url()) . "?restrict_oxapay_ipn=oxapay";
            $returnUrl = $subscription_data['return_url'];

            $oxapay_payment_data = array(
                'user_id' => $subscription_data['user_id'],
                'subscription_name' => $subscription_data['subscription_name'],
                'subscription_key' => $subscription_data['key'],
                'amount' => $amount,
            );
            @session_start();
            $_SESSION["oxapay_payment_data"] = $oxapay_payment_data;

            $url = 'https://api.oxapay.com/v1/payment/invoice';

            $body = array(
                'amount' => $Amount,
                'currency' => $currency,
                'lifetime' => $Lifetime,
                'callback_url' => $CallbackURL,
                'return_url' => $returnUrl,
                'order_id' => $subscription_data['user_id'].'-'.$subscription_data['key'],
                'description' =>  $subscription_data['payment_id'].'-'.$subscription_data['subscription_name'],
                'email' => $Email
            );
            
            $result = wp_remote_post($url, [
            'timeout' => 25,
            'body' => wp_json_encode($body),
            'headers' => [
                'Content-Type' => 'application/json',
                'merchant_api_key' => sanitize_text_field($MerchantID),
                'origin' => 'oxa-wp-rcpro-plugin-v-1.0.0',
                'Accept' => 'application/json',
            ],
            'sslverify' => false,
            ]);

            $pay = json_decode(wp_remote_retrieve_body($result));

            if ($pay->status == 200) {

                global $rcp_options, $post; ?>

                <?php if( ! is_user_logged_in() ) { ?>
                    <h3 class="rcp_header">
                        <?php echo esc_html( apply_filters( 'rcp_registration_header_logged_out', __( 'Register New Account', 'oxapay-crypto-payment-gateway-for-restrict-content' ) ) ); ?>
                    </h3>
                <?php } else { ?>
                    <h3 class="rcp_header">
                        <?php echo esc_html(apply_filters( 'rcp_registration_header_logged_in', __( 'Upgrade Your Subscription', 'oxapay-crypto-payment-gateway-for-restrict-content' ) ) ); ?>
                    </h3>
                <?php }

                rcp_show_error_messages( 'register' ); ?>

                <form id="rcp_registration_form" class="rcp_form" method="POST" action="<?php echo esc_url( rcp_get_current_url() ); ?>">

                    <?php if( ! is_user_logged_in() ) { ?>

                        <div class="rcp_login_link">
                        <?php
                        // translators: %s is the URL to the login page.
                        printf(
                            esc_html__( '%s if you wish to renew an existing subscription.', 'oxapay-crypto-payment-gateway-for-restrict-content' ),
                            '<a href="' . esc_url( rcp_get_login_url( rcp_get_current_url() ) ) . '">' . esc_html__( 'Log in', 'oxapay-crypto-payment-gateway-for-restrict-content' ) . '</a>'
                        );
                        ?>                        
                        </div>

                        <?php do_action( 'rcp_before_register_form_fields' ); ?>

                        <fieldset class="rcp_user_fieldset">
                            <p id="rcp_user_login_wrap">
                                <label for="rcp_user_login"><?php echo esc_html(apply_filters ( 'rcp_registration_username_label', __( 'Username', 'oxapay-crypto-payment-gateway-for-restrict-content' ) )); ?></label>
                                <input name="rcp_user_login" id="rcp_user_login" class="required" type="text" <?php if( isset( $_POST['rcp_user_login'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_login'] ) . '"'; } ?>/>
                            </p>
                            <p id="rcp_user_email_wrap">
                                <label for="rcp_user_email"><?php echo esc_html(apply_filters ( 'rcp_registration_email_label', __( 'Email', 'oxapay-crypto-payment-gateway-for-restrict-content' ) )); ?></label>
                                <input name="rcp_user_email" id="rcp_user_email" class="required" type="text" <?php if( isset( $_POST['rcp_user_email'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_email'] ) . '"'; } ?>/>
                            </p>
                            <p id="rcp_user_first_wrap">
                                <label for="rcp_user_first"><?php echo esc_html(apply_filters ( 'rcp_registration_firstname_label', __( 'First Name', 'oxapay-crypto-payment-gateway-for-restrict-content' ) )); ?></label>
                                <input name="rcp_user_first" id="rcp_user_first" type="text" <?php if( isset( $_POST['rcp_user_first'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_first'] ) . '"'; } ?>/>
                            </p>
                            <p id="rcp_user_last_wrap">
                                <label for="rcp_user_last"><?php echo esc_html(apply_filters ( 'rcp_registration_lastname_label', __( 'Last Name', 'oxapay-crypto-payment-gateway-for-restrict-content' )) ); ?></label>
                                <input name="rcp_user_last" id="rcp_user_last" type="text" <?php if( isset( $_POST['rcp_user_last'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_last'] ) . '"'; } ?>/>
                            </p>
                            <p id="rcp_password_wrap">
                                <label for="rcp_password"><?php echo esc_html(apply_filters ( 'rcp_registration_password_label', __( 'Password', 'oxapay-crypto-payment-gateway-for-restrict-content' ) )); ?></label>
                                <input name="rcp_user_pass" id="rcp_password" class="required" type="password"/>
                            </p>
                            <p id="rcp_password_again_wrap">
                                <label for="rcp_password_again"><?php echo esc_html(apply_filters ( 'rcp_registration_password_again_label', __( 'Password Again', 'oxapay-crypto-payment-gateway-for-restrict-content' ) )); ?></label>
                                <input name="rcp_user_pass_confirm" id="rcp_password_again" class="required" type="password"/>
                            </p>

                            <?php do_action( 'rcp_after_password_registration_field' ); ?>

                        </fieldset>
                    <?php } ?>

                    <?php do_action( 'rcp_before_subscription_form_fields' ); ?>

                    <fieldset class="rcp_subscription_fieldset">
                        <?php $levels = rcp_get_subscription_levels( 'active' );
                        if( $levels ) : ?>
                            <p class="rcp_subscription_message"><?php echo esc_html(apply_filters ( 'rcp_registration_choose_subscription', __( 'Choose your subscription level', 'oxapay-crypto-payment-gateway-for-restrict-content' ) )); ?></p>
                            <ul id="rcp_subscription_levels">
                                <?php foreach( $levels as $key => $level ) : ?>
                                    <?php if( rcp_show_subscription_level( $level->id ) ) : ?>
                                        <li class="rcp_subscription_level rcp_subscription_level_<?php echo esc_attr( $level->id ); ?>">
                                            <input 
                                            type="radio" 
                                            id="rcp_subscription_level_<?php echo esc_attr( $level->id ); ?>" 
                                            class="required rcp_level" 
                                            <?php if ( isset( $_GET['level'] ) ) { checked( $level->id, $_GET['level'] ); } ?> 
                                            name="rcp_level" 
                                            rel="<?php echo esc_attr( $level->price ); ?>" 
                                            value="<?php echo esc_attr( absint( $level->id ) ); ?>" 
                                            <?php if ( $level->duration == 0 ) { echo 'data-duration="forever"'; } ?> 
                                            />
                                            <label for="rcp_subscription_level_<?php echo esc_attr( $level->id ); ?>">
                                                <span class="rcp_subscription_level_name">
                                                    <?php echo esc_html( rcp_get_subscription_name( $level->id ) ); ?>
                                                </span>
                                                <span class="rcp_separator">&nbsp;-&nbsp;</span>
                                                <span class="rcp_price" rel="<?php echo esc_attr( $level->price ); ?>">
                                                    <?php 
                                                    echo $level->price > 0 
                                                        ? esc_html( rcp_currency_filter( $level->price ) ) 
                                                        : esc_html__( 'free', 'oxapay-crypto-payment-gateway-for-restrict-content' );
                                                    ?>
                                                    <span class="rcp_separator">&nbsp;-&nbsp;</span>
                                                </span>
                                                <span class="rcp_level_duration">
                                                    <?php 
                                                    echo $level->duration > 0 
                                                        ? esc_html( $level->duration ) . '&nbsp;' . esc_html( rcp_filter_duration_unit( $level->duration_unit, $level->duration ) )
                                                        : esc_html__( 'unlimited', 'oxapay-crypto-payment-gateway-for-restrict-content' );
                                                    ?>
                                                </span>
                                                <div class="rcp_level_description">
                                                    <?php echo wp_kses_post( rcp_get_subscription_description( $level->id ) ); ?>
                                                </div>
                                            </label>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php else : ?>
                            <p><strong><?php esc_html_e( 'You have not created any subscription levels yet', 'oxapay-crypto-payment-gateway-for-restrict-content' ); ?></strong></p>
                        <?php endif; ?>
                    </fieldset>

                    <?php if( rcp_has_discounts() ) : ?>
                        <fieldset class="rcp_discounts_fieldset">
                        <p id="rcp_discount_code_wrap">
                            <label for="rcp_discount_code">
                                <?php esc_html_e( 'Discount Code', 'oxapay-crypto-payment-gateway-for-restrict-content' ); ?>
                                <span class="rcp_discount_valid" style="display: none;"> - <?php esc_html_e( 'Valid', 'oxapay-crypto-payment-gateway-for-restrict-content' ); ?></span>
                                <span class="rcp_discount_invalid" style="display: none;"> - <?php esc_html_e( 'Invalid', 'oxapay-crypto-payment-gateway-for-restrict-content' ); ?></span>
                            </label>
                            <input type="text" id="rcp_discount_code" name="rcp_discount" class="rcp_discount_code" value=""/>
                            <button class="rcp_button" id="rcp_apply_discount"><?php esc_html_e( 'Apply', 'oxapay-crypto-payment-gateway-for-restrict-content' ); ?></button>
                        </p>
                    </fieldset>
                    <?php endif; ?>

                    <?php do_action( 'rcp_after_register_form_fields', $levels ); ?>
                    <div class="rcp_gateway_fields">
                        <?php
                        $gateways = rcp_get_enabled_payment_gateways();
                        if ( count( $gateways ) > 1 ) : ?>
                            <fieldset class="rcp_gateways_fieldset">
                                <p id="rcp_payment_gateways"<?php echo rcp_has_paid_levels() ? '' : ' style="' . esc_attr( 'display: none;' ) . '"'; ?>>
                                    <select name="rcp_gateway" id="rcp_gateway">
                                        <?php foreach ( $gateways as $key => $gateway ) :
                                            $recurring = rcp_gateway_supports( $key, 'recurring' ) ? 'yes' : 'no'; ?>
                                            <option value="<?php echo esc_attr( $key ); ?>" data-supports-recurring="<?php echo esc_attr( $recurring ); ?>">
                                                <?php echo esc_html( $gateway ); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label for="rcp_gateway"><?php esc_html_e( 'Choose Your Payment Method', 'oxapay-crypto-payment-gateway-for-restrict-content' ); ?></label>
                                </p>
                            </fieldset>
                        <?php else: ?>
                            <?php foreach ( $gateways as $key => $gateway ) :
                                $recurring = rcp_gateway_supports( $key, 'recurring' ) ? 'yes' : 'no'; ?>
                                <input type="hidden" name="rcp_gateway" value="<?php echo esc_attr( $key ); ?>" data-supports-recurring="<?php echo esc_attr( $recurring ); ?>" />
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <?php do_action( 'rcp_before_registration_submit_field', $levels ); ?>

                    <p id="rcp_submit_wrap">
                        <input type="hidden" name="rcp_register_nonce" value="<?php echo esc_attr( wp_create_nonce( 'rcp-register-nonce' ) ); ?>" />
                        <?php
                        $button_label = apply_filters( 'rcp_registration_register_button',  'Register');
                        ?>
                        <input type="submit" name="rcp_submit_registration" id="rcp_submit" value="<?php echo esc_attr( $button_label ); ?>" />
                    </p>
                </form>
                <?php
                if (!empty($pay->data->payment_url)) {
                    wp_redirect($pay->data->payment_url);
                    exit; 
                } else {
                    wp_die( esc_html( $pay->message ) ?? __('Something went wrong', 'oxapay-crypto-payment-gateway-for-restrict-content') );
                }                
            }
            else
            {
                wp_die( esc_html( $pay->message ) );
            }
            exit;
        }

        public function OxaPay_Verify()
        {

            if (!isset($_GET['restrict_oxapay_ipn'])) {
                return;
            }

            global $rcp_options, $wpdb, $rcp_payments_db_name;
            $oxapay_payment_data = '';
            if ($_GET['restrict_oxapay_ipn'] == 'oxapay')  {

                $postData = file_get_contents('php://input');
                $data = json_decode($postData, true);
                $apiSecretKey = isset($rcp_options['oxapay_merchant']) ? $rcp_options['oxapay_merchant'] : '';
                $hmacHeader = $_SERVER['HTTP_HMAC'];
                $calculatedHmac = hash_hmac('sha512', $postData, $apiSecretKey);

                if ($calculatedHmac === $hmacHeader) {
                    list($user_id, $subscription_key) = explode('-', $data['order_id'], 2);
                    list($payment_id, $subscription_name) = explode('-', $data['description'], 2);
                    $user_id = intval($user_id);
                    $payment_id = intval($payment_id);
                    $amount = $data['amount'];
                    $payment_method = isset($rcp_options['oxapay_name']) ? $rcp_options['oxapay_name'] : __('OxaPay', 'oxapay-crypto-payment-gateway-for-restrict-content');

                    $new_payment = 1;
                    $table_name = "`" . str_replace( "`", "``", $rcp_payments_db_name ) . "`";

                    // Prepare the query without quotes around %s placeholders
                    $sql = $wpdb->prepare(
                        "SELECT id FROM {$table_name} WHERE `subscription_key` = %s AND `payment_type` = %s",
                        $subscription_key,
                        $payment_method
                    );

                    if ( $wpdb->get_results( $sql ) ) {
                        $new_payment = 0;
                    }
                    unset($GLOBALS['oxapay_new']);
                    $GLOBALS['oxapay_new'] = $new_payment;
                    global $new;
                    $new = $new_payment;

                    if ($new_payment == 1) {

                        $Amount = $amount;
                            
                        if ($data['status'] == 'Paid') {
                            $payment_status = 'completed';
                            $transaction_id = $data['track_id'];
                        } elseif (in_array($data['status'], ['Expired', 'Failed'])) {
                            $payment_status = 'failed';
                            $transaction_id = 0;
                        } else {
                            echo 200;
                            exit;
                        }
                        
                        unset($GLOBALS['oxapay_payment_status']);
                        unset($GLOBALS['oxapay_transaction_id']);
                        unset($GLOBALS['oxapay_subscription_key']);
                        $GLOBALS['oxapay_payment_status'] = $payment_status;
                        $GLOBALS['oxapay_transaction_id'] = $transaction_id;
                        $GLOBALS['oxapay_subscription_key'] = $subscription_key;
                        global $oxapay_transaction;
                        $oxapay_transaction = array();
                        $oxapay_transaction['oxapay_payment_status'] = $payment_status;
                        $oxapay_transaction['oxapay_transaction_id'] = $transaction_id;
                        $oxapay_transaction['oxapay_subscription_key'] = $subscription_key;

                        if ($payment_status == 'completed') {

                            $payment_data = array(
                                'date' => gmdate('Y-m-d H:i:s'),
                                'subscription' => $subscription_name,
                                'gateway' => $payment_method,
                                'subscription_key' => $subscription_key,
                                'amount' => $amount,
                                'user_id' => $user_id,
                                'transaction_id' => $transaction_id,
                            );

                            do_action('RCP_OxaPay_Insert_Payment', $payment_data, $user_id);

                            $rcp_payments = new RCP_Payments();
                            RCP_set_verifications($rcp_payments->insert($payment_data), __CLASS__, $__param);

                            $new_subscription_id = get_user_meta($user_id, 'rcp_subscription_level_new', true);
                            if (!empty($new_subscription_id)) {
                                update_user_meta($user_id, 'rcp_subscription_level', $new_subscription_id);
                            }
                            $membership = (array) rcp_get_memberships()[0];
                            $old_status_level = $membership;
                            $replace = str_replace('\u0000*\u0000', '', json_encode($old_status_level));
                            $replace = json_decode($replace, true);
                            $status = $replace['status'];
                            $idMemberShip = (int) $replace['id'];
                            $arrayMember = array(
                                'status' => 'active',
                            );
                            if ($status == 'pending') {
                                rcp_update_membership($idMemberShip, $arrayMember);
                            } else {
                                rcp_set_status($user_id, 'active');
                            }

                            if (version_compare(RCP_PLUGIN_VERSION, '2.1.0', '<')) {
                                rcp_email_subscription_status($user_id, 'active');
                                if (!isset($rcp_options['disable_new_user_notices'])) {
                                    wp_new_user_notification($user_id);
                                }
                            }

                            update_user_meta($user_id, 'rcp_payment_profile_id', $user_id);

                            update_user_meta($user_id, 'rcp_recurring', 'no');

                            $subscription = rcp_get_subscription_details(rcp_get_subscription_id($user_id));
                            $now_utc = time(); 
                            $expiration_timestamp = strtotime('+' . $subscription->duration . ' ' . $subscription->duration_unit, $now_utc);
                            $expiration_timestamp = strtotime(gmdate('Y-m-d', $expiration_timestamp) . ' 23:59:59 UTC');
                            $member_new_expiration = gmdate('Y-m-d H:i:s', $expiration_timestamp);
                            rcp_set_expiration_date($user_id, $member_new_expiration);
                            delete_user_meta($user_id, '_rcp_expired_email_sent');

                            $log_data = array(
                                'post_title' => __('Confirmed Payment', 'oxapay-crypto-payment-gateway-for-restrict-content'),
                                'post_content' => __('Payment was successful. Transaction Id: ', 'oxapay-crypto-payment-gateway-for-restrict-content') . $transaction_id . __('Payment method: ', 'oxapay-crypto-payment-gateway-for-restrict-content') . $payment_method,
                                'post_parent' => 0,
                                'log_type' => 'gateway_error',
                            );

                            $log_meta = array(
                                'user_subscription' => $subscription_name,
                                'user_id' => $user_id,
                            );

                            $log_entry = WP_Logging::insert_log($log_data, $log_meta);

                            do_action('RCP_OxaPay_Completed', $user_id);
                        }

                        if ($payment_status == 'failed') {

                            $payments = new RCP_Payments();
                            
                            $payment_data = array( 'status' => 'failed' ); 
                            $payments->update( absint( $payment_id ), $payment_data );
                            
                            $log_data = array(
                                'post_title' => __('Failed Payment', 'oxapay-crypto-payment-gateway-for-restrict-content'),
                                'post_content' => __('Payment was Failed', 'oxapay-crypto-payment-gateway-for-restrict-content') . __('Payment method: ', 'oxapay-crypto-payment-gateway-for-restrict-content') . $payment_method,
                                'post_parent' => 0,
                                'log_type' => 'gateway_error',
                            );

                            $log_meta = array(
                                'user_subscription' => $subscription_name,
                                'user_id' => $user_id,
                            );

                            $log_entry = WP_Logging::insert_log($log_data, $log_meta);

                            do_action('RCP_OxaPay_Failed', $user_id);
                        }

                    }
                    add_filter('the_content', array($this, 'OxaPay_Content'));
                    echo 200;
                    exit;

            }
        }

        }

        public function OxaPay_Content($content)
        {

            global $oxapay_transaction, $new;

            $new_payment = isset($GLOBALS['oxapay_new']) ? $GLOBALS['oxapay_new'] : $new;
            $payment_status = isset($GLOBALS['oxapay_payment_status']) ? $GLOBALS['oxapay_payment_status'] : $oxapay_transaction['oxapay_payment_status'];
            $transaction_id = isset($GLOBALS['oxapay_transaction_id']) ? $GLOBALS['oxapay_transaction_id'] : $oxapay_transaction['oxapay_transaction_id'];

            if ($new_payment == 1) {

                $oxapay_data = array(
                    'payment_status' => $payment_status,
                    'transaction_id' => $transaction_id,
                );

                $_SESSION["oxapay_data"] = $oxapay_data;

            } 
            else { 
                $oxapay_payment_data = isset($_SESSION["oxapay_data"]) ? $_SESSION["oxapay_data"] : '';
                $payment_status = isset($oxapay_payment_data['payment_status']) ? $oxapay_payment_data['payment_status'] : '';
                $transaction_id = isset($oxapay_payment_data['transaction_id']) ? $oxapay_payment_data['transaction_id'] : '';
            }

            $message = '';

            if ($payment_status == 'completed') {
                $message = '<br/>' . __('Payment was successful. Transaction Id: ', 'oxapay-crypto-payment-gateway-for-restrict-content') . $transaction_id . '<br/>';
            }

            if ($payment_status == 'failed') {
                $message = '<br/>' . __('Payment was failed. ', 'oxapay-crypto-payment-gateway-for-restrict-content') ;
            }

            return $content . $message;
        }

    }
}
new RCP_OxaPay();

if (!function_exists('RCP_check_verifications')) {
    function RCP_check_verifications($gateway, $params)
    {

        if (!function_exists('rcp_get_payment_meta_db_name')) {
            return;
        }

        if (is_array($params) || is_object($params)) {
            $params = implode('_', (array) $params);
        }
        if (empty($params) || trim($params) == '') {
            return;
        }

        $gateway = str_ireplace(array('RCP_', 'bank'), array('', ''), $gateway);
        $params = trim(strtolower($gateway) . '_' . $params);

        $table = rcp_get_payment_meta_db_name();

        global $wpdb;
        $check = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE meta_key='_verification_params' AND meta_value='%s'", $params));

    }
}

if (!function_exists('RCP_set_verifications')) {
    function RCP_set_verifications($payment_id, $gateway, $params)
    {

        if (!function_exists('rcp_get_payment_meta_db_name')) {
            return;
        }

        if (is_array($params) || is_object($params)) {
            $params = implode('_', (array) $params);
        }
        if (empty($params) || trim($params) == '') {
            return;
        }

        $gateway = str_ireplace(array('RCP_', 'bank'), array('', ''), $gateway);
        $params = trim(strtolower($gateway) . '_' . $params);

        $table = rcp_get_payment_meta_db_name();

        global $wpdb;
        $wpdb->insert($table, array(
            'rcp_payment_id' => $payment_id,
            'meta_key' => '_verification_params',
            'meta_value' => $params,
        ), array('%d', '%s', '%s'));
    }
}
?>
