<?php

namespace MPHB\Entities;

use \MPHB\Utils;

class RoomType {

	/**
	 *
	 * @var int
	 */
	private $id;

	/**
	 *
	 *
	 * @var int
	 */
	private $originalId;

	/**
	 *
	 * @var string
	 */
	private $title;

	/**
	 *
	 * @var int
	 */
	private $adults;

	/**
	 *
	 * @var int
	 */
	private $children;

	/**
	 *
	 * @var string
	 */
	private $bedType;

	/**
	 *
	 * @var float
	 */
	private $size;

	/**
	 *
	 * @var string
	 */
	private $view;

	/**
	 *
	 * @var int[]
	 */
	private $servicesIds;

	/**
	 *
	 * @var \stdClass[]
	 */
	private $categories;

	/**
	 *
	 * @var \stdClass[]
	 */
	private $tags;

	/**
	 *
	 * @var \stdClass[]
	 */
	private $facilities;

	/**
	 * @var array [%Attribute name% => [%Term ID% => %Term title%]]
	 *
	 * @see \MPHB\Repositories\RoomTypeRepository::mapPostToEntity()
	 */
	private $attributes = null;

	/**
	 *
	 * @var int
	 */
	private $imageId;

	/**
	 *
	 * @var int[]
	 */
	private $galleryIds;

	/**
	 *
	 * @var string
	 */
	private $status;

	/**
	 *
	 * @var float
	 */
	private $_defaultPrice;

	/**
	 *
	 * @param array $atts
	 */
	public function __construct( $atts ){
		$this->id			 = $atts['id'];
		$this->originalId	 = $atts['original_id'];

		$this->title		 = $atts['title'];
		$this->adults		 = $atts['adults'];
		$this->children		 = $atts['children'];
		$this->bedType		 = $atts['bed_type'];
		$this->size			 = $atts['size'];
		$this->view			 = $atts['view'];
		$this->servicesIds	 = $atts['services_ids'];
		$this->categories	 = $atts['categories'];
		$this->tags			 = $atts['tags'];
		$this->facilities	 = $atts['facilities'];
		$this->attributes	 = $atts['attributes']; // Still null
		$this->imageId		 = $atts['image_id'];
		$this->galleryIds	 = $atts['gallery_ids'];
		$this->status		 = $atts['status'];
	}

	/**
	 *
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}

	/**
	 *
	 * @return int
	 */
	public function getOriginalId(){
		return $this->originalId;
	}

	/**
	 *
	 * @return string
	 */
	public function getTitle(){
		return $this->title;
	}

	/**
	 * Check is room type has gallery
	 *
	 * @return bool
	 */
	public function hasGallery(){
		return !empty( $this->galleryIds );
	}

	/**
	 * Retrieve ids of gallery's attachments
	 *
	 * @return array
	 */
	public function getGalleryIds(){
		return $this->galleryIds;
	}

	/**
	 * Check is room type has featured image
	 *
	 * @return bool
	 */
	public function hasFeaturedImage(){
		return (bool) $this->imageId;
	}

	/**
	 * Retrieve room type featured image id.
	 *
	 * @return string | int Room type featured image ID or empty string.
	 */
	public function getFeaturedImageId(){
		return $this->imageId;
	}

	/**
	 * Retrieve room type categories terms objects
	 *
	 * @return \stdClass
	 */
	public function getCategories(){
		return $this->categories;
	}

	/**
	 * Retrieve room type tags terms objects
	 *
	 * @return \stdClass
	 */
	public function getTags(){
		return $this->tags;
	}

	/**
	 *
	 * @return string
	 */
	public function getFacilities(){
		return $this->facilities;
	}

	/**
	 *
	 * @return array [%Attribute name% => [%Term ID% => %Term title%]]
	 */
	public function getAttributes(){
		global $mphbAttributes;

		if ( !is_null( $this->attributes ) ) {
			return $this->attributes;
		}

		$this->attributes = array();

		foreach ( $mphbAttributes as $attribute ) {
			$attributeName = $attribute['attributeName'];
			$taxonomyName  = $attribute['taxonomyName'];

			$terms = wp_get_post_terms( $this->id, $taxonomyName );

			if ( !is_wp_error( $terms ) && !empty( $terms ) ) {
				$terms = array_combine( wp_list_pluck( $terms, 'term_id' ), wp_list_pluck( $terms, 'name' ) );
				$this->attributes[$attributeName] = $terms;
			}
		}

		return $this->attributes;
	}

