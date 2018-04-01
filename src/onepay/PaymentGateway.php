<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\onepay;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CheckoutData;

/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{

    public $merchantConfig = ['class' => Merchant::class];

    public $checkoutRequestDataConfig = ['class' => CheckoutRequestData::class];

    public $checkoutResponseDataConfig = ['class' => CheckoutResponseData::class];

    public static function baseUrl(): string
    {
        // TODO: Implement baseUrl() method.
    }

    public static function version(): string
    {
        // TODO: Implement version() method.
    }

    protected function checkoutInternal(CheckoutData $data): array
    {
        // TODO: Implement checkoutInternal() method.
    }

    protected function getDefaultCheckoutMethod(): string
    {
        // TODO: Implement getDefaultCheckoutMethod() method.
    }

}