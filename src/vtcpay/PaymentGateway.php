<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace nhuluc\payment\vtcpay;

use GatewayClients\DataInterface;

use yii\base\NotSupportedException;
use yii\httpclient\Client as HttpClient;

use nhuluc\payment\BasePaymentGateway;

/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến VTCPay.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán VTCPay.
 *
 * @method ResponseData purchase(array $data, $clientId = null)
 * @method VerifiedData verifyRequestIPN(\yii\web\Request $request = null, $clientId = null)
 * @method VerifiedData verifyRequestPurchaseSuccess(\yii\web\Request $request = null, $clientId = null)
 * @method PaymentClient getClient($id = null)
 * @method PaymentClient getDefaultClient()
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.2
 */
class PaymentGateway extends BasePaymentGateway
{

    /**
     * Đường dẫn API để yêu cầu tạo giao dịch thanh toán.
     */
    const PURCHASE_URL = '/checkout.html';

    /**
     * Phương thức thanh toán bằng ví điện tử VTCPay.
     */
    const PAYMENT_METHOD_VTCPAY = 'VTCPay';

    /**
     * Phương thức thanh toán bằng ngân hàng trong nước.
     */
    const PAYMENT_METHOD_DOMESTIC_BANK = 'DomesticBank';

    /**
     * Phương thức thanh toán bằng thẻ quốc tế (visa/master).
     */
    const PAYMENT_METHOD_INTERNATIONAL_CARD = 'InternationalCard';

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
     * @throws \yii\base\InvalidConfigException
     */
    protected function requestInternal(\vxm\gatewayclients\RequestData $requestData, HttpClient $httpClient): array
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
