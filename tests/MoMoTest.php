<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\tests\unit\payment;

/**
 * Lớp MoMoTest
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class MoMoTest extends TestCase
{

    /**
     * @var \yiiviet\payment\momo\PaymentGateway
     */
    public $gateway;

    public static function gatewayId(): string
    {
        return 'MM';
    }

    public function testPurchase()
    {
        $orderId = microtime();
        $requestId = microtime();
        $responseData = $this->purchase([
            'amount' => 500000,
            'returnUrl' => 'http://localhost',
            'notifyUrl' => 'http://localhost',
            'orderId' => $orderId,
            'requestId' => $requestId
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertTrue($responseData->payUrl !== '');

        return [$orderId, $requestId];
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     * @depends testPurchase
     */
    public function testInvalidPurchase()
    {
        $this->purchase([]);
    }

    /**
     * @depends testPurchase
     */
    public function testQueryDR()
    {
        list($orderId, $requestId) = $this->testPurchase();
        $responseData = $this->queryDR([
            'orderId' => $orderId,
            'requestId' => $requestId
        ]);

        $this->assertTrue($responseData->getIsOk());
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     * @depends testQueryDR
     */
    public function testInvalidQueryDR()
    {
        $this->queryDR([]);
    }

    /**
     * @depends testPurchase
     * @depends testQueryDR
     */
    public function testRefund()
    {
        list($orderId, $requestId) = $this->testPurchase();
        $responseData = $this->refund([
            'orderId' => $orderId,
            'transId' => $orderId,
            'amount' => 10000,
            'requestId' => $requestId
        ]);

        $this->assertFalse($responseData->getIsOk());
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     * @depends testRefund
     */
    public function testInvalidRefund()
    {
        $this->refund([]);
    }

    /**
     * @depends testRefund
     */
    public function testQueryRefund()
    {
        list($orderId, $requestId) = $this->testPurchase();
        $responseData = $this->queryRefund([
            'orderId' => $orderId,
            'transId' => $orderId,
            'amount' => 10000,
            'requestId' => $requestId
        ]);

        $this->assertFalse($responseData->getIsOk());
    }


    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     * @depends testQueryRefund
     */
    public function testInvalidQueryRefund()
    {
        $this->queryRefund([]);
    }

    /**
     * @depends testPurchase
     * @depends testQueryDR
     */
    public function testVerifyRequestPurchaseSuccess()
    {
        $result = $this->verifyRequestPurchaseSuccess();
        $this->assertFalse($result);
    }

    /**
     * @depends testPurchase
     * @depends testQueryDR
     */
    public function testVerifyRequestIPN()
    {
        $result = $this->verifyRequestIPN();

        $this->assertFalse($result);
    }
}
