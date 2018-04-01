<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\base\NotSupportedException;
use yii\httpclient\Client as HttpClient;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CheckoutData;
use yii2vn\payment\Data;
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
     * @var array
     */
    public $merchantConfig = ['class' => Merchant::class];

    public $merchantInfoDataConfig = ['class' => Data::class];

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
    public static function baseUrl(): string
    {
        return 'https://www.baokim.vn';
    }

    /**
     * @param array $data
     * @return bool|CheckoutResponseData
     */
    public function cardCharge(array $data)
    {
        $data['method'] = self::CHECKOUT_METHOD_CARD_CHARGE;

        return $this->checkout($data);
    }

    /**
     * @inheritdoc
     */
    protected function getHttpClientConfig(): array
    {
        return [
            'class' => HttpClient::class,
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'format' => HttpClient::FORMAT_JSON,
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
     * @return object|Data
     */
    public function getMerchantInfo(string $emailBusiness = null, $merchantId = null): Data
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant($merchantId);

        $data = ['business' => $emailBusiness ?? $merchant->email];
        $httpMethod = 'GET';
        $dataSign = $httpMethod . '&' . urlencode(self::PRO_SELLER_INFO_URL) . '&' . urlencode(http_build_query($data)) . '&';
        $data['signature'] = $merchant->signature($dataSign, Merchant::SIGNATURE_RSA);
        $httpResponse = $this->getHttpClient()->get(self::PRO_SELLER_INFO_URL, $data, [], [
            CURLOPT_USERPWD => $merchant->apiUser . ':' . $merchant->apiPassword
        ])->send();

        return Yii::createObject($this->merchantInfoDataConfig, [$httpResponse->getData()]);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithAtmTransfer(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_ATM_TRANSFER;

        return $this->checkout($data);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithBankTransfer(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_BANK_TRANSFER;

        return $this->checkout($data);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithBaoKim(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_BAO_KIM;

        return $this->checkout($data);
    }


    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithLocalBank(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_LOCAL_BANK;

        return $this->checkout($data);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithCreditCard(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_CREDIT_CARD;

        return $this->checkout($data);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithInternetBanking(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_INTERNET_BANKING;

        return $this->checkout($data);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithCardCharge(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_CARD_CHARGE;

        return $this->checkout($data);
    }

    /**
     * @inheritdoc
     */
    protected function checkoutInternal(CheckoutData $data): array
    {
        /** @var Merchant $merchant */

        $merchant = $data->getMerchant();
        $method = $data->getMethod();
        $data = $data->getData();
        ksort($data);
        $dataSign = implode('', $data);

        switch ($method) {
            case self::CHECKOUT_METHOD_CARD_CHARGE:
                $data['data_sign'] = $merchant->signature($dataSign, Merchant::SIGNATURE_HMAC);
                $url = self::CARD_CHARGE_URL;
                break;
            case self::CHECKOUT_METHOD_ATM_TRANSFER || self::CHECKOUT_METHOD_BANK_TRANSFER || self::CHECKOUT_METHOD_BAO_KIM:
                $data['checksum'] = $merchant->signature($dataSign, Merchant::SIGNATURE_HMAC);
                $url = self::BK_PAYMENT_URL;
                break;
            case self::CHECKOUT_METHOD_LOCAL_BANK || self::CHECKOUT_METHOD_INTERNET_BANKING || self::CHECKOUT_METHOD_CREDIT_CARD:
                $dataSign = 'POST' . '&' . urlencode(self::PRO_PAYMENT_URL) . '&&' . urlencode(http_build_query($data));
                $data['signature'] = $merchant->signature($dataSign, Merchant::SIGNATURE_RSA);
                $url = self::PRO_PAYMENT_URL;
                break;
            default:
                throw new NotSupportedException("Checkout method '$method' not supported in " . __CLASS__);
        }

        $httpResponse = $this->getHttpClient()->post($url, $data, [], [CURLOPT_USERPWD => $merchant->apiUser . ':' . $merchant->apiPassword])->send();
        Yii::debug(__CLASS__ . " checkout requested sent with method: $method");

        return $httpResponse->getData();
    }

    protected function getDefaultCheckoutMethod(): string
    {
        return self::CHECKOUT_METHOD_LOCAL_BANK;
    }
}