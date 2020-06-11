<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment;

use yii\base\BaseObject;

/**
 * Lớp DataSignature hổ trợ tạo và xác minh tính hợp lệ của chữ ký dữ liệu.
 * Nó thường được dùng trong phương thức [[request()]] và [[verifyRequest()]] của [[PaymentGatewayInterface]].
 *
 * @property string $data Chuỗi dữ liệu cần được ký hoặc xác minh tính hợp lệ.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0
 */
abstract class DataSignature extends BaseObject
{
    /**
     * Thuộc tính chữa dữ liệu cần ký hoặc kiểm tra.
     *
     * @var string
     * @see getData
     */
    private $_data;

    /**
     * DataSignature constructor.
     *
     * @param string $data Dữ liệu cần ký hoặc kiểm tra tính hợp lệ.
     * @param array $config Mảng cấu hình đối tượng khởi tạo.
     */
    public function __construct(string $data, array $config = [])
    {
        $this->_data = $data;
        parent::__construct($config);
    }

    /**
     * Phương thức cung cấp dữ liệu cần ký hoặc xác minh tính hợp lệ.
     * Nó sẽ được sử dụng trong phương thức [[generate()]] và [[validate()]].
     *
     * @return string
     */
    public function getData(): string
    {
        return $this->_data;
    }

    /**
     * Phương thức khởi tạo chữ ký dữ liệu từ chuỗi lấy từ [[getData()]].
     *
     * @return string
     */
    abstract public function generate(): string;

    /**
     * Phương thức kiểm tra tính hợp lệ của dữ liệu từ chuỗi lấy từ [[getData()]].
     *
     * @param string $expect Chữ ký của dữ liệu cần được xác minh.
     * @return bool Trả về TRUE nếu như chữ ký xác minh hợp lệ và ngược lại.
     */
    abstract public function validate(string $expect): bool;

}
