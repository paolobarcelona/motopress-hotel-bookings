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

## In stripe:
- Go to Connect > Accounts
- Get the ID of the connected Hotel Account

## In Wordpress:
- Go to Accommodation > Payment Gateways > Stripe
- Store the ID from stripe to `Stripe Connect Account ID`

## To show the Stripe Connect Stripe Express button, simply use the shortcode below:
- [stripe_connect_onboarding_button]
	- has optional parameters:
		- `text`
		- `class`
		
sample usage:

[stripe_connect_onboarding_button text="Click me to connect to stripe" class="my-css-class"]
