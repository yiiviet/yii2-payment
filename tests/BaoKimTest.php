<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\tests\unit\payment;

use Yii;

use yii\helpers\Url;

use yiiviet\payment\baokim\PaymentGateway;
use yiiviet\payment\baokim\ResponseData;

/**
 * Class BaoKimTest
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class BaoKimTest extends TestCase
{

    public function testEnsureInstance()
    {
        /** @var PaymentGateway $bk */

        $bk = Yii::$app->paymentGateways->BK;
        $this->assertInstanceOf(PaymentGateway::class, $bk);
        $this->assertTrue($bk->sandbox);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     * @depends testEnsureInstance
     * @throws \yii\base\InvalidConfigException
     */
    public function testPurchase()
    {
        /**
         * @var PaymentGateway $bk
         * @var ResponseData $responseData
         */

        $bk = Yii::$app->paymentGateways->BK;

        // valid
        $responseData = $bk->purchase([
            'order_id' => 2,
            'total_amount' => 500000,
            'url_success' => '/'
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertEquals($responseData->location, 'https://sandbox.baokim.vn/payment/order/version11?business=dev.baokim%40bk.vn&checksum=c96f01e3fdb4ba665304e70c04d58ba8917f31fe&merchant_id=647&order_id=2&total_amount=500000&url_success=%2F');

        // throws
        $bk->purchase([
            'order_id' => 1,
            'url_success' => '/'
        ]);

    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     * @depends testEnsureInstance
     * @throws \yii\base\InvalidConfigException
     */
    public function testPurchasePro()
    {
        /**
         * @var PaymentGateway $bk
         * @var ResponseData $responseData
         */
        $bk = Yii::$app->paymentGateways->BK;

        // Valid
        $responseData = $bk->purchasePro([
            'bank_payment_method_id' => '128',
            'payer_name' => 'vxm',
            'payer_email' => 'vxm@gmail.com',
            'payer_phone_no' => '0909113911',
            'order_id' => microtime(),
            'total_amount' => 500000,
            'url_success' => '/',
        ]);
        $this->assertTrue($responseData->next_action === 'redirect');

        // Throws
        $bk->purchasePro([
            'bank_payment_method_id' => '128',
            'payer_name' => 'vxm',
            'payer_email' => 'vxm@gmail.com',
            'payer_phone_no' => '0909113911',
            'order_id' => time(),
            'url_success' => '/',
        ]);

    }

    /**
     * @depends testEnsureInstance
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testQueryDR()
    {
        /**
         * @var PaymentGateway $bk
         * @var ResponseData $responseData
         */
        $bk = Yii::$app->paymentGateways->BK;

        // Valid
        $responseData = $bk->queryDR(['transaction_id' => 1]);
        $this->assertTrue($responseData->getIsOk());

        // Throws
        $bk->queryDR([]);
    }


}
