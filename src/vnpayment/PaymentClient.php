<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use Yii;

use yiiviet\payment\BasePaymentClient;

/**
 * Lớp PaymentClient hổ trợ tạo và kiểm tra chữ ký dữ liệu và có các thuộc tính kết nối đến cổng thanh toán VnPayment
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentClient extends BasePaymentClient
{
    /**
     * Mảng thiết lập cấu hình mặc định của [[DataSignature]] khi được khởi tạo.
     *
     * @var array
     */
    public $dataSignatureConfig = [];

    /**
     * Thuộc tính dùng để tạo và kiểm tra chữ ký dữ liệu.
     * Nó do VnPayment cấp khi thực hiện tích hợp website của bạn.
     *
     * @var string
     */
    public $hashSecret;

    /**
     * Mã TMN được dùng để gửi lên VnPayment xác định là yêu cầu từ bạn.
     * Nó thường được dùng khi gọi [[request()]] ở [[PaymentGateway]].
     * Nó do VnPayment cấp khi thực hiện tích hợp website của bạn.
     *
     * @var string
     */
    public $tmnCode;

    /**
     * Mã loại hàng mặc định của website bạn bán.
     *
     * @var int
     */
    public $defaultOrderType;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDataSignature(string $data, string $type = null): ?\yiiviet\payment\DataSignature
    {
        return Yii::createObject(array_merge([
            'class' => DataSignature::class,
            'hashAlgo' => $type,
            'hashSecret' => $this->hashSecret
        ], $this->dataSignatureConfig), [$data]);
    }
}
