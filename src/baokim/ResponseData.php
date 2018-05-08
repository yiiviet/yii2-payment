<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\baokim;

use Yii;

use vxm\gatewayclients\ResponseData as BaseResponseData;

/**
 * Class ResponseData
 *
 * @property PaymentClient $merchant
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
