<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment\nganluong;

use yiivn\payment\Data;

/**
 * Class VerifiedData
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class VerifiedData extends Data
{

    public function rules()
    {
        return [
            [['token'], 'required', 'on' => PaymentGateway::VC_PURCHASE_SUCCESS]
        ];
    }

}