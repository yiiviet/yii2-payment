<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use yiiviet\payment\BasePaymentGateway;

/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến OnePay.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán VnPayment v2.
 *
 * @method ResponseData purchase(array $data, $clientId = null)
 * @method ResponseData queryDR(array $data, $clientId = null)
 * @method ResponseData refund(array $data, $clientId = null)
 * @method VerifiedData verifyRequestIPN(\yii\web\Request $request = null, $clientId = null)
 * @method VerifiedData verifyRequestPurchaseSuccess(\yii\web\Request $request = null, $clientId = null)
 * @method PaymentClient getClient($id = null)
 * @method PaymentClient getDefaultClient()
 *
 * @property PaymentClient $client
 * @property PaymentClient $defaultClient
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    /**
     * Đường dẫn API để yêu cầu tạo giao dịch thanh toán.
     */
    const PURCHASE_URL = '/paymentv2/vpcpay.html';

    /**
     * Đường dẫn API để truy vấn thông tin giao dịch.
     */
    const QUERY_DR_URL = '/merchant_webapi/merchant.html';

    /**
     * Đường dẫn API để yêu cầu hoàn trả tiền.
     */
    const REFUND_URL = '/merchant_webapi/merchant.html';

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
        return $this->sandbox ? 'http://sandbox.vnpayment.vn' : 'http://vnpayment.vn';
    }

    /**
     * @inheritdoc
     * @since 1.0.3
     */
    public function requestCommands(): array
    {
        return [self::RC_PURCHASE, self::RC_QUERY_DR, self::RC_REFUND];
    }

    /**
     * @inheritdoc
     */
    protected function defaultVersion(): string
    {
        return '2.0.0';
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
     * @throws \yii\base\InvalidConfigException|\yii\httpclient\Exception
     */
    protected function requestInternal(\vxm\gatewayclients\RequestData $requestData, \yii\httpclient\Client $httpClient): array
    {
        $command = $requestData->getCommand();
        $commandUrls = [
            self::RC_PURCHASE => self::PURCHASE_URL,
            self::RC_REFUND => self::REFUND_URL,
            self::RC_QUERY_DR => self::QUERY_DR_URL
        ];
        $data = $requestData->get();
        $data[0] = $commandUrls[$command];

        if ($command === self::RC_PURCHASE) {
            return ['redirect_url' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
        } else {
            return $httpClient->get($data)->send()->getData();
        }
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        $data = [];

        foreach ($request->get() as $param => $value) {
            if (strpos($param, 'vnp_') === 0) {
                $data[$param] = $value;
            }
        }

        return $data;
    }

}
