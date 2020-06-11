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
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
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
        $responseData = $this->purchase([
            'bank_code' => 'VCB',
            'buyer_fullname' => 'vxm',
            'buyer_email' => 'admin@test.app',
            'buyer_mobile' => '0909113911',
            'total_amount' => 10000000,
            'order_code' => microtime(),
            'return_url' => 'http://localhost'
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertTrue(isset($responseData['checkout_url']));

        // Throws
        $this->purchase([]);

    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testQueryDR()
    {
        // Valid
        $responseData = $this->queryDR([
            'token' => self::TOKEN
        ]);

        $this->assertEquals('81', $responseData->error_code);
        $this->assertFalse($responseData->getIsOk());

        // Throws
        $this->queryDR([]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testAuthenticate()
    {
        // Valid
        $this->gateway->setVersion('3.2');
        $responseData = $this->gateway->authenticate([
            'token' => self::TOKEN,
            'otp' => '123321',
            'auth_url' => 'http://localhost'
        ]);
        $this->assertFalse($responseData->getIsOk());

        // Throws
        $this->gateway->authenticate([
            'token' => self::TOKEN,
            'otp' => '123321'
        ]);
    }

    public function testVerifyRequestPurchaseSuccess()
    {
        $_GET = [];
        $result = $this->verifyRequestPurchaseSuccess();
        $this->assertFalse($result);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testPurchase32()
    {
        // Valid
        $this->gateway->setVersion('3.2');
        $responseData = $this->purchase([
            'bank_code' => 'VCB',
            'buyer_fullname' => 'vxm',
            'buyer_email' => 'admin@test.app',
            'buyer_mobile' => '0909113911',
            'total_amount' => 10000000,
            'order_code' => microtime(),
            'card_number' => '123123123',
            'card_fullname' => 'vxm',
            'card_month' => '01',
            'card_year' => '2012'
        ]);

        $this->assertFalse($responseData->getIsOk());

        // Throws
        $this->purchase([
            'bank_code' => 'VCB',
            'buyer_fullname' => 'vxm',
            'buyer_email' => 'admin@test.app',
            'buyer_mobile' => '0909113911',
            'total_amount' => 10000000,
            'order_code' => microtime()
        ]);
    }
}
