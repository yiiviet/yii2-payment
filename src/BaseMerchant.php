<?php
/**
 * @link http://github.com/yii2vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\Component;
use yii\di\Instance;

/**
 * @package yii2vn\payment
 * @property PaymentGatewayInterface $paymentGateway
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseMerchant extends Component implements MerchantInterface
{

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

    public function getPaymentGateway()
    {
        return $this->_paymentGateway;
    }

    public function setPaymentGateway(PaymentGatewayInterface $paymentGateway)
    {
        $this->_paymentGateway = $paymentGateway;
    }

    public function checkout(PaymentInfoInterface $info, $method = PaymentGatewayInterface::CHECKOUT_METHOD_IB)
    {
        return $this->_paymentGateway->checkout($info, $this, $method);
    }

}