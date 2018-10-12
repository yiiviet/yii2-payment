<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\vtcpay;

use GatewayClients\DataInterface;

use yii\base\NotSupportedException;
use yii\httpclient\Client as HttpClient;

use yiiviet\payment\BasePaymentGateway;

use vxm\gatewayclients\RequestData;

/**
 * Lớp PaymentGateway
 *
 * @method ResponseData purchase(array $data, $clientId = null)
 * @method ResponseData queryDR(array $data, $clientId = null)
 * @method VerifiedData verifyRequestIPN(\yii\web\Request $request = null, $clientId = null)
 * @method VerifiedData verifyRequestPurchaseSuccess(\yii\web\Request $request = null, $clientId = null)
 * @method PaymentClient getClient($id = null)
 * @method PaymentClient getDefaultClient()
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class PaymentGateway extends BasePaymentGateway
{

    /**
     * Đường dẫn API để yêu cầu tạo giao dịch thanh toán.
     */
    const PURCHASE_URL = '/checkout.html';

    const PAYMENT_METHOD_VTCPAY = 'VTCPay';

    const PAYMENT_METHOD_DOMESTIC_BANK = 'DomesticBank';

    const PAYMENT_METHOD_INTERNATIONAL_CARD = 'InternationalCard';

    const TRANSACTION_SALE = 'sale';

    /**
     * @inheritdoc
     */
    public function getBaseUrl(): string
    {
        return $this->sandbox ? 'http://alpha1.vtcpay.vn/portalgateway' : 'https://vtcpay.vn/bank-gateway';
    }

    /**
     * @inheritdoc
     */
    public function requestCommands(): array
    {
        return [self::RC_PURCHASE];
    }

    /**
     * @inheritdoc
     */
    protected function initSandboxEnvironment()
    {
        $clientConfig = require(__DIR__ . '/sandbox-client.php');
        $this->setClient($clientConfig);
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function queryDR(array $data, $clientId = null): DataInterface
    {
        throw new NotSupportedException('queryDR doesnt\'t supported in VTCPay!');
    }


    /**
     * @inheritdoc
     */
    protected function requestInternal(RequestData $requestData, HttpClient $httpClient): array
    {
        $data = $requestData->get();
        $data[0] = self::PURCHASE_URL;

        return ['redirect_url' => $httpClient->createRequest()->setUrl($data)->getFullUrl()];
    }

    /**
     * @param int|string $command
     * @param \yii\web\Request $request
     * @return array
     */
    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        if ($command === self::VRC_IPN) {
            $params = ['data', 'signature'];
            $requestMethod = 'post';
        } else {
            $params = ['amount', 'message', 'payment_type', 'reference_number', 'status', 'trans_ref_no', 'website_id', 'signature'];
            $requestMethod = 'get';
        }

        $data = [];

        foreach ($params as $param) {
            if (($value = call_user_func([$request, $requestMethod], $param)) !== null) {
                $data[$param] = $value;
            }
        }

        return $data;
    }

}
