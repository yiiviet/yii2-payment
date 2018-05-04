<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment;


/**
 * Interface PaymentGatewayInterface
 *
 *
 * @property array|MerchantInterface[] $merchants
 * @property MerchantInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface PaymentGatewayInterface
{

    /**
     * @return string
     */
    public static function version(): string;

    /**
     * @return string
     */
    public static function baseUrl(): string;

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
     * @param string|int $id
     * @param array|string|MerchantInterface $merchant
     * @return bool
     */
    public function setMerchant($id, $merchant): bool;

    /**
     * @param array $data
     * @param string|int $merchantId
     * @return DataInterface
     */
    public function purchase(array $data, $merchantId): DataInterface;

    /**
     * @param array $data
     * @param string|int $merchantId
     * @return DataInterface
     */
    public function queryDR(array $data, $merchantId): DataInterface;


    /**
     * @param string|int $merchantId
     * @param \yii\web\Request|null $request
     * @return bool|DataInterface
     */
    public function verifyPurchaseSuccessRequest($merchantId, \yii\web\Request $request = null);


    /**
     * @param $merchantId
     * @param \yii\web\Request|null $request
     * @return bool|DataInterface
     */
    public function verifyPaymentNotificationRequest($merchantId, \yii\web\Request $request = null);


}
