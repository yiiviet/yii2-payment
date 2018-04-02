<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\onepay;

use Yii;

use yii\helpers\Url;

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

    const AVS_ATTRIBUTE_PREFIX = 'AVS_';

    const VPC_ATTRIBUTE_PREFIX = 'vpc_';

    const AVS_ATTRIBUTES = [
        'Street01', 'City', 'StateProv', 'PostCode', 'Country'
    ];

    const VPC_ATTRIBUTES = [
        'Amount', 'Locale', 'TicketNo', 'MerchTxnRef', 'ReturnURL', 'Currency', 'OrderInfo',
        'SHIP_Street01', 'SHIP_Provice', 'SHIP_City', 'SHIP_Country', 'Customer_Phone', 'Customer_Email',
        'Customer_Id',
    ];

    /**
     * @inheritdoc
     */
    public function __construct(string $method, MerchantInterface $merchant, array $attributes = [], array $config = [])
    {
        parent::__construct($method, $merchant, $this->prepareAttributes($attributes), $config);
    }

    /**
     * @param array $attributes
     * @return array
     */
    protected function prepareAttributes(array $attributes): array
    {
        $attributesPrepared = [];

        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, self::AVS_ATTRIBUTES, true)) {
                $attributesPrepared[self::AVS_ATTRIBUTE_PREFIX . $attribute] = $value;
            } elseif (in_array($attribute, self::VPC_ATTRIBUTES, true)) {
                $attributesPrepared[self::VPC_ATTRIBUTE_PREFIX . $attribute] = $value;
            } else {
                $attributesPrepared[$attribute] = $value;
            }
        }

        return $attributesPrepared;
    }

    public function rules()
    {
        return [
            [['vpc_Amount', 'vpc_ReturnUrl', 'vpc_MerchTxnRef', 'vpc_OrderInfo'], 'required'],
            [['vpc_Currency'], 'required', 'on' => [PaymentGateway::CHECKOUT_METHOD_LOCAL_ATM]]
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

        // Absolutely data value
        $data['vpc_Merchant'] = $merchant->id;
        $data['vpc_AccessCode'] = $merchant->accessCode;
        $data['vpc_Command'] = 'pay';
        $data['vpc_Version'] = $merchant->getPaymentGateway()->version();

        // Ensure data value
        $data['vpc_Locale'] = $data['vpc_Locale'] ?? 'vn';
        $data['vpc_TicketNo'] = $data['vpc_TicketNo'] ?? Yii::$app->getRequest()->getUserIP();
        $data['AgainLink'] = $data['AgainLink'] ?? Url::current();
        $data['Title'] = $data['Title'] ?? (string)Yii::$app->getView()->title;

        return $data;
    }
}