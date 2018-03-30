<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use Yii;
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
     * @param Data $data
     * @param string $method
     * @return array
     * @throws NotSupportedException
     */
    protected function checkoutInternal(Data $data, string $method): array
    {
        $method = "checkoutWith" . $method;

        if (method_exists($this, $method)) {
            $data = $this->$method($data);

            Yii::debug("Checkout internal requested sent with method: $method");

            return $data;
        } else {
            throw new NotSupportedException('Checkout method: ' . $method . ' is not support on: ' . __CLASS__);
        }
    }

}