<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use yii\base\Event;

/**
 * Lớp VerifiedRequestEvent sự kiện được khởi tạo khi việc xác minh dữ liệu đầu vào thành công,
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class VerifiedRequestEvent extends Event
{
    /**
     * Lệnh được yêu cầu xác minh (IPN, success url)
     *
     * @var int|string
     */
    public $command;

    /**
     * PaymentClient dùng đã dùng để xác minh tính hợp lệ.
     *
     * @var BasePaymentClient
     */
    public $client;

    /**
     * Dữ liệu đã được xác minh hợp lệ.
     *
     * @var VerifiedData
     */
    public $verifiedData;

}
