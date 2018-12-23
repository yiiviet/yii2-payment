<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\momo;

use yii\httpclient\Client as HttpClient;

use yiiviet\payment\BasePaymentGateway;

/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến MOMO.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán MOMO All In One Payment.
 *
 * @method ResponseData purchase(array $data, $clientId = null)
 * @method ResponseData queryDR(array $data, $clientId = null)
 * @method ResponseData refund(array $data, $clientId = null)
 * @method ResponseData queryRefund(array $data, $clientId = null)
 * @method bool|VerifiedData verifyRequestIPN($clientId = null, \yii\web\Request $request = null)
 * @method bool|VerifiedData verifyRequestPurchaseSuccess($clientId = null, \yii\web\Request $request = null)
 * @method PaymentClient getClient($id = null)
 * @method PaymentClient getDefaultClient()
 *
 * @property PaymentClient $client
 * @property PaymentClient $defaultClient
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
class PaymentGateway extends BasePaymentGateway
{
    /**
     * Dùng để khai báo lệnh khi tạo request [[RC_PURCHASE]].
     */
    const REQUEST_TYPE_PURCHASE = 'captureMoMoWallet';

    /**
     * Dùng để khai báo lệnh khi tạo request [[RC_REFUND]].
     */
    const REQUEST_TYPE_REFUND = 'refundMoMoWallet';

    /**
     * Dùng để khai báo lệnh khi tạo request [[RC_QUERY_DR]].
     */
    const REQUEST_TYPE_QUERY_DR = 'transactionStatus';

    /**
     * Dùng để khai báo lệnh khi tạo request [[RC_QUERY_REFUND]].
     */
    const REQUEST_TYPE_QUERY_REFUND = 'refundStatus';

    /**
     * @inheritdoc
     */
    public $clientConfig = ['class' => PaymentClient::class];

    /**
     * @inheritdoc
     */
    public $requestDataConfig = ['class' => RequestData::class];

    /**
     * @inheritdoc
     */
    public $responseDataConfig = ['class' => ResponseData::class];

    /**
     * @inheritdoc
     */
    public $verifiedDataConfig = ['class' => VerifiedData::class];

    /**
     * @inheritdoc
     */
    public function getBaseUrl(): string
    {
        return $this->sandbox ? 'https://test-payment.momo.vn/gw_payment/transactionProcessor' : 'https://payment.momo.vn/gw_payment/transactionProcessor';
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function initSandboxEnvironment()
    {
        $clientConfig = require(__DIR__ . '/sandbox-client.php');
        $this->setClient($clientConfig);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\httpclient\Exception
     */
    protected function requestInternal(\vxm\gatewayclients\RequestData $requestData, HttpClient $httpClient): array
    {
        $data = $requestData->get();

        return $this->getHttpClient()->post('', $data)->setFormat('json')->send()->getData();
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        $params = [
            'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId', 'orderInfo', 'orderType',
            'transId', 'message', 'localMessage', 'responseTime', 'errorCode', 'payType', 'extraData', 'signature'
        ];
        $commandRequestMethods = [self::VRC_PURCHASE_SUCCESS => 'get', self::VRC_IPN => 'post'];
        $requestMethod = $commandRequestMethods[$command];

        $data = [];
        foreach ($params as $param) {
            if (($value = call_user_func([$request, $requestMethod], $param)) !== null) {
                $data[$param] = $value;
            }
        }

        return $data;
    }

}
