<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\onepay;

use Yii;

use yii\helpers\ArrayHelper;

use yii2vn\payment\DataSignature;
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


    public $id;

    public $user;

    public $password;

    public $accessCode;

    public $secureSecret;

    public $dataSignatureConfig = ['class' => HmacDataSignature::class];

    /**
     * @param string $data
     * @param string $type
     * @return null|object|HmacDataSignature|DataSignature
     * @throws \yii\base\InvalidConfigException
     */
    public function initDataSignature(string $data, string $type): ?DataSignature
    {
        return Yii::createObject(ArrayHelper::merge($this->dataSignatureConfig, [
            'key' => pack('H*', $this->secureSecret),
            'hmacAlgo' => HmacDataSignature::HMAC_ALGO_SHA256
        ]), [$data]);
    }

}