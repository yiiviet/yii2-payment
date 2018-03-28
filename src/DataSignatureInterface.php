<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment;

/**
 * Interface DataSignatureInterface
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface DataSignatureInterface
{

    /**
     * @return string
     */
    public function generate(): string;

    /**
     * @param string $expect
     * @return bool
     */
    public function validate(string $expect): bool;

}