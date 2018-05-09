<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

use Yii;

use yii\helpers\ArrayHelper;

use yiiviet\payment\BasePaymentClient;

/**
 * Lớp PaymentClient là lớp cung cấp các thông tin cần thiết để kết nối đến cổng thanh toán OnePay.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentClient extends BasePaymentClient
{

    /**
     * Mã merchant sẽ được OnePay cấp khi đăng ký tích hợp. Nó luôn được dùng khi khởi tạo [[request()]] ở [[PaymentGateway]].
     *
     * @var int
     */
    public $merchantId;

    /**
     * Access code sẽ được OnePay cấp khi đăng ký tích hợp. Nó luôn được dùng khi khởi tạo [[request()]] ở [[PaymentGateway]].
     *
     * @var int
     */
    public $accessCode;

    /**
     * Access code sẽ được OnePay cấp khi đăng ký tích hợp. Nó dùng để tạo, xác minh chữ ký dữ liệu.
     *
     * @var int
     */
    public $secureSecret;

    /**
     * Mảng cấu hình thiết lập mặc định của đối tượng [[DataSignature]].
     *
     * @var array
     */
    public $dataSignatureConfig = [];

    /**
     * @inheritdoc
     * @return object|\yiiviet\payment\HmacDataSignature
     * @throws \yii\base\InvalidConfigException
     */
    public function initDataSignature(string $data, string $type): ?\yiiviet\payment\DataSignature
    {
        $config = ArrayHelper::merge($this->dataSignatureConfig, [
            'key' => pack('H*', $this->secureSecret),
            'hmacAlgo' => 'sha256'
        ]);

        $config['class'] = $config['class'] ?? 'yiiviet\payment\HmacDataSignature';

        return Yii::createObject($config, [$data]);
    }

}
