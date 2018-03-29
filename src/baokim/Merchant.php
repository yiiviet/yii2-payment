<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\base\InvalidConfigException;

use yii2vn\payment\BaseMerchant;
use yii2vn\payment\DataSignatureInterface;

/**
 * Class Merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    public $id;

    public $apiUser;

    public $apiPassword;

    public $emailBusiness;

    public $securePassword;

    public $privateCertificate;

    public $publicCertificate;

    public $hmacDataSignatureClass = 'yii2vn\payment\baokim\HmacDataSignature';

    public $rsaDataSignatureClass = 'yii2vn\payment\baokim\RsaDataSignature';

    public function setPublicCertificateFile($file): bool
    {
        $file = Yii::getAlias($file);
        $this->publicCertificate = file_get_contents($file);

        return true;
    }

    public function setPrivateCertificateFile($file): bool
    {
        $file = Yii::getAlias($file);
        $this->privateCertificate = file_get_contents($file);

        return true;
    }

    /**
     * @param string $method
     * @return array
     */
    public function getData(string $method): array
    {
        return [
            'business' => $this->emailBusiness ?? $this->email,
            'id' => $this->id,
        ];
    }

}