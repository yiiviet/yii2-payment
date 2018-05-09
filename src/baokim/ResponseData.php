<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\baokim;

use Yii;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Lớp ResponseData cung cấp dữ liệu nhận được từ Bảo Kim khi tạo [[request()]] ở [[PaymentGateway]].
 *
 * @property PaymentClient $merchant
 * @property null|string $errorMessage
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
        return !$this->canGetProperty('error_code');
    }

    /**
     * Phương thức hổ trợ lấy và phiên dịch mã báo lỗi nhận từ Bảo Kim (nếu như bạn có thiết lập i18n).
     *
     * @return null|string Trả về NULL nếu như dữ liệu Bảo Kim gửi về không tồn tại `error_code`,
     * và ngược lại sẽ là câu thông báo đã được phiên dịch.
     */
    public function getErrorMessage(): ?string
    {
        if ($this->canGetProperty('error')) {
            return Yii::t('yii2vn/payment/baokim', $this->error);
        } else {
            return null;
        }
    }
}
