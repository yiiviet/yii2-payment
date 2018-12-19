<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\tests\unit\payment;

use Yii;

use yii\helpers\ArrayHelper;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * @var \yiiviet\payment\BasePaymentGateway
     */
    public $gateway;

    public function setUp()
    {
        parent::setUp();

        $this->mockApplication();
        $this->gateway = Yii::$app->get('paymentGateways')->getGateway(static::gatewayId());
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->destroyApplication();
        $this->gateway = null;
    }

    abstract public static function gatewayId(): string;


    public function testEnsureInstance()
    {
        $this->assertInstanceOf('\yiiviet\payment\BasePaymentGateway', $this->gateway);
        $this->assertTrue($this->gateway->sandbox);
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__, 2) . '/vendor',
            'bootstrap' => ['yiiviet\payment\Bootstrap'],
            'components' => [
                'cache' => 'yii\caching\DummyCache',
                'request' => [
                    'hostInfo' => 'http://domain.com',
                    'scriptUrl' => '/index.php'
                ],
                'paymentGateways' => [
                    'class' => 'yiiviet\payment\PaymentGatewayCollection',
                    'gatewayConfig' => [
                        'sandbox' => true
                    ],
                    'gateways' => [
                        'BK' => 'yiiviet\payment\baokim\PaymentGateway',
                        'NL' => 'yiiviet\payment\nganluong\PaymentGateway',
                        'OP' => 'yiiviet\payment\onepay\PaymentGateway',
                        'VNP' => 'yiiviet\payment\vnpayment\PaymentGateway',
                        'VTC' => 'yiiviet\payment\vtcpay\PaymentGateway'
                    ]
                ]
            ],
        ], $config));
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }

    /**
     * @param array $data
     * @param null $clientId
     * @return \vxm\gatewayclients\ResponseData
     */
    protected function purchase(array $data, $clientId = null)
    {
        return Yii::$app->get('paymentGateways')->purchase($data, static::gatewayId(), $clientId);
    }

    /**
     * @param array $data
     * @param null $clientId
     * @return \vxm\gatewayclients\ResponseData
     */
    protected function queryDR(array $data, $clientId = null)
    {
        return Yii::$app->get('paymentGateways')->queryDR($data, static::gatewayId(), $clientId);
    }

    /**
     * @param \yii\web\Request $request
     * @param null $clientId
     * @return \yiiviet\payment\VerifiedData
     */
    protected function verifyRequestPurchaseSuccess(\yii\web\Request $request = null, $clientId = null)
    {
        return Yii::$app->get('paymentGateways')->verifyRequestPurchaseSuccess(static::gatewayId(), $request, $clientId);
    }

    /**
     * @param \yii\web\Request $request
     * @param null $clientId
     * @return \yiiviet\payment\VerifiedData
     */
    protected function verifyRequestIPN(\yii\web\Request $request = null, $clientId = null)
    {
        return Yii::$app->get('paymentGateways')->verifyRequestIPN(static::gatewayId(), $request, $clientId);
    }

}
