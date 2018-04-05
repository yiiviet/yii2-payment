<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii2vn\payment\CheckoutData;

/**
 * Class BaoKimCheckoutInstance
 *
 * @property Merchant $merchant
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutRequestData extends CheckoutData
{

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['seri_field', 'pin_field', 'transaction_id', 'card_id'], 'required', 'on' => [PaymentGateway::CHECKOUT_METHOD_CARD_CHARGE]],
            [['order_id', 'total_amount', 'payer_name', 'payer_email', 'payer_phone_no', 'url_success'], 'required', 'on' => [
                PaymentGateway::CHECKOUT_METHOD_LOCAL_BANK, PaymentGateway::CHECKOUT_METHOD_CREDIT_CARD, PaymentGateway::CHECKOUT_METHOD_INTERNET_BANKING,
                PaymentGateway::CHECKOUT_METHOD_BAO_KIM, PaymentGateway::CHECKOUT_METHOD_BANK_TRANSFER, PaymentGateway::CHECKOUT_METHOD_ATM_TRANSFER,
            ]],
            [['bank_payment_method_id'], 'required', 'on' => [
                PaymentGateway::CHECKOUT_METHOD_LOCAL_BANK, PaymentGateway::CHECKOUT_METHOD_CREDIT_CARD, PaymentGateway::CHECKOUT_METHOD_INTERNET_BANKING
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

        if ($this->method === PaymentGateway::CHECKOUT_METHOD_CARD_CHARGE) {
            $attributes['merchant_id'] = $this->merchant->id;
        } else {
            $attributes['business'] = $attributes['business'] ?? $merchant->email;
        }

    }

    protected function signature(array $data): string
    {
        ksort($data);

        if (in_array($this->method, [
            PaymentGateway::CHECKOUT_METHOD_CARD_CHARGE, PaymentGateway::CHECKOUT_METHOD_ATM_TRANSFER,
            PaymentGateway::CHECKOUT_METHOD_BANK_TRANSFER, PaymentGateway::CHECKOUT_METHOD_BAO_KIM
        ], true)) {
            $dataSign = implode("", $data);
            $signType = Merchant::SIGNATURE_HMAC;
        } else {
            $dataSign = 'POST' . '&' . urlencode(PaymentGateway::PRO_PAYMENT_URL) . '&&' . urlencode(http_build_query($data));
            $signType = Merchant::SIGNATURE_RSA;
        }

        return $this->getMerchant()->signature($dataSign, $signType);
    }

}