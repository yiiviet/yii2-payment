<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment\vnpayment;

use Yii;

use yiiviet\payment\ResponseData as BaseResponseData;

/**
 * Class ResponseData
 *
 * @property Merchant|\yiiviet\payment\PaymentClientInterface $merchant
 * @property int|null $responseCode
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
        return $this->getCommand() === PaymentGateway::RC_PURCHASE || $this->getResponseCode() === 0;
    }

    /**
     * Get a response code from response result
     *
     * @return int|null
     */
    public function getResponseCode(): ?int
    {
        if ($this->canGetProperty('vnp_ResponseCode')) {
            return (int)$this->vnp_ResponseCode;
        } else {
            return null;
        }
    }

    /**
     * Check, get and translate a message from response result.
     *
     * @return null|string
     */
    public function getMessage(): ?string
    {
        if ($this->canGetProperty('vnp_Message')) {
            return Yii::t('yii2vn/payment/vnpayment', $this->vnp_Message);
        } else {
            return null;
        }
    }
}
