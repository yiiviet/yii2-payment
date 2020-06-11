<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\tests\unit\payment;

use nhuluc\payment\BankWidget;

/**
 * Lớp BankWidgetTest
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
abstract class BankWidgetTest extends TestCase
{

    protected function createWidget()
    {
        return new BankWidget([
            'gateway' => $this->gateway,
            'name' => 'a'
        ]);
    }

    public function testOutput()
    {
        $output = BankWidget::widget([
            'gateway' => $this->gateway,
            'name' => 'a'
        ]);

        $this->assertNotEmpty($output);
    }

    public function testBankList()
    {
        $widget = $this->createWidget();

        $this->assertNotEmpty($widget->provider->banks());
    }


}
