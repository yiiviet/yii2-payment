<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment;

use yii\base\InvalidConfigException;

/**
 * Lớp HashDataSignature hổ trợ tạo chữ ký dữ liệu thông qua các kiểu mã hóa hash 1 chiều như md5, sha1, sha256...
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class HashDataSignature extends DataSignature
{

    public $hashAlgo;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->hashAlgo === null) {
            throw new InvalidConfigException('Property `hashAlgo` must be set!');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function generate(): string
    {
        return hash($this->hashAlgo, $this->getData());
    }

    /**
     * @inheritdoc
     */
    public function validate(string $expect): bool
    {
        return strcmp($expect, $this->generate()) === 0;
    }

}
