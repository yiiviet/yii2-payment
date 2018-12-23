<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\momo;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Lớp ResponseData cung cấp dữ liệu nhận được từ MOMO khi tạo [[request()]] ở [[PaymentGateway]].
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 *
 * @property string $partnerCode của client sử dụng khi tạo request [[PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_REFUND, PaymentGateway::RC_QUERY_REFUND]].
 * @property string $accessKey của client sử dụng khi tạo request [[PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_REFUND, PaymentGateway::RC_QUERY_REFUND]].
 * @property mixed $requestId mã unique request id khi tạo request [[PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_REFUND, PaymentGateway::RC_QUERY_REFUND]].
 * @property double $amount số tiền của đơn hàng chỉ tồn tại với request [[PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_REFUND]].
 * @property string $orderId mã đơn hàng tại hệ thống.
 * @property string $orderType có giá trị cố định là `momo_wallet`.
 * @property string $transId mã giao dịch tại MOMO chỉ tồn tại khi tạo request [[PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_REFUND, PaymentGateway::RC_QUERY_REFUND]].
 * @property string $message thống báo (eng).
 * @property string $localMessage thống báo (vi).
 * @property string $responseTime thời gian phản hồi.
 * @property int $errorCode mã báo lỗi.
 * @property string $payType hình thức thanh toán (web hoặc qr) chỉ tồn tại khi tạo request [[PaymentGateway::RC_QUERY_DR]].
 * @property mixed $extraData dữ liệu kèm theo khi tạo request purchase chỉ tồn tại khi tạo request [[PaymentGateway::RC_QUERY_DR]].
 * @property string $payUrl đường dẫn thanh toán chỉ tồn tại khi tạo request [[PaymentGateway::RC_PURCHASE]].
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
class ResponseData extends BaseResponseData
{

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function scenarios()
    {
        $requestCommands = $this->getClient()->getGateway()->requestCommands();

        return array_fill_keys($requestCommands, ['signature']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['signature', 'required'],
            ['signature', SignatureValidator::class, 'client' => $this->getClient(), 'dataSignAttributes' => $this->getDataSignAttributes()]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getIsOk(): bool
    {
        return $this->validate();
    }

    /**
     * @inheritdoc
     */
    protected function getDataSignAttributes(): array
    {
        switch ($this->getCommand()) {
            case PaymentGateway::RC_PURCHASE:
                return ['requestId', 'orderId', 'message', 'localMessage', 'payUrl', 'errorCode', 'requestType'];
            case PaymentGateway::RC_QUERY_DR:
                return ['partnerCode', 'accessKey', 'requestId', 'orderId', 'errorCode', 'transId', 'amount', 'message', 'localMessage', 'requestType', 'payType', 'extraData'];
            case PaymentGateway::RC_REFUND:
                return ['partnerCode', 'accessKey', 'requestId', 'orderId', 'errorCode', 'transId', 'message', 'localMessage', 'requestType'];
            case PaymentGateway::RC_QUERY_REFUND:
                return ['partnerCode', 'accessKey', 'requestId', 'orderId', 'errorCode', 'transId', 'amount', 'message', 'localMessage', 'requestType'];
            default:
                return [];
        }
    }
}
