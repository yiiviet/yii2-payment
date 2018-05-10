<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\baokim;

use Yii;

use yii\di\Instance;
use yii\helpers\ArrayHelper;

use yiiviet\payment\BasePaymentGateway;

use vxm\gatewayclients\RequestEvent;
use vxm\gatewayclients\DataInterface;

/**
 * Lớp PaymentGateway thực thi các phương thức trừu tượng dùng hổ trợ kết nối đến Bảo Kim.
 * Hiện tại nó hổ trợ 100% các tính năng từ cổng thanh toán Bảo Kim.
 *
 * @method ResponseData purchase(array $data, $clientId = null)
 * @method ResponseData queryDR(array $data, $clientId = null)
 * @method VerifiedData verifyRequestIPN($clientId = null, \yii\web\Request $request = null)
 * @method VerifiedData verifyRequestPurchaseSuccess($clientId = null, \yii\web\Request $request = null)
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
     * Lệnh `purchasePro` sử dụng cho việc tạo [[request()]] yêu cầu thanh toán PRO.
     */
    const RC_PURCHASE_PRO = 'purchasePro';

    /**
     * Lệnh `getMerchantData` sử dụng cho việc tạo [[request()]] yêu cầu thông tin merchant.
     */
    const RC_GET_MERCHANT_DATA = 'getMerchantData';

    /**
     * Lệnh `verifyIPN` sử dụng cho việc tạo [[request()]] yêu cầu Bảo Kim kiểm tra dữ liệu Bảo Kim bắn sang IPN có hợp lệ hay không.
     * Chống giả mạo.
     */
    const RC_VERIFY_IPN = 'verifyIPN';

    /**
     * @event RequestEvent được gọi trước khi tạo yêu câu thanh toán PRO.
     */
    const EVENT_BEFORE_PURCHASE_PRO = 'beforePurchasePro';

    /**
     * @event RequestEvent được gọi sau khi tạo yêu câu thanh toán PRO.
     */
    const EVENT_AFTER_PURCHASE_PRO = 'afterPurchasePro';

    /**
     * @event RequestEvent được gọi trước khi tạo yêu câu lấy thông tin merchant.
     */
    const EVENT_BEFORE_GET_MERCHANT_DATA = 'beforeGetMerchantData';

    /**
     * @event RequestEvent được gọi sau khi tạo yêu câu lấy thông tin merchant.
     */
    const EVENT_AFTER_GET_MERCHANT_DATA = 'afterGetMerchantData';

    /**
     * @event RequestEvent được gọi trước khi tạo yêu cầu cập nhật trạng thái đơn hàng từ IPN.
     */
    const EVENT_BEFORE_VERIFY_IPN = 'beforeVerifyIPN';

    /**
     * @event RequestEvent được gọi sau khi tạo yêu cầu cập nhật trạng thái đơn hàng từ IPN.
     */
    const EVENT_AFTER_VERIFY_IPN = 'afterVerifyIPN';

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
     * Cache component hổ trợ cho việc cache lại dữ liệu merchant lấy từ Bảo Kim nhầm tối ưu hóa hệ thống
     * do dữ liệu này ít khi bị thay đổi.
     *
     * @see getMerchantData
     * @var bool|string|array|\yii\caching\Cache
     */
    public $merchantDataCache = 'cache';

    /**
     * Cache duration quy định thời gian cache dữ liệu của merchant lấy từ Bảo Kim.
     *
     * @var int
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
     * @throws \yii\base\InvalidConfigException
     */
    protected function initSandboxEnvironment()
    {
        $clientConfig = require(__DIR__ . '/sandbox-client.php');
        $this->setClient($clientConfig);
    }

    /**
     * Phương thức thanh toán pro (payment pro) hổ trợ tạo thanh toán với phương thức PRO của Bảo Kim.
     * Đây là phương thức ánh xạ của [[request()]] sử dụng lệnh [[RC_PURCHASE_PRO]].
     *
     * @param array $data Dữ liệu yêu cầu khởi tạo thanh toán PRO
     * @param null $clientId PaymentClient id sử dụng để tạo yêu cầu thanh toán.
     * Nếu không thiết lập [[getDefaultClient()]] sẽ được gọi để xác định client.
     * @return ResponseData|DataInterface Trả về [[ResponseData]] là dữ liệu từ Bảo Kim phản hồi.
     * @throws \ReflectionException|\yii\base\InvalidConfigException|\yii\base\InvalidArgumentException
     */
    public function purchasePro(array $data, $clientId = null): DataInterface
    {
        return $this->request(self::RC_PURCHASE_PRO, $data, $clientId);
    }

    /**
     * @inheritdoc
     */
    protected function getHttpClientConfig(): array
    {
        return [
            'transport' => 'yii\httpclient\CurlTransport',
            'requestConfig' => [
                'format' => 'json',
                'options' => [
                    CURLOPT_HTTPAUTH => CURLAUTH_DIGEST | CURLAUTH_BASIC,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false
                ]
            ]
        ];
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

        if (!$this->merchantDataCache || !$responseData = $this->merchantDataCache->get($cacheKey)) {
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
     * Phương thức tạo lệnh yêu cầu Bảo Kim kiểm tra tính hợp lệ của dữ liệu mà IPN nhận được nhầm chống giả mạo.
     * Đây là phương thức ánh xạ của [[request()]] sử dụng lệnh [[RC_IPN]].
     *
     * @param \yii\web\Request $request Đối tượng request chứa thông tin mà Bảo Kim bắn sang IPN,
     * nó được dùng để tổng hợp dữ liệu yêu cầu Bảo Kim xác minh và cập nhật.
     * Nếu không thiết lập đối tượng `request` của `Yii::$app` sẽ được sử dụng.
     * @param null $clientId Client id sử dụng để tạo yêu cầu cập nhật.
     * Nếu không thiết lập [[getDefaultClient()]] sẽ được gọi để xác định client.
     * @return ResponseData|DataInterface Trả về [[ResponseData]] là dữ liệu từ Bảo Kim phản hồi.
     * @throws \ReflectionException|\yii\base\InvalidConfigException|\yii\base\InvalidArgumentException
     */
    public function verifyIPN(\yii\web\Request $request = null, $clientId = null): DataInterface
    {
        if ($request === null) {
            $request = Instance::ensure('request', '\yii\web\Request');
        }

        return $this->request(self::RC_VERIFY_IPN, $request->post(), $clientId);
    }

    /**
     * @inheritdoc
     */
    public function beforeRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE_PRO) {
            $this->trigger(self::EVENT_BEFORE_PURCHASE_PRO, $event);
        } elseif ($event->command === self::RC_GET_MERCHANT_DATA) {
            $this->trigger(self::EVENT_BEFORE_GET_MERCHANT_DATA, $event);
        } elseif ($event->command === self::RC_VERIFY_IPN) {
            $this->trigger(self::EVENT_BEFORE_VERIFY_IPN, $event);
        }

        parent::beforeRequest($event);
    }

    /**
     * @inheritdoc
     */
    public function afterRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE_PRO) {
            $this->trigger(self::EVENT_AFTER_PURCHASE_PRO, $event);
        } elseif ($event->command === self::RC_GET_MERCHANT_DATA) {
            $this->trigger(self::EVENT_AFTER_GET_MERCHANT_DATA, $event);
        } elseif ($event->command === self::RC_VERIFY_IPN) {
            $this->trigger(self::EVENT_AFTER_VERIFY_IPN, $event);
        }

        parent::afterRequest($event);
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    protected function requestInternal(\vxm\gatewayclients\RequestData $requestData, \yii\httpclient\Client $httpClient): array
    {
        /** @var PaymentClient $client */
        $client = $requestData->getClient();
        $command = $requestData->getCommand();
        $data = $requestData->get();
        $httpMethod = 'POST';
        $options = [CURLOPT_USERPWD => $client->apiUser . ':' . $client->apiPassword];
        $responseFormat = null;

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
        } elseif ($command === self::RC_PURCHASE) {
            $data[0] = self::PURCHASE_URL;
            return ['location' => $httpClient->get($data)->getFullUrl()];
        } elseif ($command === self::RC_PURCHASE_PRO) {
            $url = [self::PURCHASE_PRO_URL, 'signature' => ArrayHelper::remove($data, 'signature')];
        } else {
            $url = self::VERIFY_IPN_URL;
            $responseFormat = 'urlencoded';
            $options = [];
        }

        return $httpClient->createRequest()
            ->setUrl($url)
            ->setMethod($httpMethod)
            ->addOptions($options)
            ->addData($data)
            ->send()
            ->setFormat($responseFormat)
            ->getData();
    }

    /**
     * @inheritdoc
     */
    protected function getVerifyRequestData($command, \yii\web\Request $request): array
    {
        $params = [
            'order_id', 'transaction_id', 'created_on', 'payment_type', 'transaction_status', 'total_amount', 'net_amount',
            'fee_amount', 'merchant_id', 'customer_name', 'customer_email', 'customer_phone', 'customer_address', 'checksum'
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
