<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use vxm\gatewayclients\RequestEvent;
use yiiviet\payment\BasePaymentGateway;

use vxm\gatewayclients\DataInterface;

/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến OnePay.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán VnPayment v2.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{
    /**
     * Lệnh `refund` sử dụng cho việc tạo [[request()]] yêu cầu hoàn trả tiền.
     */
    const RC_REFUND = 'refund';

    /**
     * @event RequestEvent được gọi trước khi khởi tạo lệnh [[RC_REFUND]] ở phương thức [[request()]].
     */
    const EVENT_BEFORE_REFUND = 'beforeRefund';

    /**
     * @event RequestEvent được gọi sau khi khởi tạo lệnh [[RC_REFUND]] ở phương thức [[request()]].
     */
    const EVENT_AFTER_REFUND = 'afterRefund';

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
     */
    public function getVersion(): string
    {
        return '2.0.0';
    }

    /**
     * Phương thức yêu cầu VnPayment hoàn trả tiền cho đơn hàng chỉ định.
     * Đây là phương thức ánh xạ của [[request()]] sử dụng lệnh [[RC_REFUND]].
     *
     * @param array $data Dữ liệu yêu cầu hoàn trả.
     * @param null $clientId Client id sử dụng để tạo yêu cầu.
     * Nếu không thiết lập [[getDefaultClient()]] sẽ được gọi để xác định client.
     * @return ResponseData|DataInterface Trả về [[DataInterface]] là dữ liệu tổng hợp từ VnPayment phản hồi.
     * @throws \ReflectionException|\yii\base\InvalidConfigException|\yii\base\InvalidArgumentException
     */
    public function refund(array $data, $clientId = null): DataInterface
    {
        return $this->request(self::RC_REFUND, $data, $clientId);
    }

    /**
     * @inheritdoc
     */
    public function beforeRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_REFUND) {
            $this->trigger(self::EVENT_BEFORE_REFUND, $event);
        }

        parent::beforeRequest($event);
    }

    /**
     * @inheritdoc
     */
    public function afterRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_REFUND) {
            $this->trigger(self::EVENT_AFTER_REFUND, $event);
        }

        parent::beforeRequest($event);
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
            return ['location' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
        } else {
            return $httpClient->get($data)->send()->getData();
        }
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        $params = [
            'vnp_TmnCode', 'vnp_Amount', 'vnp_BankCode', 'vnp_BankTranNo', 'vnp_CardType', 'vnp_PayDate', 'vnp_CurrCode',
            'vnp_OrderInfo', 'vnp_TransactionNo', 'vnp_ResponseCode', 'vnp_TxnRef', 'vnp_SecureHashType', 'vnp_SecureHash'
        ];

        $data = [];
        foreach ($params as $param) {
            if (($value = $request->get($param)) !== null) {
                $data[$param] = $value;
            }
        }

        return $data;
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
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ]
            ]
        ];
    }
}
