<?php

use MPHB\Payments\Gateways\StripeGatewayCustom;
use Stripe\Stripe;
use Stripe\OAuth as StripeOAuth;

/**
 * Get template part.
 *
 * @param string $slug
 * @param string $name Optional. Default ''.
 */
function mphb_get_template_part( $slug, $atts = array() ){

	$template = '';

	// Look in %theme_dir%/%template_path%/slug.php
	$template = locate_template( MPHB()->getTemplatePath() . "{$slug}.php" );

	// Get default template from plugin
	if ( empty( $template ) && file_exists( MPHB()->getPluginPath( "templates/{$slug}.php" ) ) ) {
		$template = MPHB()->getPluginPath( "templates/{$slug}.php" );
	}

	// Allow 3rd party plugins to filter template file from their plugin.
	$template = apply_filters( 'mphb_get_template_part', $template, $slug, $atts );

	if ( !empty( $template ) ) {
		mphb_load_template( $template, $atts );
	}
}

function mphb_load_template( $template, $templateArgs = array() ){
	if ( $templateArgs && is_array( $templateArgs ) ) {
		extract( $templateArgs );
	}
	require $template;
}

/**
 *
 * @global string $wp_version
 * @param string $type
 * @param bool $gmt
 * @return string
 */
function mphb_current_time( $type, $gmt = 0 ){
	global $wp_version;
	if ( version_compare( $wp_version, '3.9', '<=' ) && !in_array( $type, array( 'timestmap',
			'mysql' ) ) ) {
		$timestamp = current_time( 'timestamp', $gmt );
		return date( $type, $timestamp );
	} else {
		return current_time( $type, $gmt );
	}
}

/**
 * Retrieve a post status label by name
 *
 * @param string $status
 * @return string
 */
function mphb_get_status_label( $status ){
	switch ( $status ) {
		case 'new':
			$label		 = _x( 'New', 'Post Status', 'motopress-hotel-booking' );
			break;
		case 'auto-draft':
			$label		 = _x( 'Auto Draft', 'Post Status', 'motopress-hotel-booking' );
			break;
		default:
			$statusObj	 = get_post_status_object( $status );
			$label		 = !is_null( $statusObj ) && property_exists( $statusObj, 'label' ) ? $statusObj->label : '';
			break;
	}

	return $label;
}

/**
 *
 * @param string $name
 * @param string $value
 * @param int $expire
 */
