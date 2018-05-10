# YII2 Việt Nam Payment
**Yii2 Extension hổ trợ bạn tích hợp các cổng thanh toán trong nước.**

[![Latest Stable Version](https://poser.pugx.org/yiiviet/yii2-payment/v/stable)](https://packagist.org/packages/yiiviet/yii2-payment)
[![Total Downloads](https://poser.pugx.org/yiiviet/yii2-payment/downloads)](https://packagist.org/packages/yiiviet/yii2-payment)
[![Build Status](https://travis-ci.org/yiiviet/yii2-payment.svg?branch=master)](https://travis-ci.org/yiiviet/yii2-payment)
[![Code Coverage](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/5aec90130fb24f5450e02d9e/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5aec90130fb24f5450e02d9e)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Hiện các cổng thanh toán trong nước ta có cấu trúc API rất đa dạng và ít có điểm chung,
khiến cho việc chúng ta xây dựng `api-client` cũng gặp nhiều khó khăn chính vì vậy 
extension này được sinh ra nhằm `đồng bộ các phương thức của các cổng thanh toán` giúp 
cho việc bạn tích hợp sẽ dễ dàng hơn và nó được thiết kê theo nguyên tắc 
[DRY](https://www.codehub.vn/Nguyen-Ly-DRY-Dont-Repeat-Yourself) giúp bạn tối giản lại
các tham trị khi tạo `request` lên các cổng thanh toán và chính vì tất cả các cổng thanh
toán đều có phương thức chung nên nó sẽ giúp cho việc chuyển tiếp từ cổng thanh toán 
này sang cổng thanh toán khác sẽ đơn giản hóa đối với bạn.

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
```

Các cổng thanh toán hiện extension hổ trợ:

* [Bảo Kim](https://baokim.vn)
* [Ngân Lượng](https://nganluong.vn)
* [OnePay](https://onepay.vn)
* [VnPayment](https://vnpayment.vn)


## Yêu cầu
* [PHP >= 7.1](http://php.net)
* [PHP Extension: cURL, openSSL](http://pear.php.net)
* [yiisoft/yii2 >= 2.0.13](https://github.com/yiisoft/yii2/)
* [vxm/gateway-clients >= 1.0.8](https://github.com/vuongxuongminh/yii2-gateway-clients)

## Cài đặt

Cài đặt thông qua `composer` nếu như đó là một khái niệm mới với bạn xin click vào 
[đây](http://getcomposer.org/download/) để tìm hiểu nó.

```sh
composer require "yiiviet/payment"
```

hoặc thêm

```json
"yiiviet/payment": "*"
```

vào phần `require` trong file composer.json.

## Tài liệu

* [Cổng thanh toán Bảo Kim](/docs/baokim.md)
* [Cổng thanh toán Ngân Lượng](/docs/nganluong.md)
* [Cổng thanh toán OnePay](/docs/onepay.md)
* [Cổng thanh toán VnPayment](/docs/vnpayment.md)
* [Tích hợp đồng thời nhiều cổng thanh toán](/docs/multi.md)
