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
     * @var string
     */
    private $_method;

    public function getMethod(): string
    {
        return $this->_method;
    }

    public function setMethod(string $method): bool
    {
        $this->_method = $method;

        return true;
    }

    /**
     * @var MerchantInterface
     */
    private $_merchant;

    public function getMerchant(): MerchantInterface
    {
        return $this->_merchant;
    }

    public function setMerchant(MerchantInterface $merchant): bool
    {
        $this->_merchant = $merchant;

        return true;
    }


}