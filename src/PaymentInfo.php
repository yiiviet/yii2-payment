<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * @package yii2vn\payment
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentInfo extends BaseObject implements PaymentInfoInterface
{
    /**
     * @var BaseCustomer|PaymentInfoInterface
     */
    public $customer;

    /**
     * @var BaseBank|PaymentInfoInterface
     */
    public $bank;

    /**
     * @var BaseOrder|PaymentInfoInterface
     */
    public $order;


    public function getInfo() : array
    {
        $order = $this->order ? $this->order->getInfo() : [];
        $bank = $this->bank ? $this->bank->getInfo() : [];
        $customer = $this->customer ? $this->customer->getInfo() : [];

        return ArrayHelper::merge($order, $bank, $customer);
    }


}