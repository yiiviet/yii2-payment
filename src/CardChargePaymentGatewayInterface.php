<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

/**
 * Class CardChargePaymentGatewayInterface
 *
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface CardChargePaymentGatewayInterface extends PaymentGatewayInterface
{

    const CHECKOUT_METHOD_TEL_CARD = 'telCard';

    /**
     * @param string|array|CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     */
    public function cardCharge($instance): CheckoutResponseDataInterface;

    /**
     * @inheritdoc
     */
    public function checkout($instance, string $method = self::CHECKOUT_METHOD_TEL_CARD);

}