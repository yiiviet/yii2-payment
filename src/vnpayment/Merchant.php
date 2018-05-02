<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\vnpayment;

use Yii;

use yii2vn\payment\BaseMerchant;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    public $dataSignatureConfig = [];

    public $secureHash;

    /**
     * @var string
     */
    public $tmnCode;

    /**
     * @var int
     */
    public $defaultOrderType;

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDataSignature(string $data, string $type): ?\yii2vn\payment\DataSignature
    {
        return Yii::createObject(array_merge([
            'class' => DataSignature::class,
            'hashAlgo' => $type,
            'secureHash' => $this->secureHash
        ], $this->dataSignatureConfig), [$data]);
    }
}