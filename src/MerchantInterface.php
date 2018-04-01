<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;


/**
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface MerchantInterface
{
    /**
     * @return PaymentGatewayInterface
     */
    public function getPaymentGateway(): PaymentGatewayInterface;

    /**
     * @param string $data
     * @param null|string $type
     * @return string
     */
    public function signature(string $data, string $type = null): string;

    /**
     * @param string $data
     * @param string $expectSignature
     * @param null|string $type
     * @return bool
     */
    public function validateSignature(string $data, string $expectSignature, string $type = null): bool;

}