<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Monei_API_Handler {
	private $test_mode;
	private $api_base_url;
	private $preauth;
	private $auth_params;

	public static $success_codes = array(
		'000.000.000',
		'000.000.100',
		'000.100.110',
		'000.100.111',
		'000.100.112',
		'000.300.000'
	);

	public function __construct( $token, $preauth ) {
		$credentials        = json_decode( _base64_decode( $token ) );
		$this->test_mode    = $credentials->t;
		$this->api_base_url = $this->test_mode ? "https://test.monei-api.net" : "https://monei-api.net";
		$this->preauth      = $preauth;
		$this->auth_params  = array(
			'authentication.userId'   => $credentials->l,
			'authentication.password' => $credentials->p,
			'authentication.entityId' => $credentials->c,
		);
	}

	private function handle_api_response( $raw_response ) {
		if ( is_wp_error( $raw_response ) ) {
			$error_message = $raw_response->get_error_message();

			new WP_Error( 'monei-api', $error_message );

			return false;
		}
		$code = $raw_response['response']['code'];
		if ( $code < 200 || $code >= 300 ) {
			new WP_Error( 'monei-api', $raw_response['response']['message'] );

			return false;
		}

		return json_decode( $raw_response['body'] );
	}

	public function prepare_checkout( $order ) {
		$order_id     = $order->get_id();
		$amount       = $order->get_total();
		$currency     = $order->get_currency();
		$customer_id  = $order->get_customer_id();
		$order_data   = $order->get_data();
		$billing      = $order_data['billing'];
		$shipping     = $order_data['shipping'];
		$payment_type = $this->preauth ? 'PA' : 'DB';
		$params       = array_merge( $this->auth_params, array(
			'amount'                      => $amount,
			'currency'                    => $currency,
			'merchantInvoiceId'           => $order_id,
			'paymentType'                 => $payment_type,
			'customer.merchantCustomerId' => $customer_id,
			'customer.email'              => $billing['email'],
			'customer.givenName'          => $billing['first_name'],
			'customer.surname'            => $billing['last_name'],
			'customer.phone'              => $billing['phone'],
			'customer.companyName'        => $billing['company'],
			'billing.country'             => $billing['country'],
			'billing.state'               => $billing['state'],
			'billing.city'                => $billing['city'],
			'billing.postcode'            => $billing['postcode'],
			'billing.street1'             => $billing['address_1'],
			'billing.street2'             => $billing['address_2'],
			'shipping.country'            => $shipping['country'],
			'shipping.state'              => $shipping['state'],
			'shipping.city'               => $shipping['city'],
			'shipping.postcode'           => $shipping['postcode'],
			'shipping.street1'            => $shipping['address_1'],
			'shipping.street2'            => $shipping['address_2'],
			'customParameters'            => array(
				'customerNote'      => $order_data['customer_note'],
				'customerUserAgent' => $order_data['customer_user_agent']
			)
		) );
		$url          = add_query_arg( $params, $this->api_base_url . "/v1/checkouts" );

		return $this->handle_api_response( wp_safe_remote_post( $url ) );
	}

	public function refund_transaction( $order ) {
		$payment_type  = $this->preauth ? 'RV' : 'RF';
		$transactionId = $order->get_transaction_id();
		$amount        = $order->get_total();
		$currency      = $order->get_currency();
		$url           = add_query_arg( array_merge( $this->auth_params, array(
			'amount'      => $amount,
			'currency'    => $currency,
			'paymentType' => $payment_type,
		) ), $this->api_base_url . "/v1/payments/" . $transactionId );

		return $this->handle_api_response( wp_safe_remote_post( $url ) );
	}

	public function capture_transaction( $order ) {
		$transactionId = $order->get_transaction_id();
		$amount        = $order->get_total();
		$currency      = $order->get_currency();
		$url = add_query_arg( array_merge( $this->auth_params, array(
			'amount'      => $amount,
			'currency'    => $currency,
			'paymentType' => 'CP',
		) ), $this->api_base_url . "/v1/payments/" . $transactionId );

		return $this->handle_api_response( wp_safe_remote_post( $url ) );
	}

	public function get_transaction_status( $resource_path ) {
		$url = add_query_arg( $this->auth_params, $this->api_base_url . $resource_path );

		return $this->handle_api_response( wp_safe_remote_get( $url ) );
	}

	public function is_transaction_successful( $response ) {
		return in_array( $response->result->code, self::$success_codes );
	}
}