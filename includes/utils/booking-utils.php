<?php

namespace MPHB\Utils;

class BookingUtils
{
    /**
     * @param \MPHB\Entities\Booking $booking
     * @param string|null $language Optional. Language code, "original" (get the
     *     title on default language) or NULL (use current language translation).
     *     NULL by default (current language).
     * @param bool $translateIds Optional. TRUE by default.
     * @return array [Room type ID => Room type title]
     */
    public static function getReservedRoomTypesList($booking, $language = null, $translateIds = true)
    {
        $reservedRooms = $booking->getReservedRooms();
        $roomTypes = array();

        foreach ($reservedRooms as $reservedRoom) {
            $saveId = $roomTypeId = $reservedRoom->getRoomTypeId();

            if ($language !== 'original') {
                $roomTypeId = MPHB()->translation()->translateId($roomTypeId, MPHB()->postTypes()->roomType()->getPostType(), $language);

                if ($translateIds) {
                    $saveId = $roomTypeId;
                }
            }

            if (!array_key_exists($saveId, $roomTypes)) {
                $roomTypes[$saveId] = get_the_title($roomTypeId);
            }
        }

        return $roomTypes;
    }
}
