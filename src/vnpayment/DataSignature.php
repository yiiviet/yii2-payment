<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use yiiviet\payment\DataSignature as BaseDataSignature;

/**
 * Lớp DataSignature hổ trợ tạo và kiểm tra chữ ký dữ liệu khi tương tác với VnPayment.
 *
 * @property string $secureHash
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class DataSignature extends BaseDataSignature
{

    /**
     * @var string
     */
    public $secureHash;

    /**
     * @var string
     */
    public $hashAlgo;

    /**
     * @return string
     */
    public function generate(): string
    {
        return hash($this->hashAlgo, $this->secureHash . $this->getData());
    }

    /**
     * @param string $expect
     * @return bool
     */
    public function validate(string $expect): bool
    {
        $actual = $this->generate();

        return strcasecmp($expect, $actual) === 0;
    }

}
