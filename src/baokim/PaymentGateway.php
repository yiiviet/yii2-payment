<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\baokim;

use yii\di\Instance;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;

use yiiviet\payment\BasePaymentGateway;

/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    const RC_PURCHASE_PRO = 0x04;

    const RC_GET_MERCHANT_DATA = 0x08;

    const RC_ALL = 0x0F;

    const EVENT_BEFORE_PURCHASE_PRO = 'beforePurchasePro';

    const EVENT_AFTER_PURCHASE_PRO = 'afterPurchasePro';

    const EVENT_BEFORE_GET_MERCHANT_DATA = 'beforeGetMerchantData';

    const EVENT_AFTER_GET_MERCHANT_DATA = 'afterGetMerchantData';

    const PURCHASE_URL = '/payment/order/version11';

    const PURCHASE_PRO_URL = '/payment/rest/payment_pro_api/pay_by_card';

    const PRO_SELLER_INFO_URL = '/payment/rest/payment_pro_api/get_seller_info';

    const QUERY_DR_URL = '/payment/order/queryTransaction';

    const MUI_CHARGE = 'charge';

    const MUI_BASE = 'base';

    const MUI_IFRAME = 'iframe';

    const DIRECT_TRANSACTION = 1;

    const SAFE_TRANSACTION = 2;

    /**
     * @var bool|string|array|\yii\caching\Cache
     */
    public $merchantDataCache = 'cache';

    public $merchantDataCacheDuration = 86400;

    /**
     * @var array
     */
    public $merchantConfig = ['class' => Merchant::class];

    public $requestDataConfig = ['class' => RequestData::class];

    public $responseDataConfig = ['class' => RequestData::class];

    public $verifiedDataConfig = ['class' => VerifiedData::class];

    /**
     * @inheritdoc
     */
    public static function version(): string
    {
        return '1.0';
    }

    /**
     * @inheritdoc
     */
    protected static function getBaseUrl(bool $sandbox): string
    {
        return $sandbox ? 'https://sandbox.baokim.vn' : 'https://www.baokim.vn';
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @inheritdoc
     */
    public function init()
    {
        if ($this->merchantDataCache) {
            $this->merchantDataCache = Instance::ensure($this->merchantDataCache, 'yii\caching\Cache');
        }

        parent::init();
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
     * @param array $data
     * @param null $merchantId
     * @return \yiiviet\payment\ResponseData
     * @throws \yii\base\InvalidConfigException
     */
    public function purchasePro(array $data, $merchantId = null)
    {
        return $this->request(self::RC_PURCHASE_PRO, $data, $merchantId);
    }

    /**
     * @inheritdoc
     */
    protected function getHttpClientConfig(): array
    {
        return [
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'format' => 'json',
                'options' => [
                    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST | CURLAUTH_BASIC,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ]
            ]
        ];
    }


    /**
     * @param string $emailBusiness
     * @param int|string|null $merchantId
     * @throws \yii\base\InvalidConfigException|NotSupportedException
     * @return object|ResponseData|bool
     */
    public function getMerchantData(string $emailBusiness = null, $merchantId = null): ResponseData
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant($merchantId);
        $cacheKey = [
            __METHOD__,
            get_class($merchant),
            $merchant->id,
            $emailBusiness
        ];

        if (!$this->merchantDataCache || !$responseData = $this->merchantDataCache->get($cacheKey)) {
            $responseData = $this->request(self::RC_GET_MERCHANT_DATA, [
                'business' => $emailBusiness ?? $merchant->email
            ]);

            if ($this->merchantDataCache) {
                $this->merchantDataCache->set($cacheKey, $responseData, $this->merchantDataCacheDuration);
            }
        }

        return $responseData;
    }

    /**
     * @inheritdoc
     */
    public function beforeRequest(\yiiviet\payment\RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE_PRO) {
            $this->trigger(self::EVENT_BEFORE_PURCHASE_PRO, $event);
        } elseif ($event->command === self::RC_GET_MERCHANT_DATA) {
            $this->trigger(self::EVENT_BEFORE_GET_MERCHANT_DATA, $event);
        }

        parent::beforeRequest($event);
    }

    /**
     * @inheritdoc
     */
    public function afterRequest(\yiiviet\payment\RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE_PRO) {
            $this->trigger(self::EVENT_AFTER_PURCHASE_PRO, $event);
        } elseif ($event->command === self::RC_GET_MERCHANT_DATA) {
            $this->trigger(self::EVENT_AFTER_GET_MERCHANT_DATA, $event);
        }

        parent::afterRequest($event);
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException|\yii\base\InvalidConfigException
     */
    protected function requestInternal(int $command, \yiiviet\payment\BasePaymentClient $merchant, \yiiviet\payment\Data $requestData, \yii\httpclient\Client $httpClient): array
    {
        /** @var Merchant $merchant */

        $data = $requestData->get();
        $httpMethod = 'POST';

        if ($command & (self::RC_GET_MERCHANT_DATA | self::RC_QUERY_DR)) {
            if ($command === self::RC_GET_MERCHANT_DATA) {
                $url = self::PRO_SELLER_INFO_URL;
            } else {
                $url = self::QUERY_DR_URL;
            }
            $data[0] = $url;
            $url = $data;
            $data = null;
            $httpMethod = 'GET';
        } elseif ($command === self::RC_PURCHASE_PRO) {
            $url = [self::PURCHASE_PRO_URL, 'signature' => ArrayHelper::remove($data, 'signature')];
        } else {
            $data[0] = self::PURCHASE_URL;
            return ['location' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
        }

        return $httpClient->createRequest()
            ->setUrl($url)
            ->setMethod($httpMethod)
            ->setOptions([CURLOPT_USERPWD => $merchant->apiUser . ':' . $merchant->apiPassword])
            ->setData($data)
            ->send()
            ->getData();
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData(int $command, \yiiviet\payment\BasePaymentClient $merchant, \yii\web\Request $request): array
    {
        $params = [
            'order_id', 'transaction_id', 'created_on', 'payment_type', 'transaction_status', 'total_amount', 'net_amount',
            'fee_amount', 'merchant_id', 'customer_name', 'customer_email', 'customer_phone', 'customer_address', 'checksum'
        ];
        $commandRequestMethods = [self::VC_PURCHASE_SUCCESS => 'get', self::VC_PAYMENT_NOTIFICATION => 'post'];
        $requestMethod = $commandRequestMethods[$command];
        $data = [];

        foreach ($params as $param) {
            if (($value = call_user_func([$request, $requestMethod], $param)) && !is_null($value)) {
                $data[$param] = $value;
            }
        }

        return $data;
    }
}
