<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\momo;

use Yii;

use yii\base\InvalidConfigException;

use yiiviet\payment\BasePaymentClient;
use yiiviet\payment\DataSignature;
use yiiviet\payment\HmacDataSignature;

/**
 * Lớp PaymentClient chứa các thuộc tính dùng để hổ trợ [[PaymentGateway]] kết nối đến MOMO.
 *
 * @method PaymentGateway getGateway()
 * @property PaymentGateway $gateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentClient extends BasePaymentClient
{
    /**
     * Thuộc tính dùng để khai báo kết nối đến MOMO khi tạo [[request()]] ở [[PaymentGateway]].
     * Nó do MOMO cấp khi đăng ký tích hợp website.
     *
     * @var string
     */
    public $partnerCode;

    /**
     * Thuộc tính dùng để khai báo kết nối đến MOMO khi tạo [[request()]] ở [[PaymentGateway]].
     * Nó do MOMO cấp khi đăng ký tích hợp website.
     *
     * @var string
     */
    public $accessKey;

    /**
     * Thuộc tính được dùng để tạo chữ ký dữ liệu khi tạo [[request()]] ở [[PaymentGateway]].
     *
     * @var string
     */
    public $secretKey;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->partnerCode === null) {
            throw new InvalidConfigException('Property `partnerCode` must be set!');
        }

        if ($this->accessKey === null) {
            throw new InvalidConfigException('Property `accessKey` must be set!');
        }

        if ($this->secretKey === null) {
            throw new InvalidConfigException('Property `secretKey` must be set!');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     * @return DataSignature|null|object
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDataSignature(string $data, string $type = null): ?DataSignature
    {
        return Yii::createObject([
            'class' => HmacDataSignature::class,
            'key' => $this->secretKey,
            'hmacAlgo' => 'sha256'
        ]);
    }
}
