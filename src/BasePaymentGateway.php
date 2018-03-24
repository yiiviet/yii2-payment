<?php
/**
 * @link http://github.com/yii2vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\httpclient\Client as HttpClient;

/**
 * @package yii2vn\payment
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 *
 * @property MerchantInterface $defaultMerchant
 * @property MerchantInterface $merchant
 */
abstract class BasePaymentGateway extends Component implements PaymentGatewayInterface
{

    /**
     * @var array|string|HttpClient
     */
    public $httpClient = ['class' => HttpClient::class];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (is_string($this->httpClient)) {
            $httpClient = ['class' => $this->httpClient, 'baseUrl' => static::baseUrl()];
        } elseif (is_array($this->httpClient)) {
            $httpClient['baseUrl'] = static::baseUrl();
        }

        $this->httpClient = Instance::ensure($httpClient, HttpClient::class);
        parent::init();
    }

    private $_merchants = [];

    /**
     * @param bool $load
     * @return array|MerchantInterface[]
     * @throws InvalidConfigException
     */
    public function getMerchants($load = true)
    {
        if ($load) {
            $merchants = [];
            foreach ($this->_merchants as $id => $merchant) {
                $merchants[$id] = $this->getMerchant($id);
            }
        } else {
            return $this->_merchants;
        }
    }

    /**
     * @param array|MerchantInterface[] $merchants
     */
    public function setMerchants(array $merchants)
    {
        foreach ($merchants as $id => $merchant) {
            $this->setMerchant($id, $merchant);
        }
    }

    /**
     * @param null|string|int $id
     * @return mixed|MerchantInterface
     * @throws InvalidConfigException
     */
    public function getMerchant($id = null): MerchantInterface
    {
        if ($id === null) {
            return $this->getDefaultMerchant();
        } elseif ((is_string($id) || is_int($id)) && isset($this->_merchants[$id])) {
            $merchant = $this->_merchants[$id];

            if (is_string($merchant)) {
                $merchant = ['class' => $merchant, 'paymentGateway' => $this];
            } elseif (is_array($merchant)) {
                $merchant['paymentGateway'] = $this;
            }

            if (!$merchant instanceof MerchantInterface) {
                return $this->_merchants[$id] = Yii::createObject($merchant);
            } else {
                return $merchant;
            }
        } else {
            throw new InvalidArgumentException('Only accept get merchant via string or int key type!');
        }
    }

    public function setMerchant($id, $merchant = null)
    {
        if ($merchant === null) {
            $this->_merchants[] = $id;
        } else {
            $this->_merchants[$id] = $merchant;
        }
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
                throw new InvalidConfigException('Can not get default merchant on an empty array merchants list!');
            }
        } else {
            return $this->_defaultMerchant;
        }
    }

    /**
     * @param PaymentInfoInterface $info
     * @param MerchantInterface|null $merchant
     * @param string $method
     * @return CheckoutResponseDataInterface|bool
     * @throws InvalidConfigException
     */
    public function checkout(PaymentInfoInterface $info, MerchantInterface $merchant = null, $method = self::CHECKOUT_METHOD_IB)
    {
        if ($merchant === null) {
            $merchant = $this->getDefaultMerchant();
        }

        $this->trigger(self::EVENT_BEFORE_CHECKOUT, $event = new CheckoutEvent([
            'merchant' => $merchant,
            'method' => $method,
            'paymentInfo' => $info
        ]));

        if ($event->isValid) {
            $responseData = $this->checkoutInternal($info, $merchant, $method);
            $event->responseData = $responseData;
            $this->trigger(self::EVENT_AFTER_CHECKOUT, $event);
            return $responseData;
        } else {
            return false;
        }
    }

    abstract protected function checkoutInternal(PaymentInfoInterface $info, MerchantInterface $merchant, $method): CheckoutResponseDataInterface;
}