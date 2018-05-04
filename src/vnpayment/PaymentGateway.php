<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use yiiviet\payment\BasePaymentGateway;

/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const RC_REFUND = 0x04;

    const RC_ALL = 0x07;

    const PURCHASE_URL = '/paymentv2/vpcpay.html';

    const QUERY_DR_URL = '/merchant_webapi/merchant.html';

    const REFUND_URL = '/merchant_webapi/merchant.html';

    public $merchantConfig = ['class' => Merchant::class];

    public $requestDataConfig = ['class' => RequestData::class];

    public $responseDataConfig = ['class' => ResponseData::class];

    public $verifiedDataConfig = ['class' => VerifiedData::class];

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
    {
        return $sandbox ? 'http://sandbox.vnpayment.vn' : 'http://vnpayment.vn';
    }

    /**
     * @inheritdoc
     */
    public static function version(): string
    {
        return '2.0.0';
    }

    /**
     * @param array $data
     * @param null $merchantId
     * @return ResponseData|\yiiviet\payment\ResponseData
     * @throws \yii\base\InvalidConfigException
     */
    public function refund(array $data, $merchantId = null): ResponseData
    {
        return $this->request(self::RC_REFUND, $data, $merchantId);
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
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    protected function requestInternal(int $command, \yiiviet\payment\BaseMerchant $merchant, \yiiviet\payment\Data $requestData, \yii\httpclient\Client $httpClient): array
    {
        $commandUrls = [
            self::RC_PURCHASE => self::PURCHASE_URL,
            self::RC_REFUND => self::REFUND_URL,
            self::RC_QUERY_DR => self::QUERY_DR_URL
        ];
        $data = $requestData->get();
        $data[0] = $commandUrls[$command];

        if ($command === self::RC_PURCHASE) {
            return ['location' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
        } else {
            return $httpClient->get($data)->send()->getData();
        }
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData(int $command, \yiiviet\payment\BaseMerchant $merchant, \yii\web\Request $request): array
    {
        $params = [
            'vnp_TmnCode', 'vnp_Amount', 'vnp_BankCode', 'vnp_BankTranNo', 'vnp_CardType', 'vnp_PayDate', 'vnp_CurrCode',
            'vnp_OrderInfo', 'vnp_TransactionNo', 'vnp_ResponseCode', 'vnp_TxnRef', 'vnp_SecureHashType', 'vnp_SecureHash'
        ];

        $data = [];
        foreach ($params as $param) {
            $data[$param] = $request->get($param);
        }

        return $data;
    }

    protected function getHttpClientConfig(): array
    {
        return [
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'options' => [
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ]
            ]
        ];
    }
}