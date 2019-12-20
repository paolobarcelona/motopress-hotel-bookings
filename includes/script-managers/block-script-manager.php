<?php

namespace MPHB\ScriptManagers;

class BlockScriptManager extends ScriptManager {
    public function __construct() {
        parent::__construct();

        add_filter('block_categories', array($this, 'registerBlockCategory'));
        add_action('init', array($this, 'register'));
    }

    public function registerBlockCategory($categories)
    {
        $categories = array_merge(
            $categories,
            array(
                array(
                    'slug' => 'hotel-booking',
                    'title' => __('Hotel Booking', 'motopress-hotel-booking')
                )
            )
        );

        return $categories;
    }

    public function register()
    {
        wp_register_script('mphb-blocks', $this->scriptUrl('assets/blocks/blocks.min.js'), array('wp-i18n', 'wp-element', 'wp-blocks', 'wp-components', 'wp-editor', 'jquery'), MPHB()->getVersion());

        $roomTypeIds = MPHB()->getRoomTypePersistence()->getPosts(array(
            'fields' => 'ids',
            'post_status' => mphb_readable_post_statuses()
        ));

        wp_localize_script('mphb-blocks', 'MPHBBlockEditor', array(
            'minAdults' => MPHB()->settings()->main()->getMinAdults(),
            'maxAdults' => MPHB()->settings()->main()->getSearchMaxAdults(),
            'minChildren' => MPHB()->settings()->main()->getMinChildren(),
            'maxChildren' => MPHB()->settings()->main()->getSearchMaxChildren(),
            'dateFormat' => MPHB()->settings()->dateTime()->getDateFormatJS(),
            'roomTypeIds' => $roomTypeIds
        ));

        register_block_type('motopress-hotel-booking/availability-search', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderSearch'),
            'attributes' => array(
                'adults' => array('type' => 'number', 'default' => ''),
                'children' => array('type' => 'number', 'default' => ''),
                'check_in_date' => array('type' => 'string', 'default' => ''),
                'check_out_date' => array('type' => 'string', 'default' => ''),
                'attributes' => array('type' => 'string', 'default' => ''),
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/availability-calendar', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderAvailabilityCalendar'),
            'attributes' => array(
                'id' => array('type' => 'string', 'default' => ''),
                'monthstoshow' => array('type' => 'string', 'default' => ''),
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/search-results', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderSearchResults'),
            'attributes' => array(
                'title' => array('type' => 'boolean', 'default' => true),
                'featured_image' => array('type' => 'boolean', 'default' => true),
                'gallery' => array('type' => 'boolean', 'default' => true),
                'excerpt' => array('type' => 'boolean', 'default' => true),
                'details' => array('type' => 'boolean', 'default' => true),
                'price' => array('type' => 'boolean', 'default' => true),
                'view_button' => array('type' => 'boolean', 'default' => true),
                'orderby' => array('type' => 'string', 'default' => 'menu_order'),
                'order' => array('type' => 'string', 'default' => 'DESC'),
                'meta_key' => array('type' => 'string', 'default' => ''),
                'meta_type' => array('type' => 'string', 'default' => ''),
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/rooms', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderRooms'),
            'attributes' => array(
                'title' => array('type' => 'boolean', 'default' => true),
                'featured_image' => array('type' => 'boolean', 'default' => true),
                'gallery' => array('type' => 'boolean', 'default' => true),
                'excerpt' => array('type' => 'boolean', 'default' => true),
                'details' => array('type' => 'boolean', 'default' => true),
                'price' => array('type' => 'boolean', 'default' => true),
                'view_button' => array('type' => 'boolean', 'default' => true),
                'book_button' => array('type' => 'boolean', 'default' => true),
                'ids' => array('type' => 'string', 'default' => ''),
                'posts_per_page' => array('type' => 'string', 'default' => ''),
                'category' => array('type' => 'string', 'default' => ''),
                'tags' => array('type' => 'string', 'default' => ''),
                'relation' => array('type' => 'string', 'default' => 'OR'),
                'orderby' => array('type' => 'string', 'default' => 'menu_order'),
                'order' => array('type' => 'string', 'default' => 'DESC'),
                'meta_key' => array('type' => 'string', 'default' => ''),
                'meta_type' => array('type' => 'string', 'default' => ''),
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/services', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderServices'),
            'attributes' => array(
                'ids' => array('type' => 'string', 'default' => ''),
                'posts_per_page' => array('type' => 'string', 'default' => ''),
                'orderby' => array('type' => 'string', 'default' => 'menu_order'),
                'order' => array('type' => 'string', 'default' => 'DESC'),
                'meta_key' => array('type' => 'string', 'default' => ''),
                'meta_type' => array('type' => 'string', 'default' => ''),
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/room', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderRoom'),
            'attributes' => array(
                'id' => array('type' => 'string', 'default' => ''),
                'title' => array('type' => 'boolean', 'default' => true),
                'featured_image' => array('type' => 'boolean', 'default' => true),
                'gallery' => array('type' => 'boolean', 'default' => true),
                'excerpt' => array('type' => 'boolean', 'default' => true),
                'details' => array('type' => 'boolean', 'default' => true),
                'price' => array('type' => 'boolean', 'default' => true),
                'view_button' => array('type' => 'boolean', 'default' => true),
                'book_button' => array('type' => 'boolean', 'default' => true),
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/checkout', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderCheckout'),
            'attributes' => array(
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/availability', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderBookingForm'),
            'attributes' => array(
                'id' => array('type' => 'string', 'default' => ''),
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/rates', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderRoomRates'),
            'attributes' => array(
                'id' => array('type' => 'string', 'default' => ''),
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));

        register_block_type('motopress-hotel-booking/booking-confirmation', array(
            'editor_script' => 'mphb-blocks',
            'render_callback' => array(MPHB()->getBlocksRender(), 'renderBookingConfirmation'),
            'attributes' => array(
                'alignment' => array('type' => 'string', 'default' => ''),
                'className' => array('type' => 'string', 'default' => '')
            )
        ));
    }

    public function enqueue() {}
}
