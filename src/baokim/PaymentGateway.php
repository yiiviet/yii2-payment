<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\baokim;

use Yii;

use GatewayClients\DataInterface;

use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

use nhuluc\payment\BasePaymentGateway;

use vxm\gatewayclients\RequestEvent;

/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến Bảo Kim.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán Bảo Kim.
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
 * @property ResponseData $merchantData
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0
 */
class PaymentGateway extends BasePaymentGateway
{
    /**
     * Lệnh `getMerchantData` sử dụng cho việc tạo [[request()]] yêu cầu thông tin merchant.
     */
    const RC_GET_MERCHANT_DATA = 'getMerchantData';

    /**
     * @event RequestEvent được gọi trước khi tạo yêu câu lấy thông tin merchant.
     */
    const EVENT_BEFORE_GET_MERCHANT_DATA = 'beforeGetMerchantData';

    /**
     * @event RequestEvent được gọi sau khi tạo yêu câu lấy thông tin merchant.
     */
    const EVENT_AFTER_GET_MERCHANT_DATA = 'afterGetMerchantData';

    /**
     * Đường dẫn API của thanh toán Bảo Kim.
     */
    const PURCHASE_URL = '/payment/order/version11';

    /**
     * Đường dẫn API của thanh toán PRO.
     */
    const PURCHASE_PRO_URL = '/payment/rest/payment_pro_api/pay_by_card';

    /**
     * Đường dẫn API để lấy thông tin merchant.
     */
    const PRO_SELLER_INFO_URL = '/payment/rest/payment_pro_api/get_seller_info';

    /**
     * Đường dẫn API để truy vấn thông tin giao dịch.
     */
    const QUERY_DR_URL = '/payment/order/queryTransaction';

    /**
     * Đường dẫn API IPN của Bảo Kim để cập nhật và xác minh dữ liệu từ IPN request từ Bảo Kim bắn sang.
     * Nói cách khác là sẽ có 2 IPN, 1 cái nằm trên server của bạn và 1 cái là của Bảo Kim để cập nhật đơn hàng của họ.
     */
    const VERIFY_IPN_URL = '/bpn/verify';

    /**
     * MUI thuộc tính trong mảng data khi tạo thanh toán PRO, cho phép chỉ định giao diện hiển thị charge.
     */
    const MUI_CHARGE = 'charge';

    /**
     * MUI thuộc tính trong mảng data khi tạo thanh toán PRO, cho phép chỉ định giao diện hiển thị base.
     */
    const MUI_BASE = 'base';

    /**
     * MUI thuộc tính trong mảng data khi tạo thanh toán PRO, cho phép chỉ định giao diện hiển thị iframe.
     */
    const MUI_IFRAME = 'iframe';

    /**
     * Transaction mode direct là thuộc tính khi tạo thanh toán Bảo Kim và PRO, cho phép chỉ định giao dịch trực tiếp.
     */
    const DIRECT_TRANSACTION = 1;

    /**
     * Transaction mode safe là thuộc tính khi tạo thanh toán Bảo Kim và PRO, cho phép chỉ định giao dịch tạm giữ.
     */
    const SAFE_TRANSACTION = 2;

    /**
     * @var bool Thiết lập true nếu muốn thanh toán trực tiếp không chuyển khách đến Bảo Kim.
     */
    public $pro = false;

    /**
     * @see getMerchantData
     * @var bool|string|array|\yii\caching\Cache Hổ trợ cho việc cache lại dữ liệu merchant lấy từ Bảo Kim nhầm tối ưu hóa hệ thống
     * do dữ liệu này ít khi bị thay đổi.
     */
    public $merchantDataCache = 'cache';

    /**
     * @var int Thời gian cache dữ liệu của merchant lấy từ Bảo Kim.
     */
    public $merchantDataCacheDuration = 86400;

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
     * @since 1.0.3.1
     */
    public $httpClientConfig = ['transport' => 'yii\httpclient\CurlTransport'];

