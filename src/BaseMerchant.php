<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use Yii;

use yii\base\Component;
use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * @property PaymentGatewayInterface $paymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseMerchant extends Component implements MerchantInterface
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $phone;

    /**
     * @var string
     */
    public $rsaDataSignatureClass;

    /**
     * @var string
     */
    public $hmacDataSignatureClass;

    /**
     * @var array|string|PaymentGatewayInterface
     */
    private $_paymentGateway;

    /**
     * @return PaymentGatewayInterface
     */
    public function getPaymentGateway(): PaymentGatewayInterface
    {
        return $this->_paymentGateway;
    }

    /**
     * @param array|string|PaymentGatewayInterface $paymentGateway
     * @return bool
     * @throws InvalidConfigException
     */
    public function setPaymentGateway($paymentGateway): bool
    {
        $this->_paymentGateway = Instance::ensure($paymentGateway, PaymentGatewayInterface::class);

        return true;
    }

    /**
     * @param array|string|DataSignatureInterface $dataSignature
     * @param string $type
     * @return string
     * @throws InvalidConfigException|NotSupportedException
     */
    public function signature($dataSignature, string $type = null): string
    {
        $dataSignature = $this->prepareDataSignature($dataSignature, $type);

        return $dataSignature->generate();
    }

    /**
     * @param array|string|DataSignatureInterface $dataSignature
     * @param string $expectSignature
     * @param string $type
     * @return bool
     * @throws InvalidConfigException|NotSupportedException
     */
    public function validateSignature($dataSignature, string $expectSignature, string $type = null): bool
    {
        $dataSignature = $this->prepareDataSignature($dataSignature, $type);

        return $dataSignature->validate($expectSignature);
    }

    /**
     * @param $dataSignature
     * @param $type
     * @return object|DataSignatureInterface
     * @throws InvalidConfigException|NotSupportedException
     */
    protected function prepareDataSignature($dataSignature, $type): DataSignatureInterface
    {
        if ($dataSignature instanceof DataSignatureInterface) {
            return $dataSignature;
        } else {
            if (is_string($dataSignature)) {
                $dataSignature = ['class' => $dataSignature];
            } elseif (is_array($dataSignature) && !isset($dataSignature['class'])) {
                if ($type === self::SIGNATURE_HMAC) {
                    $dataSignature['class'] = $this->hmacDataSignatureClass;
                } elseif ($type === self::SIGNATURE_RSA) {
                    $dataSignature['class'] = $this->rsaDataSignatureClass;
                } else {
                    throw new NotSupportedException("Data signature type: '$type' is not supported!");
                }
            }

            return Yii::createObject($dataSignature);
        }
    }
}