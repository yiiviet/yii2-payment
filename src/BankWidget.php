<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment;

use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * Lớp BaseWidget hổ trợ render input danh sách ngân hàng cho user chọn (dropdown list).
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class BankWidget extends InputWidget
{

    use GatewayBankProviderTrait;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->hasModel()) {

            return Html::activeDropDownList($this->model, $this->attribute, $this->provider->banks(), $this->options);
        } else {

            return Html::dropDownList($this->name, $this->value, $this->provider->banks(), $this->options);
        }
    }

}
