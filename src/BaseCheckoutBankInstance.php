<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use Yii;
use yii\base\InvalidConfigException;

/**
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseCheckoutBankInstance extends BaseCheckoutInstance
{

    /**
     * @var BaseCustomer|CheckoutDataInterface
     */
    public $customerClass;

    /**
     * @var BaseBank|CheckoutDataInterface
     */
    public $bankClass;

    /**
     * @var BaseOrder|CheckoutDataInterface
     */
    public $orderClass;

    /**
     * @var null|object|BaseBank|CheckoutDataInterface
     */
    private $_bank;

    /**
     * @return BaseBank|CheckoutDataInterface
     */
    public function getBank(): CheckoutDataInterface
    {
        return $this->_bank;
    }

    /**
     * @param array|string|callable|BaseBank|CheckoutDataInterface $bank
     * @return bool
     * @throws InvalidConfigException
     */
    public function setBank($bank): bool
    {
        if (is_array($bank) && !isset($bank['class'])) {
            $bank['class'] = $this->bankClass;
        }

        if (!$bank instanceof CheckoutDataInterface) {
            $this->_bank = Yii::createObject($bank);
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
            $order['class'] = $this->bankClass;
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
            $order['class'] = $this->bankClass;
        }

        if (!$customer instanceof CheckoutDataInterface) {
            $this->_order = Yii::createObject($customer);
        }

        return true;
    }

    /**
     * @param string $method
     * @return array
     * @throws InvalidConfigException
     */
    public function getData(string $method): array
    {
        $customer = $this->getCustomer()->getData($method);
        $bank = $this->getBank()->getData($method);
        $merchant = $this->getMerchant()->getData($method);
        $order = $this->getOrder()->getData($method);

        return array_merge($bank, $customer, $merchant, $order);
    }


}