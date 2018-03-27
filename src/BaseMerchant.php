<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\Component;
use yii\di\Instance;

/**
 * @package yii2vn\payment
 *
 * @property PaymentGatewayInterface $paymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseMerchant extends Component implements MerchantInterface
{

    public $name;

    public $email;

    public $phone;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->_paymentGateway = Instance::ensure($this->_paymentGateway, PaymentGatewayInterface::class);
    }

    /**
     * @var array|string|PaymentGatewayInterface
     */
    private $_paymentGateway;

    public function getPaymentGateway(): PaymentGatewayInterface
    {
        return $this->_paymentGateway;
    }

    public function setPaymentGateway(PaymentGatewayInterface $paymentGateway): bool
    {
        $this->_paymentGateway = $paymentGateway;

        return true;
    }

    /**
     * @param BaseCheckoutBankInstance|CheckoutDataInterface $info
     * @param string $method
     * @return bool|CheckoutResponseDataInterface
     */
    public function checkout(CheckoutDataInterface $info, $method = PaymentGatewayInterface::CHECKOUT_METHOD_INTERNET_BANKING)
    {
        $info->merchant = $this;

        return $this->_paymentGateway->checkout($info, $method);
    }

}