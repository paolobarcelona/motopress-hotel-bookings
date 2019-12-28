<?php

namespace MPHB\Payments\Gateways\Stripe;

use Exception;
use Stripe\PaymentIntent;
use Stripe\Source;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Transfer as StripeTransfer;
use MPHB\Entities\Booking;
use MPHB\Entities\Payment;
use MPHB\Payments\Gateways\StripeGatewayCustom;

class StripeAPI6
{
    /**
     * @var string
     */
    public const PARTNER_ID = 'pp_partner_Fs0jSMbknaJwVC';

    /**
     * @var string
     */
    public const API_VERSION = '2019-10-17';

    /**
     * @var bool
     */
    protected static $loaderRegistered = false;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var string
     */
    private $mainStripeConnectAccountId;

    /**
     * @var string
     */
    private $hotelStripeConnectAccountId;

    /**
     * @var string
     */    
    private $commissionType;

    /**
     * @var float
     */    
    private $commissionRate;    
    
    /**
     * StripeAPI6 constructor.
     *
     * @param mixed[] $gatewaySettings
     */
    public function __construct($gatewaySettings)
    {
        $this->secretKey = $gatewaySettings['secret_key'] ?? '';
        $this->mainStripeConnectAccountId = $gatewaySettings['main_stripe_connect_account_id'] ?? '';
        $this->hotelStripeConnectAccountId = $gatewaySettings['hotel_stripe_connect_account_id'] ?? '';
        $this->commissionType = $gatewaySettings['commission_type'] 
            ?? StripeGatewayCustom::COMMISSION_TYPE_PERCENTAGE;
        $this->commissionRate = $gatewaySettings['commission_rate'] 
            ?? StripeGatewayCustom::COMMISSION_DEFAULT_RATE;

        $this->registerLoader();
    }

    protected function registerLoader()
    {
        if (self::$loaderRegistered) {
            return;
        }

        // Use autoloader instead of requiring all 120+ files each time
        spl_autoload_register(function ($className) {
            // "Stripe\Checkout\Session"
            $className = ltrim($className, '\\');

            if (strpos($className, 'Stripe') !== 0) {
                return false;
            }

            // "lib\Checkout\Session"
            $pluginFile = str_replace('Stripe\\', 'lib\\', $className);
            // "lib/Checkout/Session"
            $pluginFile = str_replace('\\', DIRECTORY_SEPARATOR, $pluginFile);
            // "lib/Checkout/Session.php"
            $pluginFile .= '.php';
            // ".../vendors/stripe-api/lib/Checkout/Session.php"
            $pluginFile = MPHB()->getPluginPath('vendors/stripe-api/') . $pluginFile;

            if (file_exists($pluginFile)) {
                require $pluginFile;
                return true;
            } else {
                return false;
            }
        });

        self::$loaderRegistered = true;
    }

    /**
     * See also convertToSmallestUnit() in stripe-gateway.js.
     *
     * @param float $amount
     * @param string $currency
     * @return int
     */
    public function convertToSmallestUnit($amount, $currency = null)
    {
        if (is_null($currency)) {
            $currency = MPHB()->settings()->currency()->getCurrencyCode();
        }

        // See all currencies presented as links on page
        // https://stripe.com/docs/currencies#presentment-currencies
        switch (strtoupper($currency)) {
            // Zero decimal currencies
            case 'BIF':
            case 'CLP':
            case 'DJF':
            case 'GNF':
            case 'JPY':
            case 'KMF':
            case 'KRW':
            case 'MGA':
            case 'PYG':
            case 'RWF':
            case 'UGX':
            case 'VND':
            case 'VUV':
            case 'XAF':
            case 'XOF':
            case 'XPF':
                $amount = absint($amount);
                break;
            default:
                $amount = round($amount, 2) * 100; // In cents
                break;
        }

        return (int)$amount;
    }

    /**
     * @param string $currency
     * @return float
     */
    public function getMinimumAmount($currency)
    {
        // See https://stripe.com/docs/currencies#minimum-and-maximum-charge-amounts
        switch (strtoupper($currency)) {
            case 'USD':
            case 'AUD':
            case 'BRL':
            case 'CAD':
            case 'CHF':
            case 'EUR':
            case 'NZD':
            case 'SGD': $minimumAmount = 0.50; break;

            case 'DKK': $minimumAmount = 2.50; break;
            case 'GBP': $minimumAmount = 0.30; break;
            case 'HKD': $minimumAmount = 4.00; break;
            case 'JPY': $minimumAmount = 50.00; break;
            case 'MXN': $minimumAmount = 10.00; break;

            case 'NOK':
            case 'SEK': $minimumAmount = 3.00; break;

            default:    $minimumAmount = 0.50; break;
        }

        return $minimumAmount;
    }

