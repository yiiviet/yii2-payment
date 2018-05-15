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
 * @method PaymentClient getClient()
 *
 * @property PaymentClient $client
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class VerifiedData extends BaseVerifiedData
{

    use MagicPropertiesTrait;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vnp_SecureHash'], 'validateSecureHash', 'message' => '{attribute} is not valid!', 'on' => [
                PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN
            ], 'skipOnEmpty' => false]
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
        $expectSignature = ArrayHelper::remove($data, $attribute, false);
        $hashType = ArrayHelper::remove($data, 'vnp_SecureHashType', 'MD5');
        ksort($data);

        $client = $this->getClient();
        $dataSign = urldecode(http_build_query($data));

        if (!$expectSignature || !$client->validateSignature($dataSign, $expectSignature, $hashType)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}
