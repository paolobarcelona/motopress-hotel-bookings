# Hotel Booking for iBooked Online

## Upon installation, setup the ff:
	### ACCOMODATION
		- `Accomodation > Settings > Payment Gateways > Stripe`
		- Populate the public and secret keys
		- Populate Platform Client ID (can be found here: https://dashboard.stripe.com/account/applications/settings, the 
		`Live mode client ID`
		- Choose Commission type (exact / percentage)
		- Populate commission rate.
		- For `Stripe Connect Account ID`
			- Go to Connect > Accounts
			- Get the ID of the connected Hotel Account		
			- Go back to wordpress.
			- Go to Accommodation > Payment Gateways > Stripe
        	        - Store the ID from stripe to `Stripe Connect Account ID`

## To show the Stripe Connect Stripe Express button, simply use the shortcode below:
- [stripe_connect_onboarding_button]
	- has optional parameters:
		- `text`
		- `class`
		
sample usage:

[stripe_connect_onboarding_button text="Click me to connect to stripe" class="my-css-class"]
