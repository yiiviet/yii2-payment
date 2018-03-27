<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\base\InvalidConfigException;
use yii\httpclient\Client as HttpClient;
use yii\httpclient\RequestEvent as HttpClientRequestEvent;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CheckoutDataInterface;
use yii2vn\payment\CheckoutResponseDataInterface;
use yii2vn\payment\CheckoutSeparateInternalTrait;
use yii2vn\payment\MerchantInterface;


/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    use CheckoutSeparateInternalTrait;

    public static function getVersion(): string
    {
        return '1.0';
    }

    public function getBaseUrl(): string
    {
        return 'https://www.baokim.vn';
    }


}