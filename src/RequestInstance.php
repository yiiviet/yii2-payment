<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\DynamicModel;
use yii\base\InvalidConfigException;

/**
 * Class CheckoutRequestInstance
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RequestInstance extends DynamicModel
{

    /**
     * @var BaseMerchant|MerchantInterface
     */
    public $merchant;

    /**
     * @var string
     */
    public $method;

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getData(): array
    {
        if ($this->validate()) {
            $data = [];

            foreach ($this->activeAttributes() as $attribute) {
                $value = $this->$attribute;

                if ($value !== null) {
                    $data[$attribute] = $value;
                }
            }

            return $data;
        } else {
            $errors = $this->getFirstErrors();
            throw new InvalidConfigException(reset($errors));
        }
    }


}