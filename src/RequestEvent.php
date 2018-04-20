<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\Event;

/**
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestEvent extends Event
{

    /**
     * @var string
     */
    public $command;

    /**
     * @var Data
     */
    public $requestData;

    /**
     * @var Data
     */
    public $responseData;

    /**
     * @var bool
     */
    public $isValid = true;

}