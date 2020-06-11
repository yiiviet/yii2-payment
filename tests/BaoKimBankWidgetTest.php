<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\tests\unit\payment;

/**
 * Lớp BaoKimBankWidgetTest
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class BaoKimBankWidgetTest extends BankWidgetTest
{
    /**
     * @var \yiiviet\payment\baokim\PaymentGateway
     */
    public $gateway;

    public static function gatewayId(): string
    {
        return 'BK';
    }

    public function testBankLogo()
    {
        $widget = $this->createWidget();

        $this->assertNotEmpty($widget->provider->getBankLogo(67));
    }
}
