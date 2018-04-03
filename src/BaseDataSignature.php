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
    /**
     * @var string
     */
    private $_data;

    /**
     * BaseDataSignature constructor.
     * @param string $data
     * @param array $config
     */
    public function __construct(string $data, array $config = [])
    {
        $this->_data = $data;
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->_data;
    }

    /**
     * @return string
     */
    abstract public function generate(): string;

    /**
     * @param string $expect
     * @return bool
     */
    abstract public function validate(string $expect): bool;

}