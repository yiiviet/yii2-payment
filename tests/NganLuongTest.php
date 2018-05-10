<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\tests\unit\payment;

use Yii;

/**
 * Class NganLuongTest
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class NganLuongTest extends TestCase
{

    const TOKEN = '58914-68d8693f31a5c6299813c4d26bb643c2';

    /**
     * @var \yiiviet\payment\nganluong\PaymentGateway
     */
    public $gateway;


    public static function gatewayId(): string
    {
        return 'NL';
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testPurchase()
    {
        // Valid
        $responseData = $this->gateway->purchase([
            'bank_code' => 'VCB',
            'buyer_fullname' => 'vxm',
            'buyer_email' => 'admin@test.app',
            'buyer_mobile' => '0909113911',
            'total_amount' => 10000000,
            'order_code' => microtime()
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertTrue(isset($responseData['checkout_url']));

        // Throws
        $this->gateway->purchase([]);

    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testQueryDR()
    {
        // Valid
        $responseData = $this->gateway->queryDR([
            'token' => self::TOKEN
        ]);

        $this->assertEquals('81', $responseData->error_code);
        $this->assertFalse($responseData->getIsOk());

        // Throws
        $this->gateway->queryDR([]);
    }

    public function testVerifyRequestPurchaseSuccess()
    {
        $_GET['token'] = self::TOKEN;

        $result = $this->gateway->verifyRequestPurchaseSuccess();
        $this->assertInstanceOf('\yiiviet\payment\VerifiedData', $result);

        $_GET = [];
        $result = $this->gateway->verifyRequestPurchaseSuccess();
        $this->assertFalse($result);
    }
}
