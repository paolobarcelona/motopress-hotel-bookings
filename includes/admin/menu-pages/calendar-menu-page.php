<?php

namespace MPHB\Admin\MenuPages;

class CalendarMenuPage extends AbstractMenuPage {

	private $calendar;

	public function addActions(){
		parent::addActions();

		$this->addTitleAction( __( 'New Booking', 'motopress-hotel-booking' ), add_query_arg( 'page', 'mphb_add_new_booking', admin_url( 'admin.php' ) ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminScripts' ), 15 );
	}

	public function setupCalendar(){
		$this->calendar = new \MPHB\BookingsCalendar();
	}

	public function enqueueAdminScripts(){
		if ( $this->isCurrentPage() ) {
			MPHB()->getAdminScriptManager()->enqueue();
		}
	}

	public function render(){
		$this->setupCalendar();
		?>
		<div class="wrap">
			<h1 class="mphb-booking-calendar-title wp-heading-inline"><?php _e( 'Booking Calendar', 'motopress-hotel-booking' ); ?></h1>
			<?php
			$this->calendar->render();
			?>
		</div>
		<?php
	}

	public function onLoad(){

	}

	protected function getMenuTitle(){
		return __( 'Calendar', 'motopress-hotel-booking' );
	}

	protected function getPageTitle(){
		return __( 'Booking Calendar', 'motopress-hotel-booking' );
	}

}
