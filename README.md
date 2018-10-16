# YII2 Việt Nam Payment
**Yii2 Extension hổ trợ bạn tích hợp các cổng thanh toán trong nước.**

[![Latest Stable Version](https://poser.pugx.org/yiiviet/yii2-payment/v/stable)](https://packagist.org/packages/yiiviet/yii2-payment)
[![Total Downloads](https://poser.pugx.org/yiiviet/yii2-payment/downloads)](https://packagist.org/packages/yiiviet/yii2-payment)
[![Build Status](https://travis-ci.org/yiiviet/yii2-payment.svg?branch=master)](https://travis-ci.org/yiiviet/yii2-payment)
[![Code Coverage](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/?branch=master)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Hiện nay các cổng thanh toán trong nước có cấu trúc API rất đa dạng và ít có điểm chung,
khiến cho việc chúng ta xây dựng các `api-client` cũng gặp nhiều khó khăn, chính vì vậy 
extension này được sinh ra nhằm `đồng bộ các phương thức của các cổng thanh toán`, giúp 
cho việc tích hợp sẽ dễ dàng hơn, nó được thiết kế theo nguyên tắc 
[DRY](https://www.codehub.vn/Nguyen-Ly-DRY-Dont-Repeat-Yourself) giúp bạn tối giản lại
các tham trị khi tạo `request` gửi lên các cổng thanh toán, chính vì tất cả các cổng thanh
toán đều có phương thức chung nên nó sẽ giúp cho bạn chuyển tiếp từ cổng thanh toán 
này sang cổng thanh toán khác đơn giản hơn.

Ví dụ:

```php

$baoKim->purchase([
    'order_id' => 2, 
    'total_amount' => 500000, 
    'url_success' => '/'
]);

$nganLuong->purchase([
    'bank_code' => 'VCB',
    'buyer_fullname' => 'vxm',
    'buyer_email' => 'admin@test.app',
    'buyer_mobile' => '0909113911',
    'total_amount' => 10000000,
    'order_code' => microtime()
]);

$onePay->purchase([
    'ReturnURL' => 'http://localhost/',
    'OrderInfo' => time(),
    'Amount' => 500000,
    'TicketNo' => '127.0.0.1',
    'AgainLink' => 'http://localhost/',
    'Title' => 'Hello World',
    'MerchTxnRef' => time()
]);

$vnPayment->purchase([
    'TxnRef' => time(),
    'OrderType' => 100000,
    'OrderInfo' => time(),
    'IpAddr' => '127.0.0.1',
    'Amount' => 1000000,
    'ReturnUrl' => 'http://localhost'
]);

$vtcPay->purchase([
    'amount' => 100000,
    'reference_number' => time()
]);
```

Các cổng thanh toán được hổ trợ:

* [Bảo Kim](https://baokim.vn)
* [Ngân Lượng](https://nganluong.vn)
* [OnePay](https://onepay.vn)
* [VnPayment](https://vnpayment.vn)
* [VTCPay](https://vtcpay.vn)

## Yêu cầu
* [PHP >= 7.1](http://php.net)
* [PHP Extension: openSSL](http://pear.php.net)
* [yiisoft/yii2 >= 2.0.13](https://github.com/yiisoft/yii2/)
* [vxm/yii2-gateway-clients >= 2.0.1](https://github.com/vuongxuongminh/yii2-gateway-clients)

## Cài đặt

Cài đặt thông qua `composer` nếu như đó là một khái niệm mới với bạn xin click vào 
[đây](http://getcomposer.org/download/) để tìm hiểu nó.

```sh
composer require "yiiviet/yii2-payment"
```

hoặc thêm

```json
"yiiviet/yii2-payment": "*"
```

vào phần `require` trong file composer.json.

## Tài liệu

* [Cổng thanh toán Bảo Kim](/docs/baokim.md).
* [Cổng thanh toán Ngân Lượng](/docs/nganluong.md).
* [Cổng thanh toán OnePay](/docs/onepay.md).
* [Cổng thanh toán VnPayment](/docs/vnpayment.md).
* [Cổng thanh toán VTCPay](/docs/vtcpay.md).
* [Tích hợp đồng thời nhiều cổng thanh toán](/docs/multi.md).
