<?php

function woo_pp_start_checkout() {
	$checkout = wc_gateway_ppec()->checkout;

	try {
		$redirect_url = $checkout->startCheckoutFromCart();
		wp_safe_redirect( $redirect_url );
		exit;
	} catch( PayPal_API_Exception $e ) {
		$final_output = '';
		foreach ( $e->errors as $error ) {
			$final_output .= '<li>' . __( $error->mapToBuyerFriendlyError(), 'woocommerce-gateway-paypal-express-checkout' ) . '</li>';
		}
		wc_add_notice( __( 'Payment error:', 'woocommerce-gateway-paypal-express-checkout' ) . $final_output, 'error' );

		$redirect_url = WC()->cart->get_cart_url();
		$settings = wc_gateway_ppec()->settings->loadSettings();

		if( 'yes' == $settings->enabled && $settings->enableInContextCheckout && $settings->getActiveApiCredentials()->get_payer_id() ) {
			ob_end_clean();
			?>
			<script type="text/javascript">
				if( ( window.opener != null ) && ( window.opener !== window ) &&
						( typeof window.opener.paypal != "undefined" ) &&
						( typeof window.opener.paypal.checkout != "undefined" ) ) {
					window.opener.location.assign( "<?php echo $redirect_url; ?>" );
					window.close();
				} else {
					window.location.assign( "<?php echo $redirect_url; ?>" );
				}
			</script>
			<?php
			exit;
		} else {
			wp_safe_redirect( $redirect_url );
			exit;
		}

	}
}
