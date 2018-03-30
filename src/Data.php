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
 * Class Data
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Data extends DynamicModel
{
    /**
     * @var string
     */
    public $method;

    /**
     * @var BaseMerchant|MerchantInterface
     */
    public $merchant;

    /**
     * @param bool $validate
     * @return array
     * @throws InvalidConfigException
     */
    public function getData(bool $validate = false): array
    {
        if (!$validate || $this->validate()) {
            $data = [];

            foreach ($this->attributes() as $attribute) {
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