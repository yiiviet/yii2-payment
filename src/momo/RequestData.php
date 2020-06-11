<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\momo;

use yii\base\NotSupportedException;
use yii\behaviors\AttributeTypecastBehavior;

use vxm\gatewayclients\RequestData as BaseRequestData;

/**
 * Lớp RequestData cung cấp dữ liệu đã được kiểm tra tính trọn vẹn khi tạo [[request()]] ở [[PaymentGateway]].
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class RequestData extends BaseRequestData
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $attributeTypes = [
            'orderId' => 'string'
        ];

        if (in_array($this->command, [PaymentGateway::RC_PURCHASE, PaymentGateway::RC_QUERY_REFUND], true)) {
            $attributeTypes['amount'] = 'string';
        }

        return [
            'typeCast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => $attributeTypes
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount'], 'required', 'on' => [PaymentGateway::RC_REFUND, PaymentGateway::RC_PURCHASE]],
            [['returnUrl', 'notifyUrl'], 'required', 'on' => PaymentGateway::RC_PURCHASE],
            [['transId'], 'required', 'on' => PaymentGateway::RC_REFUND],
            [['partnerCode', 'accessKey', 'requestId', 'orderId', 'signature', 'requestType'], 'required', 'on' => [
                PaymentGateway::RC_PURCHASE, PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_REFUND, PaymentGateway::RC_QUERY_REFUND
            ]],
        ];
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    protected function ensureAttributes(array &$attributes)
    {
        parent::ensureAttributes($attributes);
        $client = $this->getClient();
        $command = $this->getCommand();
        $attributes['partnerCode'] = $client->partnerCode;
        $attributes['accessKey'] = $client->accessKey;

        if ($command === PaymentGateway::RC_PURCHASE) {
            $attributes['orderInfo'] = $attributes['orderInfo'] ?? '';
            $attributes['extraData'] = $attributes['extraData'] ?? '';
        }

        $attributes['requestType'] = $this->getRequestType();
        $attributes['signature'] = $this->getSignature($attributes);
    }

    /**
     * Phương thức hổ trợ lấy `requestType` tương ứng với [[getCommand()]] khi gửi dữ liệu đến MOMO.
     *
     * @return string `requestType` gửi đến MOMO
     * @throws NotSupportedException
     */
    protected function getRequestType(): string
    {
        switch ($command = $this->getCommand()) {
            case PaymentGateway::RC_PURCHASE:
                return 'captureMoMoWallet';
            case PaymentGateway::RC_QUERY_DR:
                return 'transactionStatus';
            case PaymentGateway::RC_REFUND:
                return 'refundMoMoWallet';
            case PaymentGateway::RC_QUERY_REFUND:
                return 'refundStatus';
            default:
                throw new NotSupportedException("Not supported command: `$command`");
        }
    }

    /**
     * Phương thức hồ trợ ký dữ liệu gửi đến MOMO.
     *
     * @param array $attributes mảng chứa các thông tin dùng để tạo chữ ký.
     * @return string chữ ký dữ liệu.
     * @throws NotSupportedException
     */
    protected function getSignature(array $attributes): string
    {
        $dataSign = [];

        foreach ($this->getSignatureAttributes() as $signAttribute) {
            if (isset($attributes[$signAttribute])) {
                $dataSign[$signAttribute] = $attributes[$signAttribute];
            }
        }

        $strSign = urldecode(http_build_query($dataSign));

        return $this->getClient()->signature($strSign);
    }

    /**
     * Phương thức cung cấp các attribute theo [[getCommand()]] dùng để tạo chữ ký dữ liệu.
     *
     * @return array các phần tử có giá trị là tên attribute
     * @throws NotSupportedException
     * @since 1.0.4
     */
    protected function getSignatureAttributes(): array
    {
        switch ($command = $this->getCommand()) {
            case PaymentGateway::RC_PURCHASE:
                return [
                    'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId', 'orderInfo', 'returnUrl', 'notifyUrl', 'extraData'
                ];
            case PaymentGateway::RC_QUERY_DR:
            case PaymentGateway::RC_QUERY_REFUND:
                return [
                    'partnerCode', 'accessKey', 'requestId', 'orderId', 'requestType'
                ];
            case PaymentGateway::RC_REFUND:
                return [
                    'partnerCode', 'accessKey', 'requestId', 'amount', 'orderId', 'transId', 'requestType'
                ];
            default:
                throw new NotSupportedException("Not supported command: `$command`");
        }
    }

}
