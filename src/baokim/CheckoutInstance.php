<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii2vn\payment\BaseCheckoutInstance;

/**
 * Class BaoKimCheckoutInstance
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class CheckoutInstance extends BaseCheckoutInstance
{

    public $bankPaymentMethodClass = BankPaymentMethod::class;

    public $customerClass = Customer::class;

    public $orderClass = Order::class;

    public function getData(string $method): array
    {
        switch ($method) {
            case PaymentGateway::CHECKOUT_METHOD_TEL_CARD:
                return $this->getTelCard()->getData($method);
            case PaymentGateway::CHECKOUT_METHOD_LOCAL_BANK || PaymentGateway::CHECKOUT_METHOD_INTERNET_BANKING || PaymentGateway::CHECKOUT_METHOD_CREDIT_CARD:
                $bankPaymentMethodData = $this->getBankPaymentMethod()->getData($method);
                $customerData = $this->getCustomer()->getData($method);
                $orderData = $this->getOrder()->getData($method);

                return array_merge($bankPaymentMethodData, $customerData, $orderData);
            case PaymentGateway::CHECKOUT_METHOD_BANK_TRANSFER || PaymentGateway::CHECKOUT_METHOD_ATM_TRANSFER || PaymentGateway::CHECKOUT_METHOD_BAO_KIM:
                $customerData = $this->getCustomer()->getData($method);
                $orderData = $this->getOrder()->getData($method);

                return array_merge($customerData, $orderData);
            default:
                return [];
        }
    }

}