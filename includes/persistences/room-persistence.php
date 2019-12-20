<?php

namespace MPHB\Persistences;

class RoomPersistence extends RoomTypeDependencedPersistence {

    /**
     * @param array $customAtts Optional. Empty array by default.
     * @return array
     */
    protected function getDefaultQueryAtts($customAtts = array())
    {
        $defaultAtts = array(
            'orderby' => 'menu_order',
            'order'   => 'ASC'
        );

		return parent::getDefaultQueryAtts($defaultAtts);
	}

	/**
	 *
	 * @param array $atts
	 */
	protected function modifyQueryAtts( $atts ){
		$atts = parent::modifyQueryAtts( $atts );
		if ( isset( $atts['post_status'] ) && $atts['post_status'] === 'all' ) {
			$atts['post_status'] = array(
				'publish',
				'pending',
				'draft',
				'future',
				'private'
			);
		}
		return $atts;
	}

    /**
     * @param array $atts
     * @param string $atts['availability'] Optional. Accepts 'free', 'locked',
     * 'booked', 'pending'. Default is 'free'.
     *     free - has no bookings with status complete or pending for this days.
     *     locked - has bookings with status complete or pending for this days.
     *     booked - has bookings with status complete for this days.
     *     pending - has bookings with status pending for this days.
     * @param \DateTime $atts['from_date'] Optional. Default is today.
     * @param \DateTime $atts['to_date'] Optional. Default is today.
     * @param int $atts['count'] Optional. Count of rooms to search.
     * @param int $atts['room_type_id'] Optional. Type of rooms to search.
     * @param int $atts['exclude_booking'] Optional. ID of booking to exclude
     * from locked rooms list.
     * @param array $atts['exclude_rooms'] Optional. IDs of rooms to exclude
     * from locked rooms list.
     * @return string[] Array of room IDs.
     *
     * @global \WPDB $wpdb
     */
    public function searchRooms($atts = array())
    {
        global $wpdb;

        $defaultAtts = array(
            'availability'		 => 'free',
            'from_date'			 => new \DateTime(current_time('mysql')),
            'to_date'			 => new \DateTime(current_time('mysql')),
            'count'				 => null,
            'room_type_id'		 => null,
            'exclude_booking'	 => null,
            'exclude_rooms'		 => null
        );

        $atts = array_merge($defaultAtts, $atts);
        $atts = apply_filters('mphb_search_rooms_query_atts', $atts, $defaultAtts);

        switch ($atts['availability']) {
            case 'free':
                $bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses();
                break;
            case 'booked':
                $bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getBookedRoomStatuses();
                break;
            case 'pending':
                $bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getPendingRoomStatuses();
                break;
            case 'locked':
                $bookingStatuses = MPHB()->postTypes()->booking()->statuses()->getLockedRoomStatuses();
                break;
        }

        $bookingStatuses = "'" . join("','", $bookingStatuses) . "'";

        $select  = array('ID' => "DISTINCT room_id.meta_value AS ID");
        $from    = array('reserved_rooms' => "{$wpdb->posts} AS reserved_rooms");
        $joins   = array(
            'room_id'   => "INNER JOIN {$wpdb->postmeta} AS room_id ON room_id.post_id = reserved_rooms.ID AND room_id.meta_key = '_mphb_room_id'",
            'bookings'  => "INNER JOIN {$wpdb->posts} AS bookings ON bookings.ID = reserved_rooms.post_parent",
            'check_in'  => "INNER JOIN {$wpdb->postmeta} AS check_in ON check_in.post_id = bookings.ID AND check_in.meta_key = 'mphb_check_in_date'",
            'check_out' => "INNER JOIN {$wpdb->postmeta} AS check_out ON check_out.post_id = bookings.ID AND check_out.meta_key = 'mphb_check_out_date'"
        );
        $where   = array(
            $wpdb->prepare("reserved_rooms.post_type = %s", MPHB()->postTypes()->reservedRoom()->getPostType()),
            "reserved_rooms.post_status = 'publish'",
            "bookings.post_status IN ({$bookingStatuses})",
            $wpdb->prepare("check_out.meta_value > %s", $atts['from_date']->format('Y-m-d')),
            $wpdb->prepare("check_in.meta_value < %s", $atts['to_date']->format('Y-m-d'))
        );
        $orderBy = array();

        if (!is_null($atts['exclude_booking'])) {
            $where[] = $wpdb->prepare("bookings.ID != %d", $atts['exclude_bookings']);
        }

        // For "free" we'll handle "room_type_id" and "count" is second query
        if ($atts['availability'] != 'free') {
            if (!is_null($atts['room_type_id'])) {
                $where[] = $wpdb->prepare("EXISTS(SELECT 1 FROM {$wpdb->postmeta} AS room_meta WHERE room_meta.post_id = room_id.meta_value AND room_meta.meta_value = %d LIMIT 1)", $atts['room_type_id']);
            }
            if (!is_null($atts['count'])) {
                $orderBy[] = $wpdb->prepare("LIMIT %d", $atts['count']);
            }
        }

        $query = apply_filters('mphb_search_rooms_query', array(
            'select'   => $select,
            'from'     => $from,
            'joins'    => $joins,
            'where'    => $where,
            'order_by' => $orderBy
        ), $atts);

        $selectStr = implode(', ', $query['select']);
        $table     = reset($query['from']);
        $joinsStr  = implode(' ', $query['joins']);
        $whereStr  = implode(' AND ', $query['where']);
        $orderStr  = implode(' ', $query['order_by']);

        $querySql = "SELECT {$selectStr} FROM {$table} {$joinsStr} WHERE {$whereStr} {$orderStr}";

        $rooms = $wpdb->get_col($querySql);

        if ($atts['availability'] === 'free') {
            $bookedRooms = $rooms;

            $roomAtts = array(
                'fields' => 'ids',
            );

            if ( !empty( $bookedRooms ) ) {
                $roomAtts['post__not_in'] = $bookedRooms;
            }

            if ( !empty( $atts['exclude_rooms'] ) ) {
                if ( isset( $roomAtts['post__not_in'] ) ) {
                    $roomAtts['post__not_in'] = array_merge( $roomAtts['post__not_in'], $atts['exclude_rooms'] );
                } else {
                    $roomAtts['post__not_in'] = $atts['exclude_rooms'];
                }
            }

            if ( !is_null( $atts['room_type_id'] ) ) {
                $roomAtts['room_type_id'] = $atts['room_type_id'];
            }

            if ( !is_null( $atts['count'] ) ) {
                $roomAtts['posts_per_page'] = $atts['count'];
            }

            // @todo Needs order? "ORDER BY rooms.menu_order"?

            $roomAtts = apply_filters('mphb_search_rooms_second_query_atts', $roomAtts, $atts);

            $rooms = $this->getPosts( $roomAtts );
        }

        return $rooms;
    }

