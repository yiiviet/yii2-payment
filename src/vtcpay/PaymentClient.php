<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace nhuluc\payment\vtcpay;

use Yii;

use yii\base\InvalidConfigException;

use nhuluc\payment\BasePaymentClient;
use nhuluc\payment\DataSignature;

/**
 * Lớp PaymentClient hổ trợ tạo và kiểm tra chữ ký dữ liệu và có các thuộc tính kết nối đến cổng thanh toán VTCPay.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.2
 */
class PaymentClient extends BasePaymentClient
{

    /**
     * @var string tài khoản nhận tiền mặc định khi thực hiện giao dịch.
     */
    public $business;

    /**
     * @var string Mã bảo vệ nhận khi đăng ký tích hợp.
     */
    public $secureCode;

    /**
     * @var string Mã website nhận khi đăng ký
     */
    public $merchantId;

    /**
     * @var bool phân biệt ký tự hoa thường khi xác minh chữ ký.
     */
    public $caseSensitive = false;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @since 1.0.3
     */
    public function init()
    {
        if ($this->business === null) {
            throw new InvalidConfigException('Property `business` must be set!');
        }

        if ($this->secureCode === null) {
            throw new InvalidConfigException('Property `secureCode` must be set!');
        }

        if ($this->merchantId === null) {
            throw new InvalidConfigException('Property `merchantId` must be set!');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     * @return object|\yiiviet\payment\HashDataSignature
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDataSignature(string $data, string $type = null): ?DataSignature
    {
        $data .= '|' . $this->secureCode;

        return Yii::createObject([
            'class' => 'nhuluc\payment\HashDataSignature',
            'hashAlgo' => 'sha256',
            'caseSensitive' => $this->caseSensitive
        ], [$data]);
    }

}
