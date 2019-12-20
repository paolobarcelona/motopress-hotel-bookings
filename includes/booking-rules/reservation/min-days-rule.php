<?php

namespace MPHB\BookingRules\Reservation;


use MPHB\Utils\DateUtils;

class MinDaysRule extends Rule {

	/**
	 * @var int
	 */
	private $minDays;

	public function __construct( $atts ) {
		parent::__construct( $atts );
		$this->minDays = $atts['min_stay_length'];
	}

	/**
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param int       $roomTypeId
	 *
	 * @return mixed
	 */
	public function verify( \DateTime $checkInDate, \DateTime $checkOutDate, $roomTypeId = 0 ) {
		$nightCount = DateUtils::calcNights( $checkInDate, $checkOutDate );

		return $nightCount >= $this->minDays;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return array_merge( parent::toArray(), array(
			'min_stay_length' => $this->minDays,
		) );
	}

	/**
	 * @return int
	 */
	public function getMinDays() {
		return $this->minDays;
	}

}