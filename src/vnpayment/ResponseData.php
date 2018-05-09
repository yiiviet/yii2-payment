<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use Yii;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Lớp ResponseData cung cấp dữ liệu phản hồi từ VnPayment sau khi thực hiện hàm [[request()]] ở [[PaymentGateway]].
 *
 * @property PaymentClient $client
 * @property int|null $responseCode
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
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
        if ($this->canGetProperty('vnp_ResponseCode')) {
            return (int)$this->vnp_ResponseCode;
        } else {
            return null;
        }
    }

    /**
     * Phương thức hổ trợ lấy và phiên dịch message nhận từ VnPayment (nếu như bạn có thiết lập i18n).
     *
     * @return null|string Trả về NULL nếu như dữ liệu VnPayment gửi về không tồn tại `vnp_Message`,
     * và ngược lại sẽ là câu thông báo đã được phiên dịch.
     */
    public function getMessage(): ?string
    {
        if ($this->canGetProperty('vnp_Message')) {
            return Yii::t('yii2vn/payment/vnpayment', $this->vnp_Message);
        } else {
            return null;
        }
    }
}
