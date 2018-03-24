<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;


/**
 * Interface PaymentGatewayInterface
 * @property array|MerchantInterface[] $merchants
 * @property MerchantInterface $merchant
 * @package yii2vn\payment
 */
interface PaymentGatewayInterface
{

    const CHECKOUT_METHOD_IB = "INTERNET BANKING";

    const CHECKOUT_METHOD_DEBIT_CARD = "DEBIT CARD";

    const CHECKOUT_METHOD_CREDIT_CARD = "CREDIT CARD";

    const CHECKOUT_METHOD_OFFLINE_ATM = "OFFLINE ATM";

    const CHECKOUT_METHOD_BANK_OFFICE = "BANK OFFICE";

    const CHECKOUT_METHOD_QR_CODE = "QR CODE";

    const EVENT_BEFORE_CHECKOUT = "beforeCheckout";

    const EVENT_AFTER_CHECKOUT = "afterCheckout";

    public static function baseUrl();

    public static function getVersion();

    /**
     * @return array|MerchantInterface[]
     */
    public function getMerchants();

    /**
     * @param array|MerchantInterface[] $merchants
     */
    public function setMerchants(array $merchants);


    /**
     * @param string|int $id
     * @return MerchantInterface
     */
    public function getMerchant($id): MerchantInterface;

    /**
     * @param $id
     * @param array|string|MerchantInterface $merchant
     * @return mixed
     */
    public function setMerchant($id, $merchant);

    /**
     * @param PaymentInfoInterface $info
     * @param MerchantInterface $merchant
     * @param string $method
     * @return CheckoutResponseDataInterface|bool
     */
    public function checkout(PaymentInfoInterface $info, MerchantInterface $merchant, $method = self::CHECKOUT_METHOD_IB);

}