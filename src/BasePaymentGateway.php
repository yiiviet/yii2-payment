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
use yii\httpclient\Client as HttpClient;
use yii\httpclient\Response as HttpClientResponse;

/**
 *
 * @property HttpClient $httpClient
 * @property MerchantInterface $defaultMerchant
 * @property MerchantInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BasePaymentGateway extends Component implements PaymentGatewayInterface
{

    public $merchantClass;

    private $_merchants = [];

    /**
     * @param bool $load
     * @return array|MerchantInterface[]
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
     * @param array|MerchantInterface[] $merchants
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
     * @return mixed|MerchantInterface
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
                    $merchant = ['class' => $merchant, 'paymentGateway' => $this];
                } elseif (is_array($merchant)) {
                    $merchant['paymentGateway'] = $this;
                    if (!isset($merchant['class'])) {
                        $merchant['class'] = $this->merchantClass;
                    }
                }

                if (!$merchant instanceof MerchantInterface) {
                    return $this->_merchants[$id] = Yii::createObject($merchant);
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
     * @var null|MerchantInterface
     */
    private $_defaultMerchant;

    /**
     * @return MerchantInterface|BaseMerchant
     * @throws InvalidConfigException
     */
    public function getDefaultMerchant(): MerchantInterface
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
     * @param array|string|CheckoutInstanceInterface $instance
     * @param string $method
     * @return CheckoutResponseDataInterface|bool
     * @throws InvalidConfigException
     */
    public function checkout($instance, string $method = self::CHECKOUT_METHOD_INTERNET_BANKING)
    {
        $instance = $this->prepareCheckoutInstance($instance, $method);

        $event = Yii::createObject([
            'class' => CheckoutEvent::class,
            'instance' => $instance,
            'method' => $method,
        ]);

        $this->trigger(self::EVENT_BEFORE_CHECKOUT, $event);

        if ($event->isValid) {
            $responseData = $this->checkoutInternal($instance, $method);
            $event->responseData = $responseData;
            $this->trigger(self::EVENT_AFTER_CHECKOUT, $event);
            return $responseData;
        } else {
            return false;
        }
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @param string $method
     * @return CheckoutResponseDataInterface
     */
    abstract protected function checkoutInternal(CheckoutInstanceInterface $instance, string $method): CheckoutResponseDataInterface;

    /**
     * @param array|string|CheckoutInstanceInterface $instance
     * @param string $method
     * @return object|CheckoutInstanceInterface
     * @throws InvalidConfigException
     */
    protected function prepareCheckoutInstance($instance, string $method): CheckoutInstanceInterface
    {
        if ($instance instanceof CheckoutInstanceInterface) {
            return $instance;
        } elseif (is_array($instance) || is_string($instance)) {
            if (is_string($instance)) {
                $instance = ['class' => $instance];
            }

            if (!isset($instance['class']) && $class = $this->getDefaultCheckoutInstanceClass($method)) {
                $instance['class'] = $class;
            }

            if (!isset($instance['merchant'])) {
                $instance['merchant'] = $this->getDefaultMerchant();
            }
        }

        return Yii::createObject($instance);
    }

    /**
     * @param string $method
     * @return null|string
     */
    abstract protected function getDefaultCheckoutInstanceClass(string $method): ?string;


    /**
     * @var HttpClient|null
     */
    private $_httpClient = ['class' => HttpClient::class, 'transport' => 'yii\httpclient\CurlTransport'];

    /**
     * @return HttpClient
     * @throws InvalidConfigException
     */
    public function getHttpClient(): HttpClient
    {
        if (!$this->_httpClient instanceof HttpClient) {
            $client = $this->_httpClient = Yii::createObject($this->_httpClient);
            $client->baseUrl = $this->getBaseUrl();

            return $client;
        } else {
            return $this->_httpClient;
        }
    }

    /**
     * @param array|string|callable|HttpClient $client
     * @return bool
     */
    public function setHttpClient($client): bool
    {
        if ($client instanceof HttpClient) {
            $client->baseUrl = $this->getBaseUrl();
        }

        $this->_httpClient = $client;

        return true;
    }

    /**
     * @param MerchantInterface $merchant
     * @param string $httpMethod
     * @param array $queryData
     * @param string $format
     * @return HttpClientResponse
     */
    abstract protected function sendHttpRequest(MerchantInterface $merchant, string $httpMethod, array $queryData, string $format = HttpClient::FORMAT_JSON): HttpClientResponse;

}