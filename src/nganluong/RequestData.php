<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\nganluong;

use yii2vn\payment\Data;

/**
 * Class RequestData
 *
 * @property Merchant $merchant
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestData extends Data
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['merchant_id', 'merchant_password', 'version', 'function'], 'required', 'on' => [PaymentGateway::RC_PURCHASE, PaymentGateway::RC_QUERY_DR]],
            [['token'], 'required', 'on' => PaymentGateway::RC_QUERY_DR],
            [[
                'bank_code', 'buyer_fullname', 'buyer_email', 'buyer_mobile',
                'total_amount', 'order_code', 'receiver_email', 'payment_method'
            ], 'required', 'on' => PaymentGateway::RC_PURCHASE]
        ];
    }

    protected function ensureAttributes(array &$attributes)
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant();
        $command = $this->getCommand();
        $paymentGateway = $merchant->getPaymentGateway();

        $attributes = array_merge($attributes, [
            'merchant_id' => $merchant->id,
            'merchant_password' => md5($merchant->password),
            'version' => $paymentGateway::version(),
            'function' => $command === PaymentGateway::RC_PURCHASE ? 'SetExpressCheckout' : 'GetTransactionDetail'
        ]);

        if ($command === PaymentGateway::RC_PURCHASE) {
            $attributes['receiver_email'] = $attributes['receiver_email'] ?? $merchant->email;
            $attributes['payment_method'] = $attributes['payment_method'] ?? PaymentGateway::PAYMENT_METHOD_NL;
        }
    }

}