<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\tests\unit\payment;

/**
 * Lớp NganLuongBankWidgetTest
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class NganLuongBankWidgetTest extends BankWidgetTest
{
    /**
     * @var \yiiviet\payment\nganluong\PaymentGateway
     */
    public $gateway;


    public static function gatewayId(): string
    {
        return 'NL';
    }

}
