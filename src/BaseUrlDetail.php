<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

use yii\helpers\Url;

/**
 * Class BaseUrlDetail
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseUrlDetail extends BaseCheckoutData
{

    public $defaultSchema = "http";

    public $urlSuccess;

    public $urlCancel;

    public $urlDetail;

    public $urlSuccessSchema;

    public $urlCancelSchema;

    public $urlDetailSchema;

    public function init()
    {
        if ($this->urlSuccess) {
            $this->urlSuccess = $this->ensureUrl($this->urlSuccess, $this->urlSuccessSchema ?? $this->defaultSchema);
        }

        if ($this->urlCancel) {
            $this->urlCancel = $this->ensureUrl($this->urlCancel, $this->urlCancelSchema ?? $this->defaultSchema);
        }

        if ($this->urlDetail) {
            $this->urlDetail = $this->ensureUrl($this->urlDetail, $this->urlDetailSchema ?? $this->defaultSchema);
        }

        parent::init();
    }

    protected function ensureUrl($urlSuccess, $schema)
    {
        return Url::to($urlSuccess, $schema);
    }

}