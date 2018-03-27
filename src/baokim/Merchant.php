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

/**
 * Class Merchant
 * @package yii2vn\payment\baokim
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Merchant extends BaseMerchant
{

    const SIGNATURE_RSA = "RSA";

    const SIGNATURE_CHECKSUM = "CHECKSUM";

    public $id;

    public $emailBusiness;

    public $securePassword;

    public $privateCertificate;

    public function setPrivateCertificateFile($file)
    {
        $file = Yii::getAlias($file);
        $this->privateCertificate = file_get_contents($file);
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

    /**
     * @return string
     * @throws InvalidConfigException
     */
    public function signature(): string
    {
        $args = func_get_args();
        $data = array_shift($args);
        list($method, $api) = $args;
        $method = strtoupper($method);
        $str = $method . '&' . urlencode($api);
        $str .= '&' . urlencode(http_build_query($method === "GET" ? $data : [])) . '&' . urlencode(http_build_query($method === "POST" ? $data : []));

        if (($privateKey = openssl_pkey_get_private($this->privateCertificate)) && openssl_sign($str, $signature, $privateKey, OPENSSL_ALGO_SHA1)) {
            return urlencode(base64_encode($signature));
        } else {
            throw new InvalidConfigException('Can not signature data via current private certificate!');
        }
    }

    public function validateSignature(): bool
    {
        // TODO: Implement validateSignature() method.
    }

}