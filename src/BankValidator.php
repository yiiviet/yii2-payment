<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment;

use yii\validators\RangeValidator;

/**
 * Lớp BankValidator hổ trợ kiểm tra tính hợp lệ của mã ngân hàng gửi lên từ client side.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class BankValidator extends RangeValidator
{

    use GatewayBankProviderTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->range = array_keys($this->provider->banks());

        parent::init();
    }
}
