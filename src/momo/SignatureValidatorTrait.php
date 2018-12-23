<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\momo;

/**
 * Trait SignatureValidatorTrait cung cấp phương thức xác minh chữ ký dữ liệu từ MOMO.
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
trait SignatureValidatorTrait
{
    /**
     * Phương thức kiểm tra chữ ký dữ liệu có hợp lệ hay không từ MOMO gửi sang.
     *
     * @param string $attribute có giá trị là chữ ký cần kiểm tra.
     * @param array $params thiết lập từ rule
     * @param \yii\validators\InlineValidator $validator
     * @throws \yii\base\InvalidConfigException|\yii\base\NotSupportedException
     */
    public function signatureValidator($attribute, $params, \yii\validators\InlineValidator $validator)
    {
        $dataSignAttributes = $this->getDataSignAttributes();
        $data = array_merge(array_fill_keys($dataSignAttributes, ''), $this->get(false));
        $dataSign = array_intersect_key($data, array_flip($dataSignAttributes));
        $expectSignature = urldecode(http_build_query($dataSign));

        $client = $this->getClient();

        if (!$client->validateSignature($expectSignature, $expectSignature)) {
            $validator->addError($this, $attribute, $validator->message);
        }
    }

    /**
     * Phương thức hồ trợ cung cấp các attributes dùng để xác minh tính hợp lệ của chữ ký dữ liệu phản hồi từ MOMO.
     *
     * @return array attributes dùng để xác minh tính hợp lệ.
     */
    protected function getDataSignAttributes(): array
    {
        return [];
    }
}
