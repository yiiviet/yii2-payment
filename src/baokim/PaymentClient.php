<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\baokim;

use Yii;

use yii\base\InvalidConfigException;

use nhuluc\payment\BasePaymentClient;

/**
 * Lớp PaymentClient chứa các thuộc tính dùng để hổ trợ [[PaymentGateway]] kết nối đến Bảo Kim.
 *
 * @method PaymentGateway getGateway()
 * @property PaymentGateway $gateway
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0
 */
class PaymentClient extends BasePaymentClient
{
    /**
     * Hằng khai báo kiểu chữ ký `RSA` được dùng khi gọi phương thức [[signature()]] và [[validateSignature()]].
     */
    const SIGNATURE_RSA = 'RSA';

    /**
     * Hằng khai báo kiểu chữ ký `HMAC` được dùng khi gọi phương thức [[signature()]] và [[validateSignature()]].
     */
    const SIGNATURE_HMAC = 'HMAC';

    /**
     * Thuộc tính dùng để khai báo `merchant id` kết nối đến Bảo Kim khi tạo [[request()]] ở [[PaymentGateway]].
     * Nó do Bảo Kim cấp khi đăng ký tích hợp website.
     *
     * @var int
     */
    public $merchantId;

    /**
     * Thuộc tính thường được dùng để bổ sung email tài khoản nhận tiền hoặc cần lấy thông tin khi `end-user` không cung cấp thông tin.
     * Nó chính là email tài khoản Bảo Kim của bạn.
     *
     * @var string
     */
    public $merchantEmail;

    /**
     * Thuộc tính được dùng để tạo và kiểm tra chữ ký dữ liệu khi truy vấn thông tin giao dịch,
     * tạo thanh toán theo phương thức Bảo Kim, xác minh BPN (Bao Kim Payment Notification), xác minh success url.
     *
     * @var string
     */
    public $securePassword;

    /**
     * Thuộc tính dùng để khai báo `user` kết nối đến Bảo Kim khi tạo [[request()]] ở [[PaymentGateway]] khi thanh toán theo phương thức PRO.
     * Nó do Bảo Kim cấp khi đăng ký tích hợp website.
     *
     * @var string
     */
    public $apiUser;

    /**
     * Thuộc tính dùng để khai báo `password` kết nối đến Bảo Kim khi tạo [[request()]] ở [[PaymentGateway]] khi thanh toán theo phương thức PRO.
     * Nó do Bảo Kim cấp khi đăng ký tích hợp website.
     *
     * @var string
     */
    public $apiPassword;

    /**
     * Thuộc tính được dùng để tạo chữ ký dữ liệu khi thanh toán theo phương thức PRO, lấy thông tin merchant.
     *
     * @var string
     */
    public $privateCertificate;

    /**
     * Thuộc tính được dùng để kiểm tra chữ ký dữ liệu. Hiện nó chưa được dùng.
     *
     * @var string
     */
    public $publicCertificate;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @since 1.0.3
     */
    public function init()
    {
        if ($this->merchantId === null) {
            throw new InvalidConfigException('Property `merchantId` must be set!');
        }

        if ($this->merchantEmail === null) {
            throw new InvalidConfigException('Property `merchantEmail` must be set!');
        }

        if ($this->securePassword === null) {
            throw new InvalidConfigException('Property `securePassword` must be set!');
        }

        if ($this->getGateway()->pro) {
            if ($this->apiUser === null) {
                throw new InvalidConfigException('Property `apiUser` must be set on pro mode!');
            }

            if ($this->apiPassword === null) {
                throw new InvalidConfigException('Property `apiPassword` must be set on pro mode!');
            }

            if ($this->privateCertificate === null) {
                throw new InvalidConfigException('Property `privateCertificate` must be set on pro mode!');
            }
        }

        parent::init();
    }

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
    protected function initDataSignature(string $data, string $type = null): ?\yiiviet\payment\DataSignature
    {
        if ($type === self::SIGNATURE_RSA) {
            $config = [
                'class' => 'nhuluc\payment\RsaDataSignature',
                'publicCertificate' => $this->publicCertificate ?? false,
                'privateCertificate' => $this->privateCertificate,
                'openSSLAlgo' => OPENSSL_ALGO_SHA1
            ];
        } elseif ($type === self::SIGNATURE_HMAC) {
            $config = [
                'class' => 'nhuluc\payment\HmacDataSignature',
                'key' => $this->securePassword,
                'hmacAlgo' => 'SHA1',
                'caseSensitive' => false
            ];
        } else {
            return null;
        }

        return Yii::createObject($config, [$data]);
    }
}
