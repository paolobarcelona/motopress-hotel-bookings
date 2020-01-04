<?php

namespace MPHB\Payments\Gateways\Stripe;

use Exception;
use Stripe\PaymentIntent;
use Stripe\Source;
use Stripe\Stripe;
use Stripe\Transfer as StripeTransfer;
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
    private $stripeConnectAccountId;

    /**
     * @var string
     */    
    private $commissionType;

    /**
     * @var float
     */    
    private $commissionRate;    

    /**
     * @var string
     */
    private const PAYMENT_METHOD_CARD = 'card';
    
    /**
     * StripeAPI6 constructor.
     *
     * @param mixed[] $gatewaySettings
     */
    public function __construct($gatewaySettings)
    {
        $this->secretKey = $gatewaySettings['secret_key'] ?? '';
        $this->stripeConnectAccountId = $gatewaySettings['stripe_connect_account_id'] ?? '';
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
        Stripe::setAppInfo(
            MPHB()->getName(), 
            MPHB()->getVersion(), 
            MPHB()->getPluginStoreUri(), 
            self::PARTNER_ID
        );

        return $this;
    }

    /**
     * @param float $amount
     * @param string $description
     * @param string $currency
     * @param null|mixed[] $customer
     *
     * @return \Stripe\PaymentIntent|\WP_Error
     */
    public function createPaymentIntent(
        float $amount,
        ?string $description = '', 
        ?string $currency = null, 
        ?array $customer = null
    ) {
        if (is_null($currency)) {
            $currency = MPHB()->settings()->currency()->getCurrencyCode();
        }

        Stripe::setApiKey($this->secretKey);
        Stripe::setApiVersion(self::API_VERSION);

        try {
            $requestArgs = [
                'amount' => (int)$this->convertToSmallestUnit($amount, $currency),
                'application_fee_amount' => (int)$this->convertToSmallestUnit(
                    $this->getCommissionAmount($amount),
                    $currency
                ),
                'currency' => \strtolower($currency),
                'payment_method_types' => [self::PAYMENT_METHOD_CARD],
                'metadata' => \array_merge(
                    ['Hotel Name' => get_bloginfo('name')],
                    $customer
                )
            ];

            if (empty($description) === false) {
                $requestArgs['description'] = $description;
            }

            // See details in https://stripe.com/docs/api/payment_intents/create
            $paymentIntent = PaymentIntent::create(
                $requestArgs, 
                ['stripe_account' => (string)$this->stripeConnectAccountId]
            );

            return $paymentIntent;
        } catch (\Exception $e) {
            return new \WP_Error('stripe_api_error', $e->getMessage());
        }
    }

    public function retrievePaymentIntent($paymentIntentId, ?string $clientId = null)
    {
        Stripe::setApiKey($this->secretKey);
        Stripe::setApiVersion(self::API_VERSION);

        if ($clientId !== null) {
            Stripe::setClientId($clientId);
        }

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
     * Calculate commission amount.
     *
     * @param float $amount
     * 
     * @return float $commission
     */
    private function getCommissionAmount (float $amount): float
    {
        $commissionRate = $this->commissionRate;

        // Percentage?
        if ($this->commissionType === StripeGatewayCustom::COMMISSION_TYPE_PERCENTAGE) {
            $commissionRate /= 100;
            
            return (float)($amount * (float)$commissionRate);

        }

        return (float)($amount - ($amount - $commissionRate));
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
