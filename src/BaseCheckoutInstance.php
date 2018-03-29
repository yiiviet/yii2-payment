<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use Yii;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * Class BaseCheckoutInstance
 *
 * @property array $data
 * @property MerchantInterface $merchant
 * @property BaseCustomer|CheckoutDataInterface $customer
 * @property BaseOrder|CheckoutDataInterface $order
 * @property BaseBankPaymentMethod|CheckoutDataInterface $bankPaymentMethod
 * @property BaseTelCard|CheckoutDataInterface $telCard
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseCheckoutInstance extends BaseObject implements CheckoutInstanceInterface
{

    /**
     * @var string
     */
    public $customerClass;

    /**
     * @var string
     */
    public $bankPaymentMethodClass;

    /**
     * @var string
     */
    public $orderClass;

    /**
     * @var string
     */
    public $telCardClass;

    /**
     * @var null|object|BaseBankPaymentMethod|CheckoutDataInterface
     */
    private $_bankPaymentMethod;

    /**
     * @return BaseBankPaymentMethod|CheckoutDataInterface
     */
    public function getBankPaymentMethod(): CheckoutDataInterface
    {
        return $this->_bankPaymentMethod;
    }

    /**
     * @param array|string|callable|BaseBankPaymentMethod|CheckoutDataInterface $bank
     * @return bool
     * @throws InvalidConfigException
     */
    public function setBankPaymentMethod($bank): bool
    {
        if (is_array($bank) && !isset($bank['class'])) {
            $bank['class'] = $this->bankPaymentMethodClass;
        }

        if (!$bank instanceof CheckoutDataInterface) {
            $this->_bankPaymentMethod = Yii::createObject($bank);
        }

        return true;
    }

    /**
     * @var null|object|BaseOrder|CheckoutDataInterface
     */
    private $_order;

    /**
     * @return BaseOrder|CheckoutDataInterface
     */
    public function getOrder(): CheckoutDataInterface
    {
        return $this->_order;
    }

    /**
     * @param array|string|callable|BaseOrder|CheckoutDataInterface $order
     * @return bool
     * @throws InvalidConfigException
     */
    public function setOrder($order): bool
    {
        if (is_array($order) && !isset($order['class'])) {
            $order['class'] = $this->orderClass;
        }

        if (!$order instanceof CheckoutDataInterface) {
            $this->_order = Yii::createObject($order);
        }

        return true;
    }

    /**
     * @var null|object|BaseCustomer|CheckoutDataInterface
     */
    private $_customer;

    /**
     * @return BaseCustomer|CheckoutDataInterface
     */
    public function getCustomer(): CheckoutDataInterface
    {
        return $this->_customer;
    }

    /**
     * @param array|string|callable|BaseCustomer|CheckoutDataInterface $customer
     * @return bool
     * @throws InvalidConfigException
     */
    public function setCustomer($customer): bool
    {
        if (is_array($customer) && !isset($customer['class'])) {
            $order['class'] = $this->customerClass;
        }

        if (!$customer instanceof CheckoutDataInterface) {
            $this->_customer = Yii::createObject($customer);
        }

        return true;
    }

    /**
     * @var null|object|BaseTelCard|CheckoutDataInterface
     */
    private $_telCard;

    /**
     * @return BaseTelCard|CheckoutDataInterface
     */
    public function getTelCard(): CheckoutDataInterface
    {
        return $this->_telCard;
    }

    /**
     * @param array|string|callable|BaseTelCard|CheckoutDataInterface $telCard
     * @return bool
     * @throws InvalidConfigException
     */
    public function setTelCard($telCard): bool
    {
        if (is_array($telCard) && !isset($telCard['class'])) {
            $telCard['class'] = $this->telCardClass;
        }

        if (!$telCard instanceof CheckoutDataInterface) {
            $this->_telCard = Yii::createObject($telCard);
        }

        return true;
    }

    /**
     * @var null|MerchantInterface
     */
    private $_merchant;

    /**
     * @return object|MerchantInterface
     * @throws InvalidConfigException
     */
    public function getMerchant(): MerchantInterface
    {
        if (!$this->_merchant instanceof MerchantInterface) {
            return $this->_merchant = Instance::ensure($this->_merchant, MerchantInterface::class);
        } else {
            return $this->_merchant;
        }
    }

    /**
     * @param array|string|MerchantInterface $merchant
     * @return bool
     */
    public function setMerchant($merchant): bool
    {
        $this->_merchant = $merchant;

        return true;
    }


}