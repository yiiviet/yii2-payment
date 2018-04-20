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

    const REQUEST_COMMAND_PURCHASE = 'purchase';

    const REQUEST_COMMAND_QUERY_DR = 'queryDR';

    const EVENT_BEFORE_PURCHASE = 'beforePurchase';

    const EVENT_AFTER_PURCHASE = 'afterPurchase';

    const EVENT_BEFORE_REQUEST = 'beforeRequest';

    const EVENT_AFTER_REQUEST = 'afterRequest';

    const EVENT_BEFORE_QUERY_DR = 'beforeQueryDR';

    const EVENT_AFTER_QUERY_DR = 'afterQueryDR';

    /**
     * @var bool
     */
    public $sandbox = false;

    /**
     * @var array
     */
    public $requestDataConfig = [];

    /**
     * @var array
     */
    public $responseDataConfig = [];

    /**
     * @var array
     */
    public $returnRequestDataConfig = [];

    /**
     * @var array
     */
    public $merchantConfig = [];

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
        return static::getBaseUrl(func_get_arg(0));
    }

    /**
     * @param bool $sandbox
     * @return string
     */
    abstract protected static function getBaseUrl(bool $sandbox): string;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->on(self::EVENT_BEFORE_REQUEST, function (RequestEvent $event) {
            if ($event->command === self::REQUEST_COMMAND_PURCHASE) {
                $this->trigger(self::EVENT_BEFORE_PURCHASE, $event);
            } elseif ($event->command === self::REQUEST_COMMAND_QUERY_DR) {
                $this->trigger(self::EVENT_BEFORE_QUERY_DR, $event);
            }
        });

        $this->on(self::EVENT_AFTER_REQUEST, function (RequestEvent $event) {
            if ($event->command === self::REQUEST_COMMAND_PURCHASE) {
                $this->trigger(self::EVENT_AFTER_PURCHASE, $event);
            } elseif ($event->command === self::REQUEST_COMMAND_QUERY_DR) {
                $this->trigger(self::EVENT_AFTER_QUERY_DR, $event);
            }
        });

        parent::init();
    }

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
     * @return object|BaseMerchant|MerchantInterface
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
    public function getDefaultMerchant(): BaseMerchant
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
     * @param array $data
     * @param null $merchantId
     * @return bool|object|DataInterface|Data
     * @throws InvalidConfigException
     */
    public function purchase(array $data, $merchantId = null)
    {
        return $this->request(self::REQUEST_COMMAND_PURCHASE, $data, $merchantId);
    }

    /**
     * @param array $data
     * @param int|string $merchantId
     * @return bool|object|Data
     * @throws InvalidConfigException
     */
    public function queryDR(array $data, $merchantId)
    {
        return $this->request(self::REQUEST_COMMAND_QUERY_DR, $data, $merchantId);
    }

    /**
     * @param int|string $command
     * @param array $data
     * @param null $merchantId
     * @return bool|object|Data
     * @throws InvalidConfigException
     */
    public function request($command, array $data, $merchantId = null)
    {
        $merchant = $this->getMerchant($merchantId);

        /** @var Data $requestData */

        $requestData = Yii::createObject($this->requestDataConfig, [$command, $merchant, $data]);

        $event = Yii::createObject([
            'class' => RequestEvent::class,
            'requestData' => $requestData,
            'command' => $command,
        ]);

        $this->trigger(self::EVENT_BEFORE_REQUEST, $event);

        if ($event->isValid) {
            $data = $this->requestInternal($requestData, $this->getHttpClient());
            $responseData = Yii::createObject($this->responseDataConfig, [$command, $merchant, $data]);
            $event->responseData = $responseData;
            $this->trigger(self::EVENT_AFTER_REQUEST, $event);

            return $responseData;
        } else {
            return false;
        }
    }

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
                'class' => HttpClient::class,
                'baseUrl' => static::baseUrl($this->sandbox)
            ], $this->getHttpClientConfig()));

            return $client;
        } else {
            return $this->_httpClient;
        }
    }

    protected function getHttpClientConfig(): array
    {
        return [];
    }

    /**
     * @param Data $requestData
     * @param HttpClient $httpClient
     * @return array
     */
    abstract protected function requestInternal(Data $requestData, HttpClient $httpClient): array;


    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function verifyPurchaseSuccessRequest($merchantId, \yii\web\Request $request = null)
    {
        return $this->verifyReturnRequest(self::REQUEST_COMMAND_PURCHASE, $merchantId, $request);
    }

    /**
     * @param $command
     * @param null|int|string $merchantId
     * @param \yii\web\Request|null $request
     * @return bool|Data
     * @throws InvalidConfigException
     */
    public function verifyReturnRequest($command, $merchantId = null, \yii\web\Request $request = null)
    {
        $merchant = $this->getMerchant($merchantId);

        if ($request === null && Yii::$app) {
            $request = Yii::$app->getRequest();
        } else {
            throw new InvalidArgumentException('Request instance arg must be set to verify return request is valid or not!');
        }

        /** @var Data $requestData */

        $requestData = Yii::createObject($this->returnRequestDataConfig, [$command, $merchant, $request->getQueryParams()]);

        if ($requestData->validate()) {
            return $requestData;
        } else {
            return false;
        }
    }

}