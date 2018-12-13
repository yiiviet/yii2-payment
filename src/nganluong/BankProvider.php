<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\nganluong;

/**
 * Lớp BankProvider cung cấp thông tin ngân hàng mà Ngân Lượng hổ trợ.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
class BankProvider extends \yiiviet\payment\BankProvider
{
    /**
     * Loại ngân hàng giao dịch tự động trực tiếp (phổ biến).
     */
    const TYPE_ATM_ONLINE = 1;

    /**
     * Loại ngân hàng hổ trợ chuyển khoản internet banking.
     */
    const TYPE_IB_ONLINE = 2;

    /**
     * Loại ngân hàng hổ trợ qrcode.
     */
    const TYPE_QRCODE = 3;

    /**
     * Loại ngân hàng thu hộ khi giao hàng.
     */
    const TYPE_CASH_IN_SHOP = 4;

    /**
     * @var array danh sách thông tin ngân hàng
     */
    public static $bankList = [
        'AGB' => ['name' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn (Agribank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_QRCODE]],
        'BAB' => ['name' => 'Ngân hàng TMCP Bắc Á', 'types' => [self::TYPE_ATM_ONLINE]],
        'BIDV' => ['name' => 'Ngân hàng Đầu tư và Phát triển Việt Nam (BIDV)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_IB_ONLINE]],
        'EXB' => ['name' => 'Ngân hàng TMCP Xuất Nhập Khẩu (Eximbank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_IB_ONLINE]],
        'MSB' => ['name' => 'Ngân hàng TMCP Hàng Hải (MariTimeBank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_QRCODE]],
        'STB' => ['name' => 'Ngân hàng TMCP Sài Gòn Thương Tín (Sacombank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_IB_ONLINE]],
        'SGB' => ['name' => 'Ngân hàng TMCP Sài Gòn Công thương', 'types' => [self::TYPE_ATM_ONLINE]],
        'NVB' => ['name' => 'Ngân hàng TMCP Nam Việt (NaviBank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_QRCODE]],
        'PGB' => ['name' => 'Ngân Hàng TMCP Xăng Dầu Petrolimex (PGBank)', 'types' => [self::TYPE_ATM_ONLINE]],
        'GPB' => ['name' => 'Ngân hàng TMCP Dầu Khí (GPBank)', 'types' => [self::TYPE_ATM_ONLINE]],
        'ICB' => ['name' => 'Ngân hàng TMCP Công Thương (VietinBank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_IB_ONLINE, self::TYPE_QRCODE]],
        'TCB' => ['name' => 'Ngân hàng TMCP Kỹ Thương (Techcombank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_IB_ONLINE]],
        'TPB' => ['name' => 'Ngân hàng TMCP Tiên Phong (TienPhong Bank)', 'types' => [self::TYPE_ATM_ONLINE]],
        'VAB' => ['name' => 'Ngân hàng TMCP Việt Á (VietA Bank)', 'types' => [self::TYPE_ATM_ONLINE]],
        'VIB' => ['name' => 'Ngân hàng TMCP Quốc tế (VIB)', 'types' => [self::TYPE_ATM_ONLINE]],
        'VCB' => ['name' => 'Ngân hàng TMCP Ngoại Thương Việt Nam (Vietcombank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_IB_ONLINE, self::TYPE_QRCODE]],
        'DAB' => ['name' => 'Ngân hàng TMCP Đông Á (DongA Bank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_IB_ONLINE]],
        'MB' => ['name' => 'Ngân hàng TMCP Quân Đội (MB)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_QRCODE]],
        'ACB' => ['name' => 'Ngân hàng TMCP Á Châu (ACB)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_IB_ONLINE]],
        'HDB' => ['name' => 'Ngân hàng TMCP Phát Triển Nhà TP. Hồ Chí Minh (HDBank)', 'types' => [self::TYPE_ATM_ONLINE]],
        'VPB' => ['name' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng  (VPBank)', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_QRCODE]],
        'OJB' => ['name' => 'Ngân hàng TMCP Đại Dương (OceanBank)', 'types' => [self::TYPE_ATM_ONLINE]],
        'SHB' => ['name' => 'Ngân hàng TMCP Sài Gòn - Hà Nội ', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_QRCODE]],
        'SEA' => ['name' => 'Ngân hàng TMCP Đông Nam Á (SeABank)', 'types' => [self::TYPE_ATM_ONLINE]],
        'OCB' => ['name' => 'Ngân Hàng Phương Đông Việt Nam (OCB)', 'types' => [self::TYPE_ATM_ONLINE]],
        'ABB' => ['name' => 'Ngân hàng TMCP An Bình', 'types' => [self::TYPE_ATM_ONLINE, self::TYPE_QRCODE]],
        'NAB' => ['name' => 'Ngân hàng Nam Á (NamABank)', 'types' => [self::TYPE_ATM_ONLINE]],
        'SCB' => ['name' => 'Ngân hàng Thương Mại Cổ Phần Sài Gòn - Saigon Commercial Bank', 'types' => [self::TYPE_QRCODE]],
        'IVB' => ['name' => 'Ngân hàng trách nhiệm hữu hạn Indovina', 'types' => [self::TYPE_QRCODE]],
        'WCP' => ['name' => 'WeChat Pay', 'types' => [self::TYPE_QRCODE]],
        'VIETTELPOST' => ['name' => 'Viettel post', 'types' => [self::TYPE_CASH_IN_SHOP]],
    ];

    /**
     * @var int danh sách ngân hàng cần xuất.
     */
    public $type = self::TYPE_ATM_ONLINE;

    /**
     * @inheritdoc
     */
    public function banks(): array
    {
        $banks = [];

        foreach (static::$bankList as $id => $info) {
            if (in_array($this->type, $info['types'], true)) {
                $banks[$id] = $info['name'];
            }
        }

        return $banks;
    }


}
