# Hotel Booking for iBooked Online

## Upon installation, setup the ff:
	### ACCOMODATION
		- `Accomodation > Settings > Payment Gateways > Stripe`
		- Populate the public and secret keys
		- Populate `Hotel Stripe Connect Account ID`: this is the account id which the main payment goes to - ultimately, the hotel.
		- Populate `Main Connect Account ID`: this is the account id which you want commissions to be transferred.
		- Choose Commission type (exact / percentage)
		- Populate commission rate.

	### BOOKINGS
		- `Taxes and Fees > Add new Processing Fees`
		- Add necessary fees inteded for stripe.

## Files to check:
	- `/wp-content/plugins/motopress-hotel-booking-custom/includes/payments/gateways/stripe-gateway-custom.php`
	- `/wp-content/plugins/motopress-hotel-booking-custom/includes/payments/gateways/stripe/stripe-api6.php`

## For custom plugin:

-`/wp-content/plugins/motopress-hotel-booking-custom/includes/payments/gateways/gateway-manager.php`
	-- `processPayment()`
	-- `processCardPayment()`
- `/wp-content/plugins/motopress-hotel-booking-custom/includes/payments/gateways/stripe-gateway-custom.php`
	-- `chargePayment()`

## In stripe:
- Go to Connect > Accounts > Create a connect account
- Supply the necessary fields
- set payout schedule to manual
- get the ID, and store in wordpress