<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

/**
 * Class BaseTelCard
 *
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseTelCard extends BaseCheckoutData
{

    public $provider;

    public $serial;

    public $pinCode;


}