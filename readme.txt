=== Hotel Booking ===
Contributors: MotoPress
Donate link: https://motopress.com/
Tags: hotel booking, reservation, hotel, booking engine, booking, booking calendar, booking system
Requires at least: 4.6
Tested up to: 5.3
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The #1 Hotel Booking and Vacation Rental Plugin for WordPress. Online payments, seasons, rates, free or paid extras, coupons, taxes & fees.

== Description ==
Manage your hotel booking services. Perfect for hotels, villas, guest houses, hostels, and apartments of all sizes.

== Installation ==

1. Upload the MotoPress plugin to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Screenshots ==


== Copyright ==

Hotel Booking plugin, Copyright (C) 2019, MotoPress https://motopress.com/
Hotel Booking plugin is distributed under the terms of the GNU GPL.


== Credits ==

* Beanstream, https://github.com/bambora-na/beanstream-php/, Copyright (c) 2014 Pavel Kulbakin, MIT License.
* Spectrum Colorpicker, https://github.com/bgrins/spectrum, Author: Brian Grinstead, MIT License.
* Braintree, https://github.com/braintree/braintree_php, Copyright 2015 Braintree, a division of PayPal, Inc., MIT License.
* CanJS, http://canjs.com/, Copyright 2016 Bitovi, MIT License.
* SerializeJSON jQuery plugin, https://github.com/marioizquierdo/jquery.serializeJSON, Copyright (c) 2012, 2015 Mario Izquierdo, Dual licensed under the MIT and GPL licenses.
* Datepick, http://keith-wood.name/datepick.html by Keith Wood Licensed under the MIT licence.
* Magnific Popup, http://dimsemenov.com/plugins/magnific-popup/, Copyright 2016 Dmitry Semenov, MIT License.
* jQuery FlexSlider, https://github.com/woocommerce/FlexSlider, Copyright 2012 WooThemes,  Contributing Author: Tyler Smith, GNU General Public License v2.0.


== Changelog ==

= 3.7.1, Oct 30 2019 =
* Improved blocks compatibility with the new versions of the Gutenberg editor.
* Added customer email address to the Stripe payment details.
* Fixed an issue where the price breakdown was not displayed in the new booking emails.
* Fixed an issue at checkout when coupon discount was not applied to the total price at the bottom of the page.
* Fixed a bug concerning impossibility to complete Stripe payment after applying the coupon code.
* Fixed an issue where the type of the coupon code was changed after its use.

= 3.7.0, Sep 17 2019 =
* Improved the "Booking Confirmed" page with regard to displaying information on client's booking and payment in case the booking is paid online. Follow the prompts to update the content of the "Booking Confirmed" page automatically or apply the changes manually.
* Added the new email tag, which allows guests to visit their booking details page directly from the email. Important: you need to update your email templates to start using this functionality.
* New actions and filters were added for developers.
* Fixed the issue at checkout when a variable price was not applied if capacity is disabled in plugin settings.

= 3.6.1, Aug 19 2019 =
* Added Direct Bank Transfer as a new payment gateway.
* Added the ability to delete ical synchronization logs automatically.
* Added new intervals for importing bookings through the ical "Quarter an Hour" and "Half an Hour".
* The user information is no longer required while creating a booking in the admin panel. You can enable it again in the settings.
* Added new tags for email templates: Price Breakdown, Country, State, City, Postcode, Address, Full Guest Name.
* Added the ability to select the accommodation type while duplicating rates.
* Improvement: now if the accommodation type size is not set, the field will not be displayed on the website.

= 3.6.0, Aug 13 2019 =
* Implemented bookings synchronization with Expedia travel booking website.
* Updated PayPal and Stripe payment integrations to comply with PSD2 and the SCA requirements.
* Added the ability to receive payments through Bancontact, iDEAL, Giropay, SEPA Direct Debit and SOFORT payment gateways via the updated Stripe API.

= 3.5.1, Jun 25 2019 =
* Improved compatibility with WPML plugin.
* Improved compatibility with PHP 7.3.
* Improved compatibility with MySQL 8.
* Added the ability to sort bookings by check-in / check-out date in the Bookings table.

