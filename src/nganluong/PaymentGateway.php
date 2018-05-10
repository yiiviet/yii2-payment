<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\nganluong;

use yii\base\NotSupportedException;

use yiiviet\payment\BasePaymentGateway;

/**
 * Lớp PaymentGateway hổ trợ việc kết nối đến Ngân Lượng.
 * Hiện tại nó hỗ trợ 100% tính năng của Ngân Lượng v3.1
 *
 * @method ResponseData purchase(array $data, $clientId = null)
 * @method ResponseData queryDR(array $data, $clientId = null)
 * @method VerifiedData verifyRequestPurchaseSuccess($clientId = null, \yii\web\Request $request = null)
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
     * Hằng khai báo giúp Ngân Lượng xác định phương thức thanh toán là Ngân Lượng,
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_METHOD_NL = 'NL';

    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức thanh toán là QR Code,
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_METHOD_QR_CODE = 'QRCODE';

    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức thanh toán là tại ngân hàng,
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_METHOD_BANK_OFFLINE = 'NH_OFFLINE';

    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức thanh toán là thẻ tín dụng trả trước,
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_METHOD_CREDIT_CARD_PREPAID = 'CREDIT_CARD_PREPAID';

    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức thanh toán là thẻ VISA,
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_METHOD_VISA = 'VISA';

    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức thanh toán là ATM Online,
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_METHOD_ATM_ONLINE = 'ATM_ONLINE';

    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức thanh toán là ATM Offline,
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_METHOD_ATM_OFFLINE = 'ATM_ONLINE';

    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức thanh toán là Internet Banking,
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_METHOD_INTERNET_BANKING = 'IB_ONLINE';

    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức giao dịch là trực tiếp (trực tiếp nhận tiền),
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_TYPE_REDIRECT = 1;
    /**
     * Hằng khai báo giúp Ngân Lượng xác định phương thức giao dịch là tạm giữ (tạm giữ tiền),
     * khi khởi tạo lệnh [[RC_PURCHASE]] tại phương thức [[request()]].
     */
    const PAYMENT_TYPE_SAFE = 2;

    /**
     * Hằng khai báo giúp bạn xác định trạng thái giao dịch thành công,
     * khi khởi tạo lệnh [[VRC_PURCHASE_SUCCESS]] hoặc [[RC_QUERY_DR]] tại phương thức [[request()]] hoặc [[verifyRequest()]].
     */
    const TRANSACTION_STATUS_SUCCESS = '00';

    /**
     * Hằng khai báo giúp bạn xác định trạng thái giao dịch thàng công nhưng Ngân Lượng tạm giữ,
     * khi khởi tạo lệnh [[VRC_PURCHASE_SUCCESS]] hoặc [[RC_QUERY_DR]] tại phương thức [[request()]] hoặc [[verifyRequest()]].
     */
    const TRANSACTION_STATUS_PENDING = '01';

    /**
     * Hằng khai báo giúp bạn xác định trạng thái giao dịch thất bại khách hàng không thanh toán hoặc lỗi,
     * khi khởi tạo lệnh [[VRC_PURCHASE_SUCCESS]] hoặc [[RC_QUERY_DR]] tại phương thức [[request()]] hoặc [[verifyRequest()]].
     */
    const TRANSACTION_STATUS_ERROR = '02';

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
        return ($this->sandbox ? 'https://sandbox.nganluong.vn:8088/nl30' : 'https://www.nganluong.vn') . '/checkout.api.nganluong.post.php';
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return '3.1';
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
    public function verifyRequestIPN($clientId = null, \yii\web\Request $request = null)
    {
        throw new NotSupportedException(__METHOD__ . " doesn't supported in Ngan Luong gateway");
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException|NotSupportedException
     */
    protected function requestInternal(\vxm\gatewayclients\RequestData $requestData, \yii\httpclient\Client $httpClient): array
    {
        $data = $requestData->get();

        return $httpClient->post('', $data)->send()->getData();
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        return [
            'token' => $request->get('token')
        ];
    }

}
