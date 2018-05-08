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
        return [
            [['merchant_id', 'merchant_password', 'version', 'function'], 'required', 'on' => [PaymentGateway::RC_PURCHASE, PaymentGateway::RC_QUERY_DR]],
            [['token'], 'required', 'on' => PaymentGateway::RC_QUERY_DR],
            [[
                'bank_code', 'buyer_fullname', 'buyer_email', 'buyer_mobile',
                'total_amount', 'order_code', 'receiver_email', 'payment_method'
            ], 'required', 'on' => PaymentGateway::RC_PURCHASE]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function ensureAttributes(array &$attributes)
    {
        parent::ensureAttributes($attributes);
        /** @var PaymentClient $client */
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
            $attributes['payment_method'] = $attributes['payment_method'] ?? PaymentGateway::PAYMENT_METHOD_NL;
        } else {
            $attributes['function'] = 'GetTransactionDetail';
        }
    }

}
