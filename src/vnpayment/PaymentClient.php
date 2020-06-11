<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\vnpayment;

use Yii;

use yii\base\InvalidConfigException;

use nhuluc\payment\BasePaymentClient;
use nhuluc\payment\HashDataSignature;

/**
 * Lớp PaymentClient hổ trợ tạo và kiểm tra chữ ký dữ liệu và có các thuộc tính kết nối đến cổng thanh toán VnPayment
 *
 * @method PaymentGateway getGateway()
 *
 * @property PaymentGateway $gateway
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0
 */
class PaymentClient extends BasePaymentClient
{

    /**
     * @var string Mã TMN được dùng để gửi lên VnPayment xác định là yêu cầu từ bạn.
     * Nó thường được dùng khi gọi [[request()]] ở [[PaymentGateway]].
     * Nó do VnPayment cấp khi thực hiện tích hợp website của bạn.
     */
    public $tmnCode;

    /**
     * @var string Thuộc tính dùng để tạo và kiểm tra chữ ký dữ liệu.
     * Nó do VnPayment cấp khi thực hiện tích hợp website của bạn.
     */
    public $hashSecret;

    /**
     * @var int Mã loại hàng mặc định của website bạn bán.
     */
    public $defaultOrderType;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @since 1.0.3
     */
    public function init()
    {
        if ($this->tmnCode === null) {
            throw new InvalidConfigException('Property `tmnCode` must be set!');
        }

        if ($this->hashSecret === null) {
            throw new InvalidConfigException('Property `hashSecret` must be set!');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDataSignature(string $data, string $type = null): ?\yiiviet\payment\DataSignature
    {
        $data = $this->hashSecret . $data;

        return Yii::createObject([
            'class' => HashDataSignature::class,
            'hashAlgo' => $type,
        ], [$data]);
    }
}
