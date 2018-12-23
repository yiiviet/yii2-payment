<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\momo;

use yiiviet\payment\VerifiedData as BaseVerifiedData;

/**
 * Lớp VerifiedData
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 * @property string $partnerCode của client sử dụng khi tạo request purchase.
 * @property string $accessKey của client sử dụng khi tạo request purchase.
 * @property mixed $requestId mã unique request id khi tạo request purchase
 * @property double $amount số tiền của đơn hàng.
 * @property string $orderId mã đơn hàng tại hệ thống.
 * @property string $orderType có giá trị cố định là `momo_wallet`.
 * @property string $transId mã giao dịch tại MOMO.
 * @property string $message thống báo (eng).
 * @property string $localMessage thống báo (vi).
 * @property string $responseTime thời gian phản hồi.
 * @property int $errorCode mã báo lỗi.
 * @property string $payType hình thức thanh toán (web hoặc qr).
 * @property mixed $extraData dữ liệu kèm theo khi tạo request purchase.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
class VerifiedData extends BaseVerifiedData
{

    use SignatureValidatorTrait;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['signature'], 'required', 'on' => [
                PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN
            ]],
            [['signature'], 'signatureValidator', 'message' => '{attribute} is not valid!', 'on' => [
                PaymentGateway::VRC_PURCHASE_SUCCESS, PaymentGateway::VRC_IPN
            ]]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getDataSignAttributes(): array
    {
        return [
            'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId', 'orderInfo', 'orderType',
            'transId', 'message', 'localMessage', 'responseTime', 'errorCode', 'payType', 'extraData'
        ];
    }
}
