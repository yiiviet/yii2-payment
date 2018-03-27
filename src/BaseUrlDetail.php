<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\base\BaseObject;
use yii\helpers\Url;

/**
 * Class BaseUrlDetail
 * @package yii2vn\payment
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseUrlDetail extends BaseObject implements CheckoutDataInterface
{

    public $urlSuccess;

    public $urlCancel;

    public $urlDetail;

    public function init()
    {
        if ($this->urlSuccess) {
            $this->urlSuccess = $this->ensureUrl($this->urlSuccess);
        }

        if ($this->urlCancel) {
            $this->urlCancel = $this->ensureUrl($this->urlCancel);
        }

        if ($this->urlDetail) {
            $this->urlDetail = $this->ensureUrl($this->urlDetail);
        }

        parent::init();
    }

    private function ensureUrl($urlSuccess, $schema = "http")
    {
        return Url::to($urlSuccess, $schema);
    }

}