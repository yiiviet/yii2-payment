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
     * @param bool $validate
     * @return array
     * @throws InvalidConfigException
     */
    public function getData(bool $validate = false): array
    {
        if (!$validate || $this->validate()) {
            return $this->toArray();
        } else {
            $errors = $this->getFirstErrors();
            throw new InvalidConfigException(reset($errors));
        }
    }

}