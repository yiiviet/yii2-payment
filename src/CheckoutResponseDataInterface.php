<?php
/**
 * @link http://github.com/yii2-vn/esms
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yii2vn\payment;

/**
 * Interface CheckoutResponseDataInterface
 * @package yii2vn\payment
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