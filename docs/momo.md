# Cổng thanh toán MoMo

**Nếu như bạn đang muốn test thử thì xin vui lòng bỏ qua bước bên dưới và đi tới phần thiết lập.**

Đầu tiên mời bạn đăng ký tích hợp tại cổng thanh toán [MoMo](https://business.momo.vn).
Sau khi tích hợp xong bạn sẽ được MoMo cấp các dữ liệu sau đây.

| Tên dữ liệu | Kiểu |
| :-----------: | :----: |
| Partner Code | string |
| Access Key | string |
| Secret Key | string |

## Thiết lập

Thiết lập vào mảng `components` ở file `web.php` trong thư mục `config` của app với các cấu hình sau:

* Cấu hình dành cho test:

```php
'components' => [
    'momoGateway' => [
        'class' => 'yiiviet\payment\momo\PaymentGateway',
        'sandbox' => true
    ]
]

```

* Cấu hình khi chạy chính thức:

```php
'components' => [
    'momoGateway' => [
        'class' => 'yiiviet\payment\momo\PaymentGateway',
        'client' => [
            'partnerCode' => 'Partner code bạn vừa đăng ký',
            'accessKey' => 'Access key bạn vừa đăng ký',
            'secretKey' => 'Secret key bạn vừa đăng ký'
        ]
    ]
]

```

Khi đã thiết lập xong ngay lập tức bạn đã có thể truy xuất đến cổng thanh toán MoMo
bằng cú pháp `Yii::$app->momoGateway`.

## Tổng quan các phương thức (methods)

| Tên phương thức | Mục đích |
| :-----------:  | :----: |
| **purchase** | Tạo lệnh thanh toán thông qua MoMo.|
| **queryDR** | Tạo lệnh yêu cầu truy vấn thông tin giao dịch. |
| **refund** | Yêu cầu MoMo hoàn tiền lại cho đon hàng. |
| **queryRefund** | Truy vấn trạng thái hoàn tiền đã tạo lệnh trước đó. |
| **verifyRequestIPN** | Kiểm tra tính hợp lệ của dữ liệu mà MoMo gửi sang khi khách hàng thanh toán thành công (MoMo to Server). |
| **verifyRequestPurchaseSuccess** | Kiểm tra tính hợp lệ của dữ liệu mà MoMo gửi sang khi khách hàng thanh toán thành công (Client to Server). |


## Phương thức `purchase`

* Cách sử dụng cơ bản:

```php
    $responseData = Yii::$app->momoGateway->purchase([
        'amount' => 100000,
        'orderId' => time(),
        'requestId' => time(),
        'returnUrl' => 'http://localhost',
        'notifyUrl' => 'http://localhost/notify'
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| orderId | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| amount | **có** | string | Số tiền của đơn hàng. |
| requestId | **có** | string | Mã unique cho mỗi lần tạo request đến MoMo. |
| returnUrl | **có** | string | Đường dẫn MoMo sẽ dẫn khách về hệ thống của bạn khi giao dịch kết thúc. |
| notifyUrl | **có** | string | Đường dẫn MoMo sẽ bắn request về hệ thống của bạn khi giao dịch kết thúc (IPN). |
| orderInfo | không | string | Mô tả ngắn về đơn hàng. |
| extraData | không | string | Thông tin kèm theo của đơn hàng ví dụ json data. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| :-----------: | :----: | :------: | ---- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| message | **có** | string | Thông báo (eng). |
| localMessage | **có** | string | Thông báo (vi). |
| errorCode | **có** | string | Mã trạng thái. |
| payUrl | không | string | Đường dẫn thanh toán MoMo. Bạn sẽ `redirect` khách đến đường dẫn để khách thực hiện thanh toán. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| amount | không | double | Số tiền của đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| orderId | không | string | Mã đơn hàng tại hệ thống. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |

* Code hoàn chỉnh:

```php
    $responseData = Yii::$app->momoGateway->purchase([
        'amount' => 100000,
        'orderId' => time(),
        'requestId' => time(),
        'returnUrl' => 'http://localhost',
        'notifyUrl' => 'http://localhost/notify'
    ]);

    if ($responseData->isOk && $responseData->errorCode == 0) {
        Yii::$app->response->redirect($result->payUrl);
    } else {
        print $responseData->message;
    }
    
``` 
 
## Phương thức `queryDR`

Phương thức này cho bạn truy vấn thông tin giao dịch từ MoMo thông qua `orderId` mà bạn tạo ra ở
 phương thức `purchase` phía trên. 
 
* Cách truy vấn thông tin cơ bản:

```php

    $responseData = Yii::$app->momoGateway->queryDR([
        'orderId' => 123,
        'requestId' => time()
    ]);    
    
```

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| ----------- | :----: | :----: | ------ |
| orderId | **có** | string | Mã đơn hàng trên hệ thống của bạn |
| requestId | **có** | string | Mã unique request khi tạo lệnh. |


* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| partnerCode | **có** | string | Partner code của client đã dùng để tạo thanh toán. |
| accessKey | **có** | string | Access key của client đã dùng để tạo thanh toán. |
| errorCode | **có** | string | Mã trạng thái. |
| message | **có** | string | Thông báo (eng) |
| localMessage | **có** | string | Thông báo (vi) |
| requestId | không | mixed | Mã unique request id khi tạo lệnh purchase. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| amount | không | double | Số tiền đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| orderId | không | string | Mã đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| transId | không | string | Mã giao dịch tại MoMo. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| payType | không | string | Hình thức thanh toán (qr hoặc web). Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| extraData | không | string | Dữ liệu liên quan đến đơn hàng được thiết lập khi tạo lệnh purchase. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |


* Cách truy vấn thông tin hoàn chỉnh:

```php

    $responseData = Yii::$app->momoGateway->queryDR([
        'orderId' => 123,
        'requestId' => time()
    ]);    

    if ($responseData->isOk && $responseData->errorCode == 0) {
        // thực hiện nghiệm vụ tùy theo mục đích hoàn trả của bạn
        $momoTransID = $result->transId;

    } else {
    
        print $responseData->message;          
    }
    
```

 
 ## Phương thức `refund`
 
* Cách sử dụng cơ bản:

```php
    $responseData = Yii::$app->momoGateway->refund([
        'orderId' => 'mã đơn hàng tại hệ thống của bạn',
        'transId' => 'mã giao dịch tại MoMo',
        'amount' => 'số tiền',
        'requestId' => 'mã request tạo lệnh'
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| orderId | **có** | mixed | Mã đơn hàng tại hệ thống của bạn muốn hoàn tiền. |
| transId | **có** | int | Mã giao dịch tại MoMo của đơn hàng đó (nhận tại IPN và purchase success url). |
| amount | **có** | string | Số tiền muốn hoàn lại. |
| requestId | **có** | string | Mã unique request khi tạo lệnh. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| partnerCode | **có** | string | Partner code của client đã dùng để tạo thanh toán. |
| accessKey | **có** | string | Access key của client đã dùng để tạo thanh toán. |
| errorCode | **có** | string | Mã trạng thái. |
| message | **có** | string | Thông báo (eng) |
| localMessage | **có** | string | Thông báo (vi) |
| requestId | không | mixed | Mã unique request id khi tạo lệnh purchase. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| orderId | không | string | Mã đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| transId | không | string | Mã giao dịch tại MoMo. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |


* Code hoàn chỉnh:

```php
    $responseData = Yii::$app->momoGateway->refund([
        'orderId' => 'mã đơn hàng tại hệ thống của bạn',
        'transId' => 'mã giao dịch tại MoMo',
        'amount' => 'số tiền',
        'requestId' => 'mã request tạo lệnh'
    ]);

    if ($responseData->isOk && $responseData->errorCode == 0) {
        // thực hiện nghiệm vụ tùy theo mục đích hoàn trả của bạn
        $momoTransID = $result->transId;

    } else {
    
        print $responseData->message;          
    }
    
``` 
 
## Phương thức `verifyRequestPurchaseSuccess`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
MoMo gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `returnUrl` trong `purchase`, sau khi phương thức
 này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra trạng thái 
 giao dịch, từ đó hiển thị thông báo thành công hoặc thất bại...

* Cách sử dụng:

```php
    if ($verifiedData = Yii::$app->momoGateway->verifyRequestPurchaseSuccess()) {
        
        if ($verifiedData->errorCode == 0) {            
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

* Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải MoMo)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ MoMo,
bảng thuộc tính:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| partnerCode | **có** | string | Partner code của client đã dùng để tạo thanh toán. |
| accessKey | **có** | string | Access key của client đã dùng để tạo thanh toán. |
| errorCode | **có** | string | Mã trạng thái. |
| message | **có** | string | Thông báo (eng) |
| localMessage | **có** | string | Thông báo (vi) |
| requestId | không | mixed | Mã unique request id khi tạo lệnh purchase. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| amount | không | double | Số tiền đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| responseTime | không | int | Thời gian phản hồi |
| orderId | không | string | Mã đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| transId | không | string | Mã giao dịch tại MoMo. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| payType | không | string | Hình thức thanh toán (qr hoặc web). Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| extraData | không | string | Dữ liệu liên quan đến đơn hàng được thiết lập khi tạo lệnh purchase. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |


> Bạn có thể sử dụng `VerifyFilter` behavior để đảm nhiệm việc xác minh tính hợp lệ của dữ liệu trước
> khi action trong controller diễn ra nhằm đơn giản hóa nghiệp vụ xử lý. Kham khảo tài liệu tại [đây](verifyfilter.md)

## Phương thức `verifyRequestIPN`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
MoMo gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `notifyUrl` trong `purchase`, sau khi phương thức
 này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra trạng thái 
 giao dịch, từ đó cập nhật database và xử lý nghiệp vụ...

* Cách sử dụng:

```php
    
    if (($verifiedData = Yii::$app->momoGateway->verifyRequestIPN()) && $verifiedData->errorCode == 0) {
        // update database           

    } 
    
``` 

Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải MoMo)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ MoMo,
bảng thuộc tính:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| partnerCode | **có** | string | Partner code của client đã dùng để tạo thanh toán. |
| accessKey | **có** | string | Access key của client đã dùng để tạo thanh toán. |
| errorCode | **có** | string | Mã trạng thái. |
| message | **có** | string | Thông báo (eng) |
| localMessage | **có** | string | Thông báo (vi) |
| requestId | không | mixed | Mã unique request id khi tạo lệnh purchase. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| amount | không | double | Số tiền đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| responseTime | không | int | Thời gian phản hồi |
| orderId | không | string | Mã đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| transId | không | string | Mã giao dịch tại MoMo. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| payType | không | string | Hình thức thanh toán (qr hoặc web). Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |
| extraData | không | string | Dữ liệu liên quan đến đơn hàng được thiết lập khi tạo lệnh purchase. Nó chỉ tồn tại khi `isOk` là TRUE và `errorCode` bằng 0 |

> Bạn có thể sử dụng `VerifyFilter` behavior để đảm nhiệm việc xác minh tính hợp lệ của dữ liệu trước
> khi action trong controller diễn ra nhằm đơn giản hóa nghiệp vụ xử lý. Kham khảo tài liệu tại [đây](verifyfilter.md)

## Tìm hiểu về `errorCode`

* Mời bạn kham khảo tài liệu của MoMo tại [đây](https://business.momo.vn/solution/document).

## Câu hỏi thương gặp

+ Câu hỏi: Vì sao có đến 2 phương thức nhận và xác minh dữ liệu 
(`verifyRequestPurchaseSuccess`, `verifyRequestIPN`)?
    - Trả lời: vì cổng thanh toán muốn tăng sử đảm bảo cho giao dịch,
    do nếu chỉ cung cấp phương thức `verifyRequestPurchaseSuccess` thì sẽ có
    trường hợp khách hàng rớt mạng không thể `redirect` về `ReturnURL` được cho
    nên phương thức `verifyRequestIPN` được cung cấp để đảm bảo hơn do lúc này
    connection sẽ là MoMo với máy chủ của bạn tính ổn định sẽ là `99.99%`.
    
+ Câu hỏi: Vậy thì luồn xử lý sẽ ra sao nếu như có đến 2 điểm nhận thông báo 
(IPN và ReturnURL)?
    - Trả lời: với chúng tôi `action` của `ReturnUrl` chỉ dùng để xác minh tính 
    hợp lệ của dữ liệu MoMo từ đó hiển thị thanh toán thành công hoặc thất bại
    KHÔNG đụng đến phần cập nhật database và các nghiệp vụ liên quan đến cập nhật
    trạng thái đơn hàng. Phần cập nhật trạng thái và xử lý nghiệp vụ liên quan sẽ
    nằm ở `action` của `IPN`.
    
+ Câu hỏi: `IPN` là viết tắt của cụm từ gì?
    - Trả lời: `Instance Payment Notification`.
    
