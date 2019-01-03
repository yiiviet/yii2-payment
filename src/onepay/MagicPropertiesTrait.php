<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\onepay;

/**
 * Trait MagicPropertiesTrait là trait bổ sung phương thức getter và setter nhầm giảm hóa sự lập đi lập lại của prefix `vpc_`
 *
 * @property string|null $Message thông báo từ VNPayment.
 * @property int|null $ResponseCode mã phản hồi.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
trait MagicPropertiesTrait
{
    /**
     * @inheritdoc
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        try {
            return parent::__get($name);
        } catch (\yii\base\UnknownPropertyException $e) {
            if (isset($this["vpc_$name"])) {
                return $this["vpc_$name"];
            } else {
                throw $e;
            }
        }
    }

    /**
     * @inheritdoc
     * @throws \yii\base\UnknownPropertyException
     */
    public function __set($name, $value)
    {
        try {
            parent::__set($name, $value);
        } catch (\yii\base\UnknownPropertyException $e) {
            if (isset($this["vpc_$name"])) {
                $this["vpc_$name"] = $value;
            } else {
                throw $e;
            }
        }
    }

    /**
     * Phương thức hổ trợ lấy `vpc_Message` nhận từ OnePay.
     *
     * @return null|string Trả về NULL nếu như dữ liệu OnePay gửi về không tồn tại `vpc_Message` và ngược lại.
     */
    public function getMessage(): ?string
    {
        if (isset($this['vpc_Message'])) {
            return $this['vpc_Message'];
        } else {
            return null;
        }
    }

    /**
     * Phương thức hổ trợ lấy response code từ OnePay,
     * do tùy theo lệnh mà giá trị này nằm ở các attribute phản hồi khác nhau nên phương thức này sẽ tự động xác định.
     *
     * @return int|null Trả về NULL nếu như không có response code và ngược lại
     */
    public function getResponseCode(): ?int
    {
        if (isset($this['vpc_TxnResponseCode'])) {
            return $this['vpc_TxnResponseCode'];
        } elseif (isset($this['vpc_ResponseCode'])) {
            return $this['vpc_ResponseCode'];
        } else {
            return null;
        }
    }

}
