<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii\helpers\ArrayHelper;

use yii2vn\payment\Data;

/**
 * Class ResponseData
 *
 * @property Merchant $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class ResponseData extends Data
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['seller_account', 'bank_payment_methods'], 'required', 'on' => PaymentGateway::RC_GET_MERCHANT_DATA],
            [['next_action', 'rv_id'], 'required', 'on' => PaymentGateway::RC_PURCHASE_PRO],
            [['redirect_url'], 'required', 'on' => PaymentGateway::RC_PURCHASE],
            [[
                'order_id', 'transaction_id', 'created_on', 'payment_type', 'transaction_status', 'checksum',
                'total_amount', 'net_amount', 'fee_amount', 'merchant_id', 'customer_name', 'customer_email', 'customer_phone'
            ], 'required', 'on' => PaymentGateway::RC_QUERY_DR],
            [['checksum'], 'verifyChecksum', 'message' => '{attribute} not match', 'on' => PaymentGateway::RC_QUERY_DR]
        ];
    }

    /**
     * @inheritdoc
     */
    public function ensureAttributes(array &$attributes)
    {
        $ensuredAttributes = [];

        foreach ($attributes as $k => $v) {
            $ensuredAttributes[strtolower($k)] = $v;
        }

        $attributes = $ensuredAttributes;
    }

    /**
     * @param $attribute
     * @param $params
     * @param \yii\validators\InlineValidator $validator
     * @throws \yii\base\NotSupportedException
     */
    public function verifyChecksum($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant();
        $data = $this->toArray();
        $checksum = ArrayHelper::remove($data, 'checksum');

        ksort($data);

        if (!$merchant->validateSignature(implode("", $data), $checksum, Merchant::SIGNATURE_HMAC)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}