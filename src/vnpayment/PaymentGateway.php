<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\vnpayment;

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

    const CHECKOUT_METHOD_DYNAMIC = 'dynamic';

    const CHECKOUT_METHOD_LOCAL_BANK = 'localBank';

    const CHECKOUT_METHOD_INTER_BANK = 'VISA';

    const CHECKOUT_METHOD_VNMART = 'VNMART';

    public $merchantConfig = ['class' => Merchant::class];

    public $checkoutRequestDataConfig = ['class' => CheckoutRequestData::class];

    public $checkoutResponseDataConfig = ['class' => CheckoutResponseData::class];

    const PAYMENT_URL = '/paymentv2/vpcpay.html';

    public static function baseUrl(): string
    {
        return 'http://vnpayment.vn';
    }

    public static function version(): string
    {
        return '2.0.0';
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithLocalBank(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_LOCAL_BANK;

        return $this->checkout($data);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithInterBank(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_INTER_BANK;

        return $this->checkout($data);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithVNMART(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_VNMART;

        return $this->checkout($data);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithDynamic(array $data): CheckoutResponseData
    {
        $data['method'] = self::CHECKOUT_METHOD_DYNAMIC;

        return $this->checkout($data);
    }

    /**
     * @inheritdoc
     */
    protected function checkoutInternal(CheckoutData $data): array
    {
        /** @var Merchant $merchant */
        $merchant = $data->merchant;
        $queryData = $data->getData();
        ksort($queryData);
        $queryData['vnp_SecureHash'] = md5($merchant->hashSecret . urldecode(http_build_query($queryData)));
        $queryData['vnp_SecureHashType'] = 'md5';

        $location = rtrim(static::baseUrl()) . self::PAYMENT_URL . '?' . http_build_query($queryData);

        return ['location' => $location, 'code' => '00'];
    }

    protected function getDefaultCheckoutMethod(): string
    {
        return self::CHECKOUT_METHOD_DYNAMIC;
    }

}