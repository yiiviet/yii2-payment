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

    public $signature = false;

    public $signatureKey = 'signature';

    /**
     * QueryDRRequestData constructor.
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
     * @var BaseMerchant|MerchantInterface
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
     * @return array
     * @throws InvalidConfigException
     */
    public function getData(bool $validate = false): array
    {
        if (!$validate || $this->validate()) {
            $data = $this->toArray();

            if ($this->signature) {
                $data[$this->signatureKey] = $this->signature($data);
            }

            return $data;
        } else {
            throw new InvalidConfigException(current($this->getFirstErrors()));
        }
    }

    /**
     * @param $data
     * @return string
     * @throws InvalidConfigException
     */
    protected function signature(array $data): string
    {
        throw new InvalidConfigException(__METHOD__ . ' must be override when property `signature` value is true!');
    }

}