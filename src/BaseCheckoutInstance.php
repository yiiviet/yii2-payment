<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * Class BaseCheckoutInstance
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseCheckoutInstance extends BaseObject implements CheckoutInstanceInterface
{
    /**
     * @var null|MerchantInterface
     */
    private $_merchant;

    /**
     * @return object|MerchantInterface
     * @throws InvalidConfigException
     */
    public function getMerchant(): MerchantInterface
    {
        if (!$this->_merchant instanceof MerchantInterface) {
            return $this->_merchant = Instance::ensure($this->_merchant, MerchantInterface::class);
        } else {
            return $this->_merchant;
        }
    }

    /**
     * @param array|string|MerchantInterface $merchant
     * @return bool
     */
    public function setMerchant($merchant): bool
    {
        $this->_merchant = $merchant;

        return true;
    }

}