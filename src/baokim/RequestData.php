<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii2vn\payment\Data;

/**
 * Class BaoKimCheckoutInstance
 *
 * @property Merchant $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestData extends Data
{

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['business'], 'required'],
            [['payer_name', 'payer_email', 'payer_phone_no', 'bank_payment_method_id'], 'required', 'on' => PaymentGateway::RC_PURCHASE_PRO],
            [['order_id', 'total_amount', 'url_success'], 'required', 'on' => [
                PaymentGateway::RC_PURCHASE_PRO, PaymentGateway::RC_PURCHASE
            ]]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function ensureAttributes(array &$attributes)
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant();
        $attributes['business'] = $attributes['business'] ?? $merchant->email;
    }

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    protected function prepare(array &$data)
    {
        ksort($data);

        /** @var Merchant $merchant */

        $merchant = $this->getMerchant();
        $command = $this->getCommand();

        if ($command & (PaymentGateway::RC_PURCHASE_PRO | PaymentGateway::RC_MERCHANT_DATA)) {
            if ($command === PaymentGateway::RC_PURCHASE_PRO) {
                $strSign = 'POST' . '&' . urlencode(PaymentGateway::PURCHASE_PRO_URL) . '&&' . urlencode(http_build_query($data));
            } else {
                $strSign = 'GET' . '&' . urlencode(PaymentGateway::PRO_SELLER_INFO_URL) . '&' . urlencode(http_build_query($data)) . '&';
            }
            $signature = $merchant->signature($strSign, Merchant::SIGNATURE_RSA);
            $data['signature'] = urlencode(base64_encode($signature));
        } elseif ($command & (PaymentGateway::RC_PURCHASE | PaymentGateway::RC_QUERY_DR)) {
            $strSign = implode("", $data);
            $data['checksum'] = $merchant->signature($strSign, Merchant::SIGNATURE_HMAC);
        }


    }

}
