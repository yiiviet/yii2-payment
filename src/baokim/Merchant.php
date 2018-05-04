<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment\baokim;

use Yii;

use yii\helpers\ArrayHelper;

use yiivn\payment\BaseMerchant;
use yiivn\payment\DataSignature;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    const SIGNATURE_RSA = 'RSA';

    const SIGNATURE_HMAC = 'HMAC';

    public $id;

    public $apiUser;

    public $apiPassword;

    public $email;

    public $securePassword;

    public $privateCertificate;

    public $publicCertificate;

    public $hmacDataSignatureConfig = ['class' => 'yiivn\payment\HmacDataSignature'];

    public $rsaDataSignatureConfig = ['class' => 'yiivn\payment\RsaDataSignature'];

    /**
     * @param $file
     * @return bool
     */
    public function setPublicCertificateFile($file): bool
    {
        $file = Yii::getAlias($file);
        $this->publicCertificate = file_get_contents($file);

        return true;
    }

    /**
     * @param $file
     * @return bool
     */
    public function setPrivateCertificateFile($file): bool
    {
        $file = Yii::getAlias($file);
        $this->privateCertificate = file_get_contents($file);

        return true;
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDataSignature(string $data, string $type): ?\yiivn\payment\DataSignature
    {
        if ($type === self::SIGNATURE_RSA) {
            return Yii::createObject(ArrayHelper::merge($this->rsaDataSignatureConfig, [
                'publicCertificate' => $this->publicCertificate,
                'privateCertificate' => $this->privateCertificate,
                'openSSLAlgo' => OPENSSL_ALGO_SHA1
            ]), [$data]);
        } elseif ($type === self::SIGNATURE_HMAC) {
            return Yii::createObject(ArrayHelper::merge($this->hmacDataSignatureConfig, [
                'key' => $this->securePassword,
                'hmacAlgo' => 'md5'
            ]), [$data]);
        } else {
            return null;
        }
    }
}