function mphb_set_cookie( $name, $value, $expire = 0 ){
	setcookie( $name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN );
	if ( COOKIEPATH != SITECOOKIEPATH ) {
		setcookie( $name, $value, $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
	}
}

/**
 *
 * @param string $name
 * @return mixed|null Cookie value or null if not exists.
 */
function mphb_get_cookie( $name ){
	return ( mphb_has_cookie( $name ) ) ? $_COOKIE[$name] : null;
}

/**
 *
 * @param string $name
 * @return bool
 */
function mphb_has_cookie( $name ){
	return isset( $_COOKIE[$name] );
}

function mphb_is_checkout_page(){
	$checkoutPageId = MPHB()->settings()->pages()->getCheckoutPageId();
	return $checkoutPageId && is_page( $checkoutPageId );
}

function mphb_is_search_results_page(){
	$searchResultsPageId = MPHB()->settings()->pages()->getSearchResultsPageId();
	return $searchResultsPageId && is_page( $searchResultsPageId );
}

function mphb_is_single_room_type_page(){
	return is_singular( MPHB()->postTypes()->roomType()->getPostType() );
}

function mphb_is_create_booking_page(){
	return MPHB()->getCreateBookingMenuPage()->isCurrentPage();
}

function mphb_get_thumbnail_width(){
	$width = 150;

	$imageSizes = get_intermediate_image_sizes();
	if ( in_array( 'thumbnail', $imageSizes ) ) {
		$width = (int) get_option( "thumbnail_size_w", $width );
	}

	return $width;
}

/**
 *
 * @param float $price
 * @param array $atts
 * @param string $atts['decimal_separator']
 * @param string $atts['thousand_separator']
 * @param int $atts['decimals'] Number of decimals
 * @param string $atts['currency_position'] Possible values: after, before, after_space, before_space
 * @param string $atts['currency_symbol']
 * @param bool $atts['literal_free'] Use "Free" text instead of 0 price.
 * @param bool $atts['trim_zeros'] Trim decimals zeros.
 * @return string
 */
function mphb_format_price( $price, $atts = array() ){

	$defaultAtts = array(
		'decimal_separator'	 => MPHB()->settings()->currency()->getPriceDecimalsSeparator(),
		'thousand_separator' => MPHB()->settings()->currency()->getPriceThousandSeparator(),
		'decimals'			 => MPHB()->settings()->currency()->getPriceDecimalsCount(),
		'currency_position'	 => MPHB()->settings()->currency()->getCurrencyPosition(),
		'currency_symbol'	 => MPHB()->settings()->currency()->getCurrencySymbol(),
		'literal_free'		 => false,
		'trim_zeros'		 => true,
		'period'			 => false,
		'period_title'		 => '',
		'period_nights'		 => 1,
		'as_html'			 => true
	);

	$atts = wp_parse_args( $atts, $defaultAtts );

	$priceFormat = MPHB()->settings()->currency()->getPriceFormat( $atts['currency_symbol'], $atts['currency_position'], $atts['as_html'] );

	$priceClasses = array( 'mphb-price' );

	if ( $atts['literal_free'] && $price == 0 ) {
		$formattedPrice	 = apply_filters( 'mphb_free_literal', _x( 'Free', 'Zero price', 'motopress-hotel-booking' ) );
		$priceClasses[]	 = 'mphb-price-free';
	} else {
		$negative	 = $price < 0;
		$price		 = abs( $price );
		$price		 = number_format( $price, $atts['decimals'], $atts['decimal_separator'], $atts['thousand_separator'] );
		if ( $atts['trim_zeros'] ) {
			$price = mphb_trim_zeros( $price );
		}
		$formattedPrice = ( $negative ? '-' : '' ) . sprintf( $priceFormat, $price );
	}

    if ( $atts['as_html'] ) {
        $priceClassesStr = join( ' ', $priceClasses );
        $price = sprintf( '<span class="%s">%s</span>', esc_attr( $priceClassesStr ), $formattedPrice );
    } else {
        $price = $formattedPrice;
    }

	if ( $atts['period'] ) {

		$priceDescription	 = _nx( 'per night', 'for %d nights', $atts['period_nights'], 'Ex: $99 for 2 nights', 'motopress-hotel-booking' );
		$priceDescription	 = sprintf( $priceDescription, $atts['period_nights'] );
		$priceDescription	 = apply_filters( 'mphb_price_period_description', $priceDescription, $atts['period_nights'] );

        if ( $atts['as_html'] ) {
            $priceDescription = sprintf( '<span class="mphb-price-period" title="%1$s">%2$s</span>', esc_attr( $atts['period_title'] ), $priceDescription );
        }

		$price = sprintf( '%1$s %2$s', $price, $priceDescription );
	}

	return $price;
}

/**
 *
 * @param float $price
 * @param array $atts
 * @param string $atts['decimal_separator']
 * @param string $atts['thousand_separator']
 * @param int $atts['decimals'] Number of decimals
 * @return string
 */
function mphb_format_percentage( $price, $atts = array() ){

	$defaultAtts = array(
		'decimal_separator'	 => MPHB()->settings()->currency()->getPriceDecimalsSeparator(),
		'thousand_separator' => MPHB()->settings()->currency()->getPriceThousandSeparator(),
		'decimals'			 => MPHB()->settings()->currency()->getPriceDecimalsCount()
	);

	$atts = wp_parse_args( $atts, $defaultAtts );

	$isNegative		 = $price < 0;
	$price			 = abs( $price );
	$price			 = number_format( $price, $atts['decimals'], $atts['decimal_separator'], $atts['thousand_separator'] );
	$formattedPrice	 = ( $isNegative ? '-' : '' ) . $price;

	return '<span class="mphb-percentage">' . $formattedPrice . '%</span>';
}

/**
 * Trim trailing zeros off prices.
 *
 * @param mixed $price
 * @return string
 */
function mphb_trim_zeros( $price ){
	return preg_replace( '/' . preg_quote( MPHB()->settings()->currency()->getPriceDecimalsSeparator(), '/' ) . '0++$/', '', $price );
}

function mphb_trim_decimal_zeros($price)
{
    $separator = preg_quote(MPHB()->settings()->currency()->getPriceDecimalsSeparator());

    $price = preg_replace("/{$separator}0++$/", '', $price);
    $price = preg_replace("/({$separator}[^0]++)0++$/", '$1', $price);

    return $price;
}

/**
 * Get WP Query paged var
 *
 * @return int
 */
function mphb_get_paged_query_var(){
	if ( get_query_var( 'paged' ) ) {
		$paged = absint( get_query_var( 'paged' ) );
	} else if ( get_query_var( 'page' ) ) {
		$paged = absint( get_query_var( 'page' ) );
	} else {
		$paged = 1;
	}
	return $paged;
}

/**
 *
 * @param array $queryPart
 * @param array|null $metaQuery
 * @return array
 */
function mphb_add_to_meta_query( $queryPart, $metaQuery ){

	if ( is_null( $metaQuery ) ) {

		if ( mphb_meta_query_is_first_order_clause( $queryPart ) ) {
			$metaQuery = array( $queryPart );
		} else {
			$metaQuery = $queryPart;
		}

		return $metaQuery;
	}

	if ( !empty( $metaQuery ) && !isset( $metaQuery['relation'] ) ) {
		$metaQuery['relation'] = 'AND';
	}

	if ( isset( $metaQuery['relation'] ) && strtoupper( $metaQuery['relation'] ) === 'AND' ) {

		if ( mphb_meta_query_is_first_order_clause( $queryPart ) ||
			( isset( $queryPart['relation'] ) && strtoupper( $queryPart['relation'] ) === 'OR' )
		) {
			$metaQuery[] = $queryPart;
		} else {
			$metaQuery = array_merge( $metaQuery, $queryPart );
		}
	} else {
		$metaQuery = array(
			'relation' => 'AND',
			$queryPart,
			$metaQuery
		);
	}

	return $metaQuery;
}

/**
 *
 * @param array $query
 * @return bool
 */
function mphb_meta_query_is_first_order_clause( $query ){
	return isset( $query['key'] ) || isset( $query['value'] );
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @param string|array $var
 * @return string|array
 */
function mphb_clean( $var ){
	if ( is_array( $var ) ) {
		return array_map( 'mphb_clean', $var );
	} else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}

/**
 * @see https://github.com/symfony/polyfill-php56
 *
 * @param string $knownString
 * @param string $userInput
 * @return boolean
 */
function mphb_hash_equals( $knownString, $userInput ){

	if ( !is_string( $knownString ) ) {
		return false;
	}

	if ( !is_string( $userInput ) ) {
		return false;
	}

	$knownLen	 = mphb_strlen( $knownString );
	$userLen	 = mphb_strlen( $userInput );

	if ( $knownLen !== $userLen ) {
		return false;
	}

	$result = 0;

	for ( $i = 0; $i < $knownLen; ++$i ) {
		$result |= ord( $knownString[$i] ) ^ ord( $userInput[$i] );
	}

	return 0 === $result;
}

/**
 *
 * @param string $s
 * @return string
 */
function mphb_strlen( $s ){
	return ( extension_loaded( 'mbstring' ) ) ? mb_strlen( $s, '8bit' ) : strlen( $s );
}

/**
 * @todo add support for arrays
 *
 * @param string $url
 * @return array
 */
function mphb_get_query_args( $url ){

	$queryArgs = array();

	$queryStr = parse_url( $url, PHP_URL_QUERY );

	if ( $queryStr ) {
		parse_str( $queryStr, $queryArgs );
	}

	return $queryArgs;
}

/**
 * Wrapper function for wp_dropdown_pages
 *
 * @see wp_dropdown_pages
 *
 * @param array $atts
 * @return string
 */
function mphb_wp_dropdown_pages( $atts = array() ){

	do_action( '_mphb_before_dropdown_pages' );

	$dropdown = wp_dropdown_pages( $atts );

	do_action( '_mphb_after_dropdown_pages' );

	return $dropdown;
}

/**
 * Wrapper for set_time_limit to see if it is enabled.
 *
 * @param int $limit The maximum execution time, in seconds. If set to zero, no time limit is imposed.
 */
function mphb_set_time_limit( $limit = 0 ){
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && !ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}

function mphb_error_log( $message ){
	if ( !is_string( $message ) ) {
		$message = print_r( $message, true );
	}
	error_log( $message );
}

/**
 *
 * @return string
 */
function mphb_current_domain(){
	$homeHost = parse_url( home_url(), PHP_URL_HOST ); // www.booking.coms
	return preg_replace( '/^www\./', '', $homeHost );  // booking.com
}

/**
 * For local usage only. For global IDs it's better to use function
 * mphb_generate_uid().
 *
 * @return string
 */
function mphb_generate_uuid4(){
	// Source: http://php.net/manual/ru/function.uniqid.php#94959
	$uuid4 = sprintf(
		'%04x%04x%04x%04x%04x%04x%04x%04x'
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0x0fff ) | 0x4000
		, mt_rand( 0, 0x3fff ) | 0x8000
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0xffff )
		, mt_rand( 0, 0xffff )
	);
	return $uuid4;
}

