<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use yii\base\InvalidConfigException;


/**
 * Lớp RsaDataSignature dùng cho việc tạo và kiểm tra chữ ký theo chuẩn RSA.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RsaDataSignature extends DataSignature
{

    /**
     * @var string Khóa dùng cho việc xác minh tính hợp lệ của dữ liệu thông qua chữ ký.
     */
    public $publicCertificate;

    /**
     * @var string Khóa dùng cho việc tạo chữ ký dữ liệu.
     */
    public $privateCertificate;

    /**
     * @var int Loại thuật toán openSSL. Ví dụ: OPENSSL_ALGO_MD5, OPENSSL_ALGO_SHA1...
     */
    public $openSSLAlgo;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->openSSLAlgo === null) {
            throw new InvalidConfigException('Property `openSSLAlgo` must be set!');
        } else if ($this->privateCertificate === null) {
            throw new InvalidConfigException('Property `privateCertificate` must be set for generate signature!');
        } else if ($this->publicCertificate === null) {
            throw new InvalidConfigException('Property `publicCertificate` must be set for validate signature!');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function generate(): string
    {
        if (($privateKey = openssl_pkey_get_private($this->privateCertificate)) && openssl_sign($this->getData(), $signature, $privateKey, $this->openSSLAlgo)) {
            openssl_free_key($privateKey);

            return $signature;
        } else {
            throw new InvalidConfigException('Can not signature data via current private certificate!');
        }
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function validate(string $expect): bool
    {
        $isValid = ($publicKey = openssl_pkey_get_public($this->publicCertificate)) && openssl_verify($this->getData(), $expect, $publicKey, $this->openSSLAlgo);
        openssl_free_key($publicKey);

        return $isValid;
    }

}
