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
abstract class BaseDataSignature extends BaseObject implements DataSignatureInterface
{

    /**
     * @var array
     */
    private $_data = [];

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->_data;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function setData(array $data): bool
    {
        $this->_data = $data;

        return true;
    }

    abstract protected function getDataString(): string;

}