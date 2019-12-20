<?php

namespace MPHB\Persistences;

class RoomTypePersistence extends CPTPersistence {

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

}
