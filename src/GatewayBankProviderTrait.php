<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use Yii;

use yii\base\InvalidConfigException;
use yii\base\NotSupportedException;
use yii\di\Instance;

/**
 * Trait GatewayBankProviderTrait bồ sung các thuộc tính, phương thức lấy thông tin ngân hàng
 *
 * @property BankProvider $bankProvider đối tượng cung cấp thông tin ngân hàng
 * @property PaymentGatewayInterface $gateway
 * @property array $providerClasses có khóa là lớp đối tượng cổng thanh toán và giá trị là lớp đối tượng cung cấp thông tin ngân hàng.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
trait GatewayBankProviderTrait
{

    /**
     * @var array hổ trợ thiết lập giá trị của đối tượng cung cấp thông tin ngân hàng như chỉ định chỉ lấy danh sách bank qr code, offline, online...
     */
    public $providerConfig = [];

    /**
     * @var array|null có khóa là lớp đối tượng cổng thanh toán và giá trị là lớp đối tượng cung cấp thông tin ngân hàng.
     * @see [[getProviderClasses()]]
     * @see [[setProviderClasses()]]
     */
    private $_providerClasses;

    /**
     * Phương thức hổ trợ lấy thông tin lớp đối tượng cung cấp mã và thông tin ngân hàng của cổng thanh toán.
     *
     * @return array có khóa là lớp đối tượng cổng thanh toán và giá trị là lớp đối tượng cung cấp thông tin ngân hàng.
     */
    public function getProviderClasses(): array
    {
        if ($this->_providerClasses === null) {
            $this->setProviderClasses([]);
        }

        return $this->_providerClasses;
    }

    /**
     * Phương thức hổ trợ thiết lập thông tin lớp đối tượng cung cấp mã và thông tin ngân hàng của cổng thanh toán.
     *
     * @param array $providerClasses có khóa là lớp đối tượng cổng thanh toán và giá trị là lớp đối tượng cung cấp thông tin ngân hàng.
     */
    public function setProviderClasses(array $providerClasses): void
    {
        $this->_providerClasses = array_merge($this->defaultProviderClasses(), $providerClasses);
    }

    /**
     * @var PaymentGatewayInterface|null đối tượng cổng thanh toán.
     * @see [[getGateway()]]
     * @see [[setGateway()]]
     */
    private $_gateway;

    /**
     * Phương thức cung cấp cổng thanh toán để lấy thông tin các ngân hàng từ nó cung cấp.
     *
     * @return PaymentGatewayInterface đối tượng cổng thanh toán.
     * @throws InvalidConfigException
     */
    public function getGateway(): PaymentGatewayInterface
    {
        if ($this->_gateway === null) {
            throw new InvalidConfigException('Property `gateway` must be set!');
        } else {
            return $this->_gateway;
        }
    }

    /**
     * Phương thức hổ trợ thiết lập đối tượng cổng thanh toán
     * @see [[getGateway()]]
     *
     * @param array|string|PaymentGatewayInterface $gateway đối tượng hoặc cấu hình của cổng thanh toán muốn thiết lập.
     * @throws InvalidConfigException
     */
    public function setGateway($gateway): void
    {
        $this->_gateway = Instance::ensure($gateway, PaymentGatewayInterface::class);
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
            'yiiviet\payment\nganluong\PaymentGateway' => 'yiiviet\payment\nganluong\BankProvider'
        ];
    }

}
