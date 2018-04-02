<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\vnpayment;

use Yii;

use yii2vn\payment\CheckoutData;
use yii2vn\payment\MerchantInterface;

/**
 * Class CheckoutRequestData
 *
 * @property Merchant|\yii2vn\payment\MerchantInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutRequestData extends CheckoutData
{

    public function __construct(string $method, MerchantInterface $merchant, array $attributes = [], array $config = [])
    {
        parent::__construct($method, $merchant, $attributes, $config);
    }

    protected function prepareAttributes($attributes)
    {
        $attributesPrepared = [];

        foreach ($attributes as $attribute => $value) {
            if (substr($attribute, 0, 4) !== 'vnp_') {
                $attributesPrepared['vnp_' . $attribute] = $value;
            }
        }

        return $attributesPrepared;
    }

    public function rules()
    {
        return [
            [['vnp_Amount', 'vnp_OrderInfo', 'vnp_OrderType', 'vnp_ReturnUrl', 'vnp_TxnRef'], 'required'],
            [['vnp_BankCode'], 'required', 'on' => [PaymentGateway::CHECKOUT_METHOD_LOCAL_BANK]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getData(bool $validate = true): array
    {
        $data = parent::getData($validate);

        /** @var Merchant $merchant */
        $merchant = $this->getMerchant();
        $data['vnp_Version'] = $merchant->getPaymentGateway()::version();
        $data['vnp_Command'] = 'pay';
        $data['vnp_TmnCode'] = $merchant->tmnCode;
        $data['vnp_Locale'] = $data['vnp_Locale'] ?? 'vn';
        $data['vnp_CurrCode'] = 'VND';
        $data['vnp_IpAddr'] = $data['vnp_IpAddr'] ?? Yii::$app->getRequest()->getUserIP();
        $data['vnp_CreateDate'] = $data['vnp_CreateDate'] ?? date('Ymdhis');

        switch ($method = $this->method) {
            case PaymentGateway::CHECKOUT_METHOD_DYNAMIC:
                unset($data['vnp_BankCode']);
                break;
            case PaymentGateway::CHECKOUT_METHOD_INTER_BANK || PaymentGateway::CHECKOUT_METHOD_VNMART:
                $data['vnp_BankCode'] = $method;
                break;
        }

        return $data;
    }
}