<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\onepay;

use Yii;

use yii\httpclient\Client as HttpClient;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CheckoutData;
use yii2vn\payment\Data;


/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const CHECKOUT_METHOD_LOCAL_BANK = 'localBank';

    const CHECKOUT_METHOD_INTER_BANK = 'interBank';

    const ONECOMM_PAY_URL = '/onecomm-pay/vpc.op';

    const QUERY_DR_URL = '/onecomm-pay/Vpcdps.op';

    public $merchantConfig = ['class' => Merchant::class];

    public $checkoutRequestDataConfig = ['class' => CheckoutRequestData::class];

    public $checkoutResponseDataConfig = ['class' => CheckoutResponseData::class];

    public $queryDRDataConfig = ['class' => Data::class];

    public static function getBaseUrl(bool $sandbox): string
    {
        return $sandbox ? 'https://mtf.onepay.vn' : 'https://onepay.vn';
    }

    public static function version(): string
    {
        return '2';
    }

    protected function getHttpClientConfig(): array
    {
        return [
            'class' => HttpClient::class,
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'options' => [
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ]
            ]
        ];
    }

    /**
     * @param int|string $vpcMerchTxnRef
     * @param null|int|string $merchantId
     * @return Data
     * @throws \yii\base\InvalidConfigException
     */
    public function queryDR($vpcMerchTxnRef, $merchantId = null): Data
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant($merchantId);
        $url = [
            self::QUERY_DR_URL,
            'merchant' => $merchant->id,
            'command' => 'queryDR',
            'vpc_User' => $merchant->user,
            'vpc_Password' => $merchant->password,
            'vpc_AccessCode' => $merchant->accessCode,
            'vpc_Version' => static::version(),
            'vpc_MerchTxnRef' => (string)$vpcMerchTxnRef
        ];

        $responseData = $this->getHttpClient()->post($url)->send()->getData();

        return Yii::createObject($this->queryDRDataConfig, [$responseData]);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithLocalBank(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_LOCAL_BANK, $merchantId);
    }

    /**
     * @param string|int|null $merchantId
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithInterBank(array $data, $merchantId = null): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_INTER_BANK, $merchantId);
    }

    /**
     * @inheritdoc
     */
    protected function checkoutInternal(CheckoutData $data): array
    {
        /** @var Merchant $merchant */
        $merchant = $data->getMerchant();
        $dataQuery = $data->getData();
        ksort($dataQuery);
        $dataSign = [];
        foreach ($dataQuery as $attribute => $value) {
            if (substr($attribute, 0, 4) === 'vpc_') {
                $dataSign[$attribute] = $value;
            }
        }
        $dataQuery['vpc_SecureHash'] = strtoupper($merchant->signature(http_build_query($dataSign)));
        $location = rtrim(static::baseUrl()) . self::ONECOMM_PAY_URL . '?' . http_build_query($dataQuery);

        return ['location' => $location];
    }

    protected function getDefaultCheckoutMethod(): string
    {
        return self::CHECKOUT_METHOD_LOCAL_BANK;
    }

}