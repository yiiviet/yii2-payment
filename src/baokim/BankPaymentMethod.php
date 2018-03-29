<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment\baokim;

use yii2vn\payment\BaseBankPaymentMethod;

/**
 * Class BankPaymentMethod
 *
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class BankPaymentMethod extends BaseBankPaymentMethod
{

    /**
     * @inheritdoc
     */
    public function dataRules(string $method): array
    {
        return [

        ];
    }

    /**
     * @inheritdoc
     */
    protected function getDataInternal(string $method): array
    {
        return [

        ];
    }

}