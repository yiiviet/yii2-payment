<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use Yii;
use ReflectionClass;

use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client as HttpClient;

use vxm\gatewayclients\BaseGateway;
use vxm\gatewayclients\DataInterface;
use vxm\gatewayclients\RequestEvent;
use vxm\gatewayclients\ResponseData;

/**
 * Lớp BasePaymentGateway thực thi mẫu trừu tượng [[PaymentGatewayInterface]] giúp cho việc xây dựng các lợp thực thi được tối giản.
 *
 * @property BasePaymentClient $client
 * @property BasePaymentClient $defaultClient
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BasePaymentGateway extends BaseGateway implements PaymentGatewayInterface
{
    /**
     * Lệnh `purchase` sử dụng cho việc khởi tạo truy vấn thanh toán.
     */
    const RC_PURCHASE = 'purchase';

    /**
     * Lệnh `queryDR` sử dụng cho việc truy vấn thông tin giao dịch.
     */
    const RC_QUERY_DR = 'queryDR';

    /**
     * Lệnh `purchaseSuccess` sử dụng cho việc yêu cấu xác thực tính hợp lệ
     * của dữ liệu khi khách hàng thanh toán thành công (cổng thanh toán redirect khách hàng về server).
     */
    const VRC_PURCHASE_SUCCESS = 'purchaseSuccess';

    /**
     * Lệnh `IPN` sử dụng cho việc yêu cấu xác thực tính hợp lệ
     * của dữ liệu khi khách hàng thanh toán thành công (cổng thanh toán bắn request về server).
     */
    const VRC_IPN = 'IPN';

    /**
     * @event RequestEvent được gọi khi dữ liệu truy vấn đã được xác thực.
     * Lưu ý sự kiện này luôn luôn được gọi khi xác thực dữ liệu truy vấn.
     */
    const EVENT_VERIFIED_REQUEST = 'verifiedRequest';

    /**
     * @event VerifiedRequestEvent được gọi khi dữ liệu truy vấn sau khi khách hàng thanh toán thành công,
     * được cổng thanh toán dẫn về hệ thống đã xác thực.
     */
    const EVENT_VERIFIED_REQUEST_PURCHASE_SUCCESS = 'verifiedRequestPurchaseSuccess';

    /**
     * @event VerifiedRequestEvent được gọi khi dữ liệu truy vấn sau khi khách hàng thanh toán thành công,
     * được cổng thanh toán bắn `request` sang hệ thống đã xác thực.
     */
    const EVENT_VERIFIED_REQUEST_IPN = 'verifiedRequestIPN';

    /**
     * @event RequestEvent được gọi trước khi khởi tạo lệnh [[RC_PURCHASE]] ở phương thức [[request()]].
     */
    const EVENT_BEFORE_PURCHASE = 'beforePurchase';

    /**
     * @event RequestEvent được gọi sau khi khởi tạo lệnh [[RC_PURCHASE]] ở phương thức [[request()]].
     */
    const EVENT_AFTER_PURCHASE = 'afterPurchase';

    /**
     * @event RequestEvent được gọi trước khi khởi tạo lệnh [[RC_QUERY_DR]] ở phương thức [[request()]].
     */
    const EVENT_BEFORE_QUERY_DR = 'beforeQueryDR';

    /**
     * @event RequestEvent được gọi sau khi khởi tạo lệnh [[RC_QUERY_DR]] ở phương thức [[request()]].
     */
    const EVENT_AFTER_QUERY_DR = 'afterQueryDR';

    /**
     * @var bool nếu là môi trường test thì thiết lập là TRUE và ngược lại.
     */
    public $sandbox = false;

    /**
     * @var array
     */
    public $verifiedDataConfig = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->sandbox) {
            $this->initSandboxEnvironment();
        }

        parent::init();
    }

    /**
     * Phương thức khởi tạo môi trường thử nghiệm.
     * Nó chỉ được gọi khi thuộc tính `sandbox` được thiết lập là TRUE.
     */
    abstract protected function initSandboxEnvironment();

    /**
     * Thuộc tính để cache lại tiến trình của [[verifyRequestCommands()]] nhầm tối ưu tốc độ khi gọi nhiều lần.
     *
     * @see verifyRequestCommands
     * @var array
     */
    private $_verifyRequestCommands;

    /**
     * Phương thức này tự động thu thập các lệnh xác thực thông qua các hằng được khai bảo bằng tiền tố `VRC_`.
     * `VRC` có nghĩa là Verify Request Command.
     *
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function verifyRequestCommands(): array
    {
        if ($this->_verifyRequestCommands === null) {
            $reflection = new ReflectionClass($this);

            $commands = [];
            foreach ($reflection->getConstants() as $name => $value) {
                if (strpos($name, 'VRC_') === 0) {
                    $commands[] = $value;
                }
            }

            return $this->_verifyRequestCommands = $commands;
        } else {
            return $this->_verifyRequestCommands;
        }
    }

    /**
     * Phương thức này là phương thức ánh xạ của [[request()]] nó sẽ tạo lệnh [[RC_PURCHASE]] để tạo yêu cầu giao dịch tới cổng thanh toán.
     *
     * @param array $data Dữ liệu dùng để yêu cầu tạo giao dịch thanh toán bên trong thường có giá tiền, địa chỉ giao hàng...
     * @param string|int $clientId PaymentClient id dùng để tạo yêu cầu thanh toán.
     * @return ResponseData|DataInterface Phương thức sẽ trả về mẫu trừu tượng [[DataInterface]] để lấy thông tin trả về từ cổng thanh toán.
     * @throws InvalidConfigException|\ReflectionException
     */
    public function purchase(array $data, $clientId = null): DataInterface
    {
        return $this->request(self::RC_PURCHASE, $data, $clientId);
    }

    /**
     * Phương thức này là phương thức ánh xạ của [[request()]] nó sẽ tạo lệnh [[RC_QUERY_DR]] để tạo yêu cầu truy vấn giao dịch tới cổng thanh toán.
     *
     * @param array $data Dữ liệu dùng để truy vấn thông tin giao dịch bên trong thường có mã giao dịch từ cổng thanh toán...
     * @param string|int $clientId PaymentClient id dùng để tạo yêu cầu truy vấn giao dịch.
     * @return ResponseData|DataInterface Phương thức sẽ trả về mẫu trừu tượng [[DataInterface]] để lấy thông tin trả về từ cổng thanh toán.
     * @throws InvalidConfigException|\ReflectionException
     */
    public function queryDR(array $data, $clientId = null): DataInterface
    {
        return $this->request(self::RC_QUERY_DR, $data, $clientId);
    }

    /**
     * @inheritdoc
     */
    public function beforeRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE) {
            $this->trigger(self::EVENT_BEFORE_PURCHASE, $event);
        } elseif ($event->command === self::RC_QUERY_DR) {
            $this->trigger(self::EVENT_BEFORE_QUERY_DR, $event);
        }

        parent::beforeRequest($event);
    }

    /**
     * @inheritdoc
     */
    public function afterRequest(RequestEvent $event)
    {
        if ($event->command === self::RC_PURCHASE) {
            $this->trigger(self::EVENT_AFTER_PURCHASE, $event);
        } elseif ($event->command === self::RC_QUERY_DR) {
            $this->trigger(self::EVENT_AFTER_QUERY_DR, $event);
        }

        parent::afterRequest($event);
    }

    /**
     * Phương thức này là phương thức ánh xạ của [[verifyRequest()]] nó sẽ tạo lệnh [[VRC_PURCHASE_SUCCESS]]
     * để tạo yêu cầu xác minh tính hợp lệ của dữ liệu trả về từ máy khách đến máy chủ.
     *
     * @param \yii\web\Request|null $request Đối tượng `request` thực hiện truy cập hệ thống.
     * @param null|int|string $clientId PaymentClient id dùng để xác thực tính hợp lệ của dữ liệu.
     * @return bool|VerifiedData|DataInterface Sẽ trả về FALSE nếu như dữ liệu không hợp lệ ngược lại sẽ trả về thông tin đơn hàng đã được xác thực.
     * @throws InvalidConfigException|\ReflectionException
     */
    public function verifyRequestPurchaseSuccess(\yii\web\Request $request = null, $clientId = null)
    {
        return $this->verifyRequest(self::VRC_PURCHASE_SUCCESS, $request, $clientId);
    }

    /**
     * Phương thức này là phương thức ánh xạ của [[verifyRequest()]] nó sẽ tạo lệnh [[VRC_IPN]]
     * để tạo yêu cầu xác minh tính hợp lệ của dữ liệu trả về từ cổng thanh toán đến máy chủ.
     *
     * @param \yii\web\Request|null $request Đối tượng `request` thực hiện truy cập hệ thống.
     * @param null|int|string $clientId PaymentClient id dùng để xác thực tính hợp lệ của dữ liệu.
     * @return bool|VerifiedData|DataInterface Sẽ trả về FALSE nếu như dữ liệu không hợp lệ ngược lại sẽ trả về thông tin đơn hàng đã được xác thực.
     * @throws InvalidConfigException|\ReflectionException
     */
    public function verifyRequestIPN(\yii\web\Request $request = null, $clientId = null)
    {
        return $this->verifyRequest(self::VRC_IPN, $request, $clientId);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException|\ReflectionException
     */
    public function verifyRequest($command, \yii\web\Request $request = null, $clientId = null)
    {
        if (in_array($command, $this->verifyRequestCommands(), true)) {
            $client = $this->getClient($clientId);

            if ($request === null && Yii::$app) {
                $request = Yii::$app->getRequest();
            } else {
                throw new InvalidArgumentException('Request instance arg must be set to verify return request is valid or not!');
            }

            $data = $this->getVerifyRequestData($command, $request);
            /** @var VerifiedData $requestData */
            $verifyData = Yii::createObject($this->verifiedDataConfig, [$command, $data, $client]);
            if ($verifyData->validate()) {
                /** @var VerifiedRequestEvent $event */
                $event = Yii::createObject([
                    'class' => VerifiedRequestEvent::class,
                    'verifiedData' => $verifyData,
                    'client' => $client,
                    'command' => $command
                ]);
                $this->verifiedRequest($event);

                return $verifyData;
            } else {
                return false;
            }
        } else {
            throw new InvalidArgumentException("Unknown verify request command: `$command`");
        }
    }

    /**
     * Phương thúc lấy dữ liệu cần xác minh từ lệnh yêu cầu và đối tượng `request`.
     *
     * @param int|string $command Lệnh yêu cầu lấy dữ liệu cần được xác minh.
     * @param \yii\web\Request $request Đối tượng `request` được yêu cầu lấy dữ liệu.
     * @return array Trả về mảng dữ liệu cần được xác minh.
     */
    abstract protected function getVerifyRequestData($command, \yii\web\Request $request): array;

    /**
     * Phương thức được gọi sau khi việc xác minh tính hợp hệ của dữ liệu thành công.
     * Nó được xây dựng để kích hoạt các sự kiện liên quan khi dữ liệu đã được xác minh.
     *
     * @param VerifiedRequestEvent $event
     */
    public function verifiedRequest(VerifiedRequestEvent $event)
    {
        if ($event->command === self::VRC_IPN) {
            $this->trigger(self::EVENT_VERIFIED_REQUEST_IPN, $event);
        } elseif ($event->command === self::VRC_PURCHASE_SUCCESS) {
            $this->trigger(self::EVENT_VERIFIED_REQUEST_PURCHASE_SUCCESS, $event);
        }

        $this->trigger(self::EVENT_VERIFIED_REQUEST, $event);
    }


}
