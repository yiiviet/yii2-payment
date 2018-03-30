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
            [['seri_field', 'pin_field', 'transaction_id', 'card_id'], 'required', 'on' => PaymentGateway::CHECKOUT_METHOD_TEL_CARD],
            [['seri_field', 'pin_field', 'transaction_id', 'card_id'], 'string', 'on' => PaymentGateway::CHECKOUT_METHOD_TEL_CARD]

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
        $data = parent::getData();

        if ($this->method === PaymentGateway::CHECKOUT_METHOD_TEL_CARD) {
            $data['merchant_id'] = $this->merchant->id;
        }

        return $data;
    }

}