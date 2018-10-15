<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\vtcpay;

use yii\helpers\ArrayHelper;

use yiiviet\payment\VerifiedData as BaseVerifiedData;

/**
 * Lớp VerifiedData tổng hợp dữ liệu đã được xác minh từ VTCPay.
 *
 * @method PaymentClient getClient()
 *
 * @property PaymentClient $client
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class VerifiedData extends BaseVerifiedData
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['signature'], 'validateSignature', 'message' => '{attribute} is not valid!', 'on' => [
                PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN
            ], 'skipOnEmpty' => false]
        ];
    }

    /**
     * Phương thức kiểm tra chữ ký dữ liệu có hợp lệ hay không từ VTCPay gửi sang.
     *
     * @param string $attribute Attribute có giá trị là chữ ký cần kiểm tra.
     * @param array $params Mảng tham trị thiết lập từ rule
     * @param \yii\validators\InlineValidator $validator
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    public function validateSignature($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        $client = $this->getClient();
        $data = $this->get(false);
        $expectSignature = ArrayHelper::remove($data, $attribute, false);

        if ($this->command === PaymentGateway::VRC_IPN) {
            $dataSign = $data['data'] ?? '';
        } else {
            ksort($data);
            $dataSign = implode('|', $data);
        }

        if (!$expectSignature || !$client->validateSignature($dataSign, $expectSignature)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}