function mphb_generate_uid(){
	return mphb_generate_uuid4() . '@' . mphb_current_domain();
}

/**
 * Retrieves the edit post link for post regardless current user capabilities
 *
 * @param int|string $id
 * @return string
 */
function mphb_get_edit_post_link_for_everyone( $id, $context = 'display' ){

	if ( !$post = get_post( $id ) ) {
		return '';
	}

	if ( 'revision' === $post->post_type ) {
		$action = '';
	} elseif ( 'display' == $context ) {
		$action = '&amp;action=edit';
	} else {
		$action = '&action=edit';
	}

	$post_type_object = get_post_type_object( $post->post_type );
	if ( !$post_type_object ) {
		return '';
	}

	if ( $post_type_object->_edit_link ) {
		$link = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
	} else {
		$link = '';
	}

	/**
	 * Filters the post edit link.
	 *
	 * @since 2.3.0
	 *
	 * @param string $link The edit link.
	 * @param int $post_id Post ID.
	 * @param string $context The link context. If set to 'display' then ampersands
	 * are encoded.
	 */
	return apply_filters( 'get_edit_post_link', $link, $post->ID, $context );

	return $link;
}

/**
 *
 * @param int $typeId Room type ID.
 *
 * @return array [%Room ID% => %Room Number%].
 */
