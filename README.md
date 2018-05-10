# YII2 Việt Nam Payment
** Yii2 Extension hổ trợ bạn tích hợp các cổng thanh toán trong nước.**

[![Latest Stable Version](https://poser.pugx.org/yiiviet/yii2-payment/v/stable)](https://packagist.org/packages/yiiviet/yii2-payment)
[![Total Downloads](https://poser.pugx.org/yiiviet/yii2-payment/downloads)](https://packagist.org/packages/yiiviet/yii2-payment)
[![Build Status](https://travis-ci.org/yiiviet/yii2-payment.svg?branch=master)](https://travis-ci.org/yiiviet/yii2-payment)
[![Code Coverage](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yiiviet/yii2-payment/?branch=master)
[![Dependency Status](https://www.versioneye.com/user/projects/5aec90130fb24f5450e02d9e/badge.svg?style=flat-square)](https://www.versioneye.com/user/projects/5aec90130fb24f5450e02d9e)
[![Yii2](https://img.shields.io/badge/Powered_by-Yii_Framework-green.svg?style=flat)](http://www.yiiframework.com/)

Extension này hiện thực thi toàn bộ 100% tính năng của các cổng thanh toán sau:
* [Bảo Kim](https://baokim.vn)
* [Ngân Lượng](https://nganluong.vn)
* [OnePay](https://onepay.vn)
* [VnPayment](https://vnpayment.vn)

## Yêu cầu hệ thống
* [PHP >= 7.1](http://php.net)
* [PHP Extension: cURL, openSSL](http://pear.php.net)
* [yiisoft/yii2 >= 2.0.13](https://github.com/yiisoft/yii2/)
* [vxm/gateway-clients >= 1.0.8](https://github.com/vuongxuongminh/yii2-gateway-clients)

## Cài đặt

Cách tốt nhất để cài đặt `yii2-extension` là thông qua [composer](http://getcomposer.org/download/).

```sh
composer require "vxm/yii2-gateway-clients"
```

hoặc thêm

```json
"yiiviet/payment": "*"
```

vào phần `require` trong file composer.json.
