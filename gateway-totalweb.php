<?php
/**
* Gateway class
**/
class WC_Gateway_Totalweb extends WC_Payment_Gateway {
	/**
	 * Test url
	 */
	var $test_url = 'https://testsecure.totalwebsecure.com/paypage/clear.asp';
	
	/**
	 * Live url
	 */
	var $live_url = 'https://secure.totalwebsecure.com/paypage/clear.asp';
	
	/**
	 * notify url
	 */
	var $notify_url;
	
	function __construct() { 
		global $woocommerce;
		
		$this->id			= 'totalweb';
        $this->icon 		= apply_filters('woocommerce_totalweb_icon', plugins_url('images/SupportedCardswithAmex.png', __FILE__));
        $this->has_fields 	= false;
		$this->method_title = "Total Web Solutions";
		
		// Load the form fields
		$this->init_form_fields();
		
		// Load the settings.
		$this->init_settings();

		// Get setting values
		//$this->enabled 		= $this->settings['enabled'];
		$this->title 		= $this->settings['title'];
		$this->description	= $this->settings['description'];
		$this->login_id		= $this->settings['login_id'];
		$this->md5_hash		= $this->settings['md5_hash'];
		$this->type			= $this->settings['type'];
		$this->tran_mode	= $this->settings['tran_mode'];
		$this->debug		= $this->settings['debug'];
		
		// Logs cause issues in 2.3
		if ($this->debug=='yes_tws') $this->log = $woocommerce->logger();
		
		// Hooks
		add_action('woocommerce_receipt_totalweb', array(&$this, 'receipt_page'));
		
		$this->notify_url = home_url('/');;
		
		if($this->enabled == 'yes') {
			add_action( 'init', array(&$this, 'response_handler') );
		}
		
		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '<' ) ) {

		} else {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_api_wc_gateway_totalweb', array( $this, 'response_handler' ) );
			$this->notify_url   = add_query_arg('wc-api', 'WC_Gateway_Totalweb', $this->notify_url);
		}
		
		if ( !$this->is_valid_for_use() ) $this->enabled = false;
	}
	
	/**
     * Initialize Gateway Settings Form Fields
     */
    function init_form_fields() {
    
    	$this->form_fields = array(
			'enabled' => array(
							'title' => __( 'Enable/Disable', 'woocommerce' ), 
							'label' => __( 'Enable Total Web Solutions Payment Module', 'woocommerce' ), 
							'type' => 'checkbox', 
							'description' => '', 
							'default' => 'no'
						), 
			'title' => array(
							'title' => __( 'Title' ), 
							'type' => 'text', 
							'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ), 
							'default' => __( 'Pay Securely via Debit or Credit Card', 'woocommerce' ),
							'css' => "width: 300px;"
						), 
			'description' => array(
							'title' => __( 'Description', 'woocommerce' ), 
							'type' => 'textarea', 
							'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ), 
							'default' => 'Secure Card Payment via Total Web Solutions PCI Compliant Payment Gateway'
						),  
			'login_id' => array(
							'title' => __( 'API Login ID', 'woocommerce' ), 
							'type' => 'text', 
							'description' => __( 'This is your Total Web Solutions Customer Number.', 'woocommerce' ), 
							'default' => ''
						), 
			'md5_hash' => array(
							'title' => __( 'Secret Password', 'woocommerce' ), 
							'type' => 'text', 
							'description' => __( 'The Secret password to verify transactions', 'woocommerce' ), 
							'default' => ''
						),
			'tran_mode' => array(
							'title' => __( 'Transaction Mode', 'woocommerce' ), 
							'type' => 'select', 
							'description' => __( 'Transaction mode used for processing orders', 'woocommerce' ), 
							'options' => array('live'=>'Live', 'test'=>'Test'),
							'default' => 'test'
						),
			'debug' => array(
						'title' => __( 'Debug', 'woocommerce' ), 
						'type' => 'checkbox', 
						'label' => __( 'Enable logging (<code>woocommerce/logs/totalweb.txt</code>)', 'woocommerce' ), 
						'default' => 'no'
					)
			);
    }
    
    /**
	 * Admin Panel Options 
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 **/
	public function admin_options() {
?>
		<h3><?php _e('Total Web Solutions', 'woocommerce'); ?></h3>
    	<p><?php _e('Total Web Solutions plugin works by sending the user to the Total Web Solutions paypage to enter their payment information.', 'woocommerce'); ?></p>
    	
    	<table class="form-table">
    		<?php $this->generate_settings_html(); ?>
		</table><!--/.form-table-->    	
<?php
    }
	
	
	/**
	 * URL gateway
	 * 
	 */
	function get_gateway_url(){
		if($this->tran_mode == 'live'){
			return $this->live_url;
		}
		return $this->test_url;
	}
	
	/**
     * Check if this gateway is enabled and available in the user's country
     */
    function is_valid_for_use() {
    	/*
        if (!in_array(get_option('woocommerce_currency'), 
        	array('AED', 'AMD', 'ANG', 'ARS', 'AUD', 'AWG', 'AZN', 'BBD', 'BDT', 'BGN'
        		, 'BIF', 'BMD', 'BND', 'BOB', 'BRL', 'BSD', 'BWP', 'BYR', 'BZD', 'CAD'
        		, 'CHF', 'CLP', 'CNY', 'COP', 'CRC', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP'
        		, 'DZD', 'EEK', 'EGP', 'ETB', 'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GIP'
        		, 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HTG', 'HUF', 'IDR', 'ILS'
        		, 'INR', 'ISK', 'JMD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KRW', 'KYD'
        		, 'KZT', 'LAK', 'LBP', 'LKR', 'LTL', 'LVL', 'MAD', 'MDL', 'MNT', 'MOP'
        		, 'MUR', 'MVR', 'MWK', 'MXN', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK'
        		, 'NPR', 'NZD', 'PAB', 'PEN', 'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR'
        		, 'RON', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SEK', 'SGD', 'SHP', 'SLL'
        		, 'SOS', 'STD', 'SVC', 'SZL', 'THB', 'TOP', 'TRY', 'TTD', 'TWD', 'TZS'
        		, 'UAH', 'UGX', 'USD', 'UYU', 'UZS', 'VEF', 'WST', 'XAF', 'XCD', 'XOF'
        		, 'XPF', 'YER', 'ZAR', 'ZMK', 'ZWD'))) 
        	return false;
		 */ 
        return true;
    }
	/**
     * Payment form on checkout page
     */
	function payment_fields() {
?>
		<?php if ($this->tran_mode=='test') : ?><p><?php _e('TEST MODE', 'woocommerce'); ?></p><?php endif; ?>
		<?php if ($this->description) : ?><p><?php echo wpautop(wptexturize($this->description)); ?></p><?php endif; ?>
<?php

	}
	
 	/**
	 * Get args for passing
	 **/
	function get_params( $order) {
		global $woocommerce;
		
		if ($this->debug=='yes_tws') 
			$this->log->add( 'totalweb', 'Generating payment form for order #' . $order->id);
		
		$params = array();
		
		$params = array (
			"x_login"			=> $this->login_id,
			"x_show_form"		=> 'PAYMENT_FORM',			
			'x_relay_response'	=> "TRUE",
            'x_relay_url'     	=> add_query_arg('authorizeListenerSIM', 'relay', $this->notify_url),
            
			//billing
			"x_first_name" 		=> $order->billing_first_name,
			"x_last_name" 		=> $order->billing_last_name,
			"x_address" 		=> $order->billing_address_1,
			"x_city" 			=> $order->billing_city,
			"x_state" 			=> $order->billing_state,
			"x_zip" 			=> $order->billing_postcode,
			"x_country" 		=> $order->billing_country,
			"x_phone" 			=> $order->billing_phone,
			"x_email"			=> $order->billing_email,
			
			//shipping
			"x_ship_to_first_name" 		=> $order->shipping_first_name,
			"x_ship_to_last_name" 		=> $order->shipping_last_name,
			"x_ship_to_address" 		=> $order->shipping_address_1,
			"x_ship_to_city" 			=> $order->shipping_city,
			"x_ship_to_state" 			=> $order->shipping_state,
			"x_ship_to_zip" 			=> $order->shipping_postcode,
			"x_ship_to_country" 		=> $order->shipping_country,
			"x_ship_to_company" 		=> $order->shipping_company,
				
			"x_cust_id" 		=> $order->user_id,
			"x_customer_ip" 	=> $_SERVER['REMOTE_ADDR'],
			"x_invoice_num" 	=> $order->id,
			"x_fp_sequence"		=> $order->order_key,
			"x_amount" 			=> number_format($order->get_total(), 2, '.', ''),
			"x_currency_code"	=> 'GBP',
		);
		
		
		if ( get_option('woocommerce_prices_include_tax')=='yes' || $order->get_order_discount() > 0 ) {
			if ( ( $order->get_total_shipping() + $order->get_shipping_tax() ) > 0 ) {
				$params['x_freight'] = number_format($order->get_total_shipping() + $order->get_shipping_tax(), 2, '.', '');
			}
		} else {
			// Tax
			$params['x_tax'] = $order->get_total_tax();
			
			// Shipping Cost item
			if ($order->get_total_shipping()>0) {
				$params['x_freight'] = number_format($order->get_total_shipping(), 2, '.', '');
			}
		}
		
		return $params;
	}
	
	/**
	 * Get standalone line item hidden field
	 */
	function get_line_item_field($order) {
		
		$line_items = '';
		
		// If prices include tax or have order discounts, send the whole order as a single item
		if ( get_option('woocommerce_prices_include_tax')=='yes' || $order->get_order_discount() > 0 ) {
			$item_names = array();

			if (sizeof($order->get_items())>0) : foreach ($order->get_items() as $item) :
				if ($item['qty']) $item_names[] = $item['name'] . ' x ' . $item['qty'];
			endforeach; endif;
			
			$item_id 		= 1;
			$item_name 		= substr(implode(', ', $item_names), 0, 31);
			$item_desc 		= substr(implode(', ', $item_names), 0, 255);
			$item_qty 		= 1;
			$item_amount 	= (number_format($order->get_total() - $order->get_total_shipping() - $order->get_shipping_tax(), 2, '.', '')); // Include discount
			$item_tax 		= 'NO';
			
			$line_items 	.= '<input type="hidden" name="x_line_item" value="' . esc_attr("{$item_id}<|>{$item_name}<|>{$item_desc}<|>{$item_qty}<|>{$item_amount}<|>{$item_tax}") . '" />';
			
		} else {
			// Cart Contents
			$item_loop = 0;
			if (sizeof($order->get_items())>0) : foreach ($order->get_items() as $item) :
				if ($item['qty']) :

					$item_loop++;
					$product = $order->get_product_from_item($item);

					$item_name 	= $item['name'];

					$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
					$item_desc = $item_meta->display( true, true );
					
					//if ($product->get_sku()) $params['item_number_'.$item_loop] = $product->get_sku();
					$item_id 		= $item_loop;
					$item_name 		= substr($item_name, 0, 31);
					$item_desc 		= substr($item_desc, 0, 255);
					$item_qty 		= $item['qty'];
					$item_amount 	= $order->get_item_total( $item, false );
					
					if($order->get_item_tax( $item, true ) > 0) {
						$item_tax 		= 'YES';
					} else {
						$item_tax 		= 'NO';
					}
					
					$line_items 	.= '<input type="hidden" name="x_line_item" value="' . esc_attr("{$item_id}<|>{$item_name}<|>{$item_desc}<|>{$item_qty}<|>{$item_amount}<|>{$item_tax}") . '" />';
				endif;
			endforeach; endif;
		}

		return $line_items;
	}
	
	/**
	 * Generate the 2checkout button link
	 **/
    function generate_form( $order_id ) {
		global $woocommerce;
		
		$order = new WC_Order( $order_id );
		
		$pay_url = $this->get_gateway_url();
		
		$params = $this->get_params( $order );
		
		$time = time();
        	$fp_hash = AuthorizeNetSIM_Form::getFingerprint($this->login_id, '', $params['x_amount'], $params['x_fp_sequence'], $time);
		
		$params['x_fp_timestamp'] 	= $time;
		$params['x_fp_hash'] 		= $fp_hash;
		
		if ($this->debug=='yes_tws') 
			$this->log->add( 'totalweb', "Params: " . print_r($params,true));
		
		$form = new AuthorizeNetSIM_Form($params);

		ob_start();
?>
<form action="<?php echo $pay_url ?>" method="post" id="totalweb_payment_form">
	<?php echo $form->getHiddenFieldString(); ?>
	<?php echo $this->get_line_item_field($order) ?>
	<input type="submit" class="button button-alt" id="submit_totalweb_payment_form" value="<?php _e('Pay via Total Web Solutions', 'woocommerce') ?>" /> 
	<a class="button cancel" href="<?php echo $order->get_cancel_order_url() ?>"><?php _e('Cancel order &amp; restore cart', 'woocommerce') ?></a>
</form>
<script type="text/javascript">					
	jQuery(function($){
		$("body").block({
			message: '<img src="<?php echo $woocommerce->plugin_url() ?>/assets/images/ajax-loader.gif" alt="Redirecting…" style="float:left; margin-right: 10px;" /><?php _e('Thank you for your order. We are now redirecting you to TWS to make payment.', 'woocommerce') ?>', 
			overlayCSS: 
			{ 
				background: "#fff", 
				opacity: 0.6 
			},
			css: { 
				padding:        20, 
				textAlign:      "center", 
				color:          "#555", 
				border:         "3px solid #aaa", 
				backgroundColor:"#fff", 
				cursor:         "wait",
				lineHeight:		"32px"
			} 
		});
		$("#submit_totalweb_payment_form").click();
	});
</script>
<?php
		$result = ob_get_contents();
		ob_end_clean();
		
		return $result;
	}

	/**
     * Process the payment
     */
	function process_payment($order_id) {
		global $woocommerce;
		
		$order = new WC_Order( $order_id );
		
		return array(
				'result' 	=> 'success',
				'redirect'	=> add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))))
			);
	}

	/**
	 * receipt_page
	 **/
	function receipt_page( $order ) {
		echo '<p>'.__('Thank you for your order, please wait to pay with 2checkout.', 'woocommerce').'</p>';
		echo $this->generate_form( $order );
	}
	
	/**
     * Validate the payment form
     */
	function validate_fields() {
		return true;
	}

	/**
	 * Check response data
	 */
	public function response_handler() {
		global $woocommerce;
		
		if (isset($_GET['authorizeListenerSIM'])) {
			$hdl= $_GET['authorizeListenerSIM']; // handle value
			
			if($hdl == 'relay') {
				@ob_clean();
				
				$this->notify_url = get_permalink(woocommerce_get_page_id('cart'));
				
				if ($this->debug=='yes_tws') { 
					$this->log->add( 'totalweb', "Relay response:" . print_r($_POST,true));
					$this->log->add( 'totalweb', "Login ID:" . $this->login_id . "; md5: " . $this->md5_hash);
				}
				
				$response = new AuthorizeNetSIM($this->login_id, $this->md5_hash);

				if ($response->isAuthorizeNet()) {
					if ($response->approved) {
						$order_id = isset($_POST['x_invoice_num']) ? $_POST['x_invoice_num'] : '';
						if(!empty($order_id)) {
							$order = new WC_Order( $order_id );
							
							$order->add_order_note( __('Total Web Solutions payment completed', 'woocommerce') . ' (Transaction ID: ' . $response->transaction_id . ')' );
							
							if ($this->debug=='yes_tws') 
								$this->log->add( 'totalweb', 'Total Web Solutions payment completed (Transaction ID: ' . $response->transaction_id . ')');
							
							$order->payment_complete();
							$woocommerce->cart->empty_cart();
							
							$redirect = add_query_arg('key', $order->order_key, add_query_arg('order', $order_id, get_permalink(woocommerce_get_page_id('thanks'))));
						} else {
							if ($this->debug=='yes_tws') 
								$this->log->add( 'totalweb', 'Empty Order ID');
							
							$redirect = add_query_arg('authorizeListenerSIM', 'error', $this->notify_url);
							$redirect = add_query_arg('reason_text', __('Error: Empty Order ID', 'woocommerce'), $redirect); // add reeson text
						}
					} else {

						if ($this->debug=='yes_tws') 
							$this->log->add( 'totalweb', sprintf("Error %s: %s", $response->response_reason_code, $response->response_reason_text));
						
						$redirect = add_query_arg('authorizeListenerSIM', 'error', $this->notify_url);
						$redirect = add_query_arg('reason_code', $response->response_reason_code, $redirect); // add reeson code
						$redirect = add_query_arg('reason_text', $response->response_reason_text, $redirect); // add reeson text
						$redirect = add_query_arg('code', $response->response_code, $redirect); // add error code
						
					}

				} else {
					if ($this->debug=='yes_tws') 
						$this->log->add( 'totalweb', "MD5 Hash failed. Check to make sure your MD5 Setting matches the one in admin option");
					
					$redirect = add_query_arg('authorizeListenerSIM', 'error', $this->notify_url);
					$redirect = add_query_arg('reason_text', __("MD5 Hash failed. Check to make sure your MD5 Setting matches the one in admin option", "woocommerce"), $redirect); // add reeson text
				}
				
				$redirect = remove_query_arg('wc-api', $redirect);
				
				echo AuthorizeNetDPM::getRelayResponseSnippet($redirect);
				exit;
			} else { // if error
				$message = $_REQUEST['reason_text'];
				wc_add_notice($message, $notice_type = 'success' );
			}
		}
	}
	
} // end woocommerce_2checkout