function mphb_get_rooms_select_list( $typeId ){
	$rooms = MPHB()->getRoomPersistence()->getIdTitleList( array(
		'room_type_id'	 => $typeId,
		'post_status'	 => 'all'
	) );

	$roomType	 = MPHB()->getRoomTypeRepository()->findById( $typeId );
	$typeTitle	 = ( $roomType ? $roomType->getTitle() : '' );

	if ( !empty( $typeTitle ) ) {
		foreach ( $rooms as &$room ) {
			$room	 = str_replace( $typeTitle, '', $room );
			$room	 = trim( $room );
		}
		unset( $room );
	}

	return $rooms;
}

function mphb_show_multiple_instances_notice(){
	/* translators: %s: URL to plugins.php page */
	$message = __( 'You are using two instances of Hotel Booking plugin at the same time, please <a href="%s">deactivate one of them</a>.', 'motopress-hotel-booking' );
	$message = sprintf( $message, esc_url( admin_url( 'plugins.php' ) ) );

	$html_message = sprintf( '<div class="notice notice-warning is-dismissible">%s</div>', wpautop( $message ) );

	echo wp_kses_post( $html_message );
}

/**
 * @param string $wrapper Optional. Wrapper tag - "span" or "div". "span" by
 *     default. Pass the empty value to remove the wrapper
 * @param string $wrapperClass Optional. "description" by default.
 * @return string "Upgrade to Premium..." HTML.
 */
function mphb_upgrade_to_premium_message($wrapper = 'span', $wrapperClass = 'description')
{
	$message = __('<a href="%s">Upgrade to Premium</a> to enable this feature.', 'motopress-hotel-booking');
	$message = sprintf($message, esc_url(admin_url('admin.php?page=mphb_premium')));

    if (!empty($wrapper)) {
        if ($wrapper === 'div') {
            $message = '<div class="' . esc_attr($wrapperClass) . '">' . $message . '</div>';
        } else {
            $message = '<span class="' . esc_attr($wrapperClass) . '">' . $message . '</span>';
        }
    }

	return $message;
}

/**
 * Season price format history:
 * v2.6.0- - single number.
 * v2.7.1- - ["base", "enable_variations" => "0"|"1", "variations" => ""|[["adults", "children", "price"]]].
 * v2.7.2+ - ["periods", "prices", "enable_variations" => true/false, "variations" => [["adults", "children", "prices"]]].
 *
 * @param mixed $price Price in any format.
 *
 * @return array Price in format 2.7.2+.
 */
