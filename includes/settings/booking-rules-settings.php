<?php

namespace MPHB\Settings;

class BookingRulesSettings {

	private $defaultCheckInDays   = array( 0, 1, 2, 3, 4, 5, 6 );
	private $defaultCheckOutDays  = array( 0, 1, 2, 3, 4, 5, 6 );
	private $defaultMinStayLength = 1;
	private $defaultMaxStayLength = 0;

	public function getDefaultCheckInDays(){
		return $this->defaultCheckInDays;
	}

	public function getDefaultCheckOutDays(){
		return $this->defaultCheckOutDays;
	}

	public function getDefaultMinStayLength(){
		return $this->defaultMinStayLength;
	}

	public function getDefaultMaxStayLength(){
		return $this->defaultMaxStayLength;
	}

	public function getReservationRules() {
		return array(
			'check_in_days'  => get_option( 'mphb_check_in_days', array() ),
			'check_out_days' => get_option( 'mphb_check_out_days', array() ),
			'min_stay_length'  => get_option( 'mphb_min_stay_length', array() ),
			'max_stay_length'  => get_option( 'mphb_max_stay_length', array() ),
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getDefaultReservationRule(){
		return array(
			'check_in_days'   => $this->getDefaultCheckInDays(),
			'check_out_days'  => $this->getDefaultCheckOutDays(),
			'min_stay_length' => $this->getDefaultMinStayLength(),
			'max_stay_length' => $this->getDefaultMaxStayLength()
		);
	}

	/**
	 *
	 * @return array
	 */
	public function getCustomRules(){
		return get_option( 'mphb_booking_rules_custom', array() );
	}

}
