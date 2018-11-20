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
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property mixed $order_id mã đơn hàng tại hệ thống.
 * @property int $created_on thời gian tạo đơn hàng (timestamp).
 * @property int $payment_type hình thức khách giao dịch (1 Trực tiếp, 2 Tạm giữ).
 * @property int $transaction_status trạng thái giao dịch.
 * @property int $total_amount tổng số tiền khách trả.
 * @property int $net_amount tổng số tiền thực nhận.
 * @property int $fee_amount số tiền phí Bảo Kim thu.
 * @property int $merchant_id mã website tích hợp.
 * @property int $transaction_id mã giao dịch tại Bảo Kim.
 * @property string $payer_name tên người mua, chỉ tồn tại khi phương thức thanh toán thông qua bảo kim không phải `pro`.
 * @property string $payer_email email người mua, chỉ tồn tại khi phương thức thanh toán thông qua bảo kim không phải `pro`.
 * @property string $payer_phone_no số điện thoại người mua, chỉ tồn tại khi phương thức thanh toán thông qua bảo kim không phải `pro`.
 * @property string $shipping_address địa chỉ giao hàng, chỉ tồn tại khi phương thức thanh toán thông qua bảo kim không phải `pro`.
 * @property string $customer_name tên người mua, chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $customer_email email người mua, chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $customer_phone số điện thoại người mua, chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $customer_address địa chỉ giao hàng, chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $merchant_address địa chỉ merchant, chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $merchant_email email merchant, chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $merchant_location khu vực merchant, chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $merchant_name tên người bán (merchant), chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $merchant_phone số điên thoại người bán, chỉ tồn tại khi phương thức thanh toán `pro`.
 * @property string $customer_location khu vực khách hàng, chỉ tồn tại khi phương thức thanh toán `pro`.
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
            [['checksum'], 'required', 'on' => PaymentGateway::VRC_PURCHASE_SUCCESS],
            [['checksum'], 'verifyChecksum', 'message' => '{attribute} not match', 'on' => PaymentGateway::VRC_PURCHASE_SUCCESS]
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
        ksort($data);
        $dataSign = implode('', $data);

        if (!$expectSignature || !$client->validateSignature($dataSign, $expectSignature, PaymentClient::SIGNATURE_HMAC)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

}
