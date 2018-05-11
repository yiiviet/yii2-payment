<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

use Yii;

use yii\httpclient\Client as HttpClient;

use yiiviet\payment\BasePaymentGateway;
use yiiviet\payment\VerifiedRequestEvent;

use vxm\gatewayclients\DataInterface;
use vxm\gatewayclients\RequestEvent;


/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến OnePay.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán OnePay v2.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{
    /**
     * Lệnh `purchaseInternational` sử dụng cho việc khởi tạo truy vấn thanh toán quốc tế.
     */
    const RC_PURCHASE_INTERNATIONAL = 'purchaseInternational';

    /**
     * Lệnh `queryDR` sử dụng cho việc truy vấn thông tin giao dịch quốc tế.
     */
    const RC_QUERY_DR_INTERNATIONAL = 'queryDRInternational';

    /**
     * Lệnh `purchaseSuccessInternational` sử dụng cho việc yêu cấu xác thực tính hợp lệ
     * của dữ liệu khi khách hàng thanh toán thành công bằng cổng quốc tế (OnePay redirect khách hàng về server).
     */
    const VRC_PURCHASE_SUCCESS_INTERNATIONAL = 'purchaseSuccessInternational';

    /**
     * Lệnh `IPNInternational` sử dụng cho việc yêu cấu xác thực tính hợp lệ
     * của dữ liệu khi khách hàng thanh toán thành công bằng cổng quốc tế (OnePay bắn request về server).
     */
    const VRC_IPN_INTERNATIONAL = 'IPNInternational';

    /**
     * @event RequestEvent được gọi trước khi khởi tạo lệnh [[RC_PURCHASE_INTERNATIONAL]] ở phương thức [[request()]].
     */
    const EVENT_BEFORE_PURCHASE_INTERNATIONAL = 'beforePurchaseInternational';

    /**
     * @event RequestEvent được gọi sau khi khởi tạo lệnh [[RC_PURCHASE_INTERNATIONAL]] ở phương thức [[request()]].
     */
    const EVENT_AFTER_PURCHASE_INTERNATIONAL = 'afterPurchaseInternational';

    /**
     * @event RequestEvent được gọi trước khi khởi tạo lệnh [[RC_QUERY_DR_INTERNATIONAL]] ở phương thức [[request()]].
     */
    const EVENT_BEFORE_QUERY_DR_INTERNATIONAL = 'beforeQueryDRInternational';

    /**
     * @event RequestEvent được gọi sau khi khởi tạo lệnh [[RC_QUERY_DR_INTERNATIONAL]] ở phương thức [[request()]].
     */
    const EVENT_AFTER_QUERY_DR_INTERNATIONAL = 'afterQueryDRInternational';

    /**
     * @event VerifiedRequestEvent được gọi khi dữ liệu truy vấn sau khi khách hàng thanh toán thành công bằng cổng quốc tế,
     * được OnePay dẫn về hệ thống đã xác thực.
     */
    const EVENT_VERIFIED_REQUEST_PURCHASE_INTERNATIONAL_SUCCESS = 'verifiedPurchaseInternationalSuccessRequest';

    /**
     * @event VerifiedRequestEvent được gọi khi dữ liệu truy vấn sau khi khách hàng thanh toán thành công bằng cổng quốc tế,
     * được OnePay bắn `request` sang hệ thống đã xác thực.
     */
    const EVENT_VERIFIED_REQUEST_IPN_INTERNATIONAL = 'verifiedIPNInternationalRequest';

    /**
     * Đường dẫn API của thanh toán nội địa.
     */
    const PURCHASE_URL = '/onecomm-pay/vpc.op';

    /**
     * Đường dẫn API để truy vấn thông tin giao dịch nội địa.
     */
    const QUERY_DR_URL = '/onecomm-pay/Vpcdps.op';

    /**
     * Đường dẫn API của thanh toán quốc tế.
     */
    const PURCHASE_INTERNATIONAL_URL = '/vpcpay/vpcpay.op';

    /**
     * Đường dẫn API để truy vấn thông tin giao dịch quốc tế.
     */
    const QUERY_DR_INTERNATIONAL_URL = '/vpcpay/Vpcdps.op';

    /**
     * Id của client trong môi trường thử nghiệm dùng để giao tiếp với OnePay ở cổng quốc tế.
     */
    const SANDBOX_CLIENT_INTERNATIONAL_ID = '__sandboxInternational';

    /**
     * Id của client trong môi trường thử nghiệm dùng để giao tiếp với OnePay ở cổng nội địa.
     */
    const SANDBOX_CLIENT_DOMESTIC_ID = '__sandboxDomestic';

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
    public function getVersion(): string
    {
        return '2';
    }

    /**
     * @inheritdoc
     */
    public function getBaseUrl(): string
    {
        return $this->sandbox ? 'https://mtf.onepay.vn' : 'https://onepay.vn';
    }

    /**
     * @inheritdoc
     */
    public function beforeRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE_INTERNATIONAL) {
            $this->trigger(self::EVENT_BEFORE_PURCHASE_INTERNATIONAL, $event);
        } elseif ($event->command === self::RC_QUERY_DR_INTERNATIONAL) {
            $this->trigger(self::EVENT_BEFORE_QUERY_DR_INTERNATIONAL, $event);
        }

        parent::beforeRequest($event);
    }

    /**
     * @inheritdoc
     */
    public function afterRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE_INTERNATIONAL) {
            $this->trigger(self::EVENT_AFTER_PURCHASE_INTERNATIONAL, $event);
        } elseif ($event->command === self::RC_QUERY_DR_INTERNATIONAL) {
            $this->trigger(self::EVENT_AFTER_QUERY_DR_INTERNATIONAL, $event);
        }

        parent::afterRequest($event);
    }

    /**
     * @inheritdoc
     */
    public function verifiedRequest(VerifiedRequestEvent $event)
    {
        if ($event->command === self::VRC_PURCHASE_SUCCESS_INTERNATIONAL) {
            $this->trigger(self::EVENT_VERIFIED_REQUEST_PURCHASE_INTERNATIONAL_SUCCESS, $event);
        } elseif ($event->command === self::VRC_IPN_INTERNATIONAL) {
            $this->trigger(self::EVENT_VERIFIED_REQUEST_IPN_INTERNATIONAL, $event);
        }

        parent::verifiedRequest($event);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function initSandboxEnvironment()
    {
        $clientDomesticConfig = require(__DIR__ . '/sandbox-client-domestic.php');
        $clientInternationalConfig = require(__DIR__ . '/sandbox-client-international.php');

        $this->setClient(static::SANDBOX_CLIENT_DOMESTIC_ID, $clientDomesticConfig);
        $this->setClient(static::SANDBOX_CLIENT_INTERNATIONAL_ID, $clientInternationalConfig);
    }

    /**
     * @inheritdoc
     */
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
     * @return ResponseData|DataInterface
     * @inheritdoc
     */
    public function purchase(array $data, $clientId = null): DataInterface
    {
        $clientId = $clientId ?? ($this->sandbox ? static::SANDBOX_CLIENT_DOMESTIC_ID : null);

        return parent::purchase($data, $clientId);
    }

    /**
     * Phương thức này là phương thức ánh xạ của [[request()]] nó sẽ tạo lệnh [[RC_PURCHASE_INTERNATIONAL]],
     * để tạo yêu cầu giao dịch quốc tế tới OnePay.
     *
     * @param array $data Dữ liệu dùng để gửi đến OnePay khởi tạo giao dịch quốc tế
     * @param null|int|string $clientId Client id dùng để kết nối đến OnePay. Nếu không thiết lập,
     * [[getDefaultClient()]] sẽ được gọi.
     * @return ResponseData|DataInterface Phương thức sẽ trả về mẫu trừu tượng [[DataInterface]],
     * để lấy thông tin trả về từ cổng thanh toán.
     * @throws \ReflectionException|\yii\base\InvalidConfigException
     */
    public function purchaseInternational(array $data, $clientId = null): DataInterface
    {
        $clientId = $clientId ?? ($this->sandbox ? static::SANDBOX_CLIENT_INTERNATIONAL_ID : null);

        return $this->request(self::RC_PURCHASE_INTERNATIONAL, $data, $clientId);
    }

    /**
     * @return ResponseData|DataInterface
     * @inheritdoc
     */
    public function queryDR(array $data, $clientId = null): DataInterface
    {
        $clientId = $clientId ?? ($this->sandbox ? static::SANDBOX_CLIENT_DOMESTIC_ID : null);

        return parent::queryDR($data, $clientId);
    }

    /**
     * Phương thức này là phương thức ánh xạ của [[request()]] nó sẽ tạo lệnh [[RC_QUERY_DR_INTERNATIONAL]],
     * để tạo yêu cầu truy vấn giao dịch quốc tế tới OnePay.
     *
     * @param array $data Dữ liệu dùng để truy vấn thông tin giao dịch bên trong thường có mã giao dịch từ cổng thanh toán...
     * @param string|int $clientId Client id dùng để tạo yêu cầu truy vấn giao dịch quốc tế.
     * @return ResponseData|DataInterface Phương thức sẽ trả về mẫu trừu tượng [[DataInterface]],
     * để lấy thông tin trả về từ cổng thanh toán.
     * @throws \ReflectionException|\yii\base\InvalidConfigException
     */
    public function queryDRInternational(array $data, $clientId = null): DataInterface
    {
        $clientId = $clientId ?? ($this->sandbox ? static::SANDBOX_CLIENT_INTERNATIONAL_ID : null);

        return $this->request(self::RC_QUERY_DR_INTERNATIONAL, $data, $clientId);
    }

    /**
     * @return bool|VerifiedData
     * @inheritdoc
     */
    public function verifyRequestIPN($clientId = null, \yii\web\Request $request = null)
    {
        $clientId = $clientId ?? ($this->sandbox ? static::SANDBOX_CLIENT_DOMESTIC_ID : null);

        return parent::verifyRequestIPN($clientId, $request);
    }

    /**
     * Phương thức này là phương thức ánh xạ của [[verifyRequest()]] nó sẽ tạo lệnh [[VRC_IPN_INTERNATIONAL]],
     * để tạo yêu cầu xác minh tính hợp lệ của dữ liệu trả về từ OnePay đến máy chủ, khi khách hàng thanh toán qua cổng quốc tế.
     *
     * @param string|int $clientId Client id dùng để xác thực tính hợp lệ của dữ liệu.
     * @param \yii\web\Request|null $request Đối tượng `request` thực hiện truy cập hệ thống.
     * @return bool|VerifiedData Sẽ trả về FALSE nếu như dữ liệu không hợp lệ ngược lại sẽ trả về thông tin đơn hàng đã được xác thực.
     */
    public function verifyIPNInternationalRequest($clientId = null, \yii\web\Request $request = null)
    {
        $clientId = $clientId ?? ($this->sandbox ? static::SANDBOX_CLIENT_INTERNATIONAL_ID : null);

        return $this->verifyRequest(self::VRC_IPN_INTERNATIONAL, $clientId, $request);
    }

    /**
     * @return bool|VerifiedData
     * @inheritdoc
     */
    public function verifyRequestPurchaseSuccess($clientId = null, \yii\web\Request $request = null)
    {
        $clientId = $clientId ?? ($this->sandbox ? static::SANDBOX_CLIENT_DOMESTIC_ID : null);

        return parent::verifyRequestPurchaseSuccess($clientId, $request);
    }

    /**
     * Phương thức này là phương thức ánh xạ của [[verifyRequest()]] nó sẽ tạo lệnh [[VRC_PURCHASE_SUCCESS_INTERNATIONAL]]
     * để tạo yêu cầu xác minh tính hợp lệ của dữ liệu trả về từ máy khách đến máy chủ, khi khách hàng thanh toán quốc tế.
     *
     * @param string|int $clientId Client id dùng để xác thực tính hợp lệ của dữ liệu.
     * @param \yii\web\Request|null $request Đối tượng `request` thực hiện truy cập hệ thống.
     * @return bool|DataInterface Sẽ trả về FALSE nếu như dữ liệu không hợp lệ ngược lại sẽ trả về thông tin đơn hàng đã được xác thực.
     */
    public function verifyRequestPurchaseInternationalSuccess($clientId = null, \yii\web\Request $request = null)
    {
        $clientId = $clientId ?? ($this->sandbox ? static::SANDBOX_CLIENT_INTERNATIONAL_ID : null);

        return $this->verifyRequest(self::VRC_PURCHASE_SUCCESS_INTERNATIONAL, $clientId, $request);
    }


    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    protected function requestInternal(\vxm\gatewayclients\RequestData $requestData, \yii\httpclient\Client $httpClient): array
    {
        $command = $requestData->getCommand();
        $commandUrls = [
            self::RC_PURCHASE => self::PURCHASE_URL,
            self::RC_PURCHASE_INTERNATIONAL => self::PURCHASE_INTERNATIONAL_URL,
            self::RC_QUERY_DR => self::QUERY_DR_URL,
            self::RC_QUERY_DR_INTERNATIONAL => self::QUERY_DR_INTERNATIONAL_URL,
        ];

        $data = $requestData->get();
        $data[0] = $commandUrls[$command];

        if ($command === self::RC_PURCHASE || $command === self::RC_PURCHASE_INTERNATIONAL) {
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
            'vpc_Command', 'vpc_Locale', 'vpc_MerchTxnRef', 'vpc_Merchant', 'vpc_OrderInfo', 'vpc_Amount',
            'vpc_TxnResponseCode', 'vpc_TransactionNo', 'vcp_Message', 'vpc_SecureHash'
        ];

        if ($command === self::VRC_PURCHASE_SUCCESS_INTERNATIONAL || $command === self::VRC_IPN_INTERNATIONAL) {
            $params = array_merge($params, [
                'vpc_AcqResponseCode', 'vpc_Authorizeld', 'vpc_Card', 'vpc_3DSECI',
                'vpc_3Dsenrolled', 'vpc_3Dsstatus', 'vpc_CommercialCard'
            ]);
        }

        $data = [];

        foreach ($params as $param) {
            if (($value = $request->get($param)) !== null) {
                $data[$param] = $value;
            }
        }

        return $data;
    }

}
