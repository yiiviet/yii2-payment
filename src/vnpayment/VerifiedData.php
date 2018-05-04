<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment\vnpayment;

use yii\helpers\ArrayHelper;

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
                'vnp_TmnCode', 'vnp_Amount', 'vnp_BankCode', 'vnp_BankTranNo', 'vnp_CardType', 'vnp_PayDate', 'vnp_CurrCode',
                'vnp_OrderInfo', 'vnp_TransactionNo', 'vnp_ResponseCode', 'vnp_TxnRef', 'vnp_SecureHashType', 'vnp_SecureHash'
            ], 'required', 'on' => [PaymentGateway::VC_PURCHASE_SUCCESS, PaymentGateway::VC_PAYMENT_NOTIFICATION]],
            [['vnp_SecureHash'], 'validateSecureHash', 'message' => '{attribute} is not valid!', 'on' => [PaymentGateway::VC_PURCHASE_SUCCESS, PaymentGateway::VC_PAYMENT_NOTIFICATION]]
        ];
    }

    /**
     * Validate secure hash is valid or not. This method may use on rules to validate secure hash attribute.
     *
     * @param string $attribute
     * @param $params
     * @param \yii\validators\InlineValidator $validator
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    public function validateSecureHash($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        $data = $this->get(false);
        $hashType = ArrayHelper::remove($data, 'vnp_SecureHashType', 'MD5');
        ksort($data);

        if (!$this->getMerchant()->validateSignature(http_build_query($data), $hashType)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}