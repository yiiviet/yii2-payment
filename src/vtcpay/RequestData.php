<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\vtcpay;

use vxm\gatewayclients\RequestData as BaseRequestData;

/**
 * Lớp RequestData
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class RequestData extends BaseRequestData
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['website_id', 'signature', 'reference_number', 'receiver_account', 'currency', 'amount'], 'required', 'on' => PaymentGateway::RC_PURCHASE]
        ];
    }

    protected function ensureAttributes(array &$attributes)
    {
        parent::ensureAttributes($attributes);
        $client = $this->getClient();
        $attributes['website_id'] = $this
    }

}
