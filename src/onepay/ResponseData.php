<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment\onepay;

use Yii;

use yiivn\payment\ResponseData as BaseResponseData;

/**
 * Class ResponseData
 *
 * @property Merchant $merchant
 * @property string $message
 * @property int $responseCode
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
        if ($this->getCommand() & (PaymentGateway::RC_QUERY_DR | PaymentGateway::RC_QUERY_DR_INTERNATIONAL)) {
            return $this->canGetProperty('vpc_DRExists') ? strcasecmp($this->vpc_DRExists, 'Y') === 0 : false;
        } else {
            return true;
        }
    }

    /**
     * Get and translate message property return from payment gateway if it exist.
     *
     * @return null|string
     */
    public function getMessage(): ?string
    {
        if ($this->canGetProperty('vpc_Message')) {
            return Yii::t('yii2vn/payment/onepay', $this->vpc_Message);
        } else {
            return null;
        }
    }

    /**
     * Detect and get response code from 2 properties vpc_ResponseCode and vpc_TxnResponseCode.
     *
     * @return int|null
     */
    public function getResponseCode(): ?int
    {
        if ($this->canGetProperty('vpc_TxnResponseCode')) {
            return $this->vpc_TxnResponseCode;
        } elseif ($this->canGetProperty('vpc_ResponseCode')) {
            return $this->vpc_ResponseCode;
        } else {
            return null;
        }
    }

}