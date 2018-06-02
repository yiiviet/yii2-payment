<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use GatewayClients\ClientInterface;

/**
 * Mẫu trừu tượng PaymentClientInterface kế thừa [[ClientInterface]] bổ sung thêm các phương thức liên quan đến
 * chữ ký dữ liệu hổ trợ cho việc tạo chữ ký dữ liệu hoặc kiểm tra chữ ký từ cổng thanh toán.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface PaymentClientInterface extends ClientInterface
{

    /**
     * Phương thức dùng để tạo chữ ký dữ liệu dùng, thường được dùng khi bạn gửi dữ liệu đến cổng thanh toán,
     * cổng thanh toán sẽ kiếm tra chữ ký có hợp lệ hay không trước khi thực thi các tác vụ.
     *
     * @param string $data Chuỗi dữ liệu cần tạo chữ ký.
     * @param null|string $type Loại chữ ký muốn tạo. Nếu không thiết lập nghĩa là cổng thanh toán chỉ có mốt loại chữ ký hoặc
     * lớp thực thi có hổ trợ loại chữ ký mặc định.
     * @return string Trả về chuỗi chữ ký của dữ liệu.
     */
    public function signature(string $data, string $type = null): string;

    /**
     * Phương thức kiểm tra chữ ký có hợp lệ với dữ liệu hay không, thường được dùng để xác minh khi
     * cổng thanh toán gửi dữ liệu đến máy chủ của bạn.
     *
     * @param string $data Chuỗi dữ liệu cần được xác minh tính hợp lệ.
     * @param string $expectSignature Chữ ký dữ liệu của chuỗi dữ liệu cần được xác minh.
     * @param null|string $type Loại chữ ký dữ liệu sẽ sử dụng để xác minh. Nếu không thiết lập nghĩa là cổng thanh toán chỉ có mốt loại chữ ký hoặc
     * lớp thực thi có hổ trợ loại chữ ký mặc định.
     * @return bool Trả về TRUE nếu như chữ ký dữ liệu hợp lệ và ngược lại.
     */
    public function validateSignature(string $data, string $expectSignature, string $type = null): bool;

}
