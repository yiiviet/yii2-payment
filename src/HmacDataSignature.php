<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use yii\base\InvalidConfigException;

/**
 * Lớp HmacDataSignature dùng cho việc tạo và kiểm tra chữ ký theo chuẩn HMAC.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class HmacDataSignature extends DataSignature
{

    /**
     * Tên loại mã hóa. Ví dụ: md5, sha1, sha256...
     *
     * @var string
     */
    public $hmacAlgo;

    /**
     * Khóa mã hóa. Độ phức tạp càng cao thì dữ liệu càng được an toàn.
     *
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
        $actual = $this->generate();

        return strcasecmp($expect, $actual) === 0;
    }

}
