<?php

namespace MPHB\Utils;

class ThirdPartyPluginsUtils {

	/**
	 * Check is plugin active.
	 *
	 * @param string $pluginSubDirSlashFile
	 * @return bool
	 */
	public static function isPluginActive( $pluginSubDirSlashFile ){
		if ( !function_exists( 'is_plugin_active' ) ) {
			/**
			 * Detect plugin. For use on Front End only.
			 */
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		return is_plugin_active( $pluginSubDirSlashFile );
	}

	/**
	 * Check is active WooCommerce
	 *
	 * @return bool
	 */
	public static function isActiveWoocommerce(){
		return self::isPluginActive( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Check is active Hotel Booking WooCommerce Payments
	 *
	 * @return bool
	 */
	public static function isActiveMphbWoocommercePayments(){
		return self::isPluginActive( 'mphb-woocommerce/mphb-woocommerce.php' );
	}

	/**
	 * Check is active Hotel Booking Payment Request
	 *
	 * @return bool
	 */
	public static function isActiveMphbPaymentRequest(){
		return self::isPluginActive( 'mphb-request-payment/mphb-request-payment.php' );
	}

	/**
	 * Check is active Hotel Booking Reviews
	 *
	 * @return bool
	 */
	public static function isActiveMphbReviews(){
		return self::isPluginActive( 'mphb-reviews/mphb-reviews.php' );
	}

	/**
	 * Check is active Easy Digital Downloads
	 *
	 * @return bool
	 */
	public static function isActiveEDD(){
		return self::isPluginActive( 'easy-digital-downloads/easy-digital-downloads.php' );
	}

	/**
	 * Check is active Hotel Booking
	 *
	 * @return bool
	 */
	public static function isActiveMphb(){
		return self::isPluginActive( 'motopress-hotel-booking/motopress-hotel-booking.php' );
	}

	/**
	 * Check is active Hotel Booking Lite
	 *
	 * @return bool
	 */
	public static function isActiveMphbLite(){
		return self::isPluginActive( 'motopress-hotel-booking-lite/motopress-hotel-booking.php' );
	}

}
