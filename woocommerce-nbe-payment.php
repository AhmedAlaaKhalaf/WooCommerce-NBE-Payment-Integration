<?php
/**
 * Plugin Name: WooCommerce NBE Payment Gateway
 * Plugin URI:  https://github.com/AhmedAlaaKhalaf/WooCommerce-NBE-Payment-Integration/
 * Description: Custom payment gateway for NBE Hosted Checkout.
 * Version: 1.0.1
 * Author: Ahmed Khalaf
 * Author URI: https://github.com/AhmedAlaaKhalaf/
 * License: GPL-2.0+
 * Text Domain: wc-nbe-payment
 * 
 * @package WC_NBE_Payment
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add debugging function
if (!function_exists('nbe_debug_log')) {
    /**
     * Log debug messages when WP_DEBUG is enabled
     *
     * @param mixed $message Message to log
     */
    function nbe_debug_log($message) {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log('NBE Payment: ' . print_r($message, true));
            } else {
                error_log('NBE Payment: ' . $message);
            }
        }
    }
}

// Hook to add the payment gateway
add_filter('woocommerce_payment_gateways', 'add_nbe_gateway_class');

/**
 * Add NBE Gateway to WooCommerce payment gateways
 *
 * @param array $gateways Existing gateways
 * @return array Modified gateways array
 */
function add_nbe_gateway_class($gateways) {
    $gateways[] = 'WC_NBE_Gateway';
    return $gateways;
}

// Load the class for the payment gateway
add_action('plugins_loaded', 'init_nbe_gateway_class');

/**
 * Initialize the NBE Gateway class
 */
