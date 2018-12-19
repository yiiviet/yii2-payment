<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment;

use yii\base\BootstrapInterface;
use yii\validators\Validator;

/**
 * Lớp Bootstrap hổ trợ cấu hình bank validator
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritdoc
     */
    public function bootstrap($app)
    {
        Validator::$builtInValidators = array_merge(Validator::$builtInValidators, [
            'bankvn' => BankValidator::class
        ]);
    }
}
