<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace nhuluc\payment\momo;

use yii\base\InvalidConfigException;
use yii\validators\Validator;

/**
 * Lớp SignatureValidator hổ trợ xác minh chữ ký dữ liệu từ MoMo gửi sang.
 *
 * @author Nhu Luc <nguyennhuluc1990@gmail.com>
 * @since 1.0.3
 */
class SignatureValidator extends Validator
{
    /**
     * @var PaymentClient đối tượng dùng để xác minh tính hợp lệ của chữ ký dữ liệu.
     */
    public $client;

    /**
     * @var array cung cấp các attributes dùng để xác minh tính hợp lệ của chữ ký dữ liệu phản hồi từ MoMo.
     */
    public $dataSignAttributes = [];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->client === null) {
            throw new InvalidConfigException('Property `client` must be set!');
        }

        if ($this->message === null) {
            $this->message = '{attribute} is invalid!';
        }

        parent::init();
    }

    /**
     * @inheritdoc
     * @throws \yii\base\NotSupportedException
     */
    public function validateAttribute($model, $attribute)
    {
        $dataSign = [];

        foreach ($this->dataSignAttributes as $signAttribute) {
            if (isset($model[$signAttribute])) {
                $dataSign[$signAttribute] = $model[$signAttribute];
            }
        }

        $actualSignature = urldecode(http_build_query($dataSign));

        if (!$this->client->validateSignature($actualSignature, $model->$attribute ?? '')) {
            $this->addError($model, $attribute, $this->message);
        }
    }

}
