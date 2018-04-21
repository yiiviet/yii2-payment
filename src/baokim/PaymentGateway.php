<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\di\Instance;
use yii\base\NotSupportedException;
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

    const REQUEST_COMMAND_PURCHASE_PRO = 'purchasePro';

    const REQUEST_COMMAND_MERCHANT_DATA = 'merchantData';

    const PURCHASE_URL = '/payment/order/version11';

    const PURCHASE_PRO_URL = '/payment/rest/payment_pro_api/pay_by_card';

    const PRO_SELLER_INFO_URL = '/payment/rest/payment_pro_api/get_seller_info';

    const QUERY_DR_URL = '/payment/order/queryTransaction';

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

    public $returnRequestDataConfig = ['class' => ReturnRequestData::class];

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
            $emailBusiness,
            $merchant->id
        ];

        if (!$this->merchantDataCache || !$responseData = $this->merchantDataCache->get($cacheKey)) {
            $responseData = $this->request(self::REQUEST_COMMAND_MERCHANT_DATA, [
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
     * @throws NotSupportedException|\yii\base\InvalidConfigException
     */
    protected function requestInternal(Data $requestData, HttpClient $httpClient): ?array
    {
        /** @var Merchant $merchant */

        $merchant = $requestData->getMerchant();
        $command = $requestData->getCommand();
        $httpMethod = 'POST';

        if ($command === self::REQUEST_COMMAND_MERCHANT_DATA || $command === self::REQUEST_COMMAND_QUERY_DR) {
            if ($command === self::REQUEST_COMMAND_MERCHANT_DATA) {
                $url = self::PRO_SELLER_INFO_URL;
            } else {
                $url = self::QUERY_DR_URL;
            }
            $httpMethod = 'GET';
        } elseif ($command === self::REQUEST_COMMAND_PURCHASE) {
            $url = self::PURCHASE_URL;
        } elseif ($command === self::REQUEST_COMMAND_PURCHASE_PRO) {
            $url = self::PURCHASE_PRO_URL;
        } else {
            return null;
        }

        $data = $requestData->get();
        $httpRequest = $httpClient->createRequest();
        $httpResponse = $httpRequest->setUrl($url)
            ->setMethod($httpMethod)
            ->setOptions([CURLOPT_USERPWD => $merchant->apiUser . ':' . $merchant->apiPassword])
            ->setData($data)
            ->send();


        Yii::debug(__CLASS__ . " requested sent with command: $command");

        return $httpResponse->getData();
    }
}