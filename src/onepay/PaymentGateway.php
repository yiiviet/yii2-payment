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
use yiiviet\payment\Data;
use yiiviet\payment\DataInterface;


/**
 * Class PaymentGateway
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
    const EVENT_VERIFIED_PURCHASE_INTERNATIONAL_SUCCESS_REQUEST = 'verifiedPurchaseInternationalSuccessRequest';

    /**
     * @event VerifiedRequestEvent được gọi khi dữ liệu truy vấn sau khi khách hàng thanh toán thành công bằng cổng quốc tế,
     * được OnePay bắn `request` sang hệ thống đã xác thực.
     */
    const EVENT_VERIFIED_IPN_INTERNATIONAL_REQUEST = 'verifiedIPNInternationalRequest';

    const PURCHASE_URL = '/onecomm-pay/vpc.op';

    const QUERY_DR_URL = '/onecomm-pay/Vpcdps.op';

    const PURCHASE_INTERNATIONAL_URL = '/vpcpay/vpcpay.op';

    const QUERY_DR_INTERNATIONAL_URL = '/vpcpay/Vpcdps.op';

    const SANDBOX_MERCHANT_INTERNATIONAL_ID = '__sandboxInternational';

    const SANDBOX_MERCHANT_DOMESTIC_ID = '__sandboxDomestic';

    /**
     * @inheritdoc
     */
    public $merchantConfig = ['class' => PaymentClient::class];

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
    public $verifiedDataConfig = ['class' => Data::class];

    /**
     * @inheritdoc
     */
    public static function version(): string
    {
        return '2';
    }

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
    {
        return $sandbox ? 'https://mtf.onepay.vn' : 'https://onepay.vn';
    }

    /**
     * @inheritdoc
     */
    public function beforeRequest(\yiiviet\payment\RequestEvent $event)
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
    public function afterRequest(\yiiviet\payment\RequestEvent $event)
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
    public function verifiedRequest(\yiiviet\payment\VerifiedRequestEvent $event)
    {
        if ($event->command === self::VC_PURCHASE_SUCCESS_INTERNATIONAL) {
            $this->trigger(self::EVENT_VERIFIED_PURCHASE_INTERNATIONAL_SUCCESS_REQUEST, $event);
        } elseif ($event->command === self::VC_PAYMENT_NOTIFICATION_INTERNATIONAL) {
            $this->trigger(self::EVENT_VERIFIED_PAYMENT_NOTIFICATION_INTERNATIONAL_REQUEST, $event);
        }

        parent::verifiedRequest($event);
    }

    /**
     * @inheritdoc
     */
    protected function initSandboxEnvironment()
    {
        $merchantDomesticConfig = require(__DIR__ . '/sandbox-merchant-domestic.php');
        $merchantInternationalConfig = require(__DIR__ . '/sandbox-merchant-international.php');

        $this->setMerchant(static::SANDBOX_MERCHANT_DOMESTIC_ID, $merchantDomesticConfig);
        $this->setMerchant(static::SANDBOX_MERCHANT_INTERNATIONAL_ID, $merchantInternationalConfig);
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
    public function purchase(array $data, $merchantId = null): DataInterface
    {
        $merchantId = $merchantId ?? ($this->sandbox ? static::SANDBOX_MERCHANT_DOMESTIC_ID : null);

        return parent::purchase($data, $merchantId);
    }

    /**
     * @param array $data
     * @param null|int|string $merchantId
     * @return ResponseData|DataInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function purchaseInternational(array $data, $merchantId = null): DataInterface
    {
        $merchantId = $merchantId ?? ($this->sandbox ? static::SANDBOX_MERCHANT_INTERNATIONAL_ID : null);

        return $this->request(self::RC_PURCHASE_INTERNATIONAL, $data, $merchantId);
    }

    /**
     * @return ResponseData|DataInterface
     * @inheritdoc
     */
    public function queryDR(array $data, $merchantId = null): DataInterface
    {
        $merchantId = $merchantId ?? ($this->sandbox ? static::SANDBOX_MERCHANT_DOMESTIC_ID : null);

        return parent::queryDR($data, $merchantId);
    }

    /**
     * @param array $data
     * @param null|int|string $merchantId
     * @return ResponseData|DataInterface
     * @throws \yii\base\InvalidConfigException
     */
    public function queryDRInternational(array $data, $merchantId = null): DataInterface
    {
        $merchantId = $merchantId ?? ($this->sandbox ? static::SANDBOX_MERCHANT_INTERNATIONAL_ID : null);

        return $this->request(self::RC_QUERY_DR_INTERNATIONAL, $data, $merchantId);
    }

    /**
     * @return bool|VerifiedData
     * @inheritdoc
     */
    public function verifyPaymentNotificationRequest($merchantId = null, \yii\web\Request $request = null)
    {
        $merchantId = $merchantId ?? ($this->sandbox ? static::SANDBOX_MERCHANT_DOMESTIC_ID : null);

        return parent::verifyPaymentNotificationRequest($merchantId, $request);
    }

    /**
     * @param null|int|string $merchantId
     * @param \yii\web\Request|null $request
     * @return bool|VerifiedData
     * @throws \yii\base\InvalidConfigException
     */
    public function verifyPaymentNotificationInternationalRequest($merchantId = null, \yii\web\Request $request = null)
    {
        $merchantId = $merchantId ?? ($this->sandbox ? static::SANDBOX_MERCHANT_INTERNATIONAL_ID : null);

        return $this->verifyRequest(self::VC_PAYMENT_NOTIFICATION_INTERNATIONAL, $merchantId, $request);
    }

    /**
     * @return bool|VerifiedData
     * @inheritdoc
     */
    public function verifyPurchaseSuccessRequest($merchantId = null, \yii\web\Request $request = null)
    {
        $merchantId = $merchantId ?? ($this->sandbox ? static::SANDBOX_MERCHANT_DOMESTIC_ID : null);

        return parent::verifyPurchaseSuccessRequest($merchantId, $request);
    }

    /**
     * @param null|int|string $merchantId
     * @param \yii\web\Request|null $request
     * @return bool|VerifiedData
     * @throws \yii\base\InvalidConfigException
     */
    public function verifyPurchaseInternationalSuccessRequest($merchantId = null, \yii\web\Request $request = null)
    {
        $merchantId = $merchantId ?? ($this->sandbox ? static::SANDBOX_MERCHANT_INTERNATIONAL_ID : null);

        return $this->verifyRequest(self::VC_PURCHASE_SUCCESS_INTERNATIONAL, $merchantId, $request);
    }


    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    protected function requestInternal(int $command, \yiiviet\payment\BasePaymentClient $merchant, \yiiviet\payment\Data $requestData, \yii\httpclient\Client $httpClient): array
    {
        $commandUrls = [
            self::RC_PURCHASE => self::PURCHASE_URL,
            self::RC_PURCHASE_INTERNATIONAL => self::PURCHASE_INTERNATIONAL_URL,
            self::RC_QUERY_DR => self::QUERY_DR_URL,
            self::RC_QUERY_DR_INTERNATIONAL => self::QUERY_DR_INTERNATIONAL_URL,
        ];

        $data = $requestData->get();
        $data[0] = $commandUrls[$command];

        if ($command & (self::RC_PURCHASE | self::RC_PURCHASE_INTERNATIONAL)) {
            return ['location' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
        } else {
            return $httpClient->get($data)->send()->getData();
        }
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData(int $command, \yiiviet\payment\BasePaymentClient $merchant, \yii\web\Request $request): array
    {
        $params = [
            'vpc_Command', 'vpc_Locale', 'vpc_MerchTxnRef', 'vpc_Merchant', 'vpc_OrderInfo', 'vpc_Amount',
            'vpc_TxnResponseCode', 'vpc_TransactionNo', 'vcp_Message', 'vpc_SecureHash'
        ];

        if ($command & (self::VC_PURCHASE_SUCCESS_INTERNATIONAL | self::VC_PAYMENT_NOTIFICATION_INTERNATIONAL)) {
            $params = array_merge($params, [
                'vpc_AcqResponseCode', 'vpc_Authorizeld', 'vpc_Card', 'vpc_3DSECI',
                'vpc_3Dsenrolled', 'vpc_3Dsstatus', 'vpc_CommercialCard'
            ]);
        }

        $data = [];

        foreach ($params as $param) {
            $data[$param] = $request->get($param);
        }

        return $data;
    }

}
