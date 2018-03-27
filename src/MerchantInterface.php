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
interface MerchantInterface extends CheckoutDataInterface
{

    const SIGNATURE_RSA = 'RSA';

    const SIGNATURE_CHECKSUM = 'CHECKSUM';

    /**
     * @return PaymentGatewayInterface
     */
    public function getPaymentGateway(): PaymentGatewayInterface;

    /**
     * @param array|string|PaymentGatewayInterface $paymentGateway
     * @return bool
     */
    public function setPaymentGateway($paymentGateway): bool;

    /**
     * @return string
     */
    public function signature(): string;

    /**
     * @return bool
     */
    public function validateSignature(): bool;

}