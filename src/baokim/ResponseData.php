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
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property bool $isOk trạng thái phản hồi từ Bảo Kim `TRUE` thành công và ngược lại.
 * @property null|string $errorMessage chuỗi thông báo lỗi khi `isOk` là `FALSE`.
 * @property string $redirect_url đường dẫn chuyển tiếp thanh toán, chỉ tồn tại khi thuộc tính `isOk` là `TRUE`.
 * @property string $next_action hành động tiếp theo cần phải thực thi, chỉ tồn tại khi thuộc tính `isOk` là `TRUE` và với phương thức `pro`.
 * @property int $rv_id mã phiếu thu, chỉ tồn tại khi thuộc tính `isOk` là `TRUE` và với phương thức `pro`.
 * @property int $guide_url mã phiếu thu, chỉ tồn tại khi thuộc tính `isOk` là `TRUE` và với phương thức `pro` khi thuộc tính `rv_id` có giá trị là `display_guide`.
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
