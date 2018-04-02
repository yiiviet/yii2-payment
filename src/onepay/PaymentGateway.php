<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\onepay;

use yii\httpclient\Client as HttpClient;

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

    const CHECKOUT_METHOD_LOCAL_BANK = 'localBank';

    const CHECKOUT_METHOD_INTER_BANK = 'interBank';

    public $merchantConfig = ['class' => Merchant::class];

    public $checkoutRequestDataConfig = ['class' => CheckoutRequestData::class];

    public $checkoutResponseDataConfig = ['class' => CheckoutResponseData::class];

    const ONECOMM_PAY_URL = '/onecomm-pay/vpc.op';

    public static function baseUrl(): string
    {
        return 'https://onepay.vn';
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
                'format' => ''
            ]
        ];
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
    public function checkoutWithInterBank(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_INTER_BANK;

        return $this->checkout($data);
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
            if (substr($attribute, 0, strlen(CheckoutRequestData::VPC_ATTRIBUTE_PREFIX)) === CheckoutRequestData::VPC_ATTRIBUTE_PREFIX) {
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