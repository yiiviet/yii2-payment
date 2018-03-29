<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\base\NotSupportedException;
use yii\base\InvalidConfigException;
use yii\httpclient\Client as HttpClient;
use yii\httpclient\Response as HttpClientResponse;
use yii\httpclient\RequestEvent as HttpClientRequestEvent;

use yii2vn\payment\BasePaymentGateway;
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
class PaymentGateway extends BasePaymentGateway
{

    const CHECKOUT_METHOD_PRO = 'pro';

    const CHECKOUT_METHOD_BAO_KIM = 'baoKim';

    const SELLER_INFO_URL = '/payment/rest/payment_pro_api/get_seller_info';

    const PAY_BY_CARD_URL = '/payment/rest/payment_pro_api/pay_by_card';

    use CheckoutInternalSeparateTrait;

    public function init()
    {

        parent::init();
    }

    public static function getVersion(): string
    {
        return '1.0';
    }

    public function getBaseUrl(): string
    {
        return 'https://www.baokim.vn';
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException|NotSupportedException
     * @see [[checkoutWithPro]]
     */
    public function checkoutWithInternetBanking(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        return $this->checkoutWithPro($instance);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException|NotSupportedException
     * @see [[checkoutWithPro]]
     */
    public function checkoutWithCreditCard(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        return $this->checkoutWithPro($instance);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     * @return CheckoutResponseDataInterface
     * @throws InvalidConfigException|NotSupportedException
     */
    public function checkoutWithPro(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        /** @var Merchant $merchant */
        $merchant = $instance->getMerchant();
        $data = $instance->getData(self::CHECKOUT_METHOD_PRO);

        $data['signature'] = $merchant->signature([
            'data' => $data,
            'urlPath' => self::PAY_BY_CARD_URL,
            'httpMethod' => RsaDataSignature::HTTP_METHOD_POST,
            'publicCertificate' => $merchant->publicCertificate,
            'privateCertificate' => $merchant->privateCertificate
        ], Merchant::SIGNATURE_RSA);

        $httpResponse = $this->sendHttpRequest($merchant, 'POST', $data);
        $httpResponseData = $this->sendHttpRequest($merchant, 'POST', $data)->getData();

        return Yii::createObject([
            'class' => CheckoutResponseData::class,
            'message' => $httpResponseData['error'] ?? null,
            'redirectUrl' => $httpResponseData['redirect_url'] ?? null,
            'responseCode' => (int)$httpResponse->statusCode,
            'nextAction' => $httpResponseData['next_action'] ?? null,
            'errorCode' => $httpResponseData['error_code'] ?? null
        ]);
    }

    /**
     * @param CheckoutInstanceInterface $instance
     */
    public function checkoutWithBaoKim(CheckoutInstanceInterface $instance)
    {
        $merchant = $instance->getMerchant();
        $data = $instance->getData(self::CHECKOUT_METHOD_BAO_KIM);

        $data['checksum'] = $merchant->signature([
            'data' => $data,
            'urlPath' => self::PAY_BY_CARD_URL,
            'httpMethod' => RsaDataSignature::HTTP_METHOD_POST,
            'publicCertificate' => $merchant->publicCertificate,
            'privateCertificate' => $merchant->privateCertificate
        ], Merchant::SIGNATURE_RSA);
    }

    /**
     * @param string $method
     * @return null|string
     */
    protected function getDefaultCheckoutInstanceClass(string $method): ?string
    {
        switch ($method) {
            case self::CHECKOUT_METHOD_BAO_KIM:
        }
    }

    /**
     * @inheritdoc
     */
    protected function sendHttpRequest(MerchantInterface $merchant, string $httpMethod, array $queryData, string $format = HttpClient::FORMAT_JSON): HttpClientResponse
    {
        /** @var Merchant $merchant */

        $request = $this->getHttpClient()->createRequest();

        $request->setOptions([
            CURLOPT_HTTPAUTH => CURLAUTH_DIGEST | CURLAUTH_BASIC,
            CURLOPT_USERPWD => "{$merchant->apiUser}:{$merchant->apiPassword}",
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $request->setMethod($httpMethod);

        return $request->send();
    }


}