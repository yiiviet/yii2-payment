<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii2vn\payment\RequestData as BaseRequestData;

/**
 * Class BaoKimCheckoutInstance
 *
 * @property Merchant $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestData extends BaseRequestData
{

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['business'], 'required', 'on' => [PaymentGateway::RC_PURCHASE, PaymentGateway::RC_PURCHASE_PRO, PaymentGateway::RC_GET_MERCHANT_DATA]],
            [['merchant_id', 'transaction_id'], 'required', 'on' => PaymentGateway::RC_QUERY_DR],
            [['checksum'], 'required', 'on' => [PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_PURCHASE]],
            [['payer_name', 'payer_email', 'payer_phone_no', 'bank_payment_method_id'], 'required', 'on' => PaymentGateway::RC_PURCHASE_PRO],
            [['order_id', 'total_amount', 'url_success'], 'required', 'on' => [PaymentGateway::RC_PURCHASE, PaymentGateway::RC_PURCHASE_PRO]]
        ];
    }

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    protected function ensureAttributes(array &$attributes)
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant();
        $command = $this->getCommand();

        if ($command & (PaymentGateway::RC_PURCHASE | PaymentGateway::RC_PURCHASE_PRO | PaymentGateway::RC_GET_MERCHANT_DATA)) {
            $attributes['business'] = $attributes['business'] ?? $merchant->email; // because business can custom with another email in BK system not only merchant.
        } elseif ($command === PaymentGateway::RC_QUERY_DR) {
            $attributes['merchant_id'] = $merchant->id;
        }

        ksort($attributes);

        if ($command & (PaymentGateway::RC_PURCHASE_PRO | PaymentGateway::RC_GET_MERCHANT_DATA)) {
            if ($command === PaymentGateway::RC_PURCHASE_PRO) {
                $strSign = 'POST' . '&' . urlencode(PaymentGateway::PURCHASE_PRO_URL) . '&&' . urlencode(http_build_query($attributes));
            } else {
                $strSign = 'GET' . '&' . urlencode(PaymentGateway::PRO_SELLER_INFO_URL) . '&' . urlencode(http_build_query($attributes)) . '&';
            }
            $signature = $merchant->signature($strSign, Merchant::SIGNATURE_RSA);
            $attributes['signature'] = urlencode(base64_encode($signature));
        } elseif ($command & (PaymentGateway::RC_PURCHASE | PaymentGateway::RC_QUERY_DR)) {
            $strSign = implode("", $attributes);
            $attributes['checksum'] = $merchant->signature($strSign, Merchant::SIGNATURE_HMAC);
        }
    }

}
