<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use yii\base\InvalidConfigException;


/**
 * Lớp RsaDataSignature dùng cho việc tạo và kiểm tra chữ ký theo chuẩn RSA.
 *
 * @property string $publicCertificate Khóa công khai dùng cho việc xác minh tính hợp lệ của dữ liệu thông qua chữ ký.
 * @property string $privateCertificate Khóa bí mật dùng cho việc tạo chữ ký dữ liệu.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RsaDataSignature extends DataSignature
{

    /**
     * Loại thuật toán openSSL. Ví dụ: OPENSSL_ALGO_MD5, OPENSSL_ALGO_SHA1...
     * @var int
     */
    public $openSSLAlgo;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->openSSLAlgo) {
            throw new InvalidConfigException("'openSSLAlgo' property must be set!");
        }

        parent::init();
    }

    /**
     * Khóa dùng cho việc xác minh tính hợp lệ của dữ liệu thông qua chữ ký.
     *
     * @see getPublicCertificate|setPublicCertificate
     * @var null|string
     */
    private $_publicCertificate;

    /**
     * Phương thức lấy khóa dùng cho việc kiểm tra tính hợp lệ của dữ liệu từ chữ ký.
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function getPublicCertificate(): string
    {
        if ($this->_publicCertificate === null) {
            throw new InvalidConfigException(__METHOD__ . ' public certificate must be set!');
        } else {
            return $this->_publicCertificate;
        }
    }

    /**
     * Phương thức hổ trợ thiết lập khóa công khai
     *
     * @var string $certificate Khóa công khai cần được thiết lập
     * @return bool Trả về TRUE nếu thiết lập thành công và ngược lại.
     */
    public function setPublicCertificate(string $certificate): bool
    {
        $this->_publicCertificate = $certificate;

        return true;
    }

    /**
     * @var null|string
     */
    private $_privateCertificate;

    /**
     * Phương thức lấy khóa dùng cho việc tạo chữ ký dữ liệu.
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function getPrivateCertificate(): string
    {
        if ($this->_privateCertificate === null) {
            throw new InvalidConfigException(__METHOD__ . ' private certificate must be set!');
        } else {
            return $this->_privateCertificate;
        }
    }

    /**
     * Phương thức hổ trợ thiết lập khóa bí mật.
     *
     * @var string $certificate Khóa cần được thiết lập.
     * @return bool Trả về TRUE nếu thiết lập thành công và ngược lại.
     */
    public function setPrivateCertificate(string $certificate): bool
    {
        $this->_privateCertificate = $certificate;

        return true;
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function generate(): string
    {
        if (($privateKey = openssl_pkey_get_private($this->getPrivateCertificate())) && openssl_sign($this->getData(), $signature, $privateKey, $this->openSSLAlgo)) {
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
        $isValid = ($publicKey = openssl_pkey_get_public($this->getPublicCertificate())) && openssl_verify($this->getData(), $expect, $publicKey, $this->openSSLAlgo);
        openssl_free_key($publicKey);

        return $isValid;
    }

}
