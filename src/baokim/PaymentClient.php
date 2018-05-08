<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\baokim;

use Yii;

use yii\helpers\ArrayHelper;

use yiiviet\payment\BasePaymentClient;

/**
 * Class PaymentClient
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentClient extends BasePaymentClient
{

    const SIGNATURE_RSA = 'RSA';

    const SIGNATURE_HMAC = 'HMAC';

    public $apiUser;

    public $apiPassword;

    public $merchantId;

    public $merchantEmail;

    public $securePassword;

    public $privateCertificate;

    public $publicCertificate;

    public $hmacDataSignatureConfig = ['class' => 'yiiviet\payment\HmacDataSignature'];

    public $rsaDataSignatureConfig = ['class' => 'yiiviet\payment\RsaDataSignature'];

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
     * @return object|\yiiviet\payment\DataSignature
     * @throws \yii\base\InvalidConfigException
     */
    protected function initDataSignature(string $data, string $type): ?\yiiviet\payment\DataSignature
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
