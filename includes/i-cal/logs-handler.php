<?php

namespace MPHB\iCal;

class LogsHandler {

	/**
	 * @param array $processDetails [logs, stats => [total, succeed, ...]]
	 *
	 * @see \MPHB\iCal\Importer::addLog()
	 */
	public function display( $processDetails ){
		$logs  = $processDetails['logs'];
		$stats = $processDetails['stats'];

		$this->displayTitle();

		$this->displayStats( $stats );

		$this->displayLogs( $logs );
	}

	public function displayTitle(){
		echo '<h3>';
		_e( 'Process Information', 'motopress-hotel-booking' );
		echo '</h3>';
	}

	/**
	 *
	 * @param array $stats [total, succeed, ...]
	 */
	public function displayStats( $stats ){
		echo '<p class="mphb-import-stats">';
		echo sprintf( __( 'Total bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-total">' . $stats['total'] . '</span>' );
		echo '<br />';
		echo sprintf( __( 'Success bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-succeed">' . $stats['succeed'] . '</span>' );
		echo '<br />';
		echo sprintf( __( 'Skipped bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-skipped">' . $stats['skipped'] . '</span>' );
		echo '<br />';
		echo sprintf( __( 'Failed bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-failed">' . $stats['failed'] . '</span>' );
        echo '<br />';
        echo sprintf( __( 'Removed bookings: %s', 'motopress-hotel-booking' ), '<span class="mphb-removed">' . $stats['removed'] . '</span>' );
		echo '</p>';
	}

	/**
	 * @param array $logs
	 */
	public function displayLogs( $logs = array() ){
		echo '<ol class="mphb-logs">';
		foreach ( $logs as $log ) {
			echo $this->logToHtml( $log );
		}
		echo '</ol>';
	}

	public function displayProgress(){
		echo '<div class="mphb-progress">';
		echo '<div class="mphb-progress__bar"></div>';
		echo '<div class="mphb-progress__text">0%</div>';
		echo '</div>';
	}

	/**
	 *
	 * @param bool $disabled
	 */
	public function displayAbortButton( $disabled = false ){
		$disabledAttr = $disabled ? ' disabled="disabled"' : '';
		echo '<button class="button mphb-abort-process"' . $disabledAttr . '>' . __( 'Abort Process', 'motopress-hotel-booking' ) . '</button>';
	}

	/**
	 *
	 * @param bool $disabled
	 */
	public function displayClearButton( $disabled = false ){
		$disabledAttr = $disabled ? ' disabled="disabled"' : '';
		echo '<button class="button mphb-clear-all"' . $disabledAttr . '>' . __( 'Delete All Logs', 'motopress-hotel-booking' ) . '</button>';
	}

	public function displayExpandAllButton(){
		echo '<button class="button-link mphb-expand-all">' . __( 'Expand All', 'motopress-hotel-booking' ) . '</button>';
	}

	public function displayCollapseAllButton(){
		echo '<button class="button-link mphb-collapse-all">' . __( 'Collapse All', 'motopress-hotel-booking' ) . '</button>';
	}

	/**
	 *
	 * @param array $log Log entry ["status", "message", "context"].
	 * @param bool $inline
	 *
	 * @return string
	 */
	public function logToHtml( $log, $inline = false ){

		$log = array_merge(
			array(
				'status'	 => 'info',
				'message'	 => '',
				'context'	 => array()
			),
			$log
		);

		/**
		 * @var string $status "success", "info", "warning", "error" etc.
		 * @var string $message
		 * @var array $context Event info: [roomId, prodid, uid, checkIn,
         * checkOut, summary, description]; all fields are optional.
		 */
		extract( $log );

		$roomId	= isset( $context['roomId'] ) ? (int) $context['roomId'] : 0;
		$event	= isset( $context['checkIn'] ) && isset( $context['checkOut'] ) ? $context : array();

		$html = '';

		// Add title
		if ( !$inline && $roomId > 0 ) {
			$room = MPHB()->getRoomRepository()->findById( $roomId );
			if ( $room ) {
				$html .= '<b>' . sprintf( '"%1$s" (ID %2$d)', $room->getTitle(), $roomId ) . '</b>';
			} else {
				$html .= '<b>' . sprintf( '(ID %d)', $roomId ) . '</b>';
			}
		}

		// Build "event" part:
		//		%UID%, %checkIn% - %checkOut%
		//		%message%
		$eventHtml = '';
		if ( !empty( $event ) ) {
			$uid      = $event['uid'];
			$checkIn  = str_replace( '-', '', $event['checkIn'] );
			$checkOut = str_replace( '-', '', $event['checkOut'] );

            if ( !empty( $uid ) ) {
                $eventHtml .= '<code>' . "{$uid}, {$checkIn} - {$checkOut}" . '</code><br/>';
            } else {
                $eventHtml .= '<code>' . "{$checkIn} - {$checkOut}" . '</code><br/>';
            }
		}
		$eventHtml .= $message;

		// Add "event" part to result HTML
		if ( !empty( $eventHtml ) && !$inline ) {
            $class = ' class="notice notice-' . $status . '"';

			$html .= '<p' . $class . '>';
			$html .= $eventHtml;
			$html .= '</p>';

		} else {
            $html .= $eventHtml;
        }

		return ( !empty( $html ) && !$inline ) ? '<li>' . $html . '</li>' : $html;
	}

	/**
	 * Build HTML for each log.
	 *
	 * @param array $logs
	 * @param bool $inline
	 *
	 * @return array
	 */
	public function logsToHtml( $logs, $inline = false ){
		$logsHtml = array();
		foreach ( $logs as $log ) {
			$logsHtml[] = $this->logToHtml( $log, $inline );
		}
		return $logsHtml;
	}

	public function buildNotice( $succeedCount, $failedCount ){
		$message  = _n( 'All done! %1$d booking was successfully added.', 'All done! %1$d bookings were successfully added.', $succeedCount, 'motopress-hotel-booking');
		$message .= _n( ' There was %2$d failure.', ' There were %2$d failures.', $failedCount, 'motopress-hotel-booking' );
		$message  = sprintf( $message, $succeedCount, $failedCount );

		$notice = '<div class="updated notice notice-success is-dismissible">';
		$notice .= '<p>' . $message . '</p>';
		$notice .= '</div>';

		return $notice;
	}

}
