<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

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
     * @var MerchantInterface
     */
    private $_merchant;
    /**
     * @var string
     */
    private $_method;

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
        $this->_merchant = $merchant;

        parent::__construct($attributes, $config);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->_method;
    }

    /**
     * @return MerchantInterface
     */
    public function getMerchant(): MerchantInterface
    {
        return $this->_merchant;
    }


}