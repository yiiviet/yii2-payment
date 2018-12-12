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
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
class BaoKimBankWidgetTest extends TestCase
{
    /**
     * @var \yiiviet\payment\baokim\PaymentGateway
     */
    public $gateway;

    public static function gatewayId(): string
    {
        return 'BK';
    }

}
