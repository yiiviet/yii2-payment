<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\tests\unit\payment;

/**
 * Class VnPaymentTest
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class VnPaymentTest extends TestCase
{

    /**
     * @var \yiiviet\payment\vnpayment\PaymentGateway
     */
    public $gateway;

    public static function gatewayId(): string
    {
        return 'VNP';
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testPurchase()
    {
        // Valid
        $responseData = $this->purchase([
            'TxnRef' => time(),
            'OrderType' => 100000,
            'OrderInfo' => time(),
            'IpAddr' => '127.0.0.1',
            'Amount' => 1000000,
            'ReturnUrl' => 'http://localhost'
        ]);

        $this->assertTrue($responseData->getIsOk());
        $this->assertTrue(isset($responseData['redirect_url']));

        // Throws
        $this->purchase([
            'OrderType' => 100000,
            'IpAddr' => '127.0.0.1',
            'ReturnUrl' => 'http://localhost'
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testQueryDR()
    {
        // Valid
        $responseData = $this->queryDR([
            'TxnRef' => 123,
            'IpAddr' => '127.0.0.1',
            'OrderInfo' => time(),
            'TransDate' => date('Ymdhis'),
            'TransactionNo' => 123,
        ]);

        $this->assertFalse($responseData->getIsOk());

        // Throws
        $this->queryDR([
            'IpAddr' => '127.0.0.1',
            'OrderInfo' => time(),
            'TransDate' => date('Ymdhis'),
            'TransactionNo' => 123,
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     * @expectedExceptionMessage cannot be blank
     */
    public function testRefund()
    {
        // Valid
        $responseData = $this->refund([
            'TxnRef' => 123,
            'Amount' => 100000,
            'IpAddr' => '127.0.0.1',
            'OrderInfo' => time(),
            'TransDate' => date('Ymdhis'),
            'TransactionNo' => 123,
        ]);

        $this->assertFalse($responseData->getIsOk());
        $this->assertEquals(99, $responseData->getResponseCode());

        // Throws
        $this->refund([
            'IpAddr' => '127.0.0.1',
            'OrderInfo' => time(),
            'TransDate' => date('Ymdhis'),
            'TransactionNo' => 123,
        ]);
    }
}
