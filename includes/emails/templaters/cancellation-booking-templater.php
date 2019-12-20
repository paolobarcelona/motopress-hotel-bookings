<?php

namespace MPHB\Emails\Templaters;

class CancellationBookingTemplater extends AbstractTemplater {

	/**
	 *
	 * @var \MPHB\Entities\Booking
	 */
	protected $booking;

	/**
	 *
	 * @param \MPHB\Entities\Booking $booking
	 */
	public function process( $booking ){
		if ( !MPHB()->settings()->main()->canUserCancelBooking() ) {
			return '';
		}
		$content = '';

		$this->booking = $booking;

		$template = MPHB()->settings()->emails()->getCancellationDetailsTemplate();

		return $this->replaceTags( $template );
	}

	public function replaceTag( $match ){
		$tag = str_replace( '%', '', $match[0] );

		$replaceText = '';

		switch ( $tag ) {
			case 'user_cancel_link':
				if ( isset( $this->booking ) ) {
					$replaceText = MPHB()->userActions()->getBookingCancellationAction()->generateLink( $this->booking );
				}
				break;
		}

		return $replaceText;
	}

	public function setupTags(){
		$tags = array(
			array(
				'name'			 => 'user_cancel_link',
				'description'	 => __( 'User Cancellation Link', 'motopress-hotel-booking' ),
			),
		);

		foreach ( $tags as $tagDetails ) {
			$this->addTag( $tagDetails['name'], $tagDetails['description'], $tagDetails );
		}
	}

}
