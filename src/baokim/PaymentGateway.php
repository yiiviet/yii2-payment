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
use yii\httpclient\Request as HttpClientRequest;
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
     * @throws InvalidConfigException
     * @throws NotSupportedException
     */
    public function checkoutWithInternetBanking(CheckoutInstanceInterface $instance): CheckoutResponseDataInterface
    {
        /** @var Merchant $merchant */
        $merchant = $instance->getMerchant();
        $data = $instance->getData(self::CHECKOUT_METHOD_INTERNET_BANKING);

        $data['signature'] = $merchant->signature([
            'data' => $data,
            'urlPath' => self::CHECKOUT_METHOD_INTERNET_BANKING,
            'httpMethod' => RsaDataSignature::HTTP_METHOD_POST,
            'publicCertificate' => $merchant->publicCertificate,
            'privateCertificate' => $merchant->privateCertificate
        ], Merchant::SIGNATURE_RSA);

        $httpResponse = $this->createHttpRequest($merchant, 'POST')->setData($data)->send();

    }


    /**
     * @inheritdoc
     */
    protected function createHttpRequest(MerchantInterface $merchant, string $httpMethod): HttpClientRequest
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

        return $request;
    }


}