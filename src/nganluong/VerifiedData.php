<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\nganluong;

use nhuluc\payment\VerifiedData as BaseVerifiedData;

/**
 * Lớp VerifiedData cung cấp dữ liệu đã được xác thực từ Ngân Lượng gửi về.
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property string $token mã token dùng để truy vấn thông tin giao dịch.
 * @property string $error_code mã lỗi, chỉ tồn tại khi `token` hợp lệ.
 * @property mixed $order_code mã giao dịch tại hệ thống, chỉ tồn tại khi `token` hợp lệ.
 * @property double $total_amount số tiền đơn hàng, chỉ tồn tại khi `token` hợp lệ.
 * @property string $payment_method phương thức thanh toán, chỉ tồn tại khi `token` hợp lệ.
 * @property string $bank_code mã ngân hàng, chỉ tồn tại khi `token` hợp lệ.
 * @property string $payment_type hình thức thanh toán `1` là trực tiếp, `2` là tạm giữ, chỉ tồn tại khi `token` hợp lệ.
 * @property string $order_description thông tin đơn hàng, chỉ tồn tại khi `token` hợp lệ.
 * @property double $tax_amount tiền thuế, chỉ tồn tại khi `token` hợp lệ.
 * @property double $discount_amount tiền giảm giá, chỉ tồn tại khi `token` hợp lệ.
 * @property double $fee_shipping phí ship, chỉ tồn tại khi `token` hợp lệ.
 * @property string $return_url đường dẫn trả về khi thanh toán thành công, chỉ tồn tại khi `token` hợp lệ.
 * @property string $cancel_url đường dẫn trả về khi hủy thành công, chỉ tồn tại khi `token` hợp lệ.
 * @property string $notify_url đường dẫn Ngân Lượng gọi về khi khách thanh toán thành công, chỉ tồn tại khi `token` hợp lệ.
 * @property int $time_limit số phút còn lại để thực thi giao dịch, chỉ tồn tại khi `token` hợp lệ.
 * @property string $buyer_fullname tên người mua, chỉ tồn tại khi `token` hợp lệ.
 * @property string $buyer_email email người mua, chỉ tồn tại khi `token` hợp lệ.
 * @property string $buyer_mobile số điện thoại người mua, chỉ tồn tại khi `token` hợp lệ.
 * @property string $buyer_address địa chỉ người mua, chỉ tồn tại khi `token` hợp lệ.
 * @property string $affiliate_code mã đối tác của Ngân Lượng, chỉ tồn tại khi `token` hợp lệ.
 * @property string $transaction_status trạng thái giao dịch, chỉ tồn tại khi `token` hợp lệ.
 * @property string $transaction_id mã giao dịch tại Ngân Lượng, chỉ tồn tại khi `token` hợp lệ.
 * @property string $description thông tin giao dịch tại Ngân Lượng, chỉ tồn tại khi `token` hợp lệ.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
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
            [['token'], 'required', 'on' => PaymentGateway::VRC_PURCHASE_SUCCESS],
            [['token'], 'ensureToken', 'message' => '{attribute} invalid!', 'on' => PaymentGateway::VRC_PURCHASE_SUCCESS]
        ];
    }

    /**
     * Phương thức kiểm tra tính hợp lệ của mã `token` gửi từ Ngân Lượng.
     *
     * @param string $attribute Chứa giá trị thuộc tính cần kiểm tra.
     * @param string $params Mảng thông tin khi thiết lập rule.
     * @param \yii\validators\InlineValidator $validator Đối tượng [[\yii\validators\InlineValidator]] đang thực thi kiểm tra dữ liệu.
     * @throws \ReflectionException|\yii\base\InvalidConfigException
     */
    public function ensureToken($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        $response = $this->getClient()->getGateway()->queryDR(['token' => $this->token]);

        if (!$response->isOk) {
            $validator->addError($this, $attribute, $validator->message);
        } else {
            foreach ($response->get(false) as $attr => $value) {
                $this->defineAttribute($attr, $value);
            }
        }
    }

}
