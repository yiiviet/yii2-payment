<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\nganluong;

use Yii;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CheckoutData;

/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const CHECKOUT_METHOD_NL = 'NL';

    const CHECKOUT_METHOD_QR_CODE = 'QRCODE';

    const CHECKOUT_METHOD_BANK_OFFLINE = 'NH_OFFLINE';

    const CHECKOUT_METHOD_CREDIT_CARD_PREPAID = 'CREDIT_CARD_PREPAID';

    const CHECKOUT_METHOD_VISA = 'VISA';

    const CHECKOUT_METHOD_ATM_ONLINE = 'ATM_ONLINE';

    const CHECKOUT_METHOD_ATM_OFFLINE = 'ATM_ONLINE';

    const CHECKOUT_METHOD_INTERNET_BANKING = 'IB_ONLINE';

    const CHECKOUT_API_POST_URL = '/checkout.api.nganluong.post.php';

    public $merchantConfig = ['class' => Merchant::class];

    public $checkoutRequestDataConfig = ['class' => CheckoutRequestData::class];

    public $checkoutResponseDataConfig = ['class' => CheckoutResponseData::class];

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
    {
        return $sandbox ? 'https://sandbox.nganluong.vn:8088/nl30' : 'https://www.nganluong.vn';
    }

    /**
     * @inheritdoc
     */
    public static function version(): string
    {
        return '3.1';
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithNganLuong(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_NL, $merchantId);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithQrCode(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data,self::CHECKOUT_METHOD_QR_CODE, $merchantId);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithBankOffline(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_BANK_OFFLINE, $merchantId);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithCreditCardPrepaid(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_CREDIT_CARD_PREPAID, $merchantId);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithVisa(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_VISA, $merchantId);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithAtmOnline(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_ATM_ONLINE, $merchantId);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithAtmOffline(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_ATM_OFFLINE, $merchantId);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithInternetBanking(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_INTERNET_BANKING, $merchantId);
    }

    /**
     * @inheritdoc
     */
    protected function getHttpClientConfig(): array
    {
        return [
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'options' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function checkoutInternal(CheckoutData $data): array
    {
        $httpResponse = $this->getHttpClient()->post(self::CHECKOUT_API_POST_URL, $data->getData())->send();
        Yii::debug(__CLASS__ . " checkout requested sent with method: {$data->getMethod()}");

        return $httpResponse->getData();
    }

    /**
     * @return string
     */
    protected function getDefaultCheckoutMethod(): string
    {
        return self::CHECKOUT_METHOD_ATM_ONLINE;
    }

}