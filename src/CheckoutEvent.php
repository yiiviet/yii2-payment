<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\Event;

/**
 * @package yii2vn\payment
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutEvent extends Event
{

    /**
     * @var string
     */
    public $method;

    /**
     * @var CheckoutInstanceInterface|CheckoutInstanceInterface
     */
    public $instance;

    /**
     * @var CheckoutResponseData
     */
    public $responseData;

    /**
     * @var bool
     */
    public $isValid = true;

}