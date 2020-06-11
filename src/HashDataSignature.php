<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace nhuluc\payment;

use yii\base\InvalidConfigException;

/**
 * Lớp HashDataSignature hổ trợ tạo chữ ký dữ liệu thông qua các kiểu mã hóa hash 1 chiều như md5, sha1, sha256...
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.2
 */
class HashDataSignature extends DataSignature
{

    /**
     * @var string tên loại mã hóa. Ví dụ: md5, sha1, sha256...
     */
    public $hashAlgo;

    /**
     * @var bool phân biệt ký tự hoa thường khi xác minh chữ ký.
     * @since 1.0.3
     */
    public $caseSensitive = true;

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
        $actual = $this->generate();

        if ($this->caseSensitive) {
            return strcmp($expect, $actual) === 0;
        } else {
            return strcasecmp($expect, $actual) === 0;
        }
    }

}
