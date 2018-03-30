<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

/**
 * Interface TelCardPaymentGatewayInterface
 *
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface TelCardPaymentGatewayInterface extends PaymentGatewayInterface
{

    const CHECKOUT_METHOD_TEL_CARD = 'telCard';

    /**
     * @param array|string|RequestInstance $instance
     * @return ResponseInstance|bool
     */
    public function cardCharge($instance);

    /**
     * @inheritdoc
     */
    public function checkout($instance, string $method = self::CHECKOUT_METHOD_TEL_CARD);

}