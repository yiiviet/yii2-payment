<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

/**
 * Interface CardChargePaymentGatewayInterface
 *
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface CardChargePaymentGatewayInterface extends PaymentGatewayInterface
{
    /**
     * @param array $data
     * @return Data|bool
     */
    public function cardCharge(array $data);

}