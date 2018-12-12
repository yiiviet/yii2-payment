<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use Yii;

use yii\base\NotSupportedException;
use yii\di\Instance;
use yii\widgets\InputWidget;

use vxm\gatewayclients\BaseGateway;

/**
 * Lớp BaseWidget hổ trợ render input danh sách ngân hàng cho user chọn hiện tại hổ trợ 2 định kiểu là radio list và dropdown list.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
abstract class BankWidget extends InputWidget
{

    /**
     * @var BaseGateway đối tượng cổng thanh toán
     */
    public $gateway;

    /**
     * @var array có khóa là lớp đối tượng cổng thanh toán và giá trị là lớp đối tượng cung cấp thông tin ngân hàng.
     */
    public $providerClasses = [];

    /**
     * @var array hổ trợ thiết lập giá trị của đối tượng cung cấp thông tin ngân hàng như chỉ định chỉ lấy danh sách bank qr code, offline, online...
     */
    public $providerConfig = [];

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->gateway = Instance::ensure($this->gateway, PaymentGatewayInterface::class);
        $this->providerClasses = array_merge($this->defaultProviderClasses(), $this->providerClasses);
    }

    /**
     * @var null|BankProvider đối tượng cung cấp dữ liệu ngân hàng
     * @see [[getBankProvider()]]
     */
    private $_provider;

    /**
     * Phương thức hổ trợ lấy đối tượng cung cấp dữ liệu ngân hàng để lấy thông tin hiển thị.
     *
     * @return BankProvider|object
     * @throws \yii\base\InvalidConfigException|NotSupportedException
     */
    public function getBankProvider(): BankProvider
    {
        if (!$this->_provider instanceof BankProvider) {
            $gatewayClass = get_class($this->gateway);

            if (isset($this->providerClasses[$gatewayClass])) {
                $config = array_merge($this->providerConfig, [
                    'class' => $this->providerClasses[$gatewayClass]
                ]);
                /** @var BankProvider $provider */
                return $this->_provider = Yii::createObject($config, [$this->gateway]);
            } else {
                throw new NotSupportedException("Gateway: `$gatewayClass` is not supported!");
            }
        } else {
            return $this->_provider;
        }
    }

    /**
     * Phương thức cung cấp các lớp đối tượng lấy thông tin ngân hàng mặc định.
     *
     * @return array có khóa là lớp đối tượng cổng thanh toán và giá trị là lớp đối tượng cung cấp thông tin ngân hàng.
     */
    protected function defaultProviderClasses(): array
    {
        return [
            'yiiviet\payment\baokim\PaymentGateway' => 'yiiviet\payment\baokim\BankProvider',
            'yiiviet\payment\vtcpay\PaymentGateway' => 'yiiviet\payment\vtcpay\BankProvider',
            'yiiviet\payment\nganluong\PaymentGateway' => 'yiiviet\payment\nganluong\BankProvider',
            'yiiviet\payment\vnpayment\PaymentGateway' => 'yiiviet\payment\vnpayment\BankProvider',
            'yiiviet\payment\onepay\PaymentGateway' => 'yiiviet\payment\onepay\BankProvider'
        ];
    }
}