    /**
     * @inheritdoc
     */
    public function getBaseUrl(): string
    {
        return $this->sandbox ? 'https://sandbox.baokim.vn' : 'https://www.baokim.vn';
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @inheritdoc
     */
    public function init()
    {
        if ($this->merchantDataCache) {
            $this->merchantDataCache = Instance::ensure($this->merchantDataCache, 'yii\caching\Cache');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     * @since 1.0.3
     */
    public function requestCommands(): array
    {
        return [self::RC_PURCHASE, self::RC_QUERY_DR, self::RC_GET_MERCHANT_DATA];
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function initSandboxEnvironment()
    {
        $clientConfig = require(__DIR__ . '/sandbox-client.php');
        $this->setClient($clientConfig);
    }

    /**
     * Phương thức hổ trợ lấy thông tin merchant thông qua email business.
     * Đây là phương thức ánh xạ của [[request()]] sử dụng lệnh [[RC_GET_MERCHANT_DATA]].
     *
     * @param string $emailBusiness Email muốn lấy thông tin từ Bảo Kim.
     * @param int|string|null $clientId PaymentClient id sử dụng để lấy thông tin.
     * Nếu không thiết lập [[getDefaultClient()]] sẽ được gọi để xác định client.
     * @throws \ReflectionException|\yii\base\InvalidConfigException
     * @return ResponseData|DataInterface Trả về [[ResponseData]] là dữ liệu của emailBusiness từ Bảo Kim phản hồi.
     */
    public function getMerchantData(string $emailBusiness = null, $clientId = null): DataInterface
    {
        /** @var PaymentClient $client */
        $client = $this->getClient($clientId);
        $cacheKey = [
            __METHOD__,
            get_class($client),
            $client->merchantId,
            $emailBusiness
        ];

        if (!$this->merchantDataCache || !($responseData = $this->merchantDataCache->get($cacheKey))) {
            $responseData = $this->request(self::RC_GET_MERCHANT_DATA, [
                'business' => $emailBusiness ?? $client->merchantEmail
            ], $clientId);

            if ($this->merchantDataCache) {
                $this->merchantDataCache->set($cacheKey, $responseData, $this->merchantDataCacheDuration);
            }
        }

        return $responseData;
    }

    /**
     * @inheritdoc
     */
    public function beforeRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_GET_MERCHANT_DATA) {
            $this->trigger(self::EVENT_BEFORE_GET_MERCHANT_DATA, $event);
        }

        parent::beforeRequest($event);
    }

    /**
     * @inheritdoc
     */
    public function afterRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_GET_MERCHANT_DATA) {
            $this->trigger(self::EVENT_AFTER_GET_MERCHANT_DATA, $event);
        }

        parent::afterRequest($event);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException|\yii\httpclient\Exception|NotSupportedException
     */
    protected function requestInternal(\vxm\gatewayclients\RequestData $requestData, \yii\httpclient\Client $httpClient): array
    {
        /** @var PaymentClient $client */
        $client = $requestData->getClient();
        $command = $requestData->getCommand();
        $data = $requestData->get();
        $httpMethod = 'POST';

        if (in_array($command, [self::RC_GET_MERCHANT_DATA, self::RC_QUERY_DR], true)) {
            if ($command === self::RC_GET_MERCHANT_DATA) {
                $url = self::PRO_SELLER_INFO_URL;
            } else {
                $url = self::QUERY_DR_URL;
            }
            $data[0] = $url;
            $url = $data;
            $data = null;
            $httpMethod = 'GET';
        } else {
            if ($this->pro) {
                $url = [self::PURCHASE_PRO_URL, 'signature' => ArrayHelper::remove($data, 'signature')];
            } else {
                $data[0] = self::PURCHASE_URL;
                return ['redirect_url' => $httpClient->get($data)->getFullUrl()];
            }
        }

        return $httpClient
            ->createRequest()
            ->setUrl($url)
            ->setMethod($httpMethod)
            ->setFormat('json')
            ->addData($data)
            ->addOptions([
                CURLOPT_HTTPAUTH => CURLAUTH_DIGEST | CURLAUTH_BASIC,
                CURLOPT_USERPWD => $client->apiUser . ':' . $client->apiPassword
            ])
            ->send()
            ->getData();
    }

    /**
     * @inheritdoc
     */
    public function verifyRequest($command, \yii\web\Request $request = null, $clientId = null)
    {
        if ($command === self::VRC_IPN) {

            if ($request === null) {
                if (Yii::$app instanceof \yii\web\Application) {
                    $request = Yii::$app->getRequest();
                } else {
                    throw new InvalidArgumentException('Request instance arg must be set to verify return request is valid or not!');
                }
            }

            $content = $this->getHttpClient()->post(self::VERIFY_IPN_URL, $request->post())->send()->getContent();

            if (strpos($content, 'VERIFIED') === false) {
                return false;
            }
        }

        return parent::verifyRequest($command, $request, $clientId);
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        $params = [
            'order_id', 'transaction_id', 'created_on', 'payment_type', 'transaction_status', 'total_amount', 'net_amount',
            'fee_amount', 'merchant_id', 'customer_name', 'customer_email', 'customer_phone', 'customer_address', 'checksum',
            'payer_name', 'payer_email', 'payer_phone_no', 'shipping_address', 'verify_sign', 'resend', 'customer_location',
            'merchant_address', 'merchant_email', 'merchant_location', 'merchant_name', 'merchant_phone'
        ];
        $commandRequestMethods = [self::VRC_PURCHASE_SUCCESS => 'get', self::VRC_IPN => 'post'];
        $requestMethod = $commandRequestMethods[$command];
        $data = [];

        foreach ($params as $param) {
            if (($value = call_user_func([$request, $requestMethod], $param)) !== null) {
                $data[$param] = $value;
            }
        }

        return $data;
    }

}