function init_nbe_gateway_class() {
    if (!class_exists('WC_Payment_Gateway')) {
        return; // WooCommerce not active
    }

    /**
     * NBE Payment Gateway Class
     */
    class WC_NBE_Gateway extends WC_Payment_Gateway {
        
        /**
         * Constructor
         */
        public function __construct() {
            $this->id                 = 'nbe_payment';
            $this->method_title       = __('NBE Hosted Checkout', 'wc-nbe-payment');
            $this->method_description = __('Integrates NBE Hosted Checkout with WooCommerce', 'wc-nbe-payment');
            $this->has_fields         = false;
            $this->supports           = ['products'];

            // Load settings fields
            $this->init_form_fields();
            $this->init_settings();

            // Get settings
            $this->title        = $this->get_option('title');
            $this->description  = $this->get_option('description');
            $this->merchant_id  = $this->get_option('merchant_id');
            $this->api_username = $this->get_option('api_username');
            $this->api_password = $this->get_option('api_password');
            $this->test_mode    = $this->get_option('test_mode') === 'yes';

            // Set base URL based on test mode
            $this->base_url = $this->test_mode ? 
                'https://test-nbe.gateway.mastercard.com/' : 
                'https://nbe.gateway.mastercard.com/';
                
            // Save settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            
            // Add callback endpoint
            add_action('woocommerce_api_wc_nbe_gateway', [$this, 'nbe_payment_callback']);
            
            // Add admin notices for missing credentials
            add_action('admin_notices', [$this, 'check_credentials']);
        }

        /**
         * Check if credentials are configured and show admin notice
         */
        public function check_credentials() {
            if ($this->enabled === 'yes') {
                $missing = [];
                
                if (empty($this->merchant_id)) {
                    $missing[] = __('Merchant ID', 'wc-nbe-payment');
                }
                if (empty($this->api_username)) {
                    $missing[] = __('API Username', 'wc-nbe-payment');
                }
                if (empty($this->api_password)) {
                    $missing[] = __('API Password', 'wc-nbe-payment');
                }
                
                if (!empty($missing)) {
                    $settings_url = admin_url('admin.php?page=wc-settings&tab=checkout&section=nbe_payment');
                    echo '<div class="notice notice-error"><p>';
                    echo '<strong>' . __('NBE Payment Gateway:', 'wc-nbe-payment') . '</strong> ';
                    printf(
                        __('Please configure the following credentials in <a href="%s">payment settings</a>: %s', 'wc-nbe-payment'),
                        esc_url($settings_url),
                        implode(', ', $missing)
                    );
                    echo '</p></div>';
                }
            }
        }

        /**
         * Check if gateway is properly configured
         *
         * @return bool
         */
        private function is_configured() {
            return !empty($this->merchant_id) && 
                   !empty($this->api_username) && 
                   !empty($this->api_password);
        }

        /**
         * Handle payment callback from NBE
         */
        public function nbe_payment_callback() {
            $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
            
            if (!$order_id) {
                nbe_debug_log('Invalid order ID in callback');
                wp_die(__('Invalid order', 'wc-nbe-payment'));
            }

            $order = wc_get_order($order_id);
            if (!$order) {
                nbe_debug_log('Order not found: ' . $order_id);
                wp_die(__('Order not found', 'wc-nbe-payment'));
            }

            $result_indicator = isset($_GET['resultIndicator']) ? sanitize_text_field($_GET['resultIndicator']) : '';
            
            // Log callback data
            nbe_debug_log('Payment Callback - Order ID: ' . $order_id);
            nbe_debug_log('Payment Callback - Result Indicator: ' . $result_indicator);

            // Get settings
            if (!$this->is_configured()) {
                nbe_debug_log('Gateway not configured');
                $order->update_status('failed', __('Payment gateway not configured.', 'wc-nbe-payment'));
                wc_add_notice(__('Payment configuration error. Please contact support.', 'wc-nbe-payment'), 'error');
                wp_redirect(wc_get_checkout_url());
                exit;
            }

            // Get session ID
            $session_id = get_post_meta($order_id, '_nbe_session_id', true);

            if ($result_indicator) {
                // Verify payment status with NBE API
                $verify_url = $this->base_url . 'api/rest/version/57/merchant/' . $this->merchant_id . '/order/' . $order_id;
                
                $response = wp_remote_get($verify_url, [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($this->api_username . ':' . $this->api_password),
                    ],
                    'timeout' => 30,
                ]);

                if (!is_wp_error($response)) {
                    $body = json_decode(wp_remote_retrieve_body($response), true);
                    nbe_debug_log('Payment Verification Response: ' . print_r($body, true));

                    if (isset($body['result']) && $body['result'] === 'SUCCESS') {
                        $order->payment_complete();
                        $order->add_order_note(
                            sprintf(
                                __('Payment completed via NBE. Transaction ID: %s', 'wc-nbe-payment'),
                                $result_indicator
                            )
                        );
                        wp_redirect($order->get_checkout_order_received_url());
                        exit;
                    }
                } else {
                    nbe_debug_log('Verification API Error: ' . $response->get_error_message());
                }
            }

            // Payment failed
            $order->update_status('failed', __('Payment verification failed or payment was unsuccessful.', 'wc-nbe-payment'));
            wc_add_notice(__('Payment was unsuccessful. Please try again.', 'wc-nbe-payment'), 'error');
            wp_redirect(wc_get_checkout_url());
            exit;
        }

        /**
         * Define settings fields
         */
        public function init_form_fields() {
            $this->form_fields = [
                'enabled' => [
                    'title'   => __('Enable/Disable', 'wc-nbe-payment'),
                    'type'    => 'checkbox',
                    'label'   => __('Enable NBE Hosted Checkout', 'wc-nbe-payment'),
                    'default' => 'no',
                ],
                'title' => [
                    'title'       => __('Title', 'wc-nbe-payment'),
                    'type'        => 'text',
                    'description' => __('Payment method title shown to customers during checkout.', 'wc-nbe-payment'),
                    'default'     => __('NBE Payment', 'wc-nbe-payment'),
                    'desc_tip'    => true,
                ],
                'description' => [
                    'title'       => __('Description', 'wc-nbe-payment'),
                    'type'        => 'textarea',
                    'description' => __('Payment method description shown to customers during checkout.', 'wc-nbe-payment'),
                    'default'     => __('Pay securely with NBE Hosted Checkout.', 'wc-nbe-payment'),
                    'desc_tip'    => true,
                ],
                'merchant_id' => [
                    'title'       => __('Merchant ID', 'wc-nbe-payment'),
                    'type'        => 'text',
                    'description' => __('Enter your NBE Merchant ID. You can obtain this from your NBE merchant account.', 'wc-nbe-payment'),
                    'default'     => '',
                    'desc_tip'    => true,
                ],
                'api_username' => [
                    'title'       => __('API Username', 'wc-nbe-payment'),
                    'type'        => 'text',
                    'description' => __('Your NBE API username for authentication. Format is usually: merchant.YOUR_MERCHANT_ID', 'wc-nbe-payment'),
                    'default'     => '',
                    'desc_tip'    => true,
                ],
                'api_password' => [
                    'title'       => __('API Password', 'wc-nbe-payment'),
                    'type'        => 'password',
                    'description' => __('Your NBE API password. Keep this secure and never share it.', 'wc-nbe-payment'),
                    'default'     => '',
                    'desc_tip'    => true,
                ],
                'test_mode' => [
                    'title'       => __('Test Mode', 'wc-nbe-payment'),
                    'type'        => 'checkbox',
                    'label'       => __('Enable Test Mode', 'wc-nbe-payment'),
                    'default'     => 'yes',
                    'description' => __('Use NBE test environment for testing transactions. Disable for production.', 'wc-nbe-payment'),
                    'desc_tip'    => true,
                ],
            ];
        }

        /**
         * Process payment and create checkout session
         *
         * @param int $order_id Order ID
         * @return array Payment result
         */
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            
            // Check if gateway is configured
            if (!$this->is_configured()) {
                wc_add_notice(__('Payment gateway is not properly configured. Please contact support.', 'wc-nbe-payment'), 'error');
                nbe_debug_log('Attempted payment with unconfigured gateway');
                return ['result' => 'fail'];
            }
            
            // Ensure order is pending
            $order->update_status('pending', __('Awaiting payment from NBE.', 'wc-nbe-payment'));
            $order->save();
            
            // Create checkout session
            $payload = [
                'apiOperation' => 'CREATE_CHECKOUT_SESSION',
                'interaction' => [
                    'operation' => 'PURCHASE',
                    'returnUrl' => add_query_arg([
                        'wc-api'   => 'wc_nbe_gateway',
                        'order_id' => $order_id
                    ], home_url('/'))
                ],
                'order' => [
                    'amount'      => number_format($order->get_total(), 2, '.', ''),
                    'currency'    => get_woocommerce_currency(),
                    'id'          => (string) $order_id,
                    'description' => sprintf(__('Order #%s', 'wc-nbe-payment'), $order_id),
                ],
            ];
        
            $api_url = $this->base_url . 'api/rest/version/57/merchant/' . $this->merchant_id . '/session';
            
            nbe_debug_log('Creating checkout session for order: ' . $order_id);
            
            $response = wp_remote_post($api_url, [
                'method'    => 'POST',
                'headers'   => [
                    'Authorization' => 'Basic ' . base64_encode($this->api_username . ':' . $this->api_password),
                    'Content-Type'  => 'application/json',
                ],
                'body'      => json_encode($payload),
                'timeout'   => 45,
            ]);
        
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                nbe_debug_log('API Error: ' . $error_message);
                wc_add_notice(__('Payment error: ', 'wc-nbe-payment') . $error_message, 'error');
                return ['result' => 'fail'];
            }
        
            $response_body = json_decode(wp_remote_retrieve_body($response), true);
            nbe_debug_log('Session creation response: ' . print_r($response_body, true));
            
            if (!isset($response_body['session']['id'])) {
                nbe_debug_log('Invalid response from NBE API');
                wc_add_notice(__('Payment error: Invalid response from payment gateway.', 'wc-nbe-payment'), 'error');
                return ['result' => 'fail'];
            }
        
            $session_id = $response_body['session']['id'];
            update_post_meta($order_id, '_nbe_session_id', sanitize_text_field($session_id));
            
            return [
                'result'   => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            ];
        }
    }
}

