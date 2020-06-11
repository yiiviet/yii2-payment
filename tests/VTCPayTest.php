<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\tests\unit\payment;

/**
 * Lớp VTCPayTest
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.2
 */
class VTCPayTest extends TestCase
{

    /**
     * @var \yiiviet\payment\vtcpay\PaymentGateway
     */
    public $gateway;

    public static function gatewayId(): string
    {
        return 'VTC';
    }

    /**
     * @throws \ReflectionException|\yii\base\InvalidConfigException|\yii\base\NotSupportedException
     * @expectedException \yii\base\NotSupportedException
     */
    public function testQueryDR()
    {
        $this->gateway->queryDR([]);
    }

    /**
     * @expectedException \yii\base\InvalidArgumentException
     */
    public function testQueryDRViaGatewayCollection()
    {
        $this->queryDR([]);
    }

    public function testPurchase()
    {
        /** @var \yiiviet\payment\vtcpay\ResponseData $responseData */
        $responseData = $this->gateway->purchase([
            'amount' => 100000,
            'reference_number' => 1
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertContains('signature=1d31c0779f47e2bc3bfe40becf1fda0d7e881aeb90d8efb0341e258692cf896a', $responseData->redirect_url);
    }

    /**
     * @throws \ReflectionException|\yii\base\InvalidConfigException
     * @expectedException \yii\base\InvalidConfigException
     * @depends testPurchase
     */
    public function testInvalidPurchase()
    {
        $this->gateway->purchase([
            'amount' => 10000
        ]);
    }

    public function testInvalidVerifyIPN()
    {
        $_POST = [
            '_method' => 'POST',
            'data' => '100000|||1|1|1|1',
            'signature' => '643679f173526028e6bb26c2bc1256a420e5713b92fdb44cb4740f3c7c204145a'
        ];

        $this->assertFalse($this->gateway->verifyRequestIPN());
    }

    /**
     * @depends testInvalidVerifyIPN
     */
    public function testVerifyIPN()
    {
        $_POST = [
            '_method' => 'POST',
            'data' => '100000|||1|1|1|1',
            'signature' => '643679f173526028e6bb26c2bc1256a420e5713b92fdb44cb4740f3c7c204145'
        ];

        $data = $this->gateway->verifyRequestIPN();
        $this->assertInstanceOf('GatewayClients\DataInterface', $data);
        $this->assertTrue(isset($data['amount']));
    }

    public function testVerifyPurchaseSuccess()
    {
        $_GET = [
            'amount' => '100000',
            'status' => '1',
            'reference_number' => '1',
            'website_id' => '74132',
            'signature' => '1d31c0779f47e2bc3bfe40becf1fda0d7e881aeb90d8efb0341e258692cf896a'
        ];

        $this->assertFalse($this->gateway->verifyRequestPurchaseSuccess());
    }
}
