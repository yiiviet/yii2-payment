<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\vnpayment;

use nhuluc\payment\BankProvider as BaseBankProvider;

/**
 * Lớp BankProvider cung cấp thông tin ngân hàng mà VNPayment hổ trợ.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class BankProvider extends BaseBankProvider
{

    /**
     * @inheritdoc
     */
    public function banks(): array
    {
        return [
            'VIETCOMBANK' => 'Ngân hàng Ngoại thương (Vietcombank)',
            'VIETINBANK' => 'Ngân hàng Công thương (Vietinbank)',
            'BIDV' => 'Ngân hàng đầu tư và phát triển Việt Nam (BIDV)',
            'AGRIBANK' => 'Ngân hàng Nông nghiệp (Agribank)',
            'SACOMBANK' => 'Ngân hàng TMCP Sài Gòn Thương Tín (SacomBank)',
            'TECHCOMBANK' => 'Ngân hàng Kỹ thương Việt Nam (TechcomBank)',
            'ACB' => 'Ngân hàng ACB',
            'VPBANK' => 'Ngân hàng Việt Nam Thịnh vượng (VPBank)',
            'DONGABANK' => 'Ngân hàng Đông Á (DongABank)',
            'EXIMBANK' => 'Ngân hàng EximBank',
            'TPBANK' => 'Ngân hàng Tiên Phong (TPBank)',
            'NCB' => 'Ngân hàng Quốc dân (NCB)',
            'OJB' => 'Ngân hàng Đại Dương (OceanBank)',
            'MSBANK' => 'Ngân hàng Hàng Hải (MSBANK)',
            'HDBANK' => 'Ngan hàng HDBank',
            'NAMABANK' => 'Ngân hàng Nam Á (NamABank)',
            'OCB' => 'Ngân hàng Phương Đông (OCB)',
            'VISA' => 'Thẻ quốc tế',
            'VNMART' => 'Ví điện tử VnMart',
            'SCB' => 'Ngân hàng TMCP Sài Gòn (SCB)',
            'IVB' => 'Ngân hàng TNHH Indovina (IVB)',
            'ABBANK' => 'Ngân hàng thương mại cổ phần An Bình (ABBANK)',
            'SHB' => 'Ngân hàng Thương mại cổ phần Sài Gòn (SHB)',
            'VIB' => 'Ngân hàng Thương mại cổ phần Quốc tế Việt Nam (VIB)',
            'VNPAYQR' => 'Cổng thanh toán VNPAYQR',
            'VIETCAPITALBANK' => 'Ngân Hàng Bản Việt',
            'PVCOMBANK' => 'Ngân hàng TMCP Đại Chúng Việt Nam',
            'SAIGONBANK' => 'Ngân hàng thương mại cổ phần Sài Gòn Công Thương'
        ];
    }

}
