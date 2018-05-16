# Cổng thanh toán VNPayment

**Nếu như bạn đang muốn test thử thì xin vui lòng bỏ qua bước bên dưới và đi tới phần thiết lập.**

Đầu tiên mời bạn đăng ký tích hợp tại cổng thanh toán [VNPayment](https://www.vnpayment.vn).
Sau khi tích hợp xong bạn sẽ được VNPayment cấp các dữ liệu sau đây.

| Tên dữ liệu | Kiểu |
| :-----------: | :----: |
| TMN Code | string |
| Hash Secret | string |

## Thiết lập

Thiết lập vào mảng `components` ở file `web.php` trong thư mục `config` của app với các cấu hình sau:

* Cấu hình dành cho test:

```php
'components' => [
    'VNPGateway' => [
        'class' => 'yiiviet\payment\vnpayment\PaymentGateway',
        'sandbox' => true
    ]
]

```

* Cấu hình khi chạy chính thức:

```php
'components' => [
    'VNPGateway' => [
        'class' => 'yiiviet\payment\vnpayment\PaymentGateway',
        'client' => [
            'tmnCode' => 'TMN code bạn vừa đăng ký',
            'hashSecret' => 'Mã hash secret bạn vừa đăng ký'
        ]
    ]
]

```

Khi đã thiết lập xong ngay lập tức bạn đã có thể truy xuất đến cổng thanh toán VNPayment
bằng cú pháp `Yii::$app->VNPGateway`.

## Tổng quan các phương thức (methods)

| Tên phương thức | Mục đích |
| :-----------:  | :----: |
| **purchase** | Tạo lệnh thanh toán thông qua VNPayment.|
| **queryDR** | Tạo lệnh yêu cầu truy vấn thông tin giao dịch. |
| **verifyRequestIPN** | Kiểm tra tính hợp lệ của dữ liệu mà VNPayment gửi sang khi khách hàng thanh toán thành công (VNPayment to Server). |
| **verifyRequestPurchaseSuccess** | Kiểm tra tính hợp lệ của dữ liệu mà VNPayment gửi sang khi khách hàng thanh toán thành công (Client to Server). |
| **refund** | Yêu cầu VNPayment hoàn tiền lại cho đon hàng. |


## Phương thức `purchase`

* Cách sử dụng cơ bản:

```php
    $result = Yii::$app->VNPGateway->purchase([
        'TxnRef' => time(),
        'OrderType' => 100000,
        'OrderInfo' => time(),
        'IpAddr' => '127.0.0.1',
        'Amount' => 1000000,
        'ReturnUrl' => 'http://localhost'
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| TxnRef | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| OrderInfo | **có** | mixed | Mã đơn hàng hoặc là mô tả ngắn. |
| OrderType | **có** | mixed | Mã hàng hóa trong list của VNPayment. |
| Amount | **có** | int | Số tiền của đơn hàng. |
| ReturnUrl | **có** | string | Đường dẫn VNPayment sẽ dẫn khách về hệ thống của bạn khi giao dịch kết thúc. |
| BankCode | không | string | Mã ngân hàng khách sẽ thanh toán. |
| CreateDate | không | string | Ngạy tạo đơn hàng (Ymdhis). Nếu không thiết lập sẽ lấy thời gian hiện tại. |
| IpAddr | không | string | IP của khách. Nếu không thiết lập và ở môi trường web app, hệ thống sẽ tự động xác định IP. |
| Locale | không | string | Ngôn ngữ và giao diện thanh toán, có 2 giá trị `en` và `vn`. Mặc định là `vn`. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| :-----------: | :----: | :------: | ---- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| redirect_url | **có** | string | Đường dẫn thanh toán VNPayment. Bạn sẽ `redirect` khách đến đường dẫn để khách thực hiện thanh toán. |

* Code hoàn chỉnh:

```php
    $result = Yii::$app->VNPGateway->purchase([
        'TxnRef' => time(),
        'OrderType' => 100000,
        'OrderInfo' => time(),
        'IpAddr' => '127.0.0.1',
        'Amount' => 1000000,
        'ReturnUrl' => 'http://localhost'
    ]);

    if ($result->isOk) {
        Yii::$app->response->redirect($result->redirect_url);
    } 
    
``` 

## Phương thức `queryDR`

Phương thức này cho bạn truy vấn thông tin giao dịch từ VNPayment thông qua `TxnRef` mà bạn tạo ra ở
 phương thức `purchase` phía trên. 
 
* Cách truy vấn thông tin cơ bản:

```php

    $responseData = Yii::$app->VNPGateway->queryDR([
        'TxnRef' => 123,
        'IpAddr' => '127.0.0.1',
        'OrderInfo' => time(),
        'TransDate' => date('Ymdhis'),
        'TransactionNo' => 123,
    ]);    

    if ($responseData->isOk) {
        // code thêm vào đây tùy theo mục đích của bạn.
    }
    
```

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| ----------- | :----: | :----: | ------ |
| TxnRef | **có** | string | Mã đơn hàng trên hệ thống của bạn |
| TransDate | **có** | string | Ngày mà đơn hàng của bạn được tạo (Ymdhis). |
| OrderInfo | **có** | string | Mô tả thông tin yêu cầu (truy vấn đề làm gì?) |
| TransactionNo | **có** | string | Mã giao dịch của đơn hàng của bạn trên hệ thống VNPayment |
| CreateDate | không | string | Thời gian tạo yêu cầu truy vấn (Ymdhis). Nếu không thiết lập hệ thống sẽ lấy thời gian hiện tại. |
| IpAddr | không | string | IP của khách. Nếu không thiết lập và ở môi trường web app, hệ thống sẽ tự động xác định IP. |


* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| TmnCode | không | string | TMN code của client đã dùng để tạo thanh toán. Nó chỉ tồn tại khi `isOk` là TRUE |
| Amount | không | float | Số tiền đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE |
| TxnRef | không | mixed | Mã đơn hàng trên hệ thống của bạn. Nó chỉ tồn tại khi `isOk` là TRUE |
| OrderInfo | không | mixed | Mô tả đơn hàng trên hệ thống của bạn. Nó chỉ tồn tại khi `isOk` là TRUE |
| TransactionNo | không | mixed | Mã giao dịch tại VNPayment. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| BankCode | không | mixed | Mã ngân hàng khách đã dùng để thanh toán. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| PayDate | không | mixed | Thời gian khách hoàn thành thanh toán. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| TransactionNo | không | mixed | Mã giao dịch trên VNPayment. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| TransactionType | không | mixed | Hình thức giao dịch (`01` giao dịch thanh toán, `02` giao dịch hoàn trả toàn phần, `03` giao dịch hoàn trả một phần). Nó chỉ tồn tại khi `isOk` là TRUE |
| TransactionStatus | không | mixed | Trạng thái giao dịch chi tiết. Nó chỉ tồn tại khi `isOk` là TRUE |
| ResponseCode | không | mixed | Trạng thái giao dịch. Giá trị `0` nghĩa là giao dịch thành công, còn lại là thất bại, [xem chi tiết](https://sandbox.vnpayment.vn/apis/docs/bang-ma-loi/) |


* Cách truy vấn thông tin hoàn chỉnh:

```php

    $responseData = Yii::$app->VNPGateway->queryDR([
        'MerchTxnRef' => 'abc'
    ]);    

    if ($responseData->isOk && $responseData->TransactionStatus == 0) {
        // code thêm vào đây tùy theo mục đích của bạn khi giao dịch thành công.
    }
    
```
 
 ## Phương thức `refund`
 
 
* Cách sử dụng cơ bản:

```php
    $result = Yii::$app->VNPGateway->refund([
        'TxnRef' => 123,
        'Amount' => 100000,
        'IpAddr' => '127.0.0.1',
        'OrderInfo' => time(),
        'TransDate' => date('Ymdhis'),
        'TransactionNo' => 123,
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| TxnRef | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| Amount | **có** | int | Số tiền muốn hoàn trả. |
| OrderInfo | **có** | string | Mô tả đơn hàng của bạn. |
| TransDate | **có** | string | Ngày mà đơn hàng của bạn được tạo (Ymdhis). |
| TransactionNo | **có** | string | Mã đơn hàng trên hệ thống VNPayment. |
| IpAddr | không | string | IP của khách. Nếu không thiết lập và ở môi trường web app, hệ thống sẽ tự động xác định IP. |
| CreateDate | không | string | Ngạy tạo đơn hàng (Ymdhis). Nếu không thiết lập sẽ lấy thời gian hiện tại. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| TmnCode | không | string | TMN code của client đã dùng để tạo thanh toán. Nó chỉ tồn tại khi `isOk` là TRUE |
| Amount | không | float | Số tiền đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE |
| TxnRef | không | mixed | Mã đơn hàng trên hệ thống của bạn. Nó chỉ tồn tại khi `isOk` là TRUE |
| OrderInfo | không | mixed | Mô tả đơn hàng trên hệ thống của bạn. Nó chỉ tồn tại khi `isOk` là TRUE |
| TransactionNo | không | mixed | Mã giao dịch tại VNPayment. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| BankCode | không | mixed | Mã ngân hàng khách đã dùng để thanh toán. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| PayDate | không | mixed | Thời gian khách hoàn thành thanh toán. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| TransactionNo | không | mixed | Mã giao dịch trên VNPayment. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| TransactionType | không | mixed | Hình thức giao dịch (`01` giao dịch thanh toán, `02` giao dịch hoàn trả toàn phần, `03` giao dịch hoàn trả một phần). Nó chỉ tồn tại khi `isOk` là TRUE |
| TransactionStatus | không | mixed | Trạng thái giao dịch chi tiết. Nó chỉ tồn tại khi `isOk` là TRUE |
| ResponseCode | không | mixed | Trạng thái giao dịch. Giá trị `0` nghĩa là giao dịch thành công, còn lại là thất bại, [xem chi tiết](https://sandbox.vnpayment.vn/apis/docs/bang-ma-loi/) |

* Code hoàn chỉnh:

```php
    $result = Yii::$app->VNPGateway->refund([
        'TxnRef' => 123,
        'Amount' => 100000,
        'IpAddr' => '127.0.0.1',
        'OrderInfo' => time(),
        'TransDate' => date('Ymdhis'),
        'TransactionNo' => 123,
    ]);

    if ($result->isOk) {
        
        // thực hiện nghiệm vụ tùy theo mục đích hoàn trả của bạn
        
    } 
    
``` 
 
## Phương thức `verifyRequestPurchaseSuccess`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
VNPayment gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `ReturnUrl` trong `purchase`, sau khi phương thức
 này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra trạng thái 
 giao dịch, từ đó hiển thị thông báo thành công hoặc thất bại...

* Cách sử dụng:

```php
    if ($verifiedData = Yii::$app->VNPGateway->verifyRequestPurchaseSuccess()) {
        
        if ($result->isOk && $result->ResponseCode == 0) {            
            return $this->render('order_completed', [
              'message' => 'success'
            ]);
         } else {
            return $this->render('order_error', [
              'message' => 'order not found'
            ]);         
         }
    
    }
``` 

* Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải VNPayment)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ VNPayment,
bảng thuộc tính:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| OrderInfo | **có** | mixed | Mô tả đơn hàng của bạn. |
| TxnRef | **có** | mixed | Mã đơn hàng trên hệ thống của bạn. |
| Amount | **có** | float | Số tiền của đơn hàng. |
| TmnCode | không | string | TMN code của client đã dùng để tạo thanh toán. |
| TransactionNo | không | string | Mã giao dịch trên VNPayment. Nó chỉ tồn tại khi `ResponseCode` là `0` |
| Message | không | string | Thông báo lỗi. Nó chỉ tồn tại khi `ResponseCode` khác `0` |
| BankCode | không | string | Mã ngân hàng khách dùng để giao dịch. Nó chỉ tồn tại khi `ResponseCode` khác `0` |
| BankTranNo | không | string | Mã giao dịch tại ngân hàng của khách đã thanh toán. Nó chỉ tồn tại khi `ResponseCode` khác `0` |
| PayDate | không | string | Thời gian khách hoàn thành thanh toán (Ymdhis). Nó chỉ tồn tại khi `ResponseCode` khác `0` |
| ResponseCode | không | mixed | Trạng thái giao dịch. Giá trị `0` nghĩa là giao dịch thành công, còn lại là thất bại, [xem chi tiết](https://sandbox.vnpayment.vn/apis/docs/bang-ma-loi/) |


## Phương thức `verifyRequestIPN`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
VNPayment gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `IPN` trên hệ thống VNPayment, sau khi phương thức
 này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra trạng thái 
 giao dịch, từ đó cập nhật database và xử lý nghiệp vụ...

* Cách sử dụng:

```php
    Yii::$app->response->format = 'json';
    
    if ($verifiedData = Yii::$app->VNPGateway->verifyRequestIPN()) {
        
        if ($verifiedData->ResponseCode === 0) {  
        
            // update database       
            return ['RspCode' => 00, 'Message' => 'Confirm Success'];       
         } 
    } else {
        return ['RspCode' => 99, 'Message' => 'Confirm Fail']; 
    }
    

``` 

Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải VNPayment)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ VNPayment,
bảng thuộc tính:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| OrderInfo | **có** | mixed | Mô tả đơn hàng của bạn. |
| TxnRef | **có** | mixed | Mã đơn hàng trên hệ thống của bạn. |
| Amount | **có** | float | Số tiền của đơn hàng. |
| TmnCode | không | string | TMN code của client đã dùng để tạo thanh toán. |
| TransactionNo | không | string | Mã giao dịch trên VNPayment. Nó chỉ tồn tại khi `ResponseCode` là `0` |
| Message | không | string | Thông báo lỗi. Nó chỉ tồn tại khi `ResponseCode` khác `0` |
| BankCode | không | string | Mã ngân hàng khách dùng để giao dịch. Nó chỉ tồn tại khi `ResponseCode` khác `0` |
| BankTranNo | không | string | Mã giao dịch tại ngân hàng của khách đã thanh toán. Nó chỉ tồn tại khi `ResponseCode` khác `0` |
| PayDate | không | string | Thời gian khách hoàn thành thanh toán (Ymdhis). Nó chỉ tồn tại khi `ResponseCode` khác `0` |
| ResponseCode | không | mixed | Trạng thái giao dịch. Giá trị `0` nghĩa là giao dịch thành công, còn lại là thất bại, [xem chi tiết](https://sandbox.vnpayment.vn/apis/docs/bang-ma-loi/) |

Sau khi xử lý nghiệm vụ tại `action` của `IPN` bạn cần phải trả về dữ liệu
cho VNPayment biết là bạn đã cập nhật đơn hàng, giúp cho VNPayment đồng bộ
trạng thái với hệ thống của bạn.

Bảng thông tin cần trả về:

| Gía trị | Mô tả |
| :-------: | ----- |
| RspCode | Trạng thái xử lý tại hệ thống của bạn. `00` nghĩa là mọi thứ tốt đẹp ngược lại bạn hãy trả về `99`. |
| Message | Mô tả lỗi xảy ra cho VNPayment biết khi `RspCode` là `99` có lỗi xảy ra. |

Kiểu dữ liệu trả về có định dạng: `json`

## Câu hỏi thương gặp

+ Câu hỏi: Vì sao có đến 2 phương thức nhận và xác minh dữ liệu 
(`verifyRequestPurchaseSuccess`, `verifyRequestIPN`)?
    - Trả lời: vì cổng thanh toán muốn tăng sử đảm bảo cho giao dịch,
    do nếu chỉ cung cấp phương thức `verifyRequestPurchaseSuccess` thì sẽ có
    trường hợp khách hàng rớt mạng không thể `redirect` về `ReturnURL` được cho
    nên phương thức `verifyRequestIPN` được cung cấp để đảm bảo hơn do lúc này
    connection sẽ là VNPayment với máy chủ của bạn tính ổn định sẽ là `99.99%`.
    
+ Câu hỏi: Vậy thì luồn xử lý sẽ ra sao nếu như có đến 2 điểm nhận thông báo 
(IPN và ReturnURL)?
    - Trả lời: với chúng tôi `action` của `ReturnUrl` chỉ dùng để xác minh tính 
    hợp lệ của dữ liệu VNPayment từ đó hiển thị thanh toán thành công hoặc thất bại
    KHÔNG đụng đến phần cập nhật database và các nghiệp vụ liên quan đến cập nhật
    trạng thái đơn hàng. Phần cập nhật trạng thái và xử lý nghiệp vụ liên quan sẽ
    nằm ở `action` của `IPN`.
    
+ Câu hỏi: `IPN` là viết tắt của cụm từ gì?
    - Trả lời: `Instance Payment Notification`.
    
