<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiivn\payment;

/**
 * Class ResponseData
 *
 * @property bool $isOk
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
abstract class ResponseData extends Data
{

    /**
     * Check response data is ok or not. Ok meaning a result is valid you (transaction completed, query result is valid).
     *
     * @return bool
     */
    abstract public function getIsOk(): bool;


}