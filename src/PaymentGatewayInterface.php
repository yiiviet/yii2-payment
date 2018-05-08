<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use vxm\gatewayclients\DataInterface;
use vxm\gatewayclients\GatewayInterface;

/**
 * PaymentGatewayInterface là mẫu trừu tượng kế thừa [[GatewayInterface]] bổ sung thêm phương thức xác minh tính hợp lệ của dữ liệu
 * khi khách hàng giao dịch thành công được redirect về hệ thống `success_url` và `IPN`.
 *
 * Nó được sử dụng tốt cho việc xây dụng các lớp hổ trợ cho việc tích hợp các cổng thanh toán tại Việt Nam.
 *
 * @property array|PaymentClientInterface[] $merchants
 * @property PaymentClientInterface $merchant
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface PaymentGatewayInterface extends GatewayInterface
{

    /**
     * Phương thức dùng cho việc xác minh tính hợp lệ của `request`,
     * nó thường dùng cho việc kiểm tra dữ liệu khi khách hàng thanh toán thành công (IPN, success url).
     * Việc xác minh thường được thông qua bằng chữ ký dữ liệu.
     *
     * Ví dụ:
     * ```php
     *      $gateway->setClient(['token' => 123456]);
     *
     *      if ($verifiedData = $gateway->verifyRequest('IPN')) {
     *         print_r($verifiedData->get());
     *      }
     * ```
     *
     * @param int|string $command Lệnh xác minh được yêu cầu thực hiện
     * @param int|string|null $clientId Client id được yêu cầu sử dụng để xác minh.
     * Nếu không thiết lập tham trị này, client sẽ được chỉ định lấy từ [[getDefaultClient()]].
     * @param \yii\web\Request|null $request Đối tượng `request` dùng để lấy dữ liệu cần xác minh.
     * Nếu không thiết lập tham trị này, request sẽ được chỉ định lấy từ [[Yii::$app->get('request')]].
     * @return bool|DataInterface Sẽ trả về FALSE nếu như dữ liệu không hợp lệ ngược lại sẽ trả về thông tin đơn hàng đã được xác thực.
     */
    public function verifyRequest($command, $clientId = null, \yii\web\Request $request = null);


    /**
     * Phương thức trả về danh sách các lệnh được hổ trợ.
     * Phương thức [[verifyRequest()]] sẽ gọi để kiếm trả lệnh có được hổ trợ hay không.
     *
     * @return array Danh sách các lệnh được hở trợ xác minh tính hợp lệ của dữ liệu.
     */
    public function verifyRequestCommands(): array;
}
