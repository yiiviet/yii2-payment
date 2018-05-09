<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yiiviet\payment;

use vxm\gatewayclients\GatewayCollection;

/**
 * Lớp PaymentGatewayCollection dùng để tập hợp tất cả các cổng thanh toán thành 1 component trong app,
 * khi bạn có nhu cầu sử dụng nhiều cổng thanh toán.
 *
 * @property BasePaymentGateway $gateway
 * @property BasePaymentGateway[] $gateways
 * @method BasePaymentGateway getGateway($id)
 * @method BasePaymentGateway[] getGateways()
 * 
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class PaymentGatewayCollection extends GatewayCollection
{

}
