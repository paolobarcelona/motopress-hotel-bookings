<?php

namespace MPHB\UserActions;

class UserActions {

	private $actionLinkHelper;
	private $bookingCancellationAction;
	private $bookingConfirmationAction;
    private $bookingViewAction;

	public function __construct(){
		$this->actionLinkHelper			 = new ActionLinkHelper();
		$this->bookingCancellationAction = new BookingCancellationAction();
		$this->bookingConfirmationAction = new BookingConfirmationAction();
        $this->bookingViewAction         = new BookingViewAction();
	}

	/**
	 *
	 * @return ActionLinkHelper
	 */
	public function getActionLinkHelper(){
		return $this->actionLinkHelper;
	}

	/**
	 *
	 * @return BookingCancellationAction
	 */
	public function getBookingCancellationAction(){
		return $this->bookingCancellationAction;
	}

	/**
	 *
	 * @return BookingConfirmationAction
	 */
	public function getBookingConfirmationAction(){
		return $this->bookingConfirmationAction;
	}

    /**
     * @return \MPHB\UserActions\BookingViewAction
     */
    public function getBookingViewAction()
    {
        return $this->bookingViewAction;
    }

}
