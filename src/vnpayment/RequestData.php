<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use Yii;

use yii\helpers\ArrayHelper;

use yiiviet\payment\RequestData as BaseRequestData;

/**
 * Class RequestData
 *
 * @property Merchant|\yiiviet\payment\PaymentClientInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestData extends BaseRequestData
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vnp_Version', 'vnp_Command', 'vnp_TmnCode', 'vnp_TxnRef', 'vnp_OrderInfo', 'vnp_IpAddr', 'vnp_CreateDate', 'vnp_SecureHash'], 'required', 'on' => [
                PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_REFUND, PaymentGateway::RC_PURCHASE
            ]],
            [['vnp_Amount'], 'required', 'on' => [PaymentGateway::RC_REFUND, PaymentGateway::RC_PURCHASE]],
            [['vnp_TransDate'], 'required', 'on' => [PaymentGateway::RC_REFUND, PaymentGateway::RC_QUERY_DR]],
            [['vnp_Locale', 'vnp_CurrCode', 'vnp_OrderType', 'vnp_ReturnUrl'], 'required', 'on' => PaymentGateway::RC_PURCHASE],
            [['vnp_TransactionNo'], 'required', 'on' => PaymentGateway::RC_QUERY_DR]
        ];
    }

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    protected function ensureAttributes(array &$attributes)
    {
        parent::ensureAttributes($attributes);

        $attributesEnsured = [];

        foreach ($attributes as $attribute => $value) {
            if (substr($attribute, 0, 4) !== 'vnp_') {
                $attributesEnsured['vnp_' . $attribute] = $value;
            } else {
                $attributesEnsured[$attribute] = $value;
            }
        }

        /** @var Merchant $merchant */
        $merchant = $this->getMerchant();
        $command = $this->getCommand();
        $attributesEnsured['vnp_IpAddr'] = $attributesEnsured['vnp_IpAddr'] ?? Yii::$app->getRequest()->getUserIP();
        $attributesEnsured['vnp_CreateDate'] = $attributesEnsured['vnp_CreateDate'] ?? date('Ymdhis');
        $attributesEnsured['vnp_Version'] = $merchant->getPaymentGateway()->version();
        $attributesEnsured['vnp_TmnCode'] = $merchant->tmnCode;

        if ($command === PaymentGateway::RC_PURCHASE) {
            $attributesEnsured['vnp_OrderType'] = $attributesEnsured['vnp_OrderType'] ?? $merchant->defaultOrderType;
            $attributesEnsured['vnp_Locale'] = $attributesEnsured['vnp_Locale'] ?? 'vn';
            $attributesEnsured['vnp_Command'] = 'pay';
            $attributesEnsured['vnp_CurrCode'] = 'VND';
        } else {
            $attributesEnsured['vnp_Command'] = $command === PaymentGateway::RC_REFUND ? 'refund' : 'querydr';
        }

        ksort($attributesEnsured);
        $hashType = ArrayHelper::remove($attributesEnsured, 'vnp_SecureHashType', 'MD5');
        $attributesEnsured['vnp_SecureHash'] = $merchant->signature(http_build_query($attributesEnsured), $hashType);
        $attributesEnsured['vnp_SecureHashType'] = $hashType;

        $attributes = $attributesEnsured;
    }

}
