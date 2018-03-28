<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii2vn\payment\BaseHmacDataSignature;

/**
 * Class HmacDataSignature
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class HmacDataSignature extends BaseHmacDataSignature
{

    public static function hmacAlgo(): string
    {
        return self::ALGO_SHA1;
    }

    protected function getDataString(): string
    {
        $data = $this->getData();
        ksort($data);

        return implode('', $data);
    }

}