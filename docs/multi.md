# Tích hợp đồng thời nhiều cổng thanh toán

Để có thể tích hợp được nhiều cổng thanh toán thì bạn phải hiểu rõ về các phương thức: `purchase`, `queryDR`, `verifyRequestIPN`, `verifyRequestPurchaseSuccess`
ở các cổng thanh toán, nếu chưa thì mời bạn quay lại tìm hiểu tại [đây](README.md).

Ở bài viết này chúng tôi sẽ giới thiệu với bạn cách tạo `collection` cho các cổng thanh toán.
Để tạo một `collection` gồm nhiều cổng thanh toán thì bạn hãy khai báo đối tượng thuộc lớp
`\yiiviet\payment\PaymentGatewayCollection` vào component của `app` trong file `web.php` ở
thư mục `config` như sau:

* Đối với môi trường `test`

```php
    'components' => [
        'paymentGateways' => [
             'class' => 'yiiviet\payment\PaymentGatewayCollection',
             'gatewayConfig' => [
                 'sandbox' => true
             ],
             'gateways' => [
                 'BK' => 'yiiviet\payment\baokim\PaymentGateway',
                 'NL' => 'yiiviet\payment\nganluong\PaymentGateway',
                 'OP' => 'yiiviet\payment\onepay\PaymentGateway',
                 'VNP' => 'yiiviet\payment\vnpayment\PaymentGateway',
                 'VTC' => 'yiiviet\payment\vtcpay\PaymentGateway'
             ]
         ]
    ]    
```

* Đối với môi trường `production`

```php
    'components' => [
        'paymentGateways' => [
             'class' => 'yiiviet\payment\PaymentGatewayCollection',
             'gateways' => [
                 'BK' => [
                     'class' => 'yiiviet\payment\baokim\PaymentGateway',
                     'pro' => true, // Sử dụng phương thức PRO bạn sẽ `redirect` khách trực tiếp đến bank không thông qua Bảo Kim. Ngược lại `FALSE` thì thanh toán thông qua Bảo Kim.        
                     'client' => [
                         'merchantId' => 'Mã merchant bạn vừa đăng ký',
                         'merchantEmail' => 'Email tài khoản bảo kim của bạn',
                         'securePassword' => 'Secure password bạn vừa đăng ký',
                         'apiUser' => 'Api user bạn vừa đăng ký',
                         'apiPassword' => 'Api password bạn vừa đăng ký',
                         'privateCertificate' => 'Private certificate bạn vừa đăng ký, 
                         không cần thiết lập nếu như bạn không xài phương thức PRO',
                     ]
                 ],
                 'NL' => [
                     'class' => 'yiiviet\payment\nganluong\PaymentGateway',
                     'seamless' => FALSE, // Sử dụng phương thức thanh toán redirect về Ngân Lượng (FALSE) hoặc khách thanh toán trực tiếp trên trang của bạn không cần `redirect` (TRUE).
                     'client' => [
                         'email' => 'Email tài khoản ngân lượng của bạn',
                         'merchantId' => 'Mã merchant bạn vừa đăng ký',
                         'merchantPassword' => 'Merchant password bạn vừa đăng ký'
                     ]
                 ],
                 'OP' => [
                     'class' => 'yiiviet\payment\onepay\PaymentGateway',
                     'international' => false, //Thiết lập `FALSE` để sử dụng cổng nội địa và ngược lại là cổng quốc tế. Mặc định là `FALSE`.        
                     'client' => [
                         'accessCode' => 'Access code bạn vừa đăng ký',
                         'merchantId' => 'Mã merchant bạn vừa đăng ký',
                         'secureSecret' => 'Secure secret bạn vừa đăng ký'
                     ]
                 ],
                 'VNP' => [
                      'class' => 'yiiviet\payment\vnpayment\PaymentGateway',
                      'client' => [
                          'tmnCode' => 'TMN code bạn vừa đăng ký',
                          'hashSecret' => 'Mã hash secret bạn vừa đăng ký'
                      ]
                 ],
                 'VTC' => [
                    'class' => 'yiiviet\payment\vtcpay\PaymentGateway',
                    'client' => [
                        'business' => 'Tài khoản VTCPay bạn vừa đăng ký',
                        'merchantId' => 'Mã website bạn vừa đăng ký',
                        'secureCode' => 'Mã bảo mật bạn vừa đăng ký'
                    ]
                 ]
             ]
         ]
    ]     
```

