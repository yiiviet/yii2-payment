<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\onepay;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Lớp ResponseData cung cấp dữ liệu nhận được từ OnePay khi gọi [[request()]] ở lớp [[PaymentGateway]].
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property bool $isOk trạng thái phản hồi từ OnePay `TRUE` thành công và ngược lại.
 * @property string $redirect_url đường dẫn redirect khách đến trang thanh toán, chỉ tồn tại khi `isOk` là TRUE.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0
 */
class ResponseData extends BaseResponseData
{

    use MagicPropertiesTrait;

    /**
     * @inheritdoc
     */
    public function getIsOk(): bool
    {
        if ($this->getCommand() === PaymentGateway::RC_QUERY_DR) {
            return isset($this['vpc_DRExists']) ? strcasecmp($this['vpc_DRExists'], 'Y') === 0 : false;
        } else {
            return true;
        }
    }

}
