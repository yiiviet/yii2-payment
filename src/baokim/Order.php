<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment\baokim;

use yii2vn\payment\BaseOrder;

/**
 * Class Order
 *
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class Order extends BaseOrder
{

    /**
     * @inheritdoc
     */
    protected function dataRules(string $method): array
    {
        return [
            [['total_amount', 'order_id'], 'required'],
            [['shipping_fee', 'tax_fee'], 'default', 'value' => 0],
            [['total_amount', 'shipping_fee', 'tax_fee'], 'double'],
            [['order_description', 'order_id'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getDataInternal(string $method): array
    {
        return [
            'order_id' => (string)$this->id, // because usually we use an order id with number type
            'order_description' => $this->description,
            'shipping_fee' => $this->shippingFee,
            'tax_fee' => $this->taxFee,
            'total_amount' => $this->amount
        ];
    }

}