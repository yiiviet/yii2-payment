<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\nganluong;

use vxm\gatewayclients\RequestData as BaseRequestData;

/**
 * Lớp RequestData cung cấp dữ liệu cho phương thức [[request()]] theo lệnh, để truy vấn với Ngân Lượng.
 *
 * @method PaymentClient getClient()
 *
 * @property PaymentClient $client
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestData extends BaseRequestData
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['merchant_id', 'merchant_password', 'version', 'function'], 'required', 'on' => [
                PaymentGateway::RC_PURCHASE, PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_AUTHENTICATE
            ]],
            [[
                'bank_code', 'buyer_fullname', 'buyer_email', 'buyer_mobile', 'return_url',
                'total_amount', 'order_code', 'receiver_email', 'payment_method'
            ], 'required', 'on' => PaymentGateway::RC_PURCHASE],
            [['otp', 'auth_url'], 'required', 'on' => PaymentGateway::RC_AUTHENTICATE],
            [['token'], 'required', 'on' => [PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_AUTHENTICATE]]
        ];

        if ($this->getClient()->getGateway()->getSeamless()) {
            return array_merge($rules, [
                [['card_number', 'card_fullname', 'card_month', 'card_year'], 'required', 'on' => PaymentGateway::RC_PURCHASE]
            ]);
        } else {
            return $rules;
        }
    }

    /**
     * @inheritdoc
     */
    protected function ensureAttributes(array &$attributes)
    {
        $client = $this->getClient();
        $command = $this->getCommand();

        $attributes = array_merge($attributes, [
            'merchant_id' => $client->merchantId,
            'merchant_password' => md5($client->merchantPassword),
            'version' => $client->getGateway()->getVersion()
        ]);

        if ($command === PaymentGateway::RC_PURCHASE) {
            $attributes['function'] = 'SetExpressCheckout';
            $attributes['receiver_email'] = $attributes['receiver_email'] ?? $client->email;

            if ($attributes['version'] === PaymentGateway::VERSION_3_2) {
                $attributes['payment_method'] = $attributes['payment_method'] ?? PaymentGateway::PAYMENT_METHOD_ATM_ONLINE;
            } else {
                $attributes['payment_method'] = $attributes['payment_method'] ?? PaymentGateway::PAYMENT_METHOD_NL;
            }
        } elseif ($command === PaymentGateway::RC_AUTHENTICATE) {
            $attributes['function'] = 'AuthenTransaction';
        } else {
            $attributes['function'] = 'GetTransactionDetail';
        }

        parent::ensureAttributes($attributes);
    }

}
