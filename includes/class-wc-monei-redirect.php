<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Redirect Helper Class
 * This class will handle different redirect urls from monei.
 * failUrl : The URL the customer will be directed to after transaction has failed, instead of completeUrl (used in hosted payment page). This allows to provide two different URLs for successful and failed payments.
 * cancelUrl : The URL the customer will be directed to if they decide to cancel payment and return to your website (used in hosted payment page).
 *
 * @since 5.0
 * @version 5.0
 */
class WC_Monei_Redirect {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'woocommerce_cancelled_order', array( $this, 'add_notice_monei_order_cancelled' ) );
		add_action( 'wp', array( $this, 'is_add_payment_method_page' ) );
	}

	/**
	 * When MONEI send us back to get_cancel_order_url_raw()
	 * We need to show message to the customer + save it into the order.
	 *
	 * @param $order_id
	 * @return void
	 */
	public function add_notice_monei_order_cancelled( $order_id ) {
		if ( isset( $_GET['status'] ) && isset( $_GET['message'] ) && 'FAILED' === $_GET['status'] ) {
			$order_id         = absint( $_GET['order_id'] );
			$order            = wc_get_order( $order_id );

			$order->add_order_note( __( 'MONEI Status: ', 'monei' ) . esc_html( $_GET['status'] ) );
			$order->add_order_note( __( 'MONEI message: ', 'monei' ) . esc_html( $_GET['message'] ) );

			wc_add_notice( esc_html( $_GET['message'] ), 'error' );

			WC_Monei_Logger::log( __( 'Order Cancelled: ', 'monei' ) . $order_id );
			WC_Monei_Logger::log( __( 'MONEI Status: ', 'monei' ) . esc_html( $_GET['status'] ) );
			WC_Monei_Logger::log( __( 'MONEI message: ', 'monei' ) . esc_html( $_GET['message'] ) );
		}
	}

	/**
	 * When customer adds a CC on its profile, we need to make a 0 EUR payment in order to generate the payment.
	 * This means, we need to send them to MONEI, and in the callback on success, we end up in payment_method_page.
	 * Once we are in payment_method_page, we need to actually get the token from the API and save it in Woo.
	 */
	public function is_add_payment_method_page() {
		if ( ! is_add_payment_method_page() ) {
			return;
		}

		if ( ! isset( $_GET['id'] ) ) {
			return;
		}

		if ( ! isset( $_GET['status'] ) || 'SUCCEEDED' !== $_GET['status'] ) {
			return;
		}

		$payment_id = filter_input( INPUT_GET, 'id' );
		try {
			$payment        = WC_Monei_API::get_payment( $payment_id );
			$payment_token  = $payment->getPaymentToken();
			$payment_method = $payment->getPaymentMethod();

			// If Token already saved into DB, we just ignore this.
			if ( monei_token_exits( $payment_token ) ) {
				return;
			}

			WC_Monei_Logger::log( 'saving tokent into DB', 'debug' );
			WC_Monei_Logger::log( $payment_method, 'debug' );

			$token = new WC_Payment_Token_CC();
			$token->set_token( $payment_token );
			$token->set_gateway_id( MONEI_GATEWAY_ID );
			$token->set_card_type( $payment_method->getCard()->getBrand() );
			$token->set_last4( $payment_method->getCard()->getLast4() );
			// todo - no expiration and year
			$token->set_expiry_month( '12' );
			$token->set_expiry_year( '2022' );
			$token->set_user_id( get_current_user_id() );
			$token->save();
		} catch ( Exception $e ) {
			wc_add_notice( __( 'Error while adding your payment method to MONEI.', 'monei' ), 'error' );
			WC_Monei_Logger::log( $e->getMessage(), 'error' );
		}
	}

}

new WC_Monei_Redirect();

