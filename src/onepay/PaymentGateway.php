<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

use GatewayClients\DataInterface;

use yiiviet\payment\BasePaymentGateway;

/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến OnePay.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán OnePay v2.
 *
 * @method ResponseData purchase(array $data, $clientId = null)
 * @method ResponseData queryDR(array $data, $clientId = null)
 * @method bool|VerifiedData verifyRequestIPN($clientId = null, \yii\web\Request $request = null)
 * @method bool|VerifiedData verifyRequestPurchaseSuccess($clientId = null, \yii\web\Request $request = null)
 * @method PaymentClient getClient($id = null)
 * @method PaymentClient getDefaultClient()
 *
 * @property PaymentClient $client
 * @property PaymentClient $defaultClient
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{
    /**
     * Đường dẫn API của thanh toán nội địa.
     */
    const PURCHASE_DOMESTIC_URL = '/onecomm-pay/vpc.op';

    /**
     * Đường dẫn API để truy vấn thông tin giao dịch nội địa.
     */
    const QUERY_DR_DOMESTIC_URL = '/onecomm-pay/Vpcdps.op';

    /**
     * Đường dẫn API của thanh toán quốc tế.
     */
    const PURCHASE_INTERNATIONAL_URL = '/vpcpay/vpcpay.op';

    /**
     * Đường dẫn API để truy vấn thông tin giao dịch quốc tế.
     */
    const QUERY_DR_INTERNATIONAL_URL = '/vpcpay/Vpcdps.op';

    /**
     * Id của client trong môi trường thử nghiệm dùng để giao tiếp với OnePay ở cổng quốc tế.
     */
    const ID_CLIENT_SANDBOX_INTERNATIONAL = '__sandboxInternational';

    /**
     * Id của client trong môi trường thử nghiệm dùng để giao tiếp với OnePay ở cổng nội địa.
     */
    const ID_CLIENT_SANDBOX_DOMESTIC = '__sandboxDomestic';

    /**
     * @var bool Optional to use international gateway. Set to TRUE if you want use methods (requests, verifies) with international mode.
     */
    public $international = false;

    /**
     * @inheritdoc
     */
    public $clientConfig = ['class' => PaymentClient::class];

    /**
     * @inheritdoc
     */
    public $requestDataConfig = ['class' => RequestData::class];

    /**
     * @inheritdoc
     */
    public $responseDataConfig = ['class' => ResponseData::class];

    /**
     * @inheritdoc
     */
    public $verifiedDataConfig = ['class' => VerifiedData::class];

    /**
     * @inheritdoc
     */
    public function getBaseUrl(): string
    {
        return $this->sandbox ? 'https://mtf.onepay.vn' : 'https://onepay.vn';
    }

    /**
     * @inheritdoc
     * @since 1.0.3
     */
    public function requestCommands(): array
    {
        return [self::RC_PURCHASE, self::RC_QUERY_DR];
    }

    /**
     * @return ResponseData|DataInterface
     * @inheritdoc
     */
    public function request($command, array $data, $clientId = null): DataInterface
    {
        if ($clientId === null && $this->sandbox) {
            $clientId = $this->international ? self::ID_CLIENT_SANDBOX_INTERNATIONAL : self::ID_CLIENT_SANDBOX_DOMESTIC;
        }

        return parent::request($command, $data, $clientId);
    }

    /**
     * @return bool|VerifiedData
     * @inheritdoc
     */
    public function verifyRequest($command, \yii\web\Request $request = null, $clientId = null)
    {
        if ($clientId === null && $this->sandbox) {
            $clientId = $this->international ? self::ID_CLIENT_SANDBOX_INTERNATIONAL : self::ID_CLIENT_SANDBOX_DOMESTIC;
        }

        return parent::verifyRequest($command, $request, $clientId);
    }

    /**
     * @inheritdoc
     */
    protected function defaultVersion(): string
    {
        return '2';
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function initSandboxEnvironment()
    {
        $clientDomesticConfig = require(__DIR__ . '/sandbox-client-domestic.php');
        $clientInternationalConfig = require(__DIR__ . '/sandbox-client-international.php');

        $this->setClient(static::ID_CLIENT_SANDBOX_DOMESTIC, $clientDomesticConfig);
        $this->setClient(static::ID_CLIENT_SANDBOX_INTERNATIONAL, $clientInternationalConfig);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException|\yii\httpclient\Exception
     */
    protected function requestInternal(\vxm\gatewayclients\RequestData $requestData, \yii\httpclient\Client $httpClient): array
    {
        $command = $requestData->getCommand();
        $commandUrls = [
            self::RC_PURCHASE => $this->international ? self::PURCHASE_INTERNATIONAL_URL : self::PURCHASE_DOMESTIC_URL,
            self::RC_QUERY_DR => $this->international ? self::QUERY_DR_INTERNATIONAL_URL : self::QUERY_DR_DOMESTIC_URL,
        ];

        $data = $requestData->get();
        $data[0] = $commandUrls[$command];

        if ($command === self::RC_PURCHASE) {
            return ['redirect_url' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
        } else {
            return $httpClient->get($data)->send()->getData();
        }
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        $params = [
            'vpc_Command', 'vpc_Locale', 'vpc_MerchTxnRef', 'vpc_Merchant', 'vpc_OrderInfo', 'vpc_Amount',
            'vpc_TxnResponseCode', 'vpc_TransactionNo', 'vcp_Message', 'vpc_SecureHash', 'vpc_AcqResponseCode',
            'vpc_Authorizeld', 'vpc_Card', 'vpc_3DSECI', 'vpc_3Dsenrolled', 'vpc_3Dsstatus', 'vpc_CommercialCard'
        ];

        $data = [];

        foreach ($params as $param) {
            if (($value = $request->get($param)) !== null) {
                $data[$param] = $value;
            }
        }

        return $data;
    }

}
