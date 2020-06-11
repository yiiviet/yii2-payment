<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\onepay;

use Yii;

use yii\helpers\Url;

use vxm\gatewayclients\RequestData as BaseRequestData;

/**
 * Lớp RequestData cung cấp dữ liệu để giao tiếp với OnePay như truy vấn giao dịch, yêu cầu thanh toán.
 *
 * @method PaymentClient getClient() đối tượng client đã dùng để thực thi request.
 *
 * @property PaymentClient $client đối tượng client đã dùng để thực thi request.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0
 */
class RequestData extends BaseRequestData
{
    /**
     * Mảng hằng tập hợp các attributes cần thêm prefix `avs_`
     */
    const AVS_ATTRIBUTES = ['Street01', 'City', 'StateProv', 'PostCode', 'Country'];

    /**
     * Mảng hằng tập hợp các attributes cần thêm prefix `vpc_`
     */
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
        $rules = [
            [['vpc_Version', 'vpc_Command', 'vpc_AccessCode', 'vpc_Merchant', 'vpc_MerchTxnRef'], 'required', 'on' => [
                PaymentGateway::RC_PURCHASE, PaymentGateway::RC_QUERY_DR
            ]],
            [[
                'vpc_Locale', 'vpc_ReturnURL', 'vpc_OrderInfo', 'vpc_Amount',
                'vpc_TicketNo', 'AgainLink', 'Title', 'vpc_SecureHash'
            ], 'required', 'on' => PaymentGateway::RC_PURCHASE]
        ];

        if (!$this->getClient()->getGateway()->international) {
            return array_merge($rules, [
                [['vpc_Currency'], 'required', 'on' => [PaymentGateway::RC_PURCHASE]]
            ]);
        } else {
            return $rules;
        }
    }

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    protected function ensureAttributes(array &$attributes)
    {
        $client = $this->getClient();
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

        $attributesEnsured['vpc_Merchant'] = $client->merchantId;
        $attributesEnsured['vpc_AccessCode'] = $client->accessCode;
        $attributesEnsured['vpc_Version'] = $client->getGateway()->getVersion();

        if (!$client->getGateway()->international && $command === PaymentGateway::RC_PURCHASE) {
            $attributesEnsured['vpc_Currency'] = $attributesEnsured['vpc_Currency'] ?? 'VND';
        }

        if ($command === PaymentGateway::RC_QUERY_DR) {
            $attributesEnsured['vpc_Command'] = 'queryDR';
            $attributesEnsured['vpc_User'] = 'op01';
            $attributesEnsured['vpc_Password'] = 'op123456';
        } else {
            $attributesEnsured['vpc_Command'] = 'pay';
            $attributesEnsured['vpc_Locale'] = $attributesEnsured['vpc_Locale'] ?? 'vn';
            $attributesEnsured['vpc_SecureHash'] = $this->signature($attributesEnsured);

            if (Yii::$app instanceof \yii\web\Application) {
                $attributesEnsured['vpc_TicketNo'] = $attributesEnsured['vpc_TicketNo'] ?? Yii::$app->getRequest()->getUserIP();
                $attributesEnsured['AgainLink'] = $attributesEnsured['AgainLink'] ?? Url::current();
                $attributesEnsured['Title'] = $attributesEnsured['Title'] ?? (string)Yii::$app->getView()->title;
            }
        }

        $attributes = $attributesEnsured;
        parent::ensureAttributes($attributes);
    }

    /**
     * Phương thức tạo chữ ký dữ liệu
     *
     * @param array $data Dữ liệu cần tạo chữ ký
     * @return string Trả về chuỗi chữ ký của dữ liệu
     * @throws \yii\base\NotSupportedException
     */
    private function signature(array $data): string
    {
        unset($data['vpc_SecureHash']);
        ksort($data);
        $dataSign = [];

        foreach ($data as $attribute => $value) {
            if (strpos($attribute, 'vpc_') === 0) {
                $dataSign[$attribute] = $value;
            }
        }

        /** @var PaymentClient $client */
        $client = $this->getClient();
        $dataSign = urldecode(http_build_query($dataSign));
        return strtoupper($client->signature($dataSign));
    }

}
