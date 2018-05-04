<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

use yii\helpers\ArrayHelper;
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
                'vpc_Command', 'vpc_Locale', 'vpc_MerchTxnRef', 'vpc_Merchant', 'vpc_OrderInfo', 'vpc_Amount',
                'vpc_TxnResponseCode', 'vpc_TransactionNo', 'vcp_Message', 'vpc_SecureHash'
            ], 'required', 'on' => [
                PaymentGateway::VC_PAYMENT_NOTIFICATION_INTERNATIONAL, PaymentGateway::VC_PAYMENT_NOTIFICATION,
                PaymentGateway::VC_PURCHASE_SUCCESS_INTERNATIONAL, PaymentGateway::VC_PURCHASE_SUCCESS
            ]],
            [['vpc_SecureHash'], 'validateSecureHash', 'message' => '{attribute} is not valid!', 'on' => [
                PaymentGateway::VC_PAYMENT_NOTIFICATION_INTERNATIONAL, PaymentGateway::VC_PAYMENT_NOTIFICATION,
                PaymentGateway::VC_PURCHASE_SUCCESS_INTERNATIONAL, PaymentGateway::VC_PURCHASE_SUCCESS
            ]],
            [['vpc_AcqResponseCode', 'vpc_Authorizeld', 'vpc_Card', 'vpc_3DSECI', 'vpc_3Dsenrolled', 'vpc_3Dsstatus', 'vpc_CommercialCard'], 'required', 'on' => [
                PaymentGateway::VC_PURCHASE_SUCCESS_INTERNATIONAL, PaymentGateway::VC_PAYMENT_NOTIFICATION_INTERNATIONAL
            ]]
        ];
    }

    /**
     * @param $attribute
     * @param $params
     * @param \yii\validators\InlineValidator $validator
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    public function validateSecureHash($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        $data = $this->get(false);
        $expectSignature = ArrayHelper::remove($data, 'vpc_SecureHash');
        $dataSign = [];

        foreach ($data as $param => $value) {
            if (strpos($param, 'vpc_') === 0) {
                $dataSign[$param] = $value;
            }
        }

        ksort($dataSign);

        if (!$this->getMerchant()->validateSignature(http_build_query($dataSign), $expectSignature)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }
}