    /**
     * Checks Stripe minimum amount value authorized per currency.
     *
     * @param float $amount
     * @param string $currency
     * @return bool
     */
    public function checkMinimumAmount($amount, $currency)
    {
        $currentAmount = $this->convertToSmallestUnit($amount, $currency);
        $minimumAmount = $this->convertToSmallestUnit($this->getMinimumAmount($currency), $currency);
        return $currentAmount >= $minimumAmount;
    }

    public function setApp()
    {
        Stripe::setAppInfo(MPHB()->getName(), MPHB()->getVersion(), MPHB()->getPluginStoreUri(), self::PARTNER_ID);
        return $this;
    }

    /**
     * @param \MPHB\Entities\Booking $booking
     * @param \MPHB\Entities\Payment $payment
     * @param mixed[] $requestArgs
     * 
     * @return \MPHB\Entities\Payment
     */
    public function createCommission (
        Booking $booking, 
        Payment $payment,
        array $requestArgs
    ): Payment {
        Stripe::setApiKey($this->secretKey);
        Stripe::setApiVersion(self::API_VERSION);
        
        $commission = $this->convertToSmallestUnit($this->getMainTransfer(
            (float)($booking->getTotalPriceWithoutProcessingFee() ?? ($requestArgs['amount'] ?? 0.00))
        ), $requestArgs['currency']);

        $requestArgs['destination'] = $this->mainStripeConnectAccountId;
        $requestArgs['amount'] = $commission;
        $requestArgs['source_type'] = 'card';
        $requestArgs['currency'] = \strtolower($requestArgs['currency'] ?? '');

        $transfer = $this->createTransfer($requestArgs);

        if ( isset($transfer['error_transfer']) === true ) {
            // translators: %1$s - Stripe Charge ID; %2$s - payment price
            $payment->addLog(\sprintf(
                'Can\'t transfer the commission from booking reference # %s. <br>
                Actual message: %s<br>
                Payload: %s',
                $booking->getId(),
                (string)$transfer['error_transfer'],
                \json_encode($requestArgs)
            ));

        } elseif ( $transfer instanceof StripeTransfer ) {
            $payment->addLog(\sprintf(
                'Commission for booking reference # %s have been transferred correctly. Transfer ID: "%s". Destination Payment: "%s".',
                $booking->getId(), 
                $transfer->id
            )); 
        }

        return $payment;
    }  

    /**
     * @param \MPHB\Entities\Booking $booking
     * @param \MPHB\Entities\Payment $payment
     * @param mixed[] $requestArgs
     * 
     * @return \MPHB\Entities\Payment
     */
    public function createHotelTransfer (
        Booking $booking, 
        Payment $payment,
        array $requestArgs
    ): Payment {
        Stripe::setApiKey($this->secretKey);
        Stripe::setApiVersion(self::API_VERSION);

        $requestArgs['currency'] = \strtolower($requestArgs['currency'] ?? '');
        
        $transferAmount = $this->convertToSmallestUnit((float)$this->getHotelTransfer(
            (float)($payment->getAmount() ?? ($requestArgs['amount'] ?? 0.00))
        ), $requestArgs['currency']);

        $requestArgs['destination'] = $this->hotelStripeConnectAccountId;
        $requestArgs['amount'] = $transferAmount;
        $requestArgs['source_type'] = 'card';
        

        $transfer = $this->createTransfer($requestArgs);

        if ( isset($transfer['error_transfer']) === true ) {
            // translators: %1$s - Stripe Charge ID; %2$s - payment price
            $payment->addLog(\sprintf(
                'Can\'t transfer the hotel payment from booking reference # %s. <br>
                Actual message: %s<br>
                Payload: %s',
                $booking->getId(),
                (string)$transfer['error_transfer'],
                \json_encode($requestArgs)
            ));

        } elseif ( $transfer instanceof StripeTransfer ) {
            $payment->addLog(\sprintf(
                'Hotel Transfer for booking reference # %s have been transferred correctly. Transfer ID: "%s". Destination Payment: "%s".',
                $booking->getId(), 
                $transfer->id
            )); 
        }

        return $payment;
    }  

