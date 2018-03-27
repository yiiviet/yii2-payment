<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\BaseObject;

/**
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseOrder extends BaseObject implements CheckoutDataInterface
{

    public $id;

    public $amount;

    public $shippingFee;

    public $taxFee;

    public $currency;

    public $description;

    public $shippingAddress;

}