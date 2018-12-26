<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\momo;

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
 * @author Vuong Minh <vuongxuongminh@gmail.com>
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

        $attributes['signature'] = $this->getSignature($attributes);
        $attributes['requestType'] = $this->getRequestType();
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
        switch ($command = $this->getCommand()) {
            case PaymentGateway::RC_PURCHASE:
                $dataSign = [
                    'partnerCode' => $attributes['partnerCode'],
                    'accessKey' => $attributes['accessKey'],
                    'requestId' => $attributes['requestId'],
                    'amount' => $attributes['amount'],
                    'orderId' => $attributes['orderId'],
                    'orderInfo' => $attributes['orderInfo'],
                    'returnUrl' => $attributes['returnUrl'],
                    'notifyUrl' => $attributes['notifyUrl'],
                    'extraData' => $attributes['extraData'],
                ];
                break;
            case PaymentGateway::RC_QUERY_DR:
            case PaymentGateway::RC_QUERY_REFUND:
                $dataSign = [
                    'partnerCode' => $attributes['partnerCode'],
                    'accessKey' => $attributes['accessKey'],
                    'requestId' => $attributes['requestId'],
                    'orderId' => $attributes['orderId'],
                    'requestType' => $this->getRequestType()
                ];
                break;
            case PaymentGateway::RC_REFUND:
                $dataSign = [
                    'partnerCode' => $attributes['partnerCode'],
                    'accessKey' => $attributes['accessKey'],
                    'requestId' => $attributes['requestId'],
                    'amount' => $attributes['amount'],
                    'orderId' => $attributes['orderId'],
                    'transId' => $attributes['transId'],
                    'requestType' => $this->getRequestType()
                ];
                break;
            default:
                throw new NotSupportedException("Not supported command: `$command`");
        }

        $dataSign = urldecode(http_build_query($dataSign));
        return $this->getClient()->signature($dataSign);
    }
}
