<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use Yii;

use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client as HttpClient;

/**
 * Class BasePaymentGateway
 *
 * @property BaseMerchant $defaultMerchant
 * @property BaseMerchant $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BasePaymentGateway extends Component implements PaymentGatewayInterface
{

    /**
     * Purchase request command use to make request purchase.
     */
    const RC_PURCHASE = 0x01;

    /**
     * Query DR request command use to make request query DR.
     */
    const RC_QUERY_DR = 0x02;

    /**
     * Constance request command all. It only use for checking command is valid or not.
     *
     * @var int
     */
    const RC_ALL = 0x03;

    const VC_PURCHASE_SUCCESS = 0x01;

    const VC_PAYMENT_NOTIFICATION = 0x02;

    const VC_ALL = 0x03;

    const EVENT_VERIFIED_REQUEST = 'verifiedRequest';

    const EVENT_VERIFIED_PURCHASE_SUCCESS_REQUEST = 'verifiedPurchaseSuccessRequest';

    const EVENT_VERIFIED_PAYMENT_NOTIFICATION_REQUEST = 'verifiedPaymentNotificationRequest';

    const EVENT_BEFORE_REQUEST = 'beforeRequest';

    const EVENT_AFTER_REQUEST = 'afterRequest';

    const EVENT_BEFORE_PURCHASE = 'beforePurchase';

    const EVENT_AFTER_PURCHASE = 'afterPurchase';

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
    public $verifiedDataConfig = [];

    /**
     * @var array
     */
    public $merchantConfig = [];

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
        if ($this->sandbox) {
            $this->initSandboxEnvironment();
        }

        parent::init();
    }

    /**
     * Init sandbox environment.
     * In this method you may add merchants use for send request to payment gateway for test your system work corectly.
     * This method only call when property `sandbox` is true.
     */
    abstract protected function initSandboxEnvironment();

    /**
     * @var array|BaseMerchant[]
     */
    private $_merchants = [];

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

                if (is_string($merchant)) {
                    $merchant = ['class' => $merchant];
                }

                if (is_array($merchant)) {
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
     * @inheritdoc
     * @throws InvalidConfigException|InvalidArgumentException
     */
    public function purchase(array $data, $merchantId = null): DataInterface
    {
        return $this->request(self::RC_PURCHASE, $data, $merchantId);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException|InvalidArgumentException
     */
    public function queryDR(array $data, $merchantId = null): DataInterface
    {
        return $this->request(self::RC_QUERY_DR, $data, $merchantId);
    }

    /**
     * @param int $command
     * @param array $data
     * @param int|string|null $merchantId
     * @return ResponseData|DataInterface
     * @throws InvalidConfigException|InvalidArgumentException
     */
    public function request(int $command, array $data, $merchantId = null): \yiiviet\payment\ResponseData
    {
        $merchant = $this->getMerchant($merchantId);

        /** @var Data $requestData */

        $requestData = Yii::createObject($this->requestDataConfig, [$command, $merchant, $data]);
        $event = Yii::createObject([
            'class' => RequestEvent::class,
            'command' => $command,
            'merchant' => $merchant,
            'requestData' => $requestData
        ]);

        if ($command & static::RC_ALL && $command !== static::RC_ALL) {
            $this->beforeRequest($event);
            $httpClient = $this->getHttpClient();
            $data = $this->requestInternal($command, $merchant, $requestData, $httpClient);
            $responseData = Yii::createObject($this->responseDataConfig, [$command, $merchant, $data]);
            $event->responseData = $responseData;
            $this->afterRequest($event);
            Yii::debug(__CLASS__ . " requested sent with command: '$command'");

            return $responseData;
        } else {
            throw new InvalidArgumentException("Unknown request command '$command'");
        }
    }

    public function beforeRequest(\yiiviet\payment\RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE) {
            $this->trigger(self::EVENT_BEFORE_PURCHASE, $event);
        } elseif ($event->command === self::RC_QUERY_DR) {
            $this->trigger(self::EVENT_BEFORE_QUERY_DR, $event);
        }

        $this->trigger(self::EVENT_BEFORE_REQUEST, $event);
    }

    public function afterRequest(\yiiviet\payment\RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE) {
            $this->trigger(self::EVENT_AFTER_PURCHASE, $event);
        } elseif ($event->command === self::RC_QUERY_DR) {
            $this->trigger(self::EVENT_AFTER_QUERY_DR, $event);
        }

        $this->trigger(self::EVENT_AFTER_REQUEST, $event);
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
                'baseUrl' => self::baseUrl($this->sandbox)
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
     * @param int $command
     * @param BaseMerchant $merchant
     * @param Data $requestData
     * @param HttpClient $httpClient
     * @return array
     */
    abstract protected function requestInternal(int $command, \yiiviet\payment\BaseMerchant $merchant, \yiiviet\payment\Data $requestData, \yii\httpclient\Client $httpClient): array;


    /**
     * @inheritdoc
     * @throws InvalidConfigException|InvalidArgumentException
     */
    public function verifyPurchaseSuccessRequest($merchantId = null, \yii\web\Request $request = null)
    {
        return $this->verifyRequest(self::VC_PURCHASE_SUCCESS, $merchantId, $request);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException|InvalidArgumentException
     */
    public function verifyPaymentNotificationRequest($merchantId = null, \yii\web\Request $request = null)
    {
        return $this->verifyRequest(self::VC_PAYMENT_NOTIFICATION, $merchantId, $request);
    }

    /**
     * @param $command
     * @param null|int|string $merchantId
     * @param \yii\web\Request|null $request
     * @return bool|VerifiedData
     * @throws InvalidConfigException|InvalidArgumentException
     */
    public function verifyRequest($command, $merchantId = null, \yii\web\Request $request = null)
    {
        $merchant = $this->getMerchant($merchantId);

        if ($request === null && Yii::$app) {
            $request = Yii::$app->getRequest();
        } else {
            throw new InvalidArgumentException('Request instance arg must be set to verify return request is valid or not!');
        }

        if ($command & static::VC_ALL && $command !== static::VC_ALL) {
            $data = $this->getVerifyRequestData($command, $merchant, $request);
            /** @var VerifiedData $requestData */
            $verifyData = Yii::createObject($this->verifiedDataConfig, [$command, $merchant, $data]);
            if ($verifyData->validate()) {
                $event = Yii::createObject([
                    'class' => VerifiedRequestEvent::class,
                    'verifiedData' => $verifyData,
                    'merchant' => $merchant,
                    'command' => $command
                ]);
                $this->verifiedRequest($event);

                return $verifyData;
            } else {
                return false;
            }
        } else {
            throw new InvalidArgumentException("Unknown verify request command: `$command`");
        }
    }

    /**
     * @param VerifiedRequestEvent $event
     */
    public function verifiedRequest(\yiiviet\payment\VerifiedRequestEvent $event)
    {
        if ($event->command === self::VC_PAYMENT_NOTIFICATION) {
            $this->trigger(self::EVENT_VERIFIED_PAYMENT_NOTIFICATION_REQUEST, $event);
        } elseif ($event->command === self::VC_PURCHASE_SUCCESS) {
            $this->trigger(self::EVENT_VERIFIED_PURCHASE_SUCCESS_REQUEST, $event);
        }

        $this->trigger(self::EVENT_VERIFIED_REQUEST, $event);
    }

    /**
     * @param int $command
     * @param BaseMerchant $merchant
     * @param \yii\web\Request $request
     * @return array|null
     */
    abstract protected function getVerifyRequestData(int $command, \yiiviet\payment\BaseMerchant $merchant, \yii\web\Request $request): array;


}