/**
 * Enqueue scripts for checkout page
 */
add_action('wp_enqueue_scripts', 'nbe_enqueue_scripts');
function nbe_enqueue_scripts() {
    // Only enqueue on checkout page with valid session
    if (is_checkout() && isset($_GET['sessionId'])) {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $session_id = sanitize_text_field($_GET['sessionId']);
        
        if ($order_id && $session_id) {
            $order = wc_get_order($order_id);
            
            if ($order) {
                $gateway_settings = get_option('woocommerce_nbe_payment_settings', []);
                $merchant_id = isset($gateway_settings['merchant_id']) ? sanitize_text_field($gateway_settings['merchant_id']) : '';
                
                if (empty($merchant_id)) {
                    nbe_debug_log('Merchant ID not configured for script loading');
                    return;
                }
                
                $test_mode = isset($gateway_settings['test_mode']) && $gateway_settings['test_mode'] === 'yes';
                $checkout_url = $test_mode ? 
                    'https://test-nbe.gateway.mastercard.com/static/checkout/checkout.min.js' : 
                    'https://nbe.gateway.mastercard.com/static/checkout/checkout.min.js';
                
                // Enqueue NBE checkout script
                wp_enqueue_script('nbe-checkout', $checkout_url, [], null, true);
                
                // Check if custom script exists before enqueuing
                if (file_exists(plugin_dir_path(__FILE__) . 'js/nbe-custom.js')) {
                    wp_enqueue_script('nbe-custom', plugin_dir_url(__FILE__) . 'js/nbe-custom.js', ['jquery'], '1.0.1', true);
                    
                    // Pass data to script
                    wp_localize_script('nbe-custom', 'nbe_params', [
                        'session_id' => $session_id,
                        'order_id' => (string) $order_id,
                        'amount' => number_format($order->get_total(), 2, '.', ''),
                        'currency' => get_woocommerce_currency(),
                        'description' => sprintf(__('Order #%s', 'wc-nbe-payment'), $order_id),
                        'merchant_id' => $merchant_id,
                        'merchant_name' => get_bloginfo('name'),
                        'return_url' => add_query_arg([
                            'wc-api' => 'wc_nbe_gateway',
                            'order_id' => $order_id
                        ], home_url('/'))
                    ]);
                }
            }
        }
    }
}

