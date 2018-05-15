<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

/**
 * Trait MagicPropertiesTrait là trait bổ sung phương thức getter và setter nhầm giảm hóa sự lập đi lập lại của prefix `vpc_`
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
trait MagicPropertiesTrait
{
    /**
     * @inheritdoc
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (\yii\base\UnknownPropertyException $e) {
            if (isset($this["vpc_$name"])) {
                return $this["vpc_$name"];
            } else {
                throw $e;
            }
        }
    }

    /**
     * @inheritdoc
     * @throws \yii\base\UnknownPropertyException
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (\yii\base\UnknownPropertyException $e) {
            if (isset($this["vpc_$name"])) {
                $this["vpc_$name"] = $value;
            } else {
                throw $e;
            }
        }
    }

}
