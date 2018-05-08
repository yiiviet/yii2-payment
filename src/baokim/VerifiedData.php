<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\baokim;

use yii\base\InvalidConfigException;

use yiiviet\payment\VerifiedData as BaseVerifiedData;

/**
 * Class VerifiedData
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class VerifiedData extends BaseVerifiedData
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[
                'order_id', 'transaction_id', 'created_on', 'payment_type', 'transaction_status', 'checksum',
                'total_amount', 'net_amount', 'fee_amount', 'merchant_id', 'customer_name', 'customer_email', 'customer_phone'
            ], 'required', 'on' => [PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN]],
            [['checksum'], 'verifyChecksum', 'message' => '{attribute} not match', 'on' => [PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN]]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @param \yii\validators\InlineValidator $validator
     * @throws \yii\base\NotSupportedException|InvalidConfigException
     */
    public function verifyChecksum($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        /** @var PaymentClient $client */
        $client = $this->getClient();
        $data = $this->get(false);
        ksort($data);

        if (!$client->validateSignature(implode("", $data), $data['checksum'], PaymentClient::SIGNATURE_HMAC)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}
