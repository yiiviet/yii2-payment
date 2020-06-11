<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\nganluong;

use nhuluc\payment\BankProvider as BaseBankProvider;

/**
 * Lớp BankProvider cung cấp thông tin ngân hàng mà Ngân Lượng hổ trợ.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class BankProvider extends BaseBankProvider
{

    /**
     * @var int danh sách ngân hàng cần xuất.
     */
    public $type = PaymentGateway::PAYMENT_METHOD_ATM_ONLINE;

    /**
     * @var array danh sách thông tin ngân hàng
     */
    public static $bankList = [
        'AGB' => ['name' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn (Agribank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'BAB' => ['name' => 'Ngân hàng TMCP Bắc Á', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'BIDV' => ['name' => 'Ngân hàng Đầu tư và Phát triển Việt Nam (BIDV)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_INTERNET_BANKING]],
        'EXB' => ['name' => 'Ngân hàng TMCP Xuất Nhập Khẩu (Eximbank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_INTERNET_BANKING]],
        'MSB' => ['name' => 'Ngân hàng TMCP Hàng Hải (MariTimeBank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'STB' => ['name' => 'Ngân hàng TMCP Sài Gòn Thương Tín (Sacombank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_INTERNET_BANKING]],
        'SGB' => ['name' => 'Ngân hàng TMCP Sài Gòn Công thương', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'NVB' => ['name' => 'Ngân hàng TMCP Nam Việt (NaviBank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'PGB' => ['name' => 'Ngân Hàng TMCP Xăng Dầu Petrolimex (PGBank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'GPB' => ['name' => 'Ngân hàng TMCP Dầu Khí (GPBank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'ICB' => ['name' => 'Ngân hàng TMCP Công Thương (VietinBank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_INTERNET_BANKING, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'TCB' => ['name' => 'Ngân hàng TMCP Kỹ Thương (Techcombank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_INTERNET_BANKING]],
        'TPB' => ['name' => 'Ngân hàng TMCP Tiên Phong (TienPhong Bank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'VAB' => ['name' => 'Ngân hàng TMCP Việt Á (VietA Bank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'VIB' => ['name' => 'Ngân hàng TMCP Quốc tế (VIB)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'VCB' => ['name' => 'Ngân hàng TMCP Ngoại Thương Việt Nam (Vietcombank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_INTERNET_BANKING, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'DAB' => ['name' => 'Ngân hàng TMCP Đông Á (DongA Bank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_INTERNET_BANKING]],
        'MB' => ['name' => 'Ngân hàng TMCP Quân Đội (MB)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'ACB' => ['name' => 'Ngân hàng TMCP Á Châu (ACB)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_INTERNET_BANKING]],
        'HDB' => ['name' => 'Ngân hàng TMCP Phát Triển Nhà TP. Hồ Chí Minh (HDBank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'VPB' => ['name' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng  (VPBank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'OJB' => ['name' => 'Ngân hàng TMCP Đại Dương (OceanBank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'SHB' => ['name' => 'Ngân hàng TMCP Sài Gòn - Hà Nội ', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'SEA' => ['name' => 'Ngân hàng TMCP Đông Nam Á (SeABank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'OCB' => ['name' => 'Ngân Hàng Phương Đông Việt Nam (OCB)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'ABB' => ['name' => 'Ngân hàng TMCP An Bình', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE, PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'NAB' => ['name' => 'Ngân hàng Nam Á (NamABank)', 'types' => [PaymentGateway::PAYMENT_METHOD_ATM_ONLINE]],
        'SCB' => ['name' => 'Ngân hàng Thương Mại Cổ Phần Sài Gòn - Saigon Commercial Bank', 'types' => [PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'IVB' => ['name' => 'Ngân hàng trách nhiệm hữu hạn Indovina', 'types' => [PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'WCP' => ['name' => 'WeChat Pay', 'types' => [PaymentGateway::PAYMENT_METHOD_QR_CODE]],
        'VIETTELPOST' => ['name' => 'Viettel post', 'types' => [PaymentGateway::PAYMENT_METHOD_CASH_IN_SHOP]],
    ];

    /**
     * @inheritdoc
     */
    public function banks(): array
    {
        $banks = [];

        foreach (static::$bankList as $id => $info) {
            if (in_array($this->type, $info['types'], true) || $this->type === null) {
                $banks[$id] = $info['name'];
            }
        }

        return $banks;
    }


}