	/**
	 *
	 * @return string
	 */
	public function getView(){
		return $this->view;
	}

	/**
	 *
	 * @param bool $withUnits Optional. Whether to append units to size. Default FALSE.
	 * @return string
	 */
	public function getSize( $withUnits = false ){
		return (string) ( $withUnits ? $this->size . MPHB()->settings()->units()->getSquareUnit() : $this->size );
	}

	/**
	 *
	 * @return string
	 */
	public function getBedType(){
		return $this->bedType;
	}

	/**
	 *
	 * @return int
	 */
	public function getAdultsCapacity(){
		return $this->adults;
	}

	/**
	 *
	 * @return int
	 */
	public function getChildrenCapacity(){
		return $this->children;
	}

	public function getLink(){
		return get_permalink( $this->id );
	}

	/**
	 *
	 * @return bool
	 */
	public function hasServices(){
		return !empty( $this->servicesIds );
	}

	/**
	 * Retrieve services available for this room type
	 *
	 * @return int[]
	 */
	public function getServices(){
		return $this->servicesIds;
	}

	/**
	 *
	 * @return array
	 */
	public function getServicesPriceList(){
		$prices = array();
		foreach ( $this->servicesIds as $serviceId ) {
			$service = MPHB()->getServiceRepository()->findById( $serviceId );
			if ( $service ) {
				$prices[$service->getId()] = $service->getPrice();
			}
		}
		return $prices;
	}

	/**
	 *
	 * @return array
	 */
	public function getDatesHavePrice(){
		$rates = MPHB()->getRateRepository()->findAllActiveByRoomType( $this->originalId );

		$dates = array();
		foreach ( $rates as $rate ) {
			$dates = array_merge( $dates, array_keys( $rate->getDatePrices() ) );
		}

		return $dates;
	}

	/**
	 * Retrieve minimal average price from today to +X(from settings) days.
	 *
	 * @return float
	 */
	public function getDefaultPrice(){

		if ( !isset( $this->_defaultPrice ) ) {

			$defaultPrice = 0.0;

			$rates = MPHB()->getRateRepository()->findAllActiveByRoomType( $this->originalId );

			if ( !empty( $rates ) ) {

				$fromDate	 = new \DateTime( current_time( 'mysql' ) );
				$toDate		 = \MPHB\Utils\DateUtils::cloneModify( $fromDate, sprintf( '+ %d days', MPHB()->settings()->main()->getAveragePricePeriod() ) );

				$prices = array_map( function( $rate) use ($fromDate, $toDate) {
					return $rate->getMinBasePrice( $fromDate, $toDate );
				}, $rates );

				$prices = array_filter( $prices );

				$defaultPrice = !empty( $prices ) ? min( $prices ) : 0.0;
			}

			$this->_defaultPrice = $defaultPrice;
		}

		return $this->_defaultPrice;
	}

	/**
	 * Retrieve minimal price for dates
	 *
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @return float
	 */
	function getDefaultPriceForDates( \DateTime $checkInDate, \DateTime $checkOutDate ){
		$price = 0.0;

		$dates = array(
			'check_in_date'	 => $checkInDate,
			'check_out_date' => $checkOutDate
		);

		$rates = MPHB()->getRateRepository()->findAllActiveByRoomType( $this->originalId, $dates );

		// Don't pass "adults" and "children" - we will use search parameters
		// and will try to get the proper price for searched occupancy
        MPHB()->reservationRequest()->setupSearchParameters()->setupParameters( $dates );

		if ( !empty( $rates ) ) {

			$priceList = array_map( function($rate) use( $checkInDate, $checkOutDate ) {
				return $rate->calcPrice( $checkInDate, $checkOutDate );
			}, $rates );

			$price = !empty( $priceList ) ? min( $priceList ) : 0.0;
		}

		return $price;
	}

	/**
	 *
	 * @return string
	 */
	public function getStatus(){
		return $this->status;
	}

}
