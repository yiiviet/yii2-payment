<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment;

use yii\base\Event;

/**
 * Class VerifiedRequestEvent
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class VerifiedRequestEvent extends Event
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
     * @var Data
     */
    public $verifiedData;

}