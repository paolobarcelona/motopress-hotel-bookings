# Hotel Booking for iBooked Online

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
