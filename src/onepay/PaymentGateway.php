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
use yii2vn\payment\Data;


/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const RC_PURCHASE_INTERNATIONAL = 0x04;

    const EVENT_BEFORE_PURCHASE_INTERNATIONAL = 'beforePurchaseInternational';

    const EVENT_AFTER_PURCHASE_INTERNATIONAL = 'afterPurchaseInternational';

    const PURCHASE_URL = '/onecomm-pay/vpc.op';

    const PURCHASE_INTERNATIONAL_URL = '/vpcpay/vpcpay.op';

    const QUERY_DR_URL = '/onecomm-pay/Vpcdps.op';

    public $merchantConfig = ['class' => Merchant::class];

    public $requestDataConfig = ['class' => RequestData::class];

    public $responseDataConfig = ['class' => ResponseData::class];

    public $verifiedDataConfig = ['class' => Data::class];

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
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

    protected function requestInternal($command, \yii2vn\payment\BaseMerchant $merchant, \yii2vn\payment\Data $requestData, \yii\httpclient\Client $httpClient): array
    {
        /** @var Merchant $merchant */
        $commandUrls = [self::RC_PURCHASE => self::PURCHASE_URL, self::RC_PURCHASE]
        $data = $requestData->get();
        ksort($data);
        $dataSign = [];
        foreach ($data as $attribute => $value) {
            if (substr($attribute, 0, 4) === 'vpc_') {
                $dataSign[$attribute] = $value;
            }
        }
        $dataQuery['vpc_SecureHash'] = strtoupper($merchant->signature(http_build_query($dataSign)));
        $location = rtrim(static::baseUrl()) . self::ONECOMM_PAY_URL . '?' . http_build_query($dataQuery);

        return ['location' => $location];
    }

}