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
 * @property MerchantInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Data extends DynamicModel
{

    /**
     * Data constructor.
     * @param MerchantInterface $merchant
     * @param array $attributes
     * @param array $config
     */
    public function __construct(MerchantInterface $merchant, array $attributes = [], array $config = [])
    {
        $this->_merchant = $merchant;

        parent::__construct($this->ensureAttributes($attributes), $config);
    }

    /**
     * @param array $attributes
     */
    protected function ensureAttributes(array &$attributes)
    {

    }

    /**
     * @var MerchantInterface
     */
    private $_merchant;

    /**
     * @return MerchantInterface
     */
    public function getMerchant(): MerchantInterface
    {
        return $this->_merchant;
    }

    /**
     * @param bool $validate
     * @param string $signatureKey
     * @return array
     * @throws InvalidConfigException|NotSupportedException
     */
    public function getData(bool $validate = true, string $signatureKey = null): array
    {
        if (!$validate || $this->validate()) {
            $data = $this->toArray();

            if ($signatureKey) {
                $data[$signatureKey] = $this->signature($data);
            }

            return $data;
        } else {
            throw new InvalidConfigException(current($this->getFirstErrors()));
        }
    }

    /**
     * @param $data
     * @return string
     * @throws NotSupportedException
     */
    protected function signature(array $data): string
    {
        throw new NotSupportedException(__CLASS__ . ' do not support data signature by default');
    }

}