<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\vtcpay;

use vxm\gatewayclients\RequestData as BaseRequestData;

/**
 * Lớp RequestData cung cấp dữ liệu để truy vấn đến VTCPay tạo lệnh thanh toán.
 *
 * @method PaymentClient getClient()
 *
 * @property PaymentClient $client
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class RequestData extends BaseRequestData
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['website_id', 'signature', 'reference_number', 'receiver_account', 'currency', 'amount'], 'required', 'on' => PaymentGateway::RC_PURCHASE]
        ];
    }

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    protected function ensureAttributes(array &$attributes)
    {
        $client = $this->getClient();
        parent::ensureAttributes($attributes);

        $attributesEnsured = $attributes;
        $attributesEnsured['website_id'] = $client->merchantId;
        $attributesEnsured['receiver_account'] = $attributes['receiver_account'] ?? $client->business;
        $attributesEnsured['currency'] = $attributes['currency'] ?? 'VND';

        unset($attributesEnsured['signature']);
        ksort($attributesEnsured);
        $dataSign = implode('|', $attributesEnsured);
        $attributesEnsured['signature'] = $client->signature($dataSign);

        $attributes = $attributesEnsured;
    }

}
