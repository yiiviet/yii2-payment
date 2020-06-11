<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\tests\unit\payment;

use Yii;

use yii\base\DynamicModel;
use nhuluc\payment\baokim\BankProvider;

/**
 * Lớp BankValidatorTest
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class BankValidatorTest extends TestCase
{
    /**
     * @var \yiiviet\payment\baokim\PaymentGateway
     */
    public $gateway;

    public static function gatewayId(): string
    {
        return 'BK';
    }

    /**
     * @return object|BankProvider
     * @throws \yii\base\InvalidConfigException
     */
    protected function getBankProvider()
    {
        return Yii::createObject(BankProvider::class, [$this->gateway]);
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    public function testValidBankId()
    {
        $banks = $this->getBankProvider()->banks();
        $this->assertTrue($banks[91] === "Vietinbank - Ngân hàng Công thương Việt Nam");
        $model = new DynamicModel([
            'bankId' => 91
        ]);
        $model->addRule('bankId', 'bankvn', [
            'gateway' => $this->gateway
        ]);
        $this->assertTrue($model->validate());
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     * @depends testValidBankId
     */
    public function testInValidBankId()
    {
        $banks = $this->getBankProvider()->banks();
        $this->assertFalse(isset($banks[10000]));
        $model = new DynamicModel([
            'bankId' => 10000
        ]);
        $model->addRule('bankId', 'bankvn', [
            'gateway' => $this->gateway
        ]);
        $this->assertFalse($model->validate());
    }

    /**
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testException()
    {
        $model = new DynamicModel([
            'bankId' => 91
        ]);
        $model->addRule('bankId', 'bankvn');
        $this->assertTrue($model->validate());
    }
}
