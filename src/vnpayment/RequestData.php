<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use Yii;

use yii\helpers\ArrayHelper;

use vxm\gatewayclients\RequestData as BaseRequestData;

/**
 * Lớp RequestData cung cấp dữ liệu để truy vấn đến VnPayment tạo lệnh thanh toán,
 * kiểm tra giao dịch, hoàn trả hóa đơn....
 *
 * @method PaymentClient getClient()
 *
 * @property PaymentClient $client
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

        $client = $this->getClient();
        $command = $this->getCommand();
        $hashType = ArrayHelper::remove($attributesEnsured, 'vnp_SecureHashType', 'MD5');

        if (Yii::$app instanceof \yii\web\Application) {
            $attributesEnsured['vnp_IpAddr'] = $attributesEnsured['vnp_IpAddr'] ?? Yii::$app->getRequest()->getUserIP();
        }

        $attributesEnsured['vnp_CreateDate'] = $attributesEnsured['vnp_CreateDate'] ?? date('Ymdhis');
        $attributesEnsured['vnp_Version'] = $client->getGateway()->getVersion();
        $attributesEnsured['vnp_TmnCode'] = $client->tmnCode;

        if ($command === PaymentGateway::RC_PURCHASE) {
            $attributesEnsured['vnp_OrderType'] = $attributesEnsured['vnp_OrderType'] ?? $client->defaultOrderType;
            $attributesEnsured['vnp_Locale'] = $attributesEnsured['vnp_Locale'] ?? 'vn';
            $attributesEnsured['vnp_Command'] = 'pay';
            $attributesEnsured['vnp_CurrCode'] = 'VND';
        } else {
            $attributesEnsured['vnp_Command'] = $command === PaymentGateway::RC_REFUND ? 'refund' : 'querydr';
        }

        ksort($attributesEnsured);

        $dataSign = $client->hashSecret . urldecode(http_build_query($attributesEnsured));
        $attributesEnsured['vnp_SecureHash'] = $client->signature($dataSign, $hashType);
        $attributesEnsured['vnp_SecureHashType'] = $hashType;

        $attributes = $attributesEnsured;
    }

}
