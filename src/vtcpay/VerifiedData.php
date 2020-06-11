<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace nhuluc\payment\vtcpay;

use yii\helpers\ArrayHelper;

use nhuluc\payment\VerifiedData as BaseVerifiedData;

/**
 * Lớp VerifiedData tổng hợp dữ liệu đã được xác minh từ VTCPay.
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property double $amount số tiền đơn hàng.
 * @property string $message thông tin bổ sung.
 * @property string $payment_type hình thức thanh toán.
 * @property mixed $reference_number mã đơn hàng.
 * @property int $status trạng thái.
 * @property mixed $trans_ref_no mã giao dịch tại VTCPay.
 * @property string $website_id merchant id của client.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
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
            [['signature'], 'required', 'on' => [
                PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN
            ]],
            [['signature'], 'validateSignature', 'message' => '{attribute} is not valid!', 'on' => [
                PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN
            ]]
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
        } elseif ($this->command === PaymentGateway::VRC_IPN) {
            $attributes = ['amount', 'message', 'payment_type', 'reference_number', 'status', 'trans_ref_no', 'website_id'];
            $values = explode('|', $dataSign);

            foreach (array_combine($attributes, $values) as $attr => $value) {
                $this->defineAttribute($attr, $value === '' ? null : $value);
            }
        }
    }

}
