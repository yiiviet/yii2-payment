<?php
/**
 * @link http://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

use yii\base\BaseObject;
use yii\base\DynamicModel;
use yii\base\InvalidConfigException;

/**
 * Class BaseCheckoutData
 *
 * @author: Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class BaseCheckoutData extends BaseObject implements CheckoutDataInterface
{
    /**
     * @param string $method
     * @return array
     */
    protected function dataRules(string $method): array
    {
        return [];
    }

    /**
     * @param string $method
     * @return array
     * @throws InvalidConfigException
     */
    public function getData(string $method): array
    {
        $data = $this->getDataInternal($method);
        $model = DynamicModel::validateData($data, $this->dataRules($method));

        if ($model->hasErrors()) {
            $errors = $model->getFirstErrors();
            throw new InvalidConfigException(reset($errors));
        } else {
            return $data;
        }
    }

    abstract protected function getDataInternal(string $method): array;

}