<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;

/**
 * Class BaseDataSignature
 *
 * @property string $data
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseDataSignature extends BaseObject
{

    private $_data;

    /**
     * @inheritdoc
     */
    public function getData(): string
    {
        return $this->_data;
    }

    public function setData(string $data): bool
    {
        $this->_data = $data;
    }

    /**
     * @inheritdoc
     */
    abstract public function generate(): string;

    /**
     * @inheritdoc
     */
    abstract public function validate(string $expect): bool;

}