<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment;

use Yii;

use GatewayClients\DataInterface;

use yii\base\ActionFilter;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\web\ForbiddenHttpException;

/**
 * Lớp VerifyFilter dùng để xác thực tính hợp lệ của dữ liệu đầu vào.
 *
 * @property null|DataInterface $verifiedData đối tượng chứa các thuộc tính dữ liệu đã được xác minh tính hợp lệ.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class VerifyFilter extends ActionFilter
{

    /**
     * Ánh xạ của [[BasePaymentGateway::VRC_IPN]] hổ trợ khai báo thuộc tính `commands` dễ dàng hơn.
     */
    const VRC_IPN = BasePaymentGateway::VRC_IPN;

    /**
     * Ánh xạ của [[BasePaymentGateway::VRC_PURCHASE_SUCCESS]] hổ trợ khai báo thuộc tính `commands` dễ dàng hơn.
     */
    const VRC_PURCHASE_SUCCESS = BasePaymentGateway::VRC_PURCHASE_SUCCESS;

    /**
     * @var \yiiviet\payment\PaymentGatewayInterface Đối tượng cổng thanh toán dùng để xác thực tính hợp lệ của dữ liệu đầu vào,
     * bạn có thể thiết lập nó thông qua `id component` trong `app`.
     */
    public $gateway;

    /**
     * @var array chứa các action `id` map với `command` cần verify, lưu ý rằng chỉ cần action `id` chứ không phải là action `uniqueId`.
     * Ví dụ:
     *
     * ```php
     * [
     *   'ipn' => 'IPN',
     *   'purchase-success' => 'purchaseSuccess',
     * ]
     */
    public $commands = [];

    /**
     * @var \yii\web\Request đối tượng request dùng để lấy các dữ liệu đầu vào, nếu không thiết lập mặc định sẽ lấy `request` component trong `app`.
     */
    public $request = 'request';

    /**
     * @var bool tự động tắt kiểm tra `csrf` của controller hiện tại.
     * @since 1.0.3
     */
    public $autoDisableControllerCsrfValidation = true;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->gateway === null) {
            throw new InvalidConfigException('`gateway` property must be set!');
        } else {
            $this->gateway = Instance::ensure($this->gateway, 'yiiviet\payment\PaymentGatewayInterface');
        }

        if (empty($this->commands)) {
            throw new InvalidConfigException('`commands` property must be set!');
        }

        $this->request = Instance::ensure($this->request, 'yii\web\Request');

        parent::init();
    }

    /**
     * @inheritdoc
     * @throws ForbiddenHttpException|InvalidConfigException
     */
    public function beforeAction($action)
    {
        $actionId = $action->id;
        $command = $this->commands[$actionId] ?? null;

        if ($command !== null) {
            if (($verifiedData = $this->gateway->verifyRequest($command, $this->request)) instanceof DataInterface) {
                $this->_verifiedData = $verifiedData;

                if ($this->autoDisableControllerCsrfValidation) {
                    $action->controller->enableCsrfValidation = false;
                }

                return true;
            } else {
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
        } else {
            throw new InvalidConfigException("Can't find verify command of action `$actionId`");
        }
    }


    /**
     * @var DataInterface đối tượng tập hợp các thuộc tính dữ liệu đã xác thực.
     * @see [[getVerifiedData()]]
     */
    private $_verifiedData;

    /**
     * Phương thức hổ trợ lấy dữ liệu đã xác thực tính hợp lệ.
     *
     * @return null|DataInterface đối tượng chứa các thuộc tính dữ liệu đã xác thực.
     */
    public function getVerifiedData(): ?DataInterface
    {
        return $this->_verifiedData;
    }

}
