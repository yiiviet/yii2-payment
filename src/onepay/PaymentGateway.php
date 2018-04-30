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

    const RC_ALL = 0xF;

    const VC_PURCHASE_SUCCESS_INTERNATIONAL = 0x04;

    const VC_PAYMENT_NOTIFICATION_INTERNATIONAL = 0x08;

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

    public $merchantConfig = ['class' => Merchant::class];

    public $requestDataConfig = ['class' => RequestData::class];

    public $responseDataConfig = ['class' => ResponseData::class];

    public $verifiedDataConfig = ['class' => Data::class];


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
     * @param null $merchantId
     * @param \yii\web\Request|null $request
     * @return bool|VerifiedData
     * @throws \yii\base\InvalidConfigException
     */
    public function verifyPurchaseInternationalSuccessRequest($merchantId = null, \yii\web\Request $request = null)
    {
        return $this->verifyRequest(self::VC_PURCHASE_SUCCESS_INTERNATIONAL, $merchantId, $request);
    }

    /**
     * @param array $data
     * @param null $merchantId
     * @return ResponseData|\yii2vn\payment\ResponseData
     * @throws \yii\base\InvalidConfigException
     */
    public function purchaseInternational(array $data, $merchantId = null): ResponseData
    {
        return $this->request(self::RC_PURCHASE_INTERNATIONAL, $data, $merchantId);
    }

    /**
     * @param int $command
     * @param \yii2vn\payment\BaseMerchant $merchant
     * @param Data $requestData
     * @param HttpClient $httpClient
     * @return array
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

        if (isset($commandUrls[$command])) {
            $data = $requestData->get();
            $data[0] = $commandUrls[$command];
            if ($command & (self::RC_PURCHASE | self::RC_PURCHASE_INTERNATIONAL)) {
                return ['location' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
            } else {
                return $httpClient->get($data)->send()->getData();
            }
        } else {
            return null;
        }
    }

    /**
     * @param int|string $command
     * @param \yii2vn\payment\BaseMerchant $merchant
     * @param \yii\web\Request $request
     * @return array|null
     */
    protected function getVerifyRequestData($command, \yii2vn\payment\BaseMerchant $merchant, \yii\web\Request $request): array
    {
        if ($command & self::VC_ALL) {
            $params = [
                'vpc_Command', 'vpc_Locale', 'vpc_MerchTxnRef', 'vpc_Merchant', 'vpc_OrderInfo', 'vpc_Amount',
                'vpc_TxnResponseCode', 'vpc_TransactionNo', 'vcp_Message', 'vpc_SecureHash'
            ];

            if ($command & (self::VC_PURCHASE_SUCCESS_INTERNATIONAL | self::VC_PAYMENT_NOTIFICATION_INTERNATIONAL)) {
                $params = array_merge($params, [
                    'vpc_AcqResponseCode',
                    'vpc_Authorizeld',
                    'vpc_Card',
                    'vpc_3DSECI',
                    'vpc_3Dsenrolled',
                    'vpc_3Dsstatus',
                    'vpc_CommercialCard'
                ]);
            }

            $data = [];

            foreach ($params as $param) {
                $data[$param] = $request->get($param);
            }

            return $data;
        } else {
            return null;
        }
    }

}