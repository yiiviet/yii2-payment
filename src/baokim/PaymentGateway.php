<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\di\Instance;
use yii\base\NotSupportedException;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CheckoutData;
use yii2vn\payment\CardChargePaymentGatewayInterface;


/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway implements CardChargePaymentGatewayInterface
{

    const CHECKOUT_METHOD_BAO_KIM = 'baoKim';

    const CHECKOUT_METHOD_LOCAL_BANK = 'localBank';

    const CHECKOUT_METHOD_ATM_TRANSFER = 'atmTransfer';

    const CHECKOUT_METHOD_BANK_TRANSFER = 'bankTransfer';

    const CHECKOUT_METHOD_CARD_CHARGE = 'cardCharge';

    const CHECKOUT_METHOD_INTERNET_BANKING = 'internetBanking';

    const CHECKOUT_METHOD_CREDIT_CARD = 'creditCard';

    const BK_PAYMENT_URL = '/payment/order/version11';

    const CARD_CHARGE_URL = '/the-cao/restFul/send';

    const PRO_SELLER_INFO_URL = '/payment/rest/payment_pro_api/get_seller_info';

    const PRO_PAYMENT_URL = '/payment/rest/payment_pro_api/pay_by_card';

    /**
     * @var string|array|\yii\caching\Cache
     */
    public $merchantDataCache = 'cache';

    public $merchantDataCacheDuration = 86400;

    /**
     * @var array
     */
    public $merchantConfig = ['class' => Merchant::class];

    public $merchantRequestDataConfig = ['class' => MerchantRequestData::class];

    public $merchantResponseDataConfig = ['class' => MerchantResponseData::class];

    public $checkoutRequestDataConfig = ['class' => CheckoutRequestData::class];

    public $checkoutResponseDataConfig = ['class' => CheckoutResponseData::class];


    /**
     * @inheritdoc
     */
    public static function version(): string
    {
        return '1.0';
    }

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
    {
        return $sandbox ? 'https://sandbox.baokim.vn' : 'https://www.baokim.vn';
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @inheritdoc
     */
    public function init()
    {
        $this->merchantDataCache = Instance::ensure($this->merchantDataCache, 'yii\caching\Cache');

        parent::init();
    }

    /**
     * @param array $data
     * @param string|int|null $merchantId
     * @return bool|CheckoutResponseData
     */
    public function cardCharge(array $data, $merchantId = null)
    {
        return $this->checkoutWithCardCharge($data, $merchantId);
    }

    /**
     * @inheritdoc
     */
    protected function getHttpClientConfig(): array
    {
        return [
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'format' => 'json',
                'options' => [
                    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST | CURLAUTH_BASIC,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ]
            ]
        ];
    }


    /**
     * @param string $emailBusiness
     * @param int|string|null $merchantId
     * @throws \yii\base\InvalidConfigException|NotSupportedException
     * @return object|MerchantRequestData
     */
    public function getMerchantData(string $emailBusiness = null, $merchantId = null): MerchantRequestData
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant($merchantId);
        $cacheKey = [
            __METHOD__,
            $emailBusiness,
            $merchant->id
        ];

        if (!$data = $this->merchantDataCache->get($cacheKey)) {
            $requestData = Yii::createObject($this->merchantRequestDataConfig, [$merchant, [
                'business' => $emailBusiness
            ]]);

            $queryData = $requestData->getData(true, 'signature');
            $httpResponse = $this->getHttpClient()->get(self::PRO_SELLER_INFO_URL, $queryData, [], [
                CURLOPT_USERPWD => $merchant->apiUser . ':' . $merchant->apiPassword
            ])->send();

            $this->merchantDataCache->set($cacheKey, $data = $httpResponse->getData(), $this->merchantDataCacheDuration);
        }

        return Yii::createObject($this->merchantResponseDataConfig, [$merchant, $data]);
    }

    /**
     * @param array $data
     * @param string|int|null $merchantId
     * @return CheckoutResponseData
     */
    public function checkoutWithAtmTransfer(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_ATM_TRANSFER, $merchantId);
    }

    /**
     * @param array $data
     * @param string|int|null $merchantId
     * @return CheckoutResponseData
     */
    public function checkoutWithBankTransfer(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_BANK_TRANSFER, $merchantId);
    }

    /**
     * @param array $data
     * @param string|int|null $merchantId
     * @return CheckoutResponseData
     */
    public function checkoutWithBaoKim(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_BAO_KIM, $merchantId);
    }


    /**
     * @param array $data
     * @param string|int|null $merchantId
     * @return CheckoutResponseData
     */
    public function checkoutWithLocalBank(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_LOCAL_BANK, $merchantId);
    }

    /**
     * @param array $data
     * @param string|int|null $merchantId
     * @return CheckoutResponseData
     */
    public function checkoutWithCreditCard(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_CREDIT_CARD, $merchantId);
    }

    /**
     * @param array $data
     * @param string|int|null $merchantId
     * @return CheckoutResponseData
     */
    public function checkoutWithInternetBanking(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_INTERNET_BANKING, $merchantId);
    }

    /**
     * @param array $data
     * @param string|int|null $merchantId
     * @return CheckoutResponseData
     */
    public function checkoutWithCardCharge(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_CARD_CHARGE, $merchantId);
    }

    /**
     * @inheritdoc
     */
    protected function checkoutInternal(CheckoutData $data): array
    {
        /** @var Merchant $merchant */

        $merchant = $data->getMerchant();
        $method = $data->getMethod();

        switch ($method) {
            case self::CHECKOUT_METHOD_CARD_CHARGE:
                $signKey = 'data_sign';
                $url = self::CARD_CHARGE_URL;
                break;
            case self::CHECKOUT_METHOD_ATM_TRANSFER || self::CHECKOUT_METHOD_BANK_TRANSFER || self::CHECKOUT_METHOD_BAO_KIM:
                $signKey = 'checksum';
                $url = self::BK_PAYMENT_URL;
                break;
            case self::CHECKOUT_METHOD_LOCAL_BANK || self::CHECKOUT_METHOD_INTERNET_BANKING || self::CHECKOUT_METHOD_CREDIT_CARD:
                $signKey = 'signature';
                $url = self::PRO_PAYMENT_URL;
                break;
            default:
                throw new NotSupportedException("Checkout method '$method' not supported in " . __CLASS__);
        }

        $data = $data->getData(true, $signKey);
        $httpResponse = $this->getHttpClient()->post($url, $data, [], [CURLOPT_USERPWD => $merchant->apiUser . ':' . $merchant->apiPassword])->send();
        Yii::debug(__CLASS__ . " checkout requested sent with method: $method");

        return $httpResponse->getData();
    }

    protected function getDefaultCheckoutMethod(): string
    {
        return self::CHECKOUT_METHOD_LOCAL_BANK;
    }
}