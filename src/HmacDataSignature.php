<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use Yii;

/**
 * Class HmacDataSignature
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class HmacDataSignature extends BaseDataSignature
{

    const HMAC_ALGO_MD5 = 'md5';

    const HMAC_ALGO_SHA1 = 'sha1';

    const HMAC_ALGO_SHA256 = 'sha256';

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
        $actual = $this->getData();

        if (Yii::$app) {
            return Yii::$app->getSecurity()->compareString($expect, $actual);
        } else {
            return $expect === $actual;
        }
    }

}