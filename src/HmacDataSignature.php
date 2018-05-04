<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment;

use yii\base\InvalidConfigException;

/**
 * Class HmacDataSignature
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class HmacDataSignature extends DataSignature
{

    /**
     * @var string
     */
    public $hmacAlgo;

    /**
     * @var string
     */
    public $key;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->hmacAlgo || !$this->key) {
            throw new InvalidConfigException("'hmacAlgo' and 'key' properties must be set!");
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function generate(): string
    {
        return hash_hmac($this->hmacAlgo, $this->getData(), $this->key);
    }

    /**
     * @inheritdoc
     */
    public function validate(string $expect): bool
    {
        $actual = $this->generate();

        return strcasecmp($expect, $actual) === 0;
    }

}