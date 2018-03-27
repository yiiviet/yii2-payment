<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

/**
 * Interface CheckoutResponseDataInterface
 *
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
interface CheckoutResponseDataInterface
{

    public function getResponseCode(): int;

    public function setResponseCode(int $code): bool;

    public function getMessage(): string;

    public function setMessage(string $message): bool;

    public function getRedirectUrl(): string;

    public function setRedirectUrl(string $redirectUrl): bool;

}