	public function isExistsRooms( \DateTime $checkInDate, \DateTime $checkOutDate, $count, $roomTypeId = null ){
		$searchAtts = array(
			'availability'	 => 'free',
			'from_date'		 => $checkInDate,
			'to_date'		 => $checkOutDate,
			'count'			 => (int) $count
		);
		if ( !is_null( $roomTypeId ) ) {
			$searchAtts['room_type_id'] = (int) $roomTypeId;
		}

        $searchAtts = apply_filters('mphb_is_rooms_exist_query_atts', $searchAtts);

		$rooms = $this->searchRooms( $searchAtts );

		return count( $rooms ) >= $count;
	}

	/**
	 *
	 * @param \DateTime $checkInDate
	 * @param \DateTime $checkOutDate
	 * @param array $rooms
	 * @param int|null $roomTypeId
	 *
	 * @return boolean
	 */
	public function isRoomsFree( \DateTime $checkInDate, \DateTime $checkOutDate, $rooms, $roomTypeId = null ){
		$searchAtts = array(
			'availability'	 => 'free',
			'from_date'		 => $checkInDate,
			'to_date'		 => $checkOutDate
		);

		if ( !is_null( $roomTypeId ) ) {
			$searchAtts['room_type_id'] = (int)$roomTypeId;
		}

        $searchAtts = apply_filters('mphb_is_rooms_free_query_atts', $searchAtts);

		$freeRooms = $this->searchRooms( $searchAtts );

		$availableRooms = array_intersect( $rooms, $freeRooms );

		return ( count( $rooms ) == count( $availableRooms ) );
	}

	/**
	 *
	 * @param int $typeId
	 * @return int[]
	 */
	public function findAllIdsByType( $typeId ){
		$allRoomIds = $this->getPosts(
			array(
				'room_type_id'	 => $typeId,
				'post_status'	 => 'all',
				'fields'		 => 'ids',
				'posts_per_page' => -1
			)
		);

		return $allRoomIds;
	}

}
