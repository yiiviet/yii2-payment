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
            [['payer_name', 'payer_email', 'payer_phone_no'], 'required', 'on' => PaymentGateway::RC_PURCHASE],
            [['customer_name', 'customer_email', 'customer_phone_no', 'bank_payment_method_id'], 'required', 'on' => PaymentGateway::RC_PURCHASE_PRO],
            [['business', 'order_id', 'total_amount', 'url_success'], 'required', 'on' => [
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

    protected function signature(array &$data)
    {
        $dataSign = $data;
        ksort($dataSign);

        switch ($this->getCommand()) {
            case PaymentGateway::CHECKOUT_METHOD_CARD_CHARGE || PaymentGateway::CHECKOUT_METHOD_ATM_TRANSFER || PaymentGateway::CHECKOUT_METHOD_BANK_TRANSFER || PaymentGateway::CHECKOUT_METHOD_BAO_KIM:
                break;
            case PaymentGateway::REQUEST_COMMAND_MERCHANT_DATA:

        }
        if (in_array($this->getCommand(), [
            PaymentGateway::CHECKOUT_METHOD_CARD_CHARGE, PaymentGateway::CHECKOUT_METHOD_ATM_TRANSFER,
            PaymentGateway::CHECKOUT_METHOD_BANK_TRANSFER, PaymentGateway::CHECKOUT_METHOD_BAO_KIM
        ], true)) {
            $strSign = implode("", $data);
            $signType = Merchant::SIGNATURE_HMAC;
            $signKey = 'data_sign';
        } else {
            $strSign = 'POST' . '&' . urlencode(PaymentGateway::PRO_PAYMENT_URL) . '&&' . urlencode(http_build_query($data));
            $signType = Merchant::SIGNATURE_RSA;
        }

        return $this->getMerchant()->signature($dataSign, $signType);
    }

}
