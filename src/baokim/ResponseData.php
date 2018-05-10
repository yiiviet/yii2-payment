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
    public function ensureAttributes(array &$attributes)
    {
        parent::ensureAttributes($attributes);
        $ensuredAttributes = [];

        foreach ($attributes as $attribute => $value) {
            $ensuredAttributes[trim($attribute)] = $value;
        }

        $attributes = $ensuredAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getIsOk(): bool
    {
        if ($this->command === PaymentGateway::RC_VERIFY_IPN) {
            return !isset($this['INVALID']);
        } else {
            return !isset($this['error_code'], $this['error']);
        }
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
