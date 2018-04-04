<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\InvalidConfigException;


/**
 * Class RsaDataSignature
 *
 * @property string $publicCertificate
 * @property string $privateCertificate
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
 class RsaDataSignature extends DataSignature
{

    public $openSSLAlgo;

    /**
     * @var null|string
     */
    private $_publicCertificate;

    /**
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
     * @var string $certificate
     * @return bool
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
     * @var string $certificate
     * @return bool
     */
    public function setPrivateCertificate(string $certificate): bool
    {
        $this->_privateCertificate = $certificate;

        return true;
    }

    /**
     * @inheritdoc
     */
    public function generate(): string
    {
        if (($privateKey = openssl_pkey_get_private($this->getPrivateCertificate())) && openssl_sign($this->getData(), $signature, $privateKey, $this->openSSLAlgo)) {
            openssl_free_key($privateKey);

            return urlencode(base64_encode($signature));
        } else {
            throw new InvalidConfigException('Can not signature data via current private certificate!');
        }
    }

    /**
     * @inheritdoc
     */
    public function validate(string $expect): bool
    {
        $expect = urldecode(base64_decode($expect));
        $isValid = ($publicKey = openssl_pkey_get_public($this->getPublicCertificate())) && openssl_verify($this->getData(), $expect, $publicKey, $this->openSSLAlgo);
        openssl_free_key($publicKey);

        return $isValid;
    }

}