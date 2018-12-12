<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment;

use yii\base\BaseObject;
use yii\base\NotSupportedException;

/**
 * Mẫu trừu tượng BankProviderInterface cung cấp các phương thức lấy thông tin ngân hàng như tên, logo, id.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
abstract class BankProvider extends BaseObject
{

    /**
     * @var PaymentGatewayInterface xem phương thức [[__construct()]].
     * @see [[__construct()]]
     */
    protected $gateway;

    /**
     * BankProvider constructor.
     *
     * @param PaymentGatewayInterface $gateway đối tượng kết nối đến cổng thanh toán để cung cấp các thông tin cần thiết khi lấy danh sách ngân hàng.
     * @param array $config mảng thiết lập.
     */
    public function __construct(PaymentGatewayInterface $gateway, array $config = [])
    {
        $this->gateway = $gateway;
        parent::__construct($config);
    }

    /**
     * Phương thức hổ trợ lấy danh sách ngân hàng.
     *
     * @return array có khóa là mã ngân hàng và giá trị là tên ngân hàng.
     */
    abstract public function banks(): array;


    /**
     * Phương thức hổ trợ lấy logo ngân hàng thông qua id.
     *
     * @param mixed $bankId của ngân hàng.
     * @return string url logo absolute
     * @throws NotSupportedException
     */
    public function getBankLogo($bankId): string
    {
        throw new NotSupportedException('This method doesn\'t supported by default!');
    }

}
