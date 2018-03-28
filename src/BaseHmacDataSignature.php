<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use Yii;

/**
 * Class BaseHmacDataSignature
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseHmacDataSignature extends BaseDataSignature
{
    const ALGO_MD5 = 'md5';

    const ALGO_SHA1 = 'sha1';

    const ALGO_SHA256 = 'sha256';

    /**
     * @var string
     */
    public $key;

    /**
     * @return string
     */
    abstract public static function hmacAlgo(): string;

    /**
     * @inheritdoc
     */
    public function generate(): string
    {
        return hash_hmac(static::hmacAlgo(), $this->getDataString(), $this->key);
    }

    /**
     * @inheritdoc
     */
    public function validate(string $expect): bool
    {
        if (Yii::$app) {
            return Yii::$app->getSecurity()->compareString($expect, $this->generate());
        } else {
            return $expect === $this->generate();
        }
    }

}