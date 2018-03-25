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
     * @var MerchantInterface
     */
    public $merchant;

    /**
     * @var string
     */
    public $method;

    /**
     * @var PaymentInfoInterface
     */
    public $paymentInfo;

    /**
     * @var CheckoutResponseData
     */
    public $responseData;

    /**
     * @var bool
     */
    public $isValid = true;

}