/**
 * Display payment page template
 */
add_action('template_redirect', 'nbe_payment_page');
function nbe_payment_page() {
    if (isset($_GET['nbe-payment']) && $_GET['nbe-payment'] == '1') {
        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $session_id = isset($_GET['sessionId']) ? sanitize_text_field($_GET['sessionId']) : '';
        
        if (!$order_id || !$session_id) {
            wp_redirect(wc_get_checkout_url());
            exit;
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_redirect(wc_get_checkout_url());
            exit;
        }
        
        // Load payment template if it exists
        $template_path = plugin_dir_path(__FILE__) . 'templates/payment-page.php';
        if (file_exists($template_path)) {
            include($template_path);
            exit;
        }
    }
}

/**
 * Display NBE payment receipt page
 */
add_action('woocommerce_receipt_nbe_payment', 'nbe_payment_receipt_page', 10, 1);
function nbe_payment_receipt_page($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        echo '<p>' . __('Order not found.', 'wc-nbe-payment') . '</p>';
        return;
    }
    
    $session_id = get_post_meta($order_id, '_nbe_session_id', true);
    if (!$session_id) {
        echo '<p>' . __('Payment session not found.', 'wc-nbe-payment') . '</p>';
        return;
    }
    
    // Get gateway settings
    $gateway_settings = get_option('woocommerce_nbe_payment_settings', []);
    $merchant_id = isset($gateway_settings['merchant_id']) ? sanitize_text_field($gateway_settings['merchant_id']) : '';
    
    if (empty($merchant_id)) {
        echo '<div class="woocommerce-error">' . __('Payment gateway configuration error. Please contact administrator.', 'wc-nbe-payment') . '</div>';
        return;
    }
    
    $test_mode = isset($gateway_settings['test_mode']) && $gateway_settings['test_mode'] === 'yes';
    
    // Set correct checkout URL based on test mode
    $checkout_js_url = $test_mode ? 
        'https://test-nbe.gateway.mastercard.com/checkout/version/57/checkout.js' : 
        'https://nbe.gateway.mastercard.com/checkout/version/57/checkout.js';
    
    ?>
    <script src="<?php echo esc_url($checkout_js_url); ?>" 
            data-error="errorCallback"
            data-cancel="cancelCallback"
            data-complete="completeCallback">
    </script>
    
    <script type="text/javascript">
        function errorCallback(error) {
            console.log("Payment error:", error);
            window.location.href = "<?php echo esc_js(wc_get_checkout_url()); ?>?payment_status=error&order_id=<?php echo esc_js($order_id); ?>";
        }
        
        function cancelCallback() {
            console.log("Payment cancelled");
            window.location.href = "<?php echo esc_js(wc_get_checkout_url()); ?>?payment_status=cancelled&order_id=<?php echo esc_js($order_id); ?>";
        }
        
        function completeCallback(resultIndicator, sessionVersion) {
            console.log("Payment completed", resultIndicator);
            window.location.href = "<?php echo esc_js(add_query_arg([
                'wc-api' => 'wc_nbe_gateway',
                'order_id' => $order_id
            ], home_url('/'))); ?>&resultIndicator=" + encodeURIComponent(resultIndicator) + "&sessionVersion=" + encodeURIComponent(sessionVersion);
        }
        
        Checkout.configure({
            session: {
                id: "<?php echo esc_js($session_id); ?>"
            },
            merchant: "<?php echo esc_js($merchant_id); ?>",
            order: {
                amount: <?php echo esc_js(number_format($order->get_total(), 2, '.', '')); ?>,
                currency: "<?php echo esc_js($order->get_currency()); ?>",
                description: "<?php echo esc_js(sprintf(__('Order #%s', 'wc-nbe-payment'), $order_id)); ?>",
                id: "<?php echo esc_js($order_id); ?>"
            },
            interaction: {
                operation: "PURCHASE",
                merchant: {
                    name: "<?php echo esc_js(get_bloginfo('name')); ?>"
                },
                displayControl: {
                    billingAddress: "HIDE",
                    customerEmail: "HIDE",
                    orderSummary: "SHOW",
                    shipping: "HIDE"
                }
            }
        });
        
        // Automatically show payment page
        Checkout.showPaymentPage();
    </script>
    <?php
}