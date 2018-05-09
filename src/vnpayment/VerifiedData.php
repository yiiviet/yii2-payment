<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use yii\helpers\ArrayHelper;

use yiiviet\payment\VerifiedData as BaseVerifiedData;

/**
 * Lớp VerifiedData tổng hợp dữ liệu đã được xác minh từ VnPayment.
 *
 * @property PaymentClient $client
 * @property PaymentClient $defaultClient
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
            ], 'required', 'on' => [PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN]],
            [['vnp_SecureHash'], 'validateSecureHash', 'message' => '{attribute} is not valid!', 'on' => [PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN]]
        ];
    }

    /**
     * Phương thức kiểm tra chữ ký dữ liệu có hợp lệ hay không từ VnPayment gửi sang.
     *
     * @param string $attribute Attribute có giá trị là chữ ký cần kiểm tra.
     * @param array $params Mảng tham trị thiết lập từ rule
     * @param \yii\validators\InlineValidator $validator
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    public function validateSecureHash($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        $data = $this->get(false);
        $expectSignature = ArrayHelper::remove($data, $attribute);
        $hashType = ArrayHelper::remove($data, 'vnp_SecureHashType', 'MD5');
        ksort($data);

        /** @var PaymentClient $client */
        $client = $this->getClient();

        if (!$client->validateSignature(http_build_query($data), $expectSignature, $hashType)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}
