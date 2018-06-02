<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

use Yii;

use yiiviet\payment\BasePaymentClient;

/**
 * Lớp PaymentClient là lớp cung cấp các thông tin cần thiết để kết nối đến cổng thanh toán OnePay.
 *
 * @method PaymentGateway getGateway()
 *
 * @property PaymentGateway $gateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentClient extends BasePaymentClient
{

    /**
     * Mã merchant sẽ được OnePay cấp khi đăng ký tích hợp. Nó luôn được dùng khi khởi tạo [[request()]] ở [[PaymentGateway]].
     *
     * @var string
     */
    public $merchantId;

    /**
     * Access code sẽ được OnePay cấp khi đăng ký tích hợp. Nó luôn được dùng khi khởi tạo [[request()]] ở [[PaymentGateway]].
     *
     * @var string
     */
    public $accessCode;

    /**
     * Access code sẽ được OnePay cấp khi đăng ký tích hợp. Nó dùng để tạo, xác minh chữ ký dữ liệu.
     *
     * @var string
     */
    public $secureSecret;


    /**
     * @inheritdoc
     * @return object|\yiiviet\payment\HmacDataSignature
     * @throws \yii\base\InvalidConfigException
     */
    public function initDataSignature(string $data, string $type = null): ?\yiiviet\payment\DataSignature
    {
        return Yii::createObject([
            'class' => 'yiiviet\payment\HmacDataSignature',
            'key' => pack('H*', $this->secureSecret),
            'hmacAlgo' => 'sha256'
        ], [$data]);
    }

}
