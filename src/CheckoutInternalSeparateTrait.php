<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\NotSupportedException;

/**
 * Trait CheckoutInternalSeparateTrait
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
trait CheckoutInternalSeparateTrait
{

    /**
     * @param CheckoutInstanceInterface $instance
     * @param string $method
     * @return CheckoutResponseDataInterface
     * @throws NotSupportedException
     */
    protected function checkoutInternal(CheckoutInstanceInterface $instance, string $method): CheckoutResponseDataInterface
    {
        $method = "checkoutWith" . $method;

        if (method_exists($this, $method)) {
            return $this->$method($instance);
        } else {
            throw new NotSupportedException('Checkout method: ' . $method . ' is not support on: ' . __CLASS__);
        }
    }

}