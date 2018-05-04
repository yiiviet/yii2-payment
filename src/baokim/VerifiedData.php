<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment\baokim;

use Yii;

use yii\base\InvalidConfigException;

use yiivn\payment\VerifiedData as BaseVerifiedData;

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
            ], 'required', 'on' => [PaymentGateway::VC_PURCHASE_SUCCESS, PaymentGateway::VC_PAYMENT_NOTIFICATION]],
            [['checksum'], 'verifyChecksum', 'message' => '{attribute} not match', 'on' => [PaymentGateway::VC_PURCHASE_SUCCESS, PaymentGateway::VC_PAYMENT_NOTIFICATION]]
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
        $merchant = $this->getMerchant();
        $data = $this->get(false);
        ksort($data);

        if (!$merchant->validateSignature(implode("", $data), $data['checksum'], Merchant::SIGNATURE_HMAC)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}