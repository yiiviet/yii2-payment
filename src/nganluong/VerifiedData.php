<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\nganluong;

use yiiviet\payment\VerifiedData as BaseVerifiedData;

/**
 * Lớp VerifiedData cung cấp dữ liệu đã được xác thực từ Ngân Lượng gửi về.
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property string $token mã token dùng để truy vấn thông tin giao dịch.
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
            [['token'], 'required', 'on' => PaymentGateway::VRC_PURCHASE_SUCCESS]
        ];
    }

}
