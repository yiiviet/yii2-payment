<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment\onepay;

use Yii;

use yii\helpers\ArrayHelper;

use yiivn\payment\BaseMerchant;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    public $id;

    public $accessCode;

    public $secureSecret;

    public $dataSignatureConfig = [];

    /**
     * @param string $data
     * @param string $type
     * @return null|object|\yiivn\payment\HmacDataSignature
     * @throws \yii\base\InvalidConfigException
     */
    public function initDataSignature(string $data, string $type): ?\yiivn\payment\DataSignature
    {
        $config = ArrayHelper::merge($this->dataSignatureConfig, [
            'key' => pack('H*', $this->secureSecret),
            'hmacAlgo' => 'sha256'
        ]);

        $config['class'] = $config['class'] ?? 'yiivn\payment\HmacDataSignature';

        return Yii::createObject($config, [$data]);
    }

}