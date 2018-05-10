<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use yii\base\NotSupportedException;

use vxm\gatewayclients\BaseClient;

/**
 * Lớp trừu tượng BasePaymentClient thực thi [[PaymentClientInterface]] hổ trợ cho việc xây dựng các lớp kế thừa đơn giản hóa
 * việc thực thi các phương thức trừu tượng. Nó được sử dụng hổ trợ các thuộc tính cung cấp cho việc tạo dữ liệu truy vấn
 * đến cổng thanh toán và cung cấp các thông tin để truy cập được cổng thanh toán.
 *
 * @property BasePaymentGateway|PaymentGatewayInterface $gateway
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BasePaymentClient extends BaseClient implements PaymentClientInterface
{

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function signature(string $data, string $type = null): string
    {
        if ($dataSignature = $this->initDataSignature($data, $type)) {
            return $dataSignature->generate();
        } else {
            throw new NotSupportedException("Signature data with type: '$type' is not supported!");
        }
    }

    /**
     * @inheritdoc
     * @throws NotSupportedException
     */
    public function validateSignature(string $data, string $expectSignature, string $type = null): bool
    {
        if ($dataSignature = $this->initDataSignature($data, $type)) {
            return $dataSignature->validate($expectSignature);
        } else {
            throw new NotSupportedException("Validate signature with type: '$type' is not supported!");
        }
    }

    /**
     * Phương thức hổ trợ khởi tạo đối tượng [[DataSignature]] dùng cho việc tạo chữ ký dữ liệu và kiểm tra
     * tính hợp lệ của chữ ký dữ liệu.
     *
     * @param string $data Dữ liệu muốn tạo hoặc kiểm tra tính hợp lệ.
     * @param string|null $type Loại chữ ký muốn tạo hoặc sử dụng để kiếm trả tính hợp lệ. Nếu không thiết lập
     * có nghĩa là sử dụng loại chữ ký mặc định của cổng thanh toán.
     * @return null|DataSignature Trả về NULL nếu như loại chữ ký không được hổ trợ và ngược lại là đối tượng [[DataSignature]].
     */
    abstract protected function initDataSignature(string $data, string $type = null): ?DataSignature;


}
