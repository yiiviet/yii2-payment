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
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutRequestData extends Data
{
    /**
     * @var Merchant
     */
    public $merchant;

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['seri_field', 'pin_field', 'transaction_id', 'card_id'], 'required', 'on' => [PaymentGateway::CHECKOUT_METHOD_TEL_CARD]],
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
    public function getScenario(): string
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function getData(bool $validate = true): array
    {
        $data = parent::getData($validate);

        switch ($this->method) {
            case PaymentGateway::CHECKOUT_METHOD_TEL_CARD:
                $data['merchant_id'] = $this->merchant->id;
                break;
            case PaymentGateway::CHECKOUT_METHOD_BAO_KIM || PaymentGateway::CHECKOUT_METHOD_BANK_TRANSFER || PaymentGateway::CHECKOUT_METHOD_ATM_TRANSFER ||
                PaymentGateway::CHECKOUT_METHOD_LOCAL_BANK || PaymentGateway::CHECKOUT_METHOD_CREDIT_CARD || PaymentGateway::CHECKOUT_METHOD_INTERNET_BANKING:
                $data['business'] = $this->merchant->businessEmail;
                break;
        }

        return $data;
    }

}