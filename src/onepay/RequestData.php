<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

use Yii;

use vxm\gatewayclients\RequestData as BaseRequestData;
use yii\helpers\Url;

/**
 * Lớp RequestData cung cấp dữ liệu để giao tiếp với OnePay như truy vấn giao dịch, yêu cầu thanh toán.
 *
 * @property PaymentClient $client
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
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
        return [
            [['vpc_Version', 'vpc_Command', 'vpc_AccessCode', 'vpc_Merchant', 'vpc_MerchTxnRef'], 'required', 'on' => [
                PaymentGateway::RC_PURCHASE, PaymentGateway::RC_PURCHASE_INTERNATIONAL, PaymentGateway::RC_QUERY_DR, PaymentGateway::RC_QUERY_DR_INTERNATIONAL
            ]],
            [[
                'vpc_Locale', 'vpc_ReturnURL', 'vpc_OrderInfo', 'vpc_Amount',
                'vpc_TicketNo', 'AgainLink', 'Title', 'vpc_SecureHash'
            ], 'required', 'on' => [PaymentGateway::RC_PURCHASE_INTERNATIONAL, PaymentGateway::RC_PURCHASE]],
            [['vpc_Currency'], 'required', 'on' => [PaymentGateway::RC_PURCHASE]]
        ];
    }

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    protected function ensureAttributes(array &$attributes)
    {
        parent::ensureAttributes($attributes);
        /** @var PaymentClient $client */
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
        $attributesEnsured['vpc_Command'] = $command === PaymentGateway::RC_QUERY_DR ? 'queryDR' : 'pay';
        $attributesEnsured['vpc_Version'] = $client->getGateway()->getVersion();

        if ($command === PaymentGateway::RC_QUERY_DR || $command === PaymentGateway::RC_QUERY_DR_INTERNATIONAL) {
            $attributesEnsured['vpc_Command'] = 'queryDR';
            $attributesEnsured['vpc_User'] = 'op01';
            $attributesEnsured['vpc_Password'] = 'op123456';
        } else {
            $attributesEnsured['vpc_Command'] = 'pay';
            $attributesEnsured['vpc_Locale'] = $data['vpc_Locale'] ?? 'vn';
            $attributesEnsured['vpc_TicketNo'] = $data['vpc_TicketNo'] ?? Yii::$app->getRequest()->getUserIP();
            $attributesEnsured['AgainLink'] = $data['AgainLink'] ?? Url::current();
            $attributesEnsured['Title'] = $data['Title'] ?? (string)Yii::$app->getView()->title;
            $attributesEnsured['vpc_SecureHash'] = $this->signature($attributesEnsured);
        }

        $attributes = $attributesEnsured;
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
        ksort($data);
        $dataSign = [];

        foreach ($data as $attribute => $value) {
            if (strpos($attribute, 'vpc_') === 0) {
                $dataSign[$attribute] = $value;
            }
        }

        /** @var PaymentClient $client */
        $client = $this->getClient();

        return strtoupper($client->signature(http_build_query($dataSign)));
    }

}
