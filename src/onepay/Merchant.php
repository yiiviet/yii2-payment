<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\onepay;

use yii2vn\payment\BaseDataSignature;
use yii2vn\payment\BaseMerchant;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    public function createDataSignature(string $data, string $type): ?BaseDataSignature
    {
        // TODO: Implement createDataSignature() method.
    }

}