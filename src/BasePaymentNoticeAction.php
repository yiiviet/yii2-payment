<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use Yii;

use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * Class BasePaymentNoticeAction
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BasePaymentNoticeAction extends Action
{

    const EVENT_BEFORE_VERIFIED = 'beforeVerified';

    const EVENT_AFTER_VERIFIED = 'afterVerified';

    /**
     * @var \yii\web\Request
     */
    public $request;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->request = Instance::ensure($this->request, 'yii\web\Request');

        parent::init();
    }

    /**
     * @return bool
     * @throws InvalidConfigException
     */
    public function beforeRun()
    {
        $this->trigger(static::EVENT_BEFORE_VERIFIED, $event = Yii::createObject([
            'class' => PaymentNoticeEvent::class,
            'request' => $this->request
        ]));

        return $event->isValid;
    }

    /**
     * @throws InvalidConfigException
     */
    public function afterRun()
    {
        $this->trigger(static::EVENT_AFTER_VERIFIED, Yii::createObject([
            'class' => PaymentNoticeEvent::class,
            'request' => $this->request,
            'verifiedData' => $this->getVerifiedData()
        ]));
    }

    /**
     * @return bool|string|array
     */
    abstract protected function getVerifiedData();

}