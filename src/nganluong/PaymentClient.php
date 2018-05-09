<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\nganluong;

use yiiviet\payment\BasePaymentClient;

/**
 * Lớp PaymentClient là lớp chứa các thuộc tính để truy vấn đến ngân lượng nhu merchant id, merchant password, email...
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentClient extends BasePaymentClient
{

    /**
     * PaymentClient id được cấp khi bạn tích hợp website với ngân lượng.
     *
     * @var string
     */
    public $merchantId;

    /**
     * PaymentClient password được cấp khi bạn tích hợp website với ngân lượng.
     *
     * @var string
     */
    public $merchantPassword;

    /**
     * Email mặc định để nhận tiền thanh toán từ khách hàng của bạn.
     *
     * @var string
     */
    public $email;


    /**
     * @inheritdoc
     */
    protected function initDataSignature(string $data, string $type): ?\yiiviet\payment\DataSignature
    {
        return null;
    }

}
