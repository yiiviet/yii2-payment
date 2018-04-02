<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\onepay;

use Yii;

use yii2vn\payment\BaseDataSignature;
use yii2vn\payment\HmacDataSignature;
use yii2vn\payment\BaseMerchant;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    const SIGNATURE_HMAC = 'HMAC';

    public $id;

    public $accessCode;

    public $secureSecret;

    public $hmacDataSignatureConfig = ['class' => HmacDataSignature::class, 'hmacAlgo' => HmacDataSignature::HMAC_ALGO_SHA256];

    /**
     * @param string $data
     * @param string $type
     * @return null|object|HmacDataSignature|BaseDataSignature
     * @throws \yii\base\InvalidConfigException
     */
    public function createDataSignature(string $data, string $type): ?BaseDataSignature
    {
        if ($type === null || $type === self::SIGNATURE_HMAC) {
            return Yii::createObject($this->hmacDataSignatureConfig, [
                'key' => pack('H*', $this->secureSecret),
                'data' => $data,
                'hmacAlgo' => HmacDataSignature::HMAC_ALGO_SHA256
            ]);
        } else {
            return null;
        }
    }

}