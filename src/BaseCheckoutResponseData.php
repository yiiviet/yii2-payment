<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use Yii;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\di\Instance;


/**
 * Class BaseCheckoutResponseData
 *
 * @property int $responseCode
 * @property string $redirectUrl
 * @property string $message
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class BaseCheckoutResponseData extends BaseObject implements CheckoutResponseDataInterface
{

    /**
     * @var string|array|\yii\i18n\I18N
     */
    public $i18n;

    public $translateCategory;

    public $translateLanguage;

    private $_code;

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->i18n = Instance::ensure($this->i18n, 'yii\i18n\I18N');

        parent::init();
    }

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

    public function getMessage(): ?string
    {
        return $this->_message;
    }

    public function setMessage(?string $message): bool
    {
        $this->_message = $this->i18n->translate($this->translateCategory ?? 'app', $message, [], $this->translateLanguage ?? Yii::$app->language);

        return true;
    }

    private $_redirectUrl;

    public function getRedirectUrl(): ?string
    {
        return $this->_redirectUrl;
    }

    public function setRedirectUrl(?string $redirectUrl): bool
    {
        $this->_redirectUrl = $redirectUrl;

        return true;
    }

}