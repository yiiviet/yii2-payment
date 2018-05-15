<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use vxm\gatewayclients\DataInterface;
use vxm\gatewayclients\GatewayCollection;

/**
 * Lớp PaymentGatewayCollection dùng để tập hợp tất cả các cổng thanh toán thành 1 component trong app,
 * khi bạn có nhu cầu sử dụng nhiều cổng thanh toán.
 *
 * @method BasePaymentGateway getGateway($id)
 * @method BasePaymentGateway[] getGateways()
 *
 * @property BasePaymentGateway $gateway
 * @property BasePaymentGateway[] $gateways
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGatewayCollection extends GatewayCollection
{
    /**
     * Đây là phương thức ánh xạ của [[request()]]. Dùng để tạo lệnh thanh toán.
     *
     * @param array $data Dữ liệu thanh toán.
     * @param int|string $gatewayId Id của cổng thanh toán.
     * @param null|int|string $clientId Id client của cổng thanh toán dùng đề tạo lệnh.
     * Nếu không thiết lập thì [[getDefaultClient()]] sẽ được gọi trong cổng thanh toán.
     * @return \vxm\gatewayclients\ResponseData|DataInterface
     * @see [[BasePaymentGateway::purchase()]]
     * @throws \yii\base\InvalidConfigException
     */
    public function purchase(array $data, $gatewayId, $clientId = null): DataInterface
    {
        return $this->request(BasePaymentGateway::RC_PURCHASE, $data, $gatewayId, $clientId);
    }

    /**
     * Đây là phương thức ánh xạ của [[request()]]. Dùng để tạo lệnh truy vấn thông tin giao dịch.
     *
     * @param array $data Dữ liệu thanh toán.
     * @param int|string $gatewayId Id của cổng thanh toán.
     * @param null|int|string $clientId Id client của cổng thanh toán dùng đề tạo lệnh.
     * Nếu không thiết lập thì [[getDefaultClient()]] sẽ được gọi trong cổng thanh toán.
     * @return \vxm\gatewayclients\ResponseData|DataInterface
     * @see [[BasePaymentGateway::purchase()]]
     * @throws \yii\base\InvalidConfigException
     */
    public function queryDR(array $data, $gatewayId, $clientId = null): DataInterface
    {
        return $this->request(BasePaymentGateway::RC_QUERY_DR, $data, $gatewayId, $clientId);
    }


    /**
     * Đây là phương thức ánh xạ của [[verifyRequest()]]. Dùng để xác minh tính hợp lệ của dữ liệu khi khách hoàn tất thanh toán.
     *
     * @param int|string $gatewayId Id cổng thanh toán dùng để chỉ định xác minh.
     * @param \yii\web\Request|null $request Đối tượng `request` chứa dữ liệu cần được xác minh.
     * @param null|int|string $clientId Id client của cổng thanh toán dùng đề tạo lệnh.
     * Nếu không thiết lập thì [[getDefaultClient()]] sẽ được gọi trong cổng thanh toán.
     * @return bool|DataInterface|VerifiedData Trả về dữ liệu đã được xác minh hoặc là FALSE nếu dữ liệu không hợp lệ.
     * @see [[BasePaymentGateway::verifyRequestPurchaseSuccess()]].
     * @throws \yii\base\InvalidConfigException|\ReflectionException
     */
    public function verifyRequestPurchaseSuccess($gatewayId, \yii\web\Request $request = null, $clientId = null)
    {
        return $this->verifyRequest(BasePaymentGateway::VRC_PURCHASE_SUCCESS, $gatewayId, $request, $clientId);
    }

    /**
     * Đây là phương thức ánh xạ của [[verifyRequest()]]. Dùng để xác minh tính hợp lệ của dữ liệu khi cổng thanh toán gửi dữ liệu sang,
     * khi khách hoàn tất giao dịch.
     *
     * @param int|string $gatewayId Id cổng thanh toán dùng để chỉ định xác minh.
     * @param \yii\web\Request|null $request Đối tượng `request` chứa dữ liệu cần được xác minh.
     * @param null|int|string $clientId Id client của cổng thanh toán dùng đề tạo lệnh.
     * Nếu không thiết lập thì [[getDefaultClient()]] sẽ được gọi trong cổng thanh toán.
     * @return bool|DataInterface|VerifiedData Trả về dữ liệu đã được xác minh hoặc là FALSE nếu dữ liệu không hợp lệ.
     * @see [[BasePaymentGateway::verifyRequestIPN()]].
     * @throws \yii\base\InvalidConfigException|\ReflectionException
     */
    public function verifyRequestIPN($gatewayId, \yii\web\Request $request = null, $clientId = null)
    {
        return $this->verifyRequest(BasePaymentGateway::VRC_IPN, $gatewayId, $request, $clientId);
    }

    /**
     * Phương thức xác minh tính hợp lệ của request mà cổng thanh toán gửi về, đây là phương thức ánh xạ của
     * [[BasePaymentGateway::verifyRequest()]].
     *
     * @param int|string $command Lệnh yêu cầu xác minh.
     * @param \yii\web\Request $request Đối tượng `request` chứa dữ liệu cần xác minh.
     * @param int|string $gatewayId Id của cổng thanh toán dùng để tạo lệnh.
     * @param null|int|string $clientId Id client của cổng thanh toán dùng đề tạo lệnh.
     * Nếu không thiết lập thì [[BasePaymentGateway::getDefaultClient()]] sẽ được gọi trong cổng thanh toán.
     * @return bool|VerifiedData|DataInterface
     * @see [[BasePaymentGateway::verifyRequest()]]
     * @throws \yii\base\InvalidConfigException|\ReflectionException
     */
    public function verifyRequest($command, $gatewayId, \yii\web\Request $request = null, $clientId = null)
    {
        return $this->getGateway($gatewayId)->verifyRequest($command, $request, $clientId);
    }
}
