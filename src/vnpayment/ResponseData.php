<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\vnpayment;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Lớp ResponseData cung cấp dữ liệu phản hồi từ VnPayment sau khi thực hiện hàm [[request()]] ở [[PaymentGateway]].
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property bool $isOk trạng thái phản hồi từ VNPayment `TRUE` thành công và ngược lại.
 * @property int|null $responseCode mã phản hồi.
 * @property string $redirect_url đường dẫn sẽ redirect khách đến trang thanh toán, chỉ tồn tại khi `isOk` là TRUE.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0
 */
class ResponseData extends BaseResponseData
{
    /**
     * @inheritdoc
     */
    public function getIsOk(): bool
    {
        return $this->getCommand() === PaymentGateway::RC_PURCHASE || $this->getResponseCode() === 0;
    }

    /**
     * Phương thức hổ trợ lấy response code và ép kiểu về int.
     *
     * @return int|null Trả về NULL nếu như dữ liệu trả về từ VnPayment không có thuộc tính `vnp_ResponseCode` và ngược lại.
     */
    public function getResponseCode(): ?int
    {
        if (isset($this['vnp_ResponseCode'])) {
            return (int)$this['vnp_ResponseCode'];
        } else {
            return null;
        }
    }

    /**
     * Phương thức hổ trợ lấy câu thông báo từ `vnp_Message` nhận từ VnPayment.
     *
     * @return null|string Trả về NULL nếu như dữ liệu VnPayment gửi về không tồn tại `vnp_Message` và ngược lại.
     */
    public function getMessage(): ?string
    {
        if (isset($this['vnp_Message'])) {
            return $this['vnp_Message'];
        } else {
            return null;
        }
    }
}
