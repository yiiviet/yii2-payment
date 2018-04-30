<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use Yii;

use yii2vn\payment\ResponseData as BaseResponseData;

/**
 * Class ResponseData
 *
 * @property Merchant $merchant
 * @property null|string $errorMessage
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class ResponseData extends BaseResponseData
{

    /**
     * @inheritdoc
     */
    public function getIsOk(): bool
    {
        return !$this->canGetProperty('error_code');
    }

    /**
     * @return null|string
     */
    public function getErrorMessage(): ?string
    {
        if ($this->canGetProperty('error')) {
            return Yii::t('yii2vn/payment/baokim', $this->error);
        } else {
            return null;
        }
    }
}