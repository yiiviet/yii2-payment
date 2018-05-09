<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
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
                PaymentGateway::VRC_IPN_INTERNATIONAL, PaymentGateway::VRC_IPN,
                PaymentGateway::VRC_PURCHASE_SUCCESS_INTERNATIONAL, PaymentGateway::VRC_PURCHASE_SUCCESS
            ]],
            [['vpc_SecureHash'], 'validateSecureHash', 'message' => '{attribute} is not valid!', 'on' => [
                PaymentGateway::VRC_IPN_INTERNATIONAL, PaymentGateway::VRC_IPN,
                PaymentGateway::VRC_PURCHASE_SUCCESS_INTERNATIONAL, PaymentGateway::VRC_PURCHASE_SUCCESS
            ]],
            [['vpc_AcqResponseCode', 'vpc_Authorizeld', 'vpc_Card', 'vpc_3DSECI', 'vpc_3Dsenrolled', 'vpc_3Dsstatus', 'vpc_CommercialCard'], 'required', 'on' => [
                PaymentGateway::VRC_PURCHASE_SUCCESS_INTERNATIONAL, PaymentGateway::VRC_IPN_INTERNATIONAL
            ]]
        ];
    }

    /**
     * Phương thức kiểm tra chữ ký dữ liệu nhận từ OnePay.
     *
     * @param string $attribute Attribute chứa giá trị chữ ký cần kiểm tra.
     * @param array $params Mảng các tham trị được thiết lập từ rule.
     * @param \yii\validators\InlineValidator $validator Đối tượng thực thi kiểm tra.
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    public function validateSecureHash($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        $data = $this->get(false);
        $expectSignature = ArrayHelper::remove($data, $attribute);
        $dataSign = [];

        foreach ($data as $param => $value) {
            if (strpos($param, 'vpc_') === 0) {
                $dataSign[$param] = $value;
            }
        }

        ksort($dataSign);

        /** @var PaymentClient $client */
        $client = $this->getClient();

        if (!$client->validateSignature(http_build_query($dataSign), $expectSignature)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }
}
