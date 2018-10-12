<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use yii\base\InvalidConfigException;
use yiiviet\payment\DataSignature as BaseDataSignature;

/**
 * Lớp DataSignature hổ trợ tạo và kiểm tra chữ ký dữ liệu khi tương tác với VnPayment.
 *
 * @property string $secureHash
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 * @deprecated since 1.0.2 we use `yiiviet\payment\HashDataSignature` instead.
 */
class DataSignature extends BaseDataSignature
{

    /**
     * @var string
     */
    public $hashSecret;

    /**
     * @var string
     */
    public $hashAlgo;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function generate(): string
    {
        if ($this->hashSecret === null) {
            throw new InvalidConfigException('Property `hashSecret` must be set!');
        } elseif ($this->hashAlgo === null) {
            throw new InvalidConfigException('Property `hashAlgo` must be set!');
        }

        return hash($this->hashAlgo, $this->hashSecret . $this->getData());
    }

    /**
     * @throws InvalidConfigException
     * @inheritdoc
     */
    public function validate(string $expect): bool
    {
        $actual = $this->generate();

        return strcasecmp($expect, $actual) === 0;
    }

}
