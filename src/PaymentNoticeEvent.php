<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\Event;

/**
 * Class PaymentNoticeEvent
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentNoticeEvent extends Event
{

    public $isValid = true;

    /**
     * @var \yii\web\Request
     */
    public $request;

    /**
     * @var string|array
     */
    public $verifiedData;

}