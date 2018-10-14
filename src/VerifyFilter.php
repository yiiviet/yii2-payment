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
use yii\di\Instance;
use yii\web\ForbiddenHttpException;

/**
 * Lớp VerifyFilter dùng để xác thực tính hợp lệ của dữ liệu đầu vào.
 *
 * @property DataInterface $verifiedData Đối tượng chứa các thuộc tính dữ liệu đã được xác minh tính hợp lệ.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.2
 */
class VerifyFilter extends ActionFilter
{

    /**
     * @var \yiiviet\payment\PaymentGatewayInterface Đối tượng cổng thanh toán dùng để xác thực tính hợp lệ của dữ liệu đầu vào.
     */
    public $gateway;

    /**
     * @var string Lệnh muốn thực thi xác thực tính hợp lệ của dữ liệu đầu vào.
     */
    public $command;

    /**
     * @var \yii\web\Request Đối tượng request dùng để lấy các dữ liệu đầu vào, nếu không thiết lập mặc định sẽ lấy `request` component trong `app`.
     */
    public $request = 'request';

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        $this->request = Instance::ensure($this->request, 'yii\web\Request');

        parent::init();
    }

    /**
     * @inheritdoc
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if (($verifiedData = $this->gateway->verifyRequest($this->command, $this->request)) instanceof DataInterface) {
            $this->_verifiedData = $verifiedData;

            return true;
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }


    /**
     * @var DataInterface Đối tượng tập hợp các thuộc tính dữ liệu đã xác thực.
     * @see [[getVerifiedData()]]
     */
    private $_verifiedData;

    /**
     * Phương thức hổ trợ lấy dữ liệu đã xác thực tính hợp lệ.
     *
     * @return null|DataInterface Đối tượng chứa các thuộc tính dữ liệu đã xác thực.
     */
    public function getVerifiedData(): ?DataInterface
    {
        return $this->_verifiedData;
    }

}
