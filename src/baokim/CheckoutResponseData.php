<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii2vn\payment\BaseCheckoutResponseData;

/**
 * Class CheckoutResponseData
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutResponseData extends BaseCheckoutResponseData
{
    /**
     * @var string
     */
    public $nextAction;

    /**
     * @var int
     */
    public $rvId;


    /**
     * @var string
     */
    public $guideUrl;


    /**
     * @var string
     */
    public $errorCode;



}