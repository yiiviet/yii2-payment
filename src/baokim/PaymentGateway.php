<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\base\InvalidConfigException;
use yii\httpclient\Client as HttpClient;
use yii\httpclient\Request as HttpClientRequest;
use yii\httpclient\Response as HttpClientResponse;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CardChargePaymentGatewayInterface;
use yii2vn\payment\CheckoutInstanceInterface;
use yii2vn\payment\CheckoutResponseDataInterface;
use yii2vn\payment\CheckoutInternalSeparateTrait;
use yii2vn\payment\MerchantInterface;


/**
 * Class PaymentGateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway implements CardChargePaymentGatewayInterface
{

    const CHECKOUT_METHOD_BAO_KIM = 'baoKim';

    const CHECKOUT_METHOD_LOCAL_BANK = 'localBank';

    const CHECKOUT_METHOD_ATM_TRANSFER = 'atmTransfer';

    const CHECKOUT_METHOD_BANK_TRANSFER = 'bankTransfer';

    const BK_PAYMENT_URL = '/payment/order/version11';

    const CARD_CHARGE_URL = '/the-cao/restFul/send';

    const PRO_SELLER_INFO_URL = '/payment/rest/payment_pro_api/get_seller_info';

    const PRO_PAYMENT_URL = '/payment/rest/payment_pro_api/pay_by_card';

    use CheckoutInternalSeparateTrait;


    public static function version(): string
    {
        return '1.0';
    }

    public static function baseUrl(): string
    {
        return 'https://www.baokim.vn';
    }

    /**
     * @inheritdoc
     */
    protected function createHttpRequest(string $url, string $method, array $queryData, MerchantInterface $merchant = null): HttpClientRequest
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
     * @param string $method
     * @return null|string
     */
    protected function getDefaultCheckoutInstanceClass(string $method): ?string
    {
        switch ($method) {
            case self::CHECKOUT_METHOD_BAO_KIM || self::CHECKOUT_METHOD_ATM_TRANSFER || self::CHECKOUT_METHOD_BANK_TRANSFER:
                return CheckoutInstance::class;
            case self::CHECKOUT_METHOD_CREDIT_CARD || self::CHECKOUT_METHOD_LOCAL_BANK || self::CHECKOUT_METHOD_INTERNET_BANKING:
                return ProCheckoutInstance::class;
            case self::CHECKOUT_METHOD_TEL_CARD:
                return TelCardCheckoutInstance::class;
            default:
                return null;
        }
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    public function checkoutWithAtmTransfer(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        return $this->checkoutBaoKim($instance, self::CHECKOUT_METHOD_ATM_TRANSFER);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    public function checkoutWithBankTransfer(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        return $this->checkoutBaoKim($instance, self::CHECKOUT_METHOD_BANK_TRANSFER);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    public function checkoutWithBaoKim(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        return $this->checkoutBaoKim($instance, self::CHECKOUT_METHOD_BAO_KIM);
    }


    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    public function checkoutWithLocalBank(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        return $this->checkoutPro($instance, self::CHECKOUT_METHOD_LOCAL_BANK);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     * @see [[checkoutWithPro]]
     */
    public function checkoutWithCreditCard(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        return $this->checkoutPro($instance, self::CHECKOUT_METHOD_CREDIT_CARD);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     * @see [[checkoutWithPro]]
     */
    public function checkoutWithInternetBanking(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        return $this->checkoutPro($instance, self::CHECKOUT_METHOD_INTERNET_BANKING);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    public function checkoutWithTelCard(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        /** @var Merchant $merchant */
        $merchant = $instance->getMerchant();
        $data = $instance->getData(self::CHECKOUT_METHOD_TEL_CARD);

        $data['checksum'] = $merchant->signature([
            'data' => $data,
            'key' => $merchant->securePassword
        ], Merchant::SIGNATURE_HMAC);

        $httpResponse = $this->createHttpRequest(self::CARD_CHARGE_URL, 'POST', $data, $merchant)->send();

        Yii::debug("Checkout tel card requested sent");

        return $this->resolveHttpResponse($httpResponse);
    }

    /**
     * @param array|string|TelCardCheckoutInstance $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    public function cardCharge($instance): CheckoutResponseDataInterface
    {
        $instance = $this->prepareCheckoutInstance($instance, static::CHECKOUT_METHOD_TEL_CARD);

        return $this->checkoutWithTelCard($instance);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @param string $method
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    private function checkoutPro(CheckoutInstanceInterface $instance, string $method): CheckoutResponseDataInterface
    {
        /** @var Merchant $merchant */
        $merchant = $instance->getMerchant();
        $data = $instance->getData($method);

        $data['signature'] = $merchant->signature([
            'data' => $data,
            'urlPath' => self::PRO_PAYMENT_URL,
            'httpMethod' => RsaDataSignature::HTTP_METHOD_POST,
            'publicCertificate' => $merchant->publicCertificate,
            'privateCertificate' => $merchant->privateCertificate
        ], Merchant::SIGNATURE_RSA);

        $httpResponse = $this->createHttpRequest(self::PRO_PAYMENT_URL, 'POST', $data, $merchant)->send();

        Yii::debug("Checkout pro requested sent with method: $method");

        return $this->resolveHttpResponse($httpResponse);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @param string $method
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    private function checkoutBaoKim(CheckoutInstanceInterface $instance, string $method): CheckoutResponseDataInterface
    {
        /** @var Merchant $merchant */
        $merchant = $instance->getMerchant();
        $data = $instance->getData($method);

        $data['checksum'] = $merchant->signature([
            'data' => $data,
            'key' => $merchant->securePassword
        ], Merchant::SIGNATURE_HMAC);

        $httpResponse = $this->createHttpRequest(self::BK_PAYMENT_URL, 'POST', $data, $merchant)->send();

        Yii::debug("Checkout bao kim requested sent with method: $method");

        return $this->resolveHttpResponse($httpResponse);
    }

    /**
     * @param HttpClientResponse $httpResponse
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException
     */
    private function resolveHttpResponse(HttpClientResponse $httpResponse): CheckoutResponseDataInterface
    {
        $httpResponseData = $httpResponse->getData();

        return Yii::createObject([
            'class' => CheckoutResponseData::class,
            'message' => $httpResponseData['error'] ?? null,
            'redirectUrl' => $httpResponseData['redirect_url'] ?? null,
            'responseCode' => (int)$httpResponse->statusCode,
            'nextAction' => $httpResponseData['next_action'] ?? null,
            'errorCode' => $httpResponseData['error_code'] ?? null,
            'rvId' => $httpResponse['rv_id'] ?? null
        ]);
    }

}