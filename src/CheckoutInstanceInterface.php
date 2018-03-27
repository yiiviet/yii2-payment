<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

/**
 * Interface CheckoutInstanceInterface
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface CheckoutInstanceInterface extends CheckoutDataInterface
{

    /**
     * @return MerchantInterface
     */
    public function getMerchant(): MerchantInterface;

    /**
     * @param array|string|MerchantInterface $merchant
     * @return bool
     */
    public function setMerchant($merchant): bool;

}