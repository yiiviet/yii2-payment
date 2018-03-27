<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;


/**
 * Interface PaymentGatewayInterface
 *
 * @package yii2vn\payment
 *
 * @property array|MerchantInterface[] $merchants
 * @property MerchantInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface PaymentGatewayInterface
{

    const CHECKOUT_METHOD_TEL_CARD = 'TEL CARD';

    const CHECKOUT_METHOD_INTERNET_BANKING = 'INTERNET BANKING';

    const CHECKOUT_METHOD_DEBIT_CARD = 'DEBIT CARD';

    const CHECKOUT_METHOD_CREDIT_CARD = 'CREDIT CARD';

    const CHECKOUT_METHOD_OFFLINE_ATM = 'OFFLINE ATM';

    const CHECKOUT_METHOD_BANK_OFFICE = 'BANK OFFICE';

    const CHECKOUT_METHOD_QR_CODE = 'QR CODE';

    const EVENT_BEFORE_CHECKOUT = 'beforeCheckout';

    const EVENT_AFTER_CHECKOUT = 'afterCheckout';

    /**
     * @return string
     */
    public static function getVersion(): string;

    /**
     * @return string
     */
    public function getBaseUrl(): string;

    /**
     * @return array|MerchantInterface[]
     */
    public function getMerchants(): array;

    /**
     * @param array|MerchantInterface[] $merchants
     * @return bool
     */
    public function setMerchants(array $merchants): bool;


    /**
     * @param string|int $id
     * @return MerchantInterface
     */
    public function getMerchant($id): MerchantInterface;

    /**
     * @param $id
     * @param array|string|MerchantInterface $merchant
     * @return bool
     */
    public function setMerchant($id, $merchant): bool;

    /**
     * @param array|string|CheckoutInstanceInterface $instance
     * @param string $method
     * @return CheckoutResponseDataInterface|bool
     */
    public function checkout($instance, string $method = self::CHECKOUT_METHOD_INTERNET_BANKING);

}