    /**
     * @param float $amount
     * @param string $description
     * @param string $currency
     * @return \Stripe\PaymentIntent|\WP_Error
     */
    public function createPaymentIntent($amount, $description = '', $currency = null)
    {
        if (is_null($currency)) {
            $currency = MPHB()->settings()->currency()->getCurrencyCode();
        }

        Stripe::setApiKey($this->secretKey);
        Stripe::setApiVersion(self::API_VERSION);

        try {
            $processingFee = $this->getProcessingFeeBasedOnAmount($amount);

            $amountWithoutFee = ($amount - $processingFee);

            $transferGroup = $this->generateRandomString();
            
            $requestArgs = array(
                'amount'               => $this->convertToSmallestUnit($amount, $currency),
                'currency'             => \strtolower($currency),
                'payment_method_types' => array('card'),
                // 'application_fee_amount' => $this->convertToSmallestUnit($processingFee, $currency),
                'transfer_group' => $transferGroup
            );

            if (!empty($description)) {
                $requestArgs['description'] = $description;
            }

            // See details in https://stripe.com/docs/api/payment_intents/create
            $paymentIntent = PaymentIntent::create($requestArgs);
            
            $this->createTransfer([
                'amount' => $this->convertToSmallestUnit(
                    $this->getHotelTransfer($amountWithoutFee),
                    $currency
                ),
                'currency' => \strtolower($currency),
                'destination' => $this->hotelStripeConnectAccountId,
                'metadata' => [
                    'payment_intent_id' => $paymentIntent->id,
                    'total_amount' => $paymentIntent->amount,
                    'customer' => $paymentIntent->customer,
                    'payment_description' => $paymentIntent->description
                ],
                'transfer_group' => $transferGroup
            ]);

            $this->createTransfer([
                'amount' => $this->convertToSmallestUnit(
                    $this->getMainTransfer($amountWithoutFee),
                    $currency
                ),
                'currency' => \strtolower($currency),
                'destination' => $this->mainStripeConnectAccountId,
                'metadata' => [
                    'payment_intent_id' => $paymentIntent->id,
                    'total_amount' => $paymentIntent->amount,
                    'customer' => $paymentIntent->customer,
                    'payment_description' => $paymentIntent->description
                ],
                'transfer_group' => $transferGroup
            ]);

            return $paymentIntent;
        } catch (\Exception $e) {
            return new \WP_Error('stripe_api_error', $e->getMessage());
        }
    }

    public function retrievePaymentIntent($paymentIntentId)
    {
        Stripe::setApiKey($this->secretKey);
        Stripe::setApiVersion(self::API_VERSION);

        return PaymentIntent::retrieve($paymentIntentId);
    }

    public function retrieveSource($sourceId)
    {
        Stripe::setApiKey($this->secretKey);
        Stripe::setApiVersion(self::API_VERSION);

        return Source::retrieve($sourceId);
    }
    
    /**
     * Create a transfer
     *
     * @param mixed[] $args
     *
     * @return StripeObject|bool Transfer created or false on failure
     */
    private function createTransfer(array $args) 
    {
        try {
            $transfer = StripeTransfer::create( $args );
        } catch ( Exception $e ) {
            return array( 'error_transfer' => $e->getMessage() );
        }

        return $transfer;
    }

    /**
     * Calculate main transfer.
     *
     * @param float $amount
     * 
     * @return float $commission
     */
    private function getMainTransfer (float $amount): float
    {
        $commissionRate = $this->commissionRate;

        // Percentage?
        if ($this->commissionType === StripeGatewayCustom::COMMISSION_TYPE_PERCENTAGE) {
            $commissionRate /= 100;
            
            return (int)($amount * (float)$commissionRate);

        }

        return (int)($amount - ($amount - $commissionRate));
    }

    /**
     * Calculate for hotel transfer.
     *
     * @param float $amount
     * 
     * @return float $commission
     */
    private function getHotelTransfer (float $amount): float
    {
        $commissionRate = $this->commissionRate;

        // Percentage?
        if ($this->commissionType === StripeGatewayCustom::COMMISSION_TYPE_PERCENTAGE) {
            $commissionRate /= 100;
            
            $commission = (int)($amount * (float)$commissionRate);

            return $amount - $commission;
        }

        return $amount - (int)($amount - ($amount - $commissionRate));
    }

    /**
     * Generate random string.
     * 
     * @return string
     */
    private function generateRandomString(): string
    {
        return \substr(str_shuffle('0123456789bcdfghjklmnpqrstvwxyz'), 0, 10);        
    }

    /**
     * Get processing fees
     * 
     * @param float $amount
     *
     * @return float
     */    
    private function getProcessingFeeBasedOnAmount(float $amount): float
    {
		$fees = MPHB()->settings()->taxesAndFees()->getProcessingFees();

		$processingFee = 0;

		foreach ( $fees as $fee ) {
			$feePrice = 0;

			switch ( $fee['type'] ) {
				case 'exact':
					$feePrice = $fee['amount'];
					break;
				case 'percentage':
					$feePrice = $amount / 100 * $fee['amount'];
					break;
            }
            
			$processingFee += $feePrice;
		}

		return (float)$processingFee;
	}
}