Tùy theo số lượng cổng thanh toán mà bạn muốn sử dụng từ đó hãy tùy chỉnh cho phù hợp.

Khi đã thiết lập xong ngay lập tức bạn đã có thể truy xuất đến các cổng thanh toán
bằng cú pháp:
 
 ```php
 
 $baoKim = Yii::$app->paymentGateways->BK;
 $nganLuong = Yii::$app->paymentGateways->NL;
 $op = Yii::$app->paymentGateways->OP;
 $vnpayment = Yii::$app->paymentGateways->VNP;
 $vtc = Yii::$app->paymentGateways->VTC;
 
 ```
 
 Như bạn thấy id của `gateway` có thể dùng như là một thuộc tính (property) trên đối
 tượng `paymentGateways` component của `app`. Đây là một short code giúp bạn thao
 tác dễ dàng hơn.

## Tổng quan các phương thức (methods) của `collection`

| Tên phương thức | Mục đích |
| :-----------:  | :----: |
| **purchase** | Tạo lệnh thanh toán thông qua cổng thanh toán chỉ định.|
| **queryDR** | Tạo lệnh yêu cầu cổng thanh toán truy vấn thông tin giao dịch. |
| **verifyRequestIPN** | Kiểm tra tính hợp lệ của dữ liệu mà cổng thanh toán gửi sang khi khách hàng thanh toán thành công (VNPayment to Server). |
| **verifyRequestPurchaseSuccess** | Kiểm tra tính hợp lệ của dữ liệu mà cổng thanh toán gửi sang khi khách hàng thanh toán thành công (Client to Server). |

Cách dùng sơ lược cũng như trong hướng dẫn của từng cổng thanh toán đã giới thiệu, chỉ có
điểm khác là bạn phải chỉ định cổng thanh toán sẽ thực thi lệnh `request` hoặc xác minh tính hợp lệ
của `request`.

Ví dụ:

```php
    Yii::$app->paymentGateways->purchase([
        'order_id' => 2, 
        'total_amount' => 500000, 
        'url_success' => '/'
    ], 'BK');
    
    Yii::$app->paymentGateways->purchase([
        'bank_code' => 'VCB',
        'buyer_fullname' => 'vxm',
        'buyer_email' => 'admin@test.app',
        'buyer_mobile' => '0909113911',
        'total_amount' => 10000000,
        'order_code' => microtime(),
        'return_url' => \yii\helpers\Url::to(['order/success'])
    ], 'NL');
    
    
    Yii::$app->paymentGateways->purchase([
        'ReturnURL' => 'http://localhost/',
        'OrderInfo' => time(),
        'Amount' => 500000,
        'TicketNo' => '127.0.0.1',
        'AgainLink' => 'http://localhost/',
        'Title' => 'Hello World',
        'MerchTxnRef' => time()
    ], 'OP');
    
    
    Yii::$app->paymentGateways->purchase([
        'TxnRef' => time(),
        'OrderType' => 100000,
        'OrderInfo' => time(),
        'IpAddr' => '127.0.0.1',
        'Amount' => 1000000,
        'ReturnUrl' => 'http://localhost'
    ], 'VNP');

    Yii::$app->paymentGateways->->purchase([
        'amount' => 100000,
        'reference_number' => time()
    ], 'VTC');
```

Như bạn thấy cách sử dụng không khác gì so với từng cổng thanh toán, điểm khác duy nhất
là bạn phải chỉ định cổng thanh toán nào sẽ thực thi. Từ đó bạn có thể xây dựng 
mô hình thanh toán `dynamic` tùy theo sự tiện lợi của khách hàng mà họ sẽ tùy chọn
cổng thanh toán cho mình.
