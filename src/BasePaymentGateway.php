<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use Yii;

use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client as HttpClient;

/**
 *
 * @property BaseMerchant $defaultMerchant
 * @property BaseMerchant $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BasePaymentGateway extends Component implements PaymentGatewayInterface
{

    const EVENT_BEFORE_CHECKOUT = 'beforeCheckout';

    const EVENT_AFTER_CHECKOUT = 'afterCheckout';

    /**
     * @var bool
     */
    public $sandbox = false;

    /**
     * @var array
     */
    public $checkoutRequestDataConfig = ['class' => CheckoutData::class];

    /**
     * @var array
     */
    public $checkoutResponseDataConfig = ['class' => CheckoutData::class];

    /**
     * @var array
     */
    public $checkoutReturnRequestDataConfig = ['class' => CheckoutData::class];

    /**
     * @var array
     */
    public $merchantConfig;

    /**
     * @var array|BaseMerchant[]
     */
    private $_merchants = [];

    /**
     * @param bool $sandbox
     * @inheritdoc
     */
    public static function baseUrl(): string
    {
        $sandbox = ArrayHelper::getValue(func_get_args(), 0, false);

        return static::getBaseUrl($sandbox);
    }

    /**
     * @param bool $sandbox
     * @return string
     */
    abstract protected static function getBaseUrl(bool $sandbox): string;

    /**
     * @param bool $load
     * @return array|BaseMerchant[]
     * @throws InvalidConfigException
     */
    public function getMerchants($load = true): array
    {
        if ($load) {
            $merchants = [];
            foreach ($this->_merchants as $id => $merchant) {
                $merchants[$id] = $this->getMerchant($id);
            }
        } else {
            $merchants = $this->_merchants;
        }

        return $merchants;
    }

    /**
     * @param array|BaseMerchant[] $merchants
     * @return bool
     */
    public function setMerchants(array $merchants): bool
    {
        foreach ($merchants as $id => $merchant) {
            $this->setMerchant($id, $merchant);
        }

        return true;
    }

    /**
     * @param null|string|int $id
     * @return object|BaseMerchant
     * @throws InvalidConfigException|InvalidArgumentException
     */
    public function getMerchant($id = null): MerchantInterface
    {
        if ($id === null) {
            return $this->getDefaultMerchant();
        } elseif ((is_string($id) || is_int($id))) {
            if (isset($this->_merchants[$id])) {
                $merchant = $this->_merchants[$id];

                if (is_array($merchant) || is_string($merchant)) {
                    if (is_string($merchant)) {
                        $merchant = ['class' => $merchant];
                    }
                    $merchant = ArrayHelper::merge($this->merchantConfig, $merchant);
                }

                if (!$merchant instanceof BaseMerchant) {
                    return $this->_merchants[$id] = Yii::createObject($merchant, [$this]);
                } else {
                    return $merchant;
                }

            } else {
                throw new InvalidConfigException(__METHOD__ . ": merchant id: `$id` not found!");
            }
        } else {
            throw new InvalidArgumentException('Only accept get merchant via string or int key type!');
        }
    }

    /**
     * @param int|string $id
     * @param null $merchant
     * @return bool
     */
    public function setMerchant($id, $merchant = null): bool
    {
        if ($merchant === null) {
            $this->_merchants[] = $id;
        } else {
            $this->_merchants[$id] = $merchant;
        }

        return true;
    }


    /**
     * @var null|BaseMerchant
     */
    private $_defaultMerchant;

    /**
     * @return BaseMerchant
     * @throws InvalidConfigException
     */
    protected function getDefaultMerchant(): BaseMerchant
    {
        if (!$this->_defaultMerchant) {
            $merchantIds = array_keys($this->_merchants);
            if ($merchantId = reset($merchantIds)) {
                return $this->_defaultMerchant = $this->getMerchant($merchantId);
            } else {
                throw new InvalidConfigException('Can not get default merchant on an empty array merchants!');
            }
        } else {
            return $this->_defaultMerchant;
        }
    }

    /**
     * @inheritdoc
     */
    public function checkout(array $data, string $method = null, $merchantId = null)
    {
        $method = $method ?? $this->getDefaultCheckoutMethod();
        $merchant = $this->getMerchant($merchantId);

        /** @var CheckoutData $requestData */

        $requestData = Yii::createObject($this->checkoutRequestDataConfig, [$method, $merchant, $data]);

        $event = Yii::createObject([
            'class' => CheckoutEvent::class,
            'requestData' => $requestData,
            'method' => $method,
        ]);

        $this->trigger(self::EVENT_BEFORE_CHECKOUT, $event);

        if ($event->isValid) {
            $data = $this->checkoutInternal($requestData);
            $responseData = Yii::createObject($this->checkoutResponseDataConfig, [$method, $merchant, $data]);
            $event->responseData = $responseData;
            $this->trigger(self::EVENT_AFTER_CHECKOUT, $event);

            return $responseData;
        } else {
            return false;
        }
    }

    /**
     * @throws InvalidConfigException|InvalidArgumentException
     * @inheritdoc
     */
    public function verifyCheckoutReturnRequest(string $method = null, $merchantId = null, \yii\web\Request $request = null): bool
    {
        $merchant = $this->getMerchant($merchantId);
        $method = $method ?? $this->getDefaultCheckoutMethod();

        if ($request === null && Yii::$app) {
            $request = Yii::$app->getRequest();
        } else {
            throw new InvalidArgumentException('Request instance arg must be set to verify return request is valid or not!');
        }

        $requestData = Yii::createObject($this->checkoutReturnRequestDataConfig, [$method, $merchant, $request->getQueryParams()]);

        return $this->verifyCheckoutReturnRequestInternal($requestData);
    }

    abstract protected function verifyCheckoutReturnRequestInternal(CheckoutData $requestData): bool;

    /**
     * @return null|string
     */
    abstract protected function getDefaultCheckoutMethod(): string;

    /**
     * @param CheckoutData $data
     * @return array
     */
    abstract protected function checkoutInternal(CheckoutData $data): array;


    /**
     * @var HttpClient|null
     */
    private $_httpClient;

    /**
     * @param bool $force
     * @return object|HttpClient
     * @throws InvalidConfigException
     */
    protected function getHttpClient(bool $force = false): HttpClient
    {
        if (!$this->_httpClient === null || $force) {
            /** @var HttpClient $client */

            $client = $this->_httpClient = Yii::createObject(ArrayHelper::merge([
                'class' => HttpClient::class
            ], $this->getHttpClientConfig()));
            $client->baseUrl = static::baseUrl($this->sandbox);

            return $client;
        } else {
            return $this->_httpClient;
        }
    }

    protected function getHttpClientConfig(): array
    {
        return [];
    }

}