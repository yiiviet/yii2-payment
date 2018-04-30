<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\onepay;

use Yii;

use yii2vn\payment\RequestData as BaseRequestData;
use yii\helpers\Url;

/**
 * Class RequestData
 *
 * @property Merchant $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestData extends BaseRequestData
{

    const AVS_ATTRIBUTES = ['Street01', 'City', 'StateProv', 'PostCode', 'Country'];

    const VPC_ATTRIBUTES = [
        'Amount', 'Locale', 'TicketNo', 'MerchTxnRef', 'ReturnURL', 'Currency', 'OrderInfo',
        'SHIP_Street01', 'SHIP_Provice', 'SHIP_City', 'SHIP_Country', 'Customer_Phone', 'Customer_Email',
        'Customer_Id',
    ];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vpc_Version', 'vpc_Command', 'vpc_AccessCode', 'vpc_Merchant', 'vpc_MerchTxnRef'], 'required', 'on' => [
                PaymentGateway::RC_PURCHASE, PaymentGateway::RC_PURCHASE_INTERNATIONAL, PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_DR_INTERNATIONAL
            ]],
            [[
                'vpc_Locale', 'vpc_ReturnURL', 'vpc_OrderInfo', 'vpc_Amount',
                'vpc_TicketNo', 'AgainLink', 'Title', 'vpc_SecureHash'
            ], 'vpc_User', 'on' => [PaymentGateway::RC_PURCHASE_INTERNATIONAL, PaymentGateway::RC_PURCHASE]],
            [['vpc_Currency'], 'required', 'on' => [PaymentGateway::RC_PURCHASE]],
            [['vpc_User', 'vpc_Password'], 'required', 'on' => [PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_DR_INTERNATIONAL]],
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
        $attributesEnsured = [];

        foreach ($attributes as $attribute => $value) {
            if (in_array($attribute, self::AVS_ATTRIBUTES, true)) {
                $attributesEnsured['AVS_' . $attribute] = $value;
            } elseif (in_array($attribute, self::VPC_ATTRIBUTES, true)) {
                $attributesEnsured['vpc_' . $attribute] = $value;
            } else {
                $attributesEnsured[$attribute] = $value;
            }
        }

        $attributesEnsured['vpc_Merchant'] = $merchant->id;
        $attributesEnsured['vpc_AccessCode'] = $merchant->accessCode;
        $attributesEnsured['vpc_Command'] = $command === PaymentGateway::RC_QUERY_DR ? 'queryDR' : 'pay';
        $attributesEnsured['vpc_Version'] = $merchant->getPaymentGateway()->version();

        if ($command === PaymentGateway::RC_QUERY_DR) {
            $attributesEnsured['vpc_User'] = $merchant->user;
            $attributesEnsured['vpc_Password'] = $merchant->password;
        } else {
            $attributesEnsured['vpc_Locale'] = $data['vpc_Locale'] ?? 'vn';
            $attributesEnsured['vpc_TicketNo'] = $data['vpc_TicketNo'] ?? Yii::$app->getRequest()->getUserIP();
            $attributesEnsured['AgainLink'] = $data['AgainLink'] ?? Url::current();
            $attributesEnsured['Title'] = $data['Title'] ?? (string)Yii::$app->getView()->title;
            $attributesEnsured['vpc_SecureHash'] = $this->signature($attributesEnsured);
        }

        $attributes = $attributesEnsured;
    }

    /**
     * @param array $attributes
     * @return string
     * @throws \yii\base\NotSupportedException
     */
    private function signature(array $attributes): string
    {
        ksort($attributes);
        $attributesSign = [];

        foreach ($attributes as $attribute => $value) {
            if (strpos($attribute, 'vpc_') === 0) {
                $attributesSign[$attribute] = $value;
            }
        }

        return strtoupper($this->getMerchant()->signature(http_build_query($attributesSign)));
    }

}