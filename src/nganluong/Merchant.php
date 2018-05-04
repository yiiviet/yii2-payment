<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment\nganluong;

use yii\base\NotSupportedException;

use yiivn\payment\DataSignature;
use yiivn\payment\BaseMerchant;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    public $id;

    public $email;

    public $password;

    /**
     * @inheritdoc
     */
    protected function initDataSignature(string $data, string $type): ?\yiivn\payment\DataSignature
    {
        return null;
    }

}