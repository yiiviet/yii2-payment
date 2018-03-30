<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\base\NotSupportedException;
use yii\httpclient\Client as HttpClient;
use yii\httpclient\Request as HttpClientRequest;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\Data;
use yii2vn\payment\MerchantInterface;
use yii2vn\payment\TelCardPaymentGatewayInterface;


/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway implements TelCardPaymentGatewayInterface
{

    const CHECKOUT_METHOD_BAO_KIM = 'baoKim';

    const CHECKOUT_METHOD_LOCAL_BANK = 'localBank';

    const CHECKOUT_METHOD_ATM_TRANSFER = 'atmTransfer';

    const CHECKOUT_METHOD_BANK_TRANSFER = 'bankTransfer';

    const BK_PAYMENT_URL = '/payment/order/version11';

    const CARD_CHARGE_URL = '/the-cao/restFul/send';

    const PRO_SELLER_INFO_URL = '/payment/rest/payment_pro_api/get_seller_info';

    const PRO_PAYMENT_URL = '/payment/rest/payment_pro_api/pay_by_card';

    public $merchantConfig = ['class' => Merchant::class];

    public $checkoutRequestDataConfig = ['class' => CheckoutRequestData::class];

    public $checkoutResponseDataConfig = ['class' => CheckoutResponseData::class];

    public $merchantDataConfig = ['class' => Data::class];

    /**
     * @inheritdoc
     */
    public static function version(): string
    {
        return '1.0';
    }

    /**
     * @inheritdoc
     */
    public static function baseUrl(): string
    {
        return 'https://www.baokim.vn';
    }

    /**
     * @param array $data
     * @return bool|CheckoutResponseData
     */
    public function cardCharge(array $data)
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_TEL_CARD);
    }

    /**
     * @inheritdoc
     */
    public function createHttpRequest(string $url, string $method, array $queryData, MerchantInterface $merchant = null): HttpClientRequest
    {
        /** @var Merchant $merchant */

        if ($merchant === null) {
            $merchant = $this->getDefaultMerchant();
        }

        $request = $this->getHttpClient()->createRequest();

        $request->setUrl($url)
            ->setMethod($method)
            ->setData($queryData)
            ->setFormat(HttpClient::FORMAT_JSON)
            ->setOptions([
                CURLOPT_HTTPAUTH => CURLAUTH_DIGEST | CURLAUTH_BASIC,
                CURLOPT_USERPWD => "{$merchant->apiUser}:{$merchant->apiPassword}",
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

        return $request;
    }


    /**
     * @param Merchant|null $merchant
     * @throws \yii\base\InvalidConfigException
     * @return object|Data
     */
    public function getMerchantInfo(Merchant $merchant = null): Data
    {
        if ($merchant === null) {
            $merchant = $this->getDefaultMerchant();
        }

        $data = ['business' => $merchant->businessEmail];
        $httpMethod = 'GET';
        $dataSign = $httpMethod . '&' . urlencode(self::PRO_PAYMENT_URL) . '&' . urlencode(http_build_query($data)) . '&';
        $data['signature'] = $merchant->signature($dataSign, Merchant::SIGNATURE_RSA);
        $httpResponse = $this->createHttpRequest(self::PRO_PAYMENT_URL, $httpMethod, $data, $merchant)->send();

        return Yii::createObject($this->merchantConfig, [$httpResponse->getData()]);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithAtmTransfer(array $data): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_ATM_TRANSFER);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithBankTransfer(array $data): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_BANK_TRANSFER);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithBaoKim(array $data): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_BAO_KIM);
    }


    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithLocalBank(array $data): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_LOCAL_BANK);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithCreditCard(array $data): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_CREDIT_CARD);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithInternetBanking(array $data): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_INTERNET_BANKING);
    }

    /**
     * @param array $data
     * @return CheckoutResponseData
     */
    public function checkoutWithTelCard(array $data): CheckoutResponseData
    {
        return $this->checkout($data, self::CHECKOUT_METHOD_TEL_CARD);
    }

    /**
     * @param Data $data
     * @param string $method
     * @return array
     * @throws \yii\base\InvalidConfigException|NotSupportedException
     */
    protected function checkoutInternal(Data $data, string $method): array
    {
        /** @var Merchant $merchant */

        $merchant = $data->merchant;
        $data = $data->getData();
        ksort($data);
        $dataSign = implode('', $data);

        switch ($method) {
            case self::CHECKOUT_METHOD_TEL_CARD:
                $data['data_sign'] = $merchant->signature($dataSign, Merchant::SIGNATURE_HMAC);
                $httpResponse = $this->createHttpRequest(self::CARD_CHARGE_URL, 'POST', $data, $merchant)->send();
                break;
            case self::CHECKOUT_METHOD_ATM_TRANSFER || self::CHECKOUT_METHOD_BANK_TRANSFER || self::CHECKOUT_METHOD_BAO_KIM:
                $data['checksum'] = $merchant->signature($dataSign, Merchant::SIGNATURE_HMAC);
                $httpResponse = $this->createHttpRequest(self::BK_PAYMENT_URL, 'POST', $data, $merchant)->send();
                break;
            case self::CHECKOUT_METHOD_LOCAL_BANK || self::CHECKOUT_METHOD_INTERNET_BANKING || self::CHECKOUT_METHOD_CREDIT_CARD:
                $httpMethod = 'POST';
                $dataSign = $httpMethod . '&' . urlencode(self::PRO_PAYMENT_URL) . '&&' . urlencode(http_build_query($data));
                $data['signature'] = $merchant->signature($dataSign, Merchant::SIGNATURE_RSA);
                $httpResponse = $this->createHttpRequest(self::PRO_PAYMENT_URL, $httpMethod, $data, $merchant)->send();
                break;
            default:
                throw new NotSupportedException("Checkout method '$method' not supported in " . __CLASS__);

        }

        Yii::debug("Checkout requested sent with method: $method");

        return $httpResponse->getData();
    }


}