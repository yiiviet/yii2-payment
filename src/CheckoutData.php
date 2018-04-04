<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\InvalidConfigException;

/**
 * Class CheckoutData
 *
 * @property string $method
 * @property MerchantInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutData extends Data
{

    /**
     * CheckoutData constructor.
     * @param string $method
     * @param MerchantInterface $merchant
     * @param array $attributes
     * @param array $config
     */
    public function __construct(string $method, MerchantInterface $merchant, array $attributes = [], array $config = [])
    {
        $this->_method = $method;

        parent::__construct($merchant, $attributes, $config);
    }

    /**
     * @inheritdoc
     */
    public function getScenario()
    {
        return $this->getMethod();
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function setScenario($value)
    {
        throw new InvalidConfigException('Scenario must be checkout method value! It can not be set!');
    }

    /**
     * @var string
     */
    private $_method;

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->_method;
    }


}