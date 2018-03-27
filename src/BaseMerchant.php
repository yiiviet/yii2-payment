<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * @property PaymentGatewayInterface $paymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseMerchant extends Component implements MerchantInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var array|string|PaymentGatewayInterface
     */
    private $_paymentGateway;

    /**
     * @return PaymentGatewayInterface
     */
    public function getPaymentGateway(): PaymentGatewayInterface
    {
        return $this->_paymentGateway;
    }

    /**
     * @param array|string|PaymentGatewayInterface $paymentGateway
     * @return bool
     * @throws InvalidConfigException
     */
    public function setPaymentGateway($paymentGateway): bool
    {
        $this->_paymentGateway = Instance::ensure($paymentGateway, PaymentGatewayInterface::class);

        return true;
    }

}