= 3.5.0, Jun 17 2019 =
* Added the ability to export bookings data in the CSV format.
* Added the ability to preset the first value of the Attributes list instead of an empty string for the search form field.
* Fixed the issue with incorrect results when searching through Attributes.
* Fixed the issue at checkout when a variable price was displayed instead of the base one.
* Fixed the issue with the Price Breakdown table encoding.
* Fixed the issue when the room_type_categories was displayed as Array.
* Improved management of the variable pricing: it is now possible to add price variations in a random order.
* Added css classes for the Price Breakdown table.
* Updated the list of available currencies for PayPal.

= 3.4.0, May 22 2019 =
* Major improvements on booking synchronization with online channels via iCal interface.
* Added option for filtering and hiding imported bookings in the Bookings table. 
* Added option for including Blocked Accommodations to exportable calendars.
* Minor bugfixes and improvements.

= 3.3.0, Apr 5 2019 =
* Improved compatibility with WPML plugin.
* Fixed the bug appeared while calculating the subtotal amount in Price Breakdown when a discount code is applied.
* Added Hotel Booking Extensions page. Developers may opt-out of displaying this page via "mphb_show_extension_links" filter.

= 3.2.0, Mar 25 2019 =
* Booking Calendar improvements:
  * Tooltip extended with the customer data: full name, email, phone, guests number, imported bookings info.
  * Added a popup option to display the detailed booking information.
* Bookings table improvements:
  * Added a column with booked accommodations.
  * Added the ability to filter bookings by accommodation type.
  * Added the ability to search bookings by First Name, Last Name, Check-in Date, Check-out Date, Phone, Price, etc.
* Added a Service option that enables to specify the number of times guest would like to order this service.

= 3.1.0, Feb 12 2019 =
* Added support for WordPress 5.0:
  * Added new blocks to Gutenberg.
  * Added option to switch to the new block editor for Accommodation Types and Services in plugin settings.
* Added option to set the Price Breakdown to be unfolded by default.
* Improved design of Accommodation titles in Price Breakdown for better user experience.

= 3.0.3, Dec 24 2018 =
* Minor bugfixes and improvements.

= 3.0.2, Nov 12 2018 =
* Enhanced user interface of the New Booking section on the back-end.
* Added text field for specifying the Administrator Email. Used in cases when email of the actual booking manager is different from email of the site admin.
* Increased number of digits (up to 4) to be entered after decimal point for the Taxes field.
* Significantly improved way of presentation of the booking synchronization logs data. The synchronization logs were transferred into the separate database tables. Added pagination for viewing synchronization results.

= 3.0.1, Oct 3 2018 =
* Improved implementation of content visibility option for Accommodation Types.
* Changed the order of address fields on the checkout page.
* 'State' field on the checkout page renamed as 'State/County'.

= 3.0.0, Sep 12 2018 =
* Introducing attributes. By using the attributes you are able to define extra accommodation data such as location and type and use these attributes in the search availability form as advanced search filters.
* Improved the way to display the booking rules in the availability calendar.
* Added the new payment method to pay on arrival.
* Added the ability to create fixed amount coupon codes.
* Added the availability to send multiple emails to notify the administrator and other staff about new booking.
* Fixed the bug appeared in the Braintree payment method if a few plugins for making payment are set up.
* Added the ability to set the default country on the checkout page.

= 2.7.6, Jul 27 2018 =
* A new way to display available/unavailable dates in a calendar using a diagonal line (half-booked day). This will properly show your guests that they are able to use the same date as check in/out one.
* Disabled predefined parameters for Adults and Children on the checkout page to let guests have more perceived control over options they choose.
* Fixed the issue with booking rules and WPML. Now all translations of accommodations are not displayed in a list and the booking rules are applied to all translations.
* Fixed the issue with Stripe when creating a booking from the backend.
* Fixed the issue with the booking rules not applying while checking an accommodation availability with the "Skip search results" enabled.
* Added a new feature "Guest Management". It is currently in beta and applied only to the frontend. Here are the main options of this feature:
  * Hide "adults" and "children" fields within search availability forms.
  * Disable "children" option for the website (hide "children" field and use Guests label instead).
  * Disable "adults" and "children" options.
* Replaced "Per adult" label with a more catch-all term "per guest" for Services.

= 2.7.5, Jul 18 2018 =
* Increased the number of digits after comma for setting a per-night price. This will help you set accurate prices for weekly, monthly and custom rates.
* Improved the way to display a rate pricing on the checkout page: the price is updated automatically based on the number of guests if there are any per-guest price variables.
* Added the Availability Calendar shortcode.
* Added sorting parameters to shortcodes.
* Added all missing currencies to the list of currencies.

= 2.7.4, Jun 27 2018 =
* Fixed PHP warning that may occur in version 2.7.2.

= 2.7.3, Jun 21 2018 =
* Fixed PHP warning that may occur in version 2.7.2.

= 2.7.2, Jun 20 2018 =
* Added the ability to add monthly, weekly and custom (based on any length of stay) rates.

= 2.7.1, Jun 5 2018 =
* Fixed the bug with the missing slider icons that appeared in a previous update.
* Added a new admin data picker style.
* Single room type data output was rewritten in actions to provide developers with more flexible customization.
* Fixed the bug with the months localization of the admin booking calendar.
* Fixed the alphabetic ordering of countries for non-English websites.
* Added Summary and Description info to iCal import logs to help you easier identify bookings from different channels.

= 2.7.0, May 18 2018 =
* Added the ability to create a reservation manually.
* Added terms and conditions checkbox to booking confirmation page.

= 2.6.1, Apr 23 2018 =
* Fix: Reverted CSS class of image galleries as it breaks some themes.

= 2.6.0, Apr 20 2018 =
* Added the ability to set different prices for one accommodation based on a number of guests.
* Optimized the image galleries of accommodations.
* Added support for Jetpack Lazy Images Module.
* Fixed the bug with displaying the age of children in the search availability form added via a shortcode.
* Fixed the price format in the Recommended accommodations section.

= 2.5.0, Apr 4 2018 =
* Minor bugfixes and improvements.

= 2.4.4, Mar 14 2018 =
* Improved compatibility with Elementor Page Builder plugin.
* Fixed the bug with missing slash in calendar URL.

= 2.4.3, Mar 7 2018 =
* Added a new option to skip search results page and enable direct booking from accommodation pages.

= 2.4.2, Feb 28 2018 =
* Fixed the bug with check-in and check-out time not saving. Time settings were set to 24-hour clock system.
* Added tags to Accommodations.
* Added the following mphb_rooms shortcode parameters: category, tags, IDs and relation. Now you can display accommodations by categories, tags or accommodation IDs.
* Added a new field to settings where you can set a standard child's age accepted in your hotel establishment. This is an optional text, which will complete "Children" field label clarifying this info for your visitors.
* Improved the search availability calendar. Now it correctly displays the minimum stay-in days depending on a check-in date.
* Fixed the bug with all dates displaying as unavailable within certain booking rules.
* Fixed the bug with a custom rule not being applied because of a global booking rule.
* Fixed the bug with the Availability calendar not showing the correct number of available accommodations.
* Added "Blocked accommodation" status to the Booking Calendar.
* Added a new DESCRIPTION field with the booking info to the Export Calendar in iCal format.
* The Export Calendar in iCal format now shows the SUMMARY in the following format: first name, last name and booking_id.
* Now the booking information from external calendars is sent across booking channels without changes.
* Fixed the error with deleting an expiration date of the coupon code.

= 2.4.1, Jan 31 2018 =
* Added support of WooCommerce Payments extension.
* Bug fix: fixed fatal error on booking confirmation page.

= 2.4.0, Jan 24 2018 =
* Added the ability to add accommodation taxes, services taxes, fees (mandatory services) and taxes on fees.

= 2.3.1, Dec 22 2017 =
* Added the ability to enable automatic external calendars synchronization.
* Fixed the bug with the rate duplication.
* Fixed localization issues of accommodation titles featured in "Recommended" block.
* Fixed the bug with the NextGen gallery plugin.

= 2.3.0, Dec 13 2017 =
* Added more flexible booking rules:
  * ability to set check-in check-out dates for individual accommodation types during chosen seasons;
  * ability to set minimum and maximum stay-in days for individual accommodation types during chosen seasons;
  * ability to block individual accommodation types and actual accommodations for chosen dates;
* Note: This release will perform an upgrade process on the database in the background. Please make sure that your previous booking rules are successfully transformed into new ones.
* Updated translation files. 
* Please note! Due to peculiarities of multilingual settings, the titles of custom post types and taxonomy (e.g. accommodation types, categories) are not translated from English into other languages in the URLs of the pages.

= 2.2.0, Oct 9 2017 =
* Implemented bookings synchronization with online channels via iCal interface.
* Replaced Services text 'per night' with 'per day'.
* Renamed Facilities to Amenities.
* Bug fixed: Stripe is now displayed on the Booking confirmation page even if it's the only payment method selected.
* Bug fixed: the amount of Accommodation types displayed in widget is now not dependent on the global WordPress settings.
* Removed limit for setting the number of adults and children in Accommodation type menu.
* Fixed the link for viewing a booking in the administrator's email.
* Fixed the rate duplication issue when clicking on a link in the list of rates.
* Fixed the issue with the %customer_note% tag that was not displayed in email.

= 2.1.2, Aug 14 2017 =
* Bug fix: put "beds" back to Settings.
* The Booking Confirmation page update: guests must select the number of adults and children.

= 2.1.1, Jul 19 2017 =
* Bug fix: fixed the placeholder of posts titles.

= 2.1.0, Jul 13 2017 =
* Added the ability to create and apply coupon codes.

= 2.0.0, Jun 28 2017 =
* Note: This release will perform an upgrade process on the database in the background.
* Note: This release adds new tags for email templates. Update your templates please. To reset an email template just remove the current text and save settings.
* Note: This release doesn't limit the number of adults and children in the Search availability form. Please update "Max Adults" and "Max Children" number in plugin settings.
* The updated plugin allows guests reserve and pay for several (more than one) accommodation types during one reservation process.
* Search results page was updated: guests can choose and add several accommodation types into one reservation.
* On the search results page, the plugin displays all recommended accommodations according to the number of guests specified in a search request. This option can be turned off.
* Email templates were updated to support Multiple booking.
* Admin page descriptions were updated to ease the work with the plugin.
* Bug fix: fixed the issue with saving check-in and check-out dates in Settings.
* Improved compatibility with Jetpack gallery and lightbox modules.
* Added a theme-friendly pagination option that allows specify the number of posts per page for accommodations and services.
* A cancellation email template is available as a separate tag - it's used when a booking cancellation option is turned on.
* A Price Breakdown Table on the Booking confirmation page was updated: it's now smaller with the ability to expend details of each booked accommodation.
* Updated the list of data your guests are required to provide when submitting a booking. Admins can set it to: no data required / country required / full address required. Please choose the preferable option.
* 15 new themes were added to calendar to fit your theme design much better.
* New filters, actions and CSS classes were added for developers.

= 1.2.3, May 12 2017 =
* Added the ability to receive payments through Beanstream/Bambora payment gateway.

= 1.2.2, Apr 26 2017 =
* Added the ability to receive payments through Braintree payment gateway.

= 1.2.1 Apr 24 2017 =
* Bug fix: fixed the issue of undelivered emails after booking placement.
* Bug fix: fixed the issue of booking calendar localization.

= 1.2.0, Mar 29 2017 =
* New algorithm of displaying accommodation pricing:
  * it displays minimum available price of accommodation for dates set in the search form by visitor;
  * it displays minimum available price of accommodation from the day of visit and for the next fixed number of days predefined in settings (if dates are not chosen by visitor);
  * it displays a total price for chosen dates or the price of "minimum days to stay" set in settings.
* Added the ability to create a payment manually. Useful feature to keep all your finances in one place.
* Added the ability to search booking by email or ID in the list of bookings.
* Added the ability to filter payments by status and search them by email or ID in the list of payments.
* Added a new email template to notify Administrator about new booking, which is paid and confirmed.
* Added the ability to enable comments for accommodation and services on the frontend.
* Thumbnail size of accommodation gallery is set to 'thumbnail' to make all images the same size.
* Bug fix: fixed an issue when rates list displayed rates in the past on the frontend.
* Bug fix: fixed an issue when price of the service was displayed twice.
* Added new Arabic language files.

= 1.1.0, Mar 14 2017 =
* Added the ability to receive payments through PayPal, 2Checkout and Stripe gateways.
* Made the plugin multilingual ready.
* Added translation into 13 languages (Portuguese, Polish, Russian, Spanish, Turkish, Swedish, Italian, Hungarian, Czech, Chinese, Dutch, French, German).

= 1.0.1, Jan 13 2017 =
* Added the ability to input dates via keyboard
* Added the ability to duplicate Rate
* Added the ability to choose date format in plugin Settings

= 1.0.0, Dec 23 2016 =
* Initial release
