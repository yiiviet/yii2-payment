<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\baokim;

use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

use yiiviet\payment\VerifiedData as BaseVerifiedData;

/**
 * Lớp VerifiedData cung cấp dữ liệu đã được xác minh từ các truy vấn IPN, success url.
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

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['checksum'], 'verifyChecksum', 'message' => '{attribute} not match', 'on' => PaymentGateway::VRC_PURCHASE_SUCCESS, 'skipOnEmpty' => false]
        ];
    }

    /**
     * Phương thức kiểm tra tính hợp lệ của mã `checksum` gửi từ Bảo Kim.
     *
     * @param string $attribute Chứa giá trị thuộc tính cần kiểm tra.
     * @param string $params Mảng thông tin khi thiết lập rule.
     * @param \yii\validators\InlineValidator $validator Đối tượng [[\yii\validators\InlineValidator]] đang thực thi kiểm tra dữ liệu.
     * @throws \yii\base\NotSupportedException|InvalidConfigException
     */
    public function verifyChecksum($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        /** @var PaymentClient $client */
        $client = $this->getClient();
        $data = $this->get(false);
        $expectSignature = ArrayHelper::remove($data, $attribute, false);
        $dataSign = implode('', $data);
        ksort($data);

        if (!$expectSignature || !$client->validateSignature($dataSign, $expectSignature, PaymentClient::SIGNATURE_HMAC)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}