function mphb_normilize_season_price( $price ){
	$value = array(
		'periods' => array( 1 ),
		'prices'  => array( 0 ),
		'enable_variations' => false,
		'variations' => array()
	);

	if ( !is_numeric( $price ) && !is_array( $price ) ) {
		return $value;
	}

	if ( is_numeric( $price ) ) {
		// Convert v2.6.0- into v2.7.2+
		$value['prices'][0] = $price;

	} else if ( isset( $price['base'] ) ) {
		// Convert v2.7.1- into v2.7.2+
		$value['prices'][0] = $price['base'];
		$value['enable_variations'] = \MPHB\Utils\ValidateUtils::validateBool( $price['enable_variations'] );

	} else {
		// Merge values from v2.7.2+
		$value['periods'] = $price['periods'];
		$value['prices'] = $price['prices'];
		$value['enable_variations'] = $price['enable_variations'];
	}

	// Merge variations
	if ( isset( $price['variations'] ) && is_array( $price['variations'] ) ) {
		foreach ( $price['variations'] as $variation ) {
			if ( isset( $variation['price'] ) ) {
				// Convert v2.7.1- into v2.7.2+
				$prices = array( $variation['price'] );
			} else {
				// Copy prices from v2.7.2+
				$prices = $variation['prices'];
			}

			$value['variations'][] = array(
				'adults'	 => intval( $variation['adults'] ),
				'children'	 => intval( $variation['children'] ),
				'prices'	 => $prices
			);
		}
	}

	return $value;
}

/**
 * Check if term name is reserved.
 *
 * @param  string $termName Term name.
 *
 * @return bool
 *
 * @see https://codex.wordpress.org/Function_Reference/register_taxonomy#Reserved_Terms
 */
function mphb_is_reserved_term( $termName ){
	$reservedTerms = array(
		'attachment',
		'attachment_id',
		'author',
		'author_name',
		'calendar',
		'cat',
		'category',
		'category__and',
		'category__in',
		'category__not_in',
		'category_name',
		'comments_per_page',
		'comments_popup',
		'customize_messenger_channel',
		'customized',
		'cpage',
		'day',
		'debug',
		'error',
		'exact',
		'feed',
		'fields',
		'hour',
		'link_category',
		'm',
		'minute',
		'monthnum',
		'more',
		'name',
		'nav_menu',
		'nonce',
		'nopaging',
		'offset',
		'order',
		'orderby',
		'p',
		'page',
		'page_id',
		'paged',
		'pagename',
		'pb',
		'perm',
		'post',
		'post__in',
		'post__not_in',
		'post_format',
		'post_mime_type',
		'post_status',
		'post_tag',
		'post_type',
		'posts',
		'posts_per_archive_page',
		'posts_per_page',
		'preview',
		'robots',
		's',
		'search',
		'second',
		'sentence',
		'showposts',
		'static',
		'subpost',
		'subpost_id',
		'tag',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_id',
		'tag_slug__and',
		'tag_slug__in',
		'taxonomy',
		'tb',
		'term',
		'theme',
		'type',
		'w',
		'withcomments',
		'withoutcomments',
		'year'
	);

	return in_array( $termName, $reservedTerms, true );
}

/**
 * @param string $haystack
 * @param string $needle
 * @return bool
 *
 * @author MrHus
 * @link http://stackoverflow.com/a/834355/3918377
 */
