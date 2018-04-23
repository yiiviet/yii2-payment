<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\DynamicModel;
use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;

/**
 * Class Data
 *
 * @property BaseMerchant $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Data extends DynamicModel implements DataInterface
{

    /**
     * Data constructor.
     * @param string $command
     * @param BaseMerchant $merchant
     * @param array $attributes
     * @param array $config
     */
    public function __construct(string $command, BaseMerchant $merchant, array $attributes = [], array $config = [])
    {
        $this->_command = $command;
        $this->_merchant = $merchant;

        $this->setScenario($command);

        parent::__construct($this->ensureAttributes($attributes), $config);
    }

    /**
     * @param array $attributes
     */
    protected function ensureAttributes(array &$attributes)
    {

    }

    /**
     * @var string
     */
    private $_command;

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->_command;
    }

    /**
     * @var BaseMerchant
     */
    private $_merchant;

    /**
     * @return BaseMerchant
     */
    public function getMerchant(): BaseMerchant
    {
        return $this->_merchant;
    }

    /**
     * @param bool $validate
     * @return array
     * @throws InvalidConfigException|NotSupportedException
     */
    public function get(bool $validate = true): array
    {
        if (!$validate || $this->validate()) {
            return $this->toArray();
        } else {
            throw new InvalidConfigException(current($this->getFirstErrors()));
        }
    }


}