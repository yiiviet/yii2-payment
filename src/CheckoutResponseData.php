<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use Yii;

use yii\base\BaseObject;
use yii\di\Instance;


/**
 * @package yii2vn\payment
 *
 * @property int $responseCode
 * @property string $redirectUrl
 * @property string $message
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutResponseData extends BaseObject implements CheckoutResponseDataInterface
{

    public $translateCategory = 'app';


    private $_code;

    public function getResponseCode(): int
    {
        return $this->_code;
    }

    public function setResponseCode(int $code): bool
    {
        $this->_code = $code;

        return true;
    }

    private $_message;

    public function getMessage(): string
    {
        return $this->_message;
    }

    public function setMessage(string $message): bool
    {
        $this->_message = Yii::t($this->translateCategory, $message);

        return true;
    }

    private $_redirectUrl;

    public function getRedirectUrl(): string
    {
        return $this->_redirectUrl;
    }

    public function setRedirectUrl(string $redirectUrl): bool
    {
        $this->_redirectUrl = $redirectUrl;

        return true;
    }

}