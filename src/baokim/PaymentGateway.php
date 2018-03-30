<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii\httpclient\Client as HttpClient;
use yii\httpclient\Request as HttpClientRequest;

use yii2vn\payment\BasePaymentGateway;
use yii2vn\payment\CheckoutInternalSeparateTrait;
use yii2vn\payment\MerchantInterface;
use yii2vn\payment\ResponseInstance;
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

    use CheckoutInternalSeparateTrait;

    public $merchantConfig = ['class' => Merchant::class];

    public $checkoutRequestInstanceConfig = ['class' => CheckoutRequestInstance::class];

    public $checkoutResponseInstanceConfig = ['class' => CheckoutResponseInstance::class];

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
     * @param array|string|CheckoutRequestInstance $instance
     * @return bool|CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException
     */
    public function cardCharge($instance)
    {
        return $this->checkout($instance, self::CHECKOUT_METHOD_TEL_CARD);
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
     * @param CheckoutRequestInstance $instance
     * @return CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     * @see [[checkoutBaoKim]]
     */
    private function checkoutWithAtmTransfer(CheckoutRequestInstance $instance): CheckoutResponseInstance
    {
        return $this->checkoutBaoKim($instance, self::CHECKOUT_METHOD_ATM_TRANSFER);
    }

    /**
     * @param CheckoutRequestInstance $instance
     * @return CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     * @see [[checkoutBaoKim]]
     */
    private function checkoutWithBankTransfer(CheckoutRequestInstance $instance): CheckoutResponseInstance
    {
        return $this->checkoutBaoKim($instance, self::CHECKOUT_METHOD_BANK_TRANSFER);
    }

    /**
     * @param CheckoutRequestInstance $instance
     * @return CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     * @see [[checkoutBaoKim]]
     */
    private function checkoutWithBaoKim(CheckoutRequestInstance $instance): CheckoutResponseInstance
    {
        return $this->checkoutBaoKim($instance, self::CHECKOUT_METHOD_BAO_KIM);
    }


    /**
     * @param CheckoutRequestInstance $instance
     * @return CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     * @see [[checkoutPro]]
     */
    private function checkoutWithLocalBank(CheckoutRequestInstance $instance): CheckoutResponseInstance
    {
        return $this->checkoutPro($instance, self::CHECKOUT_METHOD_LOCAL_BANK);
    }

    /**
     * @param CheckoutRequestInstance $instance
     * @return CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     * @see [[checkoutPro]]
     */
    private function checkoutWithCreditCard(CheckoutRequestInstance $instance): CheckoutResponseInstance
    {
        return $this->checkoutPro($instance, self::CHECKOUT_METHOD_CREDIT_CARD);
    }

    /**
     * @param CheckoutRequestInstance $instance
     * @return CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     * @see [[checkoutPro]]
     */
    private function checkoutWithInternetBanking(CheckoutRequestInstance $instance): CheckoutResponseInstance
    {
        return $this->checkoutPro($instance, self::CHECKOUT_METHOD_INTERNET_BANKING);
    }

    /**
     * @param CheckoutRequestInstance $instance
     * @return object|CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    private function checkoutWithTelCard(CheckoutRequestInstance $instance): CheckoutResponseInstance
    {
        /** @var Merchant $merchant */
        $merchant = $instance->merchant;

        $data = $instance->getData();
        ksort($data);
        $dataSign = implode('', $data);
        $data['checksum'] = $merchant->signature($dataSign, Merchant::SIGNATURE_HMAC);

        $httpResponse = $this->createHttpRequest(self::CARD_CHARGE_URL, 'POST', $data, $merchant)->send();

        Yii::debug("Checkout tel card requested sent");

        return Yii::createObject($this->checkoutResponseInstanceConfig, [$httpResponse->getData()]);
    }

    /**
     * @param CheckoutRequestInstance $instance
     * @param string $method
     * @return object|CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    private function checkoutPro(CheckoutRequestInstance $instance, string $method): CheckoutResponseInstance
    {
        /** @var Merchant $merchant */

        $merchant = $instance->merchant;
        $data = $instance->getData();

        ksort($data);
        $httpMethod = 'POST';
        $dataSign = $httpMethod . '&' . urlencode(self::PRO_PAYMENT_URL) . '&&' . urlencode(http_build_query($data));
        $data['signature'] = $merchant->signature($dataSign, Merchant::SIGNATURE_RSA);

        $httpResponse = $this->createHttpRequest(self::PRO_PAYMENT_URL, $httpMethod, $data, $merchant)->send();

        Yii::debug("Checkout pro requested sent with method: $method");

        return Yii::createObject($this->checkoutResponseInstanceConfig, [$httpResponse->getData()]);
    }

    /**
     * @param CheckoutRequestInstance $instance
     * @param string $method
     * @return object|CheckoutResponseInstance
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    private function checkoutBaoKim(CheckoutRequestInstance $instance, string $method): CheckoutResponseInstance
    {
        /** @var Merchant $merchant */
        $merchant = $instance->merchant;
        $data = $instance->getData();

        ksort($data);
        $dataSign = implode('', $data);
        $data['checksum'] = $merchant->signature($dataSign, Merchant::SIGNATURE_HMAC);

        $httpResponse = $this->createHttpRequest(self::BK_PAYMENT_URL, 'POST', $data, $merchant)->send();

        Yii::debug("Checkout bao kim requested sent with method: $method");

        return Yii::createObject($this->checkoutResponseInstanceConfig, [$httpResponse->getData()]);
    }



}