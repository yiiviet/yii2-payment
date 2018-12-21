<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\momo;

use GatewayClients\DataInterface;

use yii\httpclient\Client as HttpClient;

use vxm\gatewayclients\RequestData;
use vxm\gatewayclients\RequestEvent;

use yiiviet\payment\BasePaymentGateway;

/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến MOMO.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán MOMO All In One Payment.
 *
 * @method ResponseData purchase(array $data, $clientId = null)
 * @method ResponseData queryDR(array $data, $clientId = null)
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
     * Lệnh `refund` sử dụng cho việc tạo [[request()]] yêu cầu hoàn trả tiền.
     */
    const RC_REFUND = 'refund';

    /**
     * Lệnh `queryRefund` sử dụng cho việc tạo [[request()]] để kiểm tra trang thái của lệnh `refund` đã tạo.
     */
    const RC_QUERY_REFUND = 'queryRefund';

    /**
     * @event RequestEvent được gọi trước khi khởi tạo lệnh [[RC_REFUND]] ở phương thức [[request()]].
     */
    const EVENT_BEFORE_REFUND = 'beforeRefund';

    /**
     * @event RequestEvent được gọi sau khi khởi tạo lệnh [[RC_REFUND]] ở phương thức [[request()]].
     */
    const EVENT_AFTER_REFUND = 'afterRefund';

    /**
     * @event RequestEvent được gọi trước khi khởi tạo lệnh [[RC_QUERY_REFUND]] ở phương thức [[request()]].
     */
    const EVENT_BEFORE_QUERY_REFUND = 'beforeQueryRefund';

    /**
     * @event RequestEvent được gọi sau khi khởi tạo lệnh [[RC_QUERY_REFUND]] ở phương thức [[request()]].
     */
    const EVENT_AFTER_QUERY_REFUND = 'afterQueryRefund';

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
     * Phương thức yêu cầu MOMO hoàn trả tiền cho đơn hàng chỉ định.
     * Đây là phương thức ánh xạ của [[request()]] sử dụng lệnh [[RC_REFUND]].
     *
     * @param array $data Dữ liệu yêu cầu hoàn trả.
     * @param null $clientId Client id sử dụng để tạo yêu cầu.
     * Nếu không thiết lập [[getDefaultClient()]] sẽ được gọi để xác định client.
     * @return ResponseData|DataInterface Trả về [[DataInterface]] là dữ liệu tổng hợp từ MOMO phản hồi.
     * @throws \ReflectionException|\yii\base\InvalidConfigException|\yii\base\InvalidArgumentException
     */
    public function refund(array $data, $clientId = null): DataInterface
    {
        return $this->request(self::RC_REFUND, $data, $clientId);
    }

    /**
     * Phương thức truy vấn thông tin của lệnh hoàn tiền tại MOMO.
     * Đây là phương thức ánh xạ của [[request()]] sử dụng lệnh [[RC_QUERY_REFUND]].
     *
     * @param array $data Dữ liệu trạng thái hoàn tiền.
     * @param null $clientId Client id sử dụng để tạo yêu cầu truy vấn trạng thái.
     * Nếu không thiết lập [[getDefaultClient()]] sẽ được gọi để xác định client.
     * @return ResponseData|DataInterface Trả về [[DataInterface]] là dữ liệu tổng hợp từ MOMO phản hồi.
     * @throws \ReflectionException|\yii\base\InvalidConfigException|\yii\base\InvalidArgumentException
     */
    public function queryRefund(array $data, $clientId = null): DataInterface
    {
        return $this->request(self::RC_QUERY_REFUND, $data, $clientId);
    }

    /**
     * @inheritdoc
     */
    public function beforeRequest(RequestEvent $event)
    {
        switch ($event->command) {
            case self::RC_REFUND:
                $this->trigger(self::EVENT_BEFORE_REFUND, $event);
                break;
            case self::RC_QUERY_REFUND:
                $this->trigger(self::EVENT_BEFORE_QUERY_REFUND, $event);
                break;
            default:
                break;
        }

        parent::beforeRequest($event);
    }

    /**
     * @inheritdoc
     */
    public function afterRequest(RequestEvent $event)
    {
        switch ($event->command) {
            case self::RC_REFUND:
                $this->trigger(self::EVENT_AFTER_REFUND, $event);
                break;
            case self::RC_QUERY_REFUND:
                $this->trigger(self::EVENT_AFTER_QUERY_REFUND, $event);
                break;
            default:
                break;
        }

        parent::afterRequest($event);
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
    protected function requestInternal(RequestData $requestData, HttpClient $httpClient): array
    {
        $data = $requestData->get();

        return $this->getHttpClient()->post('', $data)->send()->getData();
    }

    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        // TODO: Implement getVerifyRequestData() method.
    }

}