function mphb_string_starts_with($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

/**
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 *
 * @author MrHus
 *
 * @see https://stackoverflow.com/a/834355/3918377
 */
function mphb_string_ends_with( $haystack, $needle ){
    $length = strlen( $needle );

    if ( $length == 0 ) {
        return true;
    }

    return ( substr( $haystack, -$length ) === $needle );
}

function mphb_array_disjunction( $a, $b ){
    return array_merge( array_diff( $a, $b ), array_diff( $b, $a ) );
}

/**
 * @return array "publish", and maybe "private", if current user can read
 * private posts.
 */
function mphb_readable_post_statuses(){
	if ( current_user_can( 'read_private_posts' ) ) {
		return array( 'publish', 'private' );
	} else {
		return array( 'publish' );
	}
}

function mphb_db_version()
{
    // Min version "1.0.1" can be found in the upgrader constants
    return get_option('mphb_db_version', '1.0.1');
}

function mphb_db_version_at_least($requiredVersion)
{
    $dbVersion = mphb_db_version();
    return version_compare($dbVersion, $requiredVersion, '>=');
}

function mphb_version_at_least($requiredVersion)
{
    $actualVersion = MPHB()->getVersion();
    return version_compare($actualVersion, $requiredVersion, '>=');
}

/**
 * @see Issue ticket in WordPress Trac: https://core.trac.wordpress.org/ticket/45495
 */
function mphb_fix_blocks_autop()
{
    if (has_filter('the_content', 'wpautop') !== false) {
        remove_filter('the_content', 'wpautop');
        add_filter('the_content', function ($content) {
            if (has_blocks()) {
                return $content;
            }

            return wpautop($content);
        });
    }
}

/**
 * @param string $json JSON string with possibly escaped Unicode symbols (\uXXXX).
 * @return string JSON string with escaped Unicode symbols (\\uXXXX).
 */
function mphb_escape_json_unicodes($json)
{
    return preg_replace('/(\\\\u[0-9a-f]{4})/i', '\\\\$1', $json);
}

/**
 * @return string "/path/to/wordpress/wp-content/uploads/mphb/"
 */
function mphb_uploads_dir()
{
    $uploads = wp_upload_dir();
    return trailingslashit($uploads['basedir']) . 'mphb/';
}

function mphb_create_uploads_dir()
{
    $dir = mphb_uploads_dir();

    if (file_exists($dir)) {
        return;
    }

    // Create .../uploads/mphb/
    wp_mkdir_p($dir);

    // Create .../uploads/mphb/index.php
    @file_put_contents($dir . 'index.php', '<?php' . PHP_EOL);

    // Create .../uploads/mphb/.htaccess
    $htaccess = "Options -Indexes\n"
        . "deny from all\n"
        . "<FilesMatch '\.(jpg|jpeg|png|gif|mp3|ogg)$'>\n"
        . "Order Allow,Deny\n"
        . "Allow from all\n"
        . "</FilesMatch>\n";

    @file_put_contents($dir . '.htaccess', $htaccess);
}

function mphb_verify_nonce($action, $nonceName = 'mphb_nonce')
{
    if (!isset($_REQUEST[$nonceName])) {
        return false;
    }

    $nonce = $_REQUEST[$nonceName];

    return wp_verify_nonce($nonce, $action);
}

function mphb_get_polyfill_for($function)
{
    switch ($function) {
        case 'mb_convert_encoding': require_once MPHB()->getPluginPath('includes/polyfills/mbstring.php'); break;
    }
}

add_action('parse_request', 'authorizeStripeConnect');

function authorizeStripeConnect() {
	global $wp;

	if($wp->request == 'authorize-stripe-connect') {

		$code = $_REQUEST['code'] ?? null;

		if ($code === null) {
			global $wp_query;

			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}

		$message = '';

		try {
			// Set your secret key: remember to change this to your live secret key in production
			// See your keys here: https://dashboard.stripe.com/account/apikeys
			Stripe::setApiKey((new StripeGatewayCustom())->getSecretKey());

			$response = StripeOAuth::token([
				'grant_type' => 'authorization_code',
				'code' => $code,
			]);

			$message = \sprintf('Connection success for %s.', $response->stripe_user_id);
		} catch (Exception $e) {
			$message = $e->getMessage();
		}
		
		// @TODO: redirect if successful, and output message.
		exit();
   }
}

// register shortcode
add_shortcode('stripe_connect_onboarding_button', 'stripeConnectButton');

function stripeConnectButton($atts, $content=null) { 
	$stripeConnectUrl = 'https://connect.stripe.com/express/oauth/authorize';

	$atts = shortcode_atts(array(
        'text' => null,
        'class'  => null,
	), $atts, 'stripe_connect_onboarding_button');

	$platformClientId = (new StripeGatewayCustom())->getPlatformClientId();

	$platformClientIdParameter = \sprintf('client_id=%s', $platformClientId);
	
	$redirectUri = \sprintf('redirect_uri=%s/authorize-stripe-connect',site_url());

	$fullUrl = \sprintf(
		'%s?%s&%s&%s&%s',
		$stripeConnectUrl,
		'response_type=code',
		$platformClientIdParameter,
		$redirectUri,
		'scope=read_write'
	);

	ob_start();
	?>

	<a target="_blank" href="<?php echo $fullUrl; ?>" class="button button-primary <?php echo $atts['class'] ?? ''; ?>">
		<i class="fa fa-cc-stripe"></i> <?php echo $atts['text'] ?? 'Connect to Stripe'; ?>
	</a>

	<?php
	$html = ob_get_clean();
	// Output needs to be return
	return $html;
}
