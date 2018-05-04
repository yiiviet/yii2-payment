<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment;

use yii\base\Event;

/**
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestEvent extends Event
{
    /**
     * @var int
     */
    public $command;

    /**
     * @var BaseMerchant
     */
    public $merchant;

    /**
     * @var RequestData
     */
    public $requestData;

    /**
     * @var ResponseData
     */
    public $responseData;


}