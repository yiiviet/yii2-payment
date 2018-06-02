<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\baokim;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Lớp ResponseData cung cấp dữ liệu nhận được từ Bảo Kim khi tạo [[request()]] ở [[PaymentGateway]].
 *
 * @method PaymentClient getClient()
 *
 * @property PaymentClient $client
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
        return !isset($this['error_code'], $this['error']);
    }

    /**
     * Phương thức hổ trợ lấy câu báo lỗi nhận từ Bảo Kim.
     *
     * @return null|string Trả về NULL nếu như dữ liệu Bảo Kim gửi về không tồn tại `error` và ngược lại.
     */
    public function getErrorMessage(): ?string
    {
        if (isset($this['error'])) {
            return $this['error'];
        } else {
            return null;
        }
    }
}
