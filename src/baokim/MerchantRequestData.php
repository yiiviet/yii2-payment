<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment\baokim;

use yii2vn\payment\Data;

/**
 * Class MerchantRequestData
 *
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class MerchantRequestData extends Data
{

    public function rules()
    {
        return [
            [['business'], 'required']
        ];
    }

    protected function ensureAttributes(array &$attributes)
    {
        /** @var Merchant $merchant */
        $merchant = $this->getMerchant();

        $attributes['business'] = $attributes['business'] ?? $merchant->email;
    }


    protected function signature(array $data): string
    {
        ksort($data);
        $dataSign = 'GET' . '&' . urlencode(PaymentGateway::PRO_SELLER_INFO_URL) . '&' . urlencode(http_build_query($data)) . '&';

        return $this->getMerchant()->signature($dataSign, Merchant::SIGNATURE_RSA);
    }

}