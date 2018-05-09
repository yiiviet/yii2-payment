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
class TestCase extends BaseTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->destroyApplication();
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
            'components' => [
                'cache' => [
                    'class' => 'yii\caching\FileCache',
                    'cachePath' => '@yiiviet/tests/unit/payment/runtime'
                ],
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
                        'NL' => 'yiiviet\payment\baokim\PaymentGateway',
                        'OP' => 'yiiviet\payment\onepay\PaymentGateway',
                        'VNP' => 'yiiviet\payment\vnpayment\PaymentGateway'
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
}
