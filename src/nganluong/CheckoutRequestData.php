<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\nganluong;

use yii2vn\payment\CheckoutData;
use yii2vn\payment\MerchantInterface;

/**
 * Class CheckoutRequestData
 *
 * @property Merchant $merchant
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutRequestData extends CheckoutData
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['total_amount', 'order_code', 'buyer_fullname', 'buyer_email', 'buyer_mobile'], 'required'],
            [['bank_code'], 'required', 'on' => [
                PaymentGateway::CHECKOUT_METHOD_ATM_OFFLINE, PaymentGateway::CHECKOUT_METHOD_ATM_ONLINE, PaymentGateway::CHECKOUT_METHOD_BANK_OFFLINE,
                PaymentGateway::CHECKOUT_METHOD_QR_CODE, PaymentGateway::CHECKOUT_METHOD_INTERNET_BANKING,
            ]]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getData(bool $validate = true): array
    {
        $data = parent::getData($validate);

        /** @var Merchant $merchant */
        $merchant = $this->getMerchant();
        $paymentGateway = $merchant->getPaymentGateway();

        return array_merge($data, [
            'merchant_id' => $merchant->id,
            'merchant_password' => md5($merchant->password),
            'version' => $paymentGateway::version(),
            'function' => 'SetExpressCheckout',
            'receiver_email' => $data['receiver_email'] ?? $merchant->email,
            'payment_method' => $this->getMethod()
        ]);

    }

}