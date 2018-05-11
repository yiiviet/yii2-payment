<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\tests\unit\payment;

use Yii;

/**
 * Class BaoKimTest
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class BaoKimTest extends TestCase
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
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testPurchase()
    {
        // valid
        $responseData = $this->gateway->purchase([
            'order_id' => 2,
            'total_amount' => 500000,
            'url_success' => '/'
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertTrue(isset($responseData['redirect_url']));

        // throws
        $this->gateway->purchase([
            'order_id' => 1,
            'url_success' => '/'
        ]);

    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testPurchasePro()
    {
        // Valid
        $responseData = $this->gateway->purchasePro([
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
        $this->gateway->purchasePro([
            'bank_payment_method_id' => '128',
            'payer_name' => 'vxm',
            'payer_email' => 'vxm@gmail.com',
            'payer_phone_no' => '0909113911',
            'order_id' => time(),
            'url_success' => '/',
        ]);

    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testQueryDR()
    {
        // Valid
        $responseData = $this->gateway->queryDR(['transaction_id' => 1]);
        $this->assertTrue($responseData->getIsOk());

        // Throws
        $this->gateway->queryDR([]);
    }

    public function testGetMerchantData()
    {
        $merchantData = $this->gateway->getMerchantData();
        $this->assertTrue($merchantData->getIsOk());
    }

    public function testVerifyIPN()
    {
        $_POST = [
            'business' => 'dev.baokim@bk.vn',
            'order_id' => 2,
            'total_amount' => 500000,
            'checksum' => 'c96f01e3fdb4ba665304e70c04d58ba8917f31fe',
            'merchant_id' => 647,
            'url_success' => '/'
        ];
        $responseData = $this->gateway->verifyIPN();
        $this->assertFalse($responseData->getIsOk());
    }

    public function testVerifyRequestIPN()
    {
        $_POST = [
            'business' => 'dev.baokim@bk.vn',
            'order_id' => 2,
            'total_amount' => 500000,
            'checksum' => 'c96f01e3fdb4ba665304e70c04d58ba8917f31fe',
            'merchant_id' => 647,
            'url_success' => '/'
        ];
        $responseData = $this->gateway->verifyRequestIPN();
        $this->assertFalse($responseData);
    }

    public function testVerifyRequestPurchaseSuccess()
    {
        $_GET = [
            'business' => 'dev.baokim@bk.vn',
            'order_id' => 2,
            'total_amount' => 500000,
            'checksum' => 'c96f01e3fdb4ba665304e70c04d58ba8917f31fe',
            'merchant_id' => 647,
            'url_success' => '/'
        ];
        $responseData = $this->gateway->verifyRequestPurchaseSuccess();
        $this->assertFalse($responseData);
    }
}
