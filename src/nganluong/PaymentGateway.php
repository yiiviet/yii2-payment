<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\nganluong;

use yii\base\NotSupportedException;

use yii2vn\payment\BasePaymentGateway;

/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const VC_ALL = self::VC_PURCHASE_SUCCESS;

    const PAYMENT_METHOD_NL = 'NL';

    const PAYMENT_METHOD_QR_CODE = 'QRCODE';

    const PAYMENT_METHOD_BANK_OFFLINE = 'NH_OFFLINE';

    const PAYMENT_METHOD_CREDIT_CARD_PREPAID = 'CREDIT_CARD_PREPAID';

    const PAYMENT_METHOD_VISA = 'VISA';

    const PAYMENT_METHOD_ATM_ONLINE = 'ATM_ONLINE';

    const PAYMENT_METHOD_ATM_OFFLINE = 'ATM_ONLINE';

    const PAYMENT_METHOD_INTERNET_BANKING = 'IB_ONLINE';

    const PAYMENT_TYPE_REDIRECT = 1;

    const PAYMENT_TYPE_SAFE = 2;

    const TRANSACTION_STATUS_SUCCESS = '00';

    const TRANSACTION_STATUS_PENDING = '01';

    const TRANSACTION_STATUS_ERROR = '02';

    public $merchantConfig = ['class' => Merchant::class];

    public $requestDataConfig = ['class' => RequestData::class];

    public $responseDataConfig = ['class' => ResponseData::class];

    public $verifiedDataConfig = ['class' => VerifiedData::class];

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
    {
        return ($sandbox ? 'https://sandbox.nganluong.vn:8088/nl30' : 'https://www.nganluong.vn') . '/checkout.api.nganluong.post.php';
    }


    /**
     * @inheritdoc
     */
    public static function version(): string
    {
        return '3.1';
    }

    /**
     * @inheritdoc
     */
    protected function initSandboxEnvironment()
    {
        $merchantConfig = require(__DIR__ . '/sandbox-merchant.php');
        $this->setMerchant($merchantConfig);
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
     * @throws NotSupportedException
     */
    public function verifyPaymentNotificationRequest($merchantId = null, \yii\web\Request $request = null)
    {
        throw new NotSupportedException(__METHOD__ . " doesn't supported in Ngan Luong gateway");
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException|NotSupportedException
     */
    protected function requestInternal(int $command, \yii2vn\payment\BaseMerchant $merchant, \yii2vn\payment\Data $requestData, \yii\httpclient\Client $httpClient): array
    {
        $data = $requestData->get();

        return $httpClient->post('', $data)->send()->getData();
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData(int $command, \yii2vn\payment\BaseMerchant $merchant, \yii\web\Request $request): array
    {
        return [
            'token' => $request->get('token')
        ];
    }

}