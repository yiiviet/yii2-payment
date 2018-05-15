<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\tests\unit\payment;

/**
 * Class OnePayTest
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class OnePayTest extends TestCase
{

    /**
     * @var \yiiviet\payment\onepay\PaymentGateway
     */
    public $gateway;

    public static function gatewayId(): string
    {
        return 'OP';
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testPurchase()
    {
        // Valid
        $responseData = $this->gateway->purchase([
            'ReturnURL' => 'http://localhost/',
            'OrderInfo' => time(),
            'Amount' => 500000,
            'TicketNo' => '127.0.0.1',
            'AgainLink' => 'http://localhost/',
            'Title' => 'Hello World',
            'MerchTxnRef' => time()
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertTrue(isset($responseData['redirect_url']));

        // Throws
        $this->gateway->purchase([
            'ReturnURL' => 'http://localhost/',
            'TicketNo' => '127.0.0.1',
            'AgainLink' => 'http://localhost/'
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testPurchaseInternational()
    {
        // Valid
        $this->gateway->international = true;
        $responseData = $this->gateway->purchase([
            'ReturnURL' => 'http://localhost/',
            'OrderInfo' => time(),
            'Amount' => 500000,
            'TicketNo' => '127.0.0.1',
            'AgainLink' => 'http://localhost/',
            'Title' => 'Hello World',
            'MerchTxnRef' => time()
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertTrue(isset($responseData['redirect_url']));

        // Throws
        $this->gateway->purchase([
            'ReturnURL' => 'http://localhost/',
            'TicketNo' => '127.0.0.1',
            'AgainLink' => 'http://localhost/'
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testQueryDR()
    {
        // Valid
        $responseData = $this->gateway->queryDR([
            'MerchTxnRef' => 1
        ]);

        $this->assertTrue($responseData->getIsOk());

        // Throws
        $this->gateway->queryDR([]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testQueryDRInternational()
    {
        // Valid
        $this->gateway->international = true;
        $responseData = $this->gateway->queryDR([
            'MerchTxnRef' => 1
        ]);

        $this->assertTrue($responseData->getIsOk());

        // Throws
        $this->gateway->queryDR([]);
    }

    public function testVerifyRequestPurchaseSuccess()
    {
        $result = $this->gateway->verifyRequestPurchaseSuccess();
        $this->assertFalse($result);
    }

    public function testVerifyRequestPurchaseInternationalSuccess()
    {
        $this->gateway->international = true;
        $result = $this->gateway->verifyRequestPurchaseSuccess();

        $this->assertFalse($result);
    }

    public function testVerifyRequestIPN()
    {
        $result = $this->gateway->verifyRequestIPN();

        $this->assertFalse($result);
    }

    public function testVerifyRequestIPNInternational()
    {
        $this->gateway->international = true;
        $result = $this->gateway->verifyRequestIPN();

        $this->assertFalse($result);
    }
}
