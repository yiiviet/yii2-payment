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
use yii2vn\payment\DataInterface;


/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const RC_PURCHASE_INTERNATIONAL = 0x04;

    const RC_QUERY_DR_INTERNATIONAL = 0x08;

    const VC_PURCHASE_SUCCESS_INTERNATIONAL = 0x04;

    const VC_PAYMENT_NOTIFICATION_INTERNATIONAL = 0x08;

    /**
     * @inheritdoc
     */
    const RC_ALL = 0xF;

    /**
     * @inheritdoc
     */
    const VC_ALL = 0xF;

    const EVENT_BEFORE_PURCHASE_INTERNATIONAL = 'beforePurchaseInternational';

    const EVENT_AFTER_PURCHASE_INTERNATIONAL = 'afterPurchaseInternational';

    const EVENT_BEFORE_QUERY_DR_INTERNATIONAL = 'beforeQueryDRInternational';

    const EVENT_AFTER_QUERY_DR_INTERNATIONAL = 'afterQueryDRInternational';

    const EVENT_VERIFIED_PURCHASE_INTERNATIONAL_SUCCESS_REQUEST = 'verifiedPurchaseInternationalSuccessRequest';

    const EVENT_VERIFIED_PAYMENT_NOTIFICATION_INTERNATIONAL_REQUEST = 'verifiedPaymentNotificationInternationalRequest';

    const PURCHASE_URL = '/onecomm-pay/vpc.op';

    const QUERY_DR_URL = '/onecomm-pay/Vpcdps.op';

    const PURCHASE_INTERNATIONAL_URL = '/vpcpay/vpcpay.op';

    const QUERY_DR_INTERNATIONAL_URL = '/vpcpay/Vpcdps.op';

    const SANDBOX_MERCHANT_INTERNATIONAL_ID = '__sandboxInternational';

    const SANDBOX_MERCHANT_DOMESTIC_ID = '__sandboxDomestic';

    /**
     * @inheritdoc
     */
    public $merchantConfig = ['class' => Merchant::class];

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
    public function beforeRequest(\yii2vn\payment\RequestEvent $event)
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
    public function afterRequest(\yii2vn\payment\RequestEvent $event)
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
    public function verifiedRequest(\yii2vn\payment\VerifiedRequestEvent $event)
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
    protected function requestInternal(int $command, \yii2vn\payment\BaseMerchant $merchant, \yii2vn\payment\Data $requestData, \yii\httpclient\Client $httpClient): array
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
    protected function getVerifyRequestData(int $command, \yii2vn\payment\BaseMerchant $merchant, \yii\web\Request $request): array
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