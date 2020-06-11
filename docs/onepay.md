# Cổng thanh toán OnePay

**Nếu như bạn đang muốn test thử thì xin vui lòng bỏ qua bước bên dưới và đi tới phần thiết lập.**

Đầu tiên mời bạn đăng ký tích hợp tại cổng thanh toán [OnePay](https://www.onepay.vn).
Sau khi tích hợp xong bạn sẽ được OnePay cấp các dữ liệu sau đây.

| Tên dữ liệu | Kiểu |
| :-----------: | :----: |
| Access Code | string |
| Merchant Id | string |
| Secure Secret | string |

## Thiết lập

Thiết lập vào mảng `components` ở file `web.php` trong thư mục `config` của app với các cấu hình sau:

* Cấu hình dành cho test:

```php
'components' => [
    'OPGateway' => [
        'class' => 'nhuluc\payment\onepay\PaymentGateway',
        'international' => false, //Thiết lập `FALSE` để sử dụng cổng nội địa và ngược lại là cổng quốc tế. Mặc định là `FALSE`.
        'sandbox' => true
    ]
]

```

* Cấu hình khi chạy chính thức:

```php
'components' => [
    'OPGateway' => [
        'class' => 'nhuluc\payment\onepay\PaymentGateway',
        'international' => false, //Thiết lập `FALSE` để sử dụng cổng nội địa và ngược lại là cổng quốc tế. Mặc định là `FALSE`.        
        'client' => [
            'accessCode' => 'Access code bạn vừa đăng ký',
            'merchantId' => 'Mã merchant bạn vừa đăng ký',
            'secureSecret' => 'Secure secret bạn vừa đăng ký'
        ]
    ]
]

```

Khi đã thiết lập xong ngay lập tức bạn đã có thể truy xuất đến cổng thanh toán OnePay
bằng cú pháp `Yii::$app->OPGateway`.

## Tổng quan các phương thức (methods)

| Tên phương thức | Mục đích |
| :-----------:  | :----: |
| **purchase** | Tạo lệnh thanh toán thông qua OnePay.|
| **queryDR** | Tạo lệnh yêu cầu truy vấn thông tin giao dịch. |
| **verifyRequestIPN** | Kiểm tra tính hợp lệ của dữ liệu mà OnePay gửi sang khi khách hàng thanh toán thành công (OnePay to Server). |
| **verifyRequestPurchaseSuccess** | Kiểm tra tính hợp lệ của dữ liệu mà OnePay gửi sang khi khách hàng thanh toán thành công (Client to Server). |


## Phương thức `purchase` cổng nội địa (`international` = `FALSE`)

* Cách sử dụng cơ bản:

```php
    $responseData = Yii::$app->OPGateway->purchase([
        'ReturnURL' => 'http://localhost/',
        'OrderInfo' => time(),
        'Amount' => 500000,
        'TicketNo' => '127.0.0.1',
        'AgainLink' => 'http://localhost/',
        'Title' => 'Hello World',
        'MerchTxnRef' => time()
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| MerchTxnRef | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| OrderInfo | **có** | mixed | Mã đơn hàng hoặc là mô tả ngắn. |
| Amount | **có** | int | Số tiền của đơn hàng. |
| ReturnURL | **có** | string | Đường dẫn OnePay sẽ dẫn khách về hệ thống của bạn khi giao dịch kết thúc. |
| TicketNo | không | string | IP của khách. Nếu không thiết lập và ở môi trường web app, hệ thống sẽ tự động xác định IP. |
| AgainLink | không | string | Link thanh toán của đơn hàng. Nếu không thiết lập và ở môi trường web app, giá trị sẽ là `\yii\helpers\Url::current()`. |
| Title | không | string | Tiêu đề trang thanh toán. Nếu không thiết lập và ở môi trường web app, giá trị sẽ là `Yii::$app->view->title`. |
| Locale | không | string | Ngôn ngữ và giao diện thanh toán, có 2 giá trị `en` và `vn`. Mặc định là `vn`. |
| SHIP_Street01 | không | string | Địa chỉ gửi hàng. |
| SHIP_Provice | không | string | Quận huyện gửi hàng theo địa chỉ. |
| SHIP_City | không | string | Thành phố gửi hàng theo địa chỉ. |
| SHIP_Country | không | string | Quốc gia gửi hàng theo địa chỉ. |
| Customer_Email | không | int | Email người mua hàng. |
| Customer_Phone | không | int | Số điện thoại người mua hàng. |
| Customer_Id | không | int | Id người mua hàng trên hệ thống của bạn. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| :-----------: | :----: | :------: | ---- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| redirect_url | **có** | string | Đường dẫn thanh toán OnePay. Bạn sẽ `redirect` khách đến đường dẫn để khách thực hiện thanh toán. |

* Code hoàn chỉnh:

```php
    $responseData = Yii::$app->OPGateway->purchase([
        'ReturnURL' => 'http://localhost/',
        'OrderInfo' => time(),
        'Amount' => 500000,
        'TicketNo' => '127.0.0.1',
        'AgainLink' => 'http://localhost/',
        'Title' => 'Hello World',
        'MerchTxnRef' => time()
    ]);

    if ($responseData->isOk) {
        Yii::$app->response->redirect($responseData->redirect_url);
    } 
    
``` 

## Phương thức `purchase` cổng quốc tế (`international` = `TRUE`)

* Cách sử dụng cơ bản:

```php
    $responseData = Yii::$app->OPGateway->purchase([
        'ReturnURL' => 'http://localhost/',
        'OrderInfo' => time(),
        'Amount' => 500000,
        'TicketNo' => '127.0.0.1',
        'AgainLink' => 'http://localhost/',
        'Title' => 'Hello World',
        'MerchTxnRef' => time()
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| MerchTxnRef | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| OrderInfo | **có** | mixed | Mã đơn hàng hoặc là mô tả ngắn. |
| Amount | **có** | int | Số tiền của đơn hàng. |
| ReturnURL | **có** | string | Đường dẫn OnePay sẽ dẫn khách về hệ thống của bạn khi giao dịch kết thúc. |
| TicketNo | không | string | IP của khách. Nếu không thiết lập và ở môi trường web app, hệ thống sẽ tự động xác định IP. |
| AgainLink | không | string | Link thanh toán của đơn hàng. Nếu không thiết lập và ở môi trường web app, giá trị sẽ là `\yii\helpers\Url::current()`. |
| Title | không | string | Tiêu đề trang thanh toán. Nếu không thiết lập và ở môi trường web app, giá trị sẽ là `Yii::$app->view->title`. |
| Locale | không | string | Ngôn ngữ và giao diện thanh toán, có 2 giá trị `en` và `vn`. Mặc định là `vn`. |
| SHIP_Street01 | không | string | Địa chỉ gửi hàng. |
| SHIP_Provice | không | string | Quận huyện gửi hàng theo địa chỉ. |
| SHIP_City | không | string | Thành phố gửi hàng theo địa chỉ. |
| SHIP_Country | không | string | Quốc gia gửi hàng theo địa chỉ. |
| Customer_Email | không | int | Email người mua hàng. |
| Customer_Phone | không | int | Số điện thoại người mua hàng. |
| Customer_Id | không | int | Id người mua hàng trên hệ thống của bạn. |
| Street01 | không | string | Địa chỉ đăng ký nhận sao kê với ngân hàng. |
| City | không | string | Thành phố của địa chỉ đăng ký nhận sao kê với ngân hàng. |
| StateProv | không | string | Quận, huyện của địa chỉ đăng ký nhận sao kê với ngân hàng. |
| PostCode | không | string | Mã quận, huyện của địa chỉ đăng ký nhận sao kê với ngân hàng. |
| Country | không | string | Mã quốc gia của địa chỉ đăng ký nhận sao kê với ngân hàng (2 ký tự). |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| :-----------: | :----: | :------: | ---- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| redirect_url | **có** | string | Đường dẫn thanh toán OnePay. Bạn sẽ `redirect` khách đến đường dẫn để khách thực hiện thanh toán. |

* Code hoàn chỉnh:

```php
    $responseData = Yii::$app->OPGateway->purchase([
        'ReturnURL' => 'http://localhost/',
        'OrderInfo' => time(),
        'Amount' => 500000,
        'TicketNo' => '127.0.0.1',
        'AgainLink' => 'http://localhost/',
        'Title' => 'Hello World',
        'MerchTxnRef' => time()
    ]);

    if ($responseData->isOk) {
        Yii::$app->response->redirect($responseData->redirect_url);
    } 
    
``` 

## Phương thức `queryDR`

Phương thức này cho bạn truy vấn thông tin giao dịch từ OnePay thông qua `MerchTxnRef` mà bạn tạo ra ở
 phương thức `purchase` phía trên. Lưu ý `MerchTxnRef` của cổng quốc tế và cổng nội địa không thể dùng chung,
 tất là nếu giao dịch tạo bằng cổng nội địa thì khi check ở cổng quốc tế giá trị sẽ không tồn tại.

Cách truy vấn thông tin cơ bản:

```php

    $responseData = Yii::$app->OPGateway->queryDR([
        'MerchTxnRef' => 'abc'
    ]);    

    if ($responseData->isOk) {
        // code thêm vào đây tùy theo mục đích của bạn.
    }
    
```

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| ----------- | :----: | :----: | ------ |
| MerchTxnRef | **có** | string | `MerchTxnRef` của đơn hàng bạn tạo trên hệ thống OnePay |


* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| AdditionData | không | string | Thông tin từ OnePay. Nó chỉ tồn tại khi `isOk` là TRUE |
| Amount | không | float | Số tiền đơn hàng. Nó chỉ tồn tại khi `isOk` là TRUE |
| MerchTxnRef | không | mixed | Mã đơn hàng trên hệ thống của bạn. Nó chỉ tồn tại khi `isOk` là TRUE |
| OrderInfo | không | mixed | Mô tả đơn hàng trên hệ thống của bạn. Nó chỉ tồn tại khi `isOk` là TRUE |
| TransactionNo | không | mixed | Mã giao dịch tại OnePay. Nó chỉ tồn tại khi `isOk` là TRUE và đơn hàng giao dịch thành công |
| ResponseCode | không | mixed | Trạng thái giao dịch. Nó chỉ tồn tại khi `isOk` là TRUE |

* Bảng trạng thái giao dịch:

| Gía trị | Mô tả |
| :-------: | ----- |
| **0** | giao dịch thành công, tất cả các giá trị còn lại là thất bại |

Cách truy vấn thông tin hoàn chỉnh:

```php

    $responseData = Yii::$app->OPGateway->queryDR([
        'MerchTxnRef' => 'abc'
    ]);    

    if ($responseData->isOk && $responseData->ResponseCode === 0) {
        // code thêm vào đây tùy theo mục đích của bạn khi giao dịch thành công.
    }
    
```
 
## Phương thức `verifyRequestPurchaseSuccess`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
OnePay gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `ReturnURL` trong `purchase`, sau khi phương thức
 này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra trạng thái 
 giao dịch, từ đó hiển thị thông báo thành công hoặc thất bại...

Cách sử dụng:

```php
    if ($verifiedData = Yii::$app->OPGateway->verifyRequestPurchaseSuccess()) {
        
        if ($verifiedData->ResponseCode === 0) {            
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

Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải OnePay)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ OnePay,
bảng thuộc tính:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| OrderInfo | **có** | mixed | Mô tả đơn hàng của bạn. |
| MerchTxnRef | **có** | mixed | Mã đơn hàng trên hệ thống của bạn. |
| ResponseCode | **có** | int | Trạng thái đơn hàng. |
| Amount | **có** | float | Số tiền của đơn hàng. |
| Locale | **có** | string | Loại ngôn ngữ mà khách sử dụng để thanh toán. |
| CurrencyCode | **có** | string | Loại tiền mà khách chọn để thanh toán. Có 2 giá trị `VND` và `USD`. |
| Merchant | **có** | string | Merchant Id dùng để thanh toán. |
| TransactionNo | không | string | Mã giao dịch trên OnePay. Nó chỉ tồn tại khi `ResponseCode` là `0` |
| Message | không | string | Thông báo lỗi. Nó chỉ tồn tại khi `ResponseCode` khác `0` |

* Bảng trạng thái giao dịch:

| Gía trị | Mô tả |
| :-------: | ----- |
| **0** | giao dịch thành công, tất cả các giá trị còn lại là thất bại |

> Bạn có thể sử dụng `VerifyFilter` behavior để đảm nhiệm việc xác minh tính hợp lệ của dữ liệu trước
> khi action trong controller diễn ra nhằm đơn giản hóa nghiệp vụ xử lý. Kham khảo tài liệu tại [đây](verifyfilter.md)

## Phương thức `verifyRequestIPN`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
OnePay gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `IPN` trên hệ thống OnePay, sau khi phương thức
 này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra trạng thái 
 giao dịch, từ đó cập nhật database và xử lý nghiệp vụ...

Cách sử dụng:

```php
    Yii::$app->response->format = 'urlencoded';
      
    if ($verifiedData = Yii::$app->OPGateway->verifyRequestIPN()) {
        
        if ($verifiedData->ResponseCode === 0) {
            // update database    
            return [
                'responsecode' => 1,
                'desc' => 'confirm-success'
            ];         
        }          

    } else {
        return [
            'responsecode' => 0,
            'desc' => 'confirm-fail'
        ];
    }
``` 

Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải OnePay)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ OnePay,
bảng thuộc tính:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| OrderInfo | **có** | mixed | Mô tả đơn hàng của bạn. |
| MerchTxnRef | **có** | mixed | Mã đơn hàng trên hệ thống của bạn. |
| ResponseCode | **có** | int | Trạng thái đơn hàng. |
| Amount | **có** | float | Số tiền của đơn hàng. |
| Locale | **có** | string | Loại ngôn ngữ mà khách sử dụng để thanh toán. |
| CurrencyCode | **có** | string | Loại tiền mà khách chọn để thanh toán. Có 2 giá trị `VND` và `USD`. |
| Merchant | **có** | string | Merchant Id dùng để thanh toán. |
| TransactionNo | không | string | Mã giao dịch trên OnePay. Nó chỉ tồn tại khi `ResponseCode` là `0` |
| Message | không | string | Thông báo lỗi. Nó chỉ tồn tại khi `ResponseCode` khác `0` |

Bảng trạng thái giao dịch:

| Gía trị | Mô tả |
| :-------: | ----- |
| **0** | giao dịch thành công, tất cả các giá trị còn lại là thất bại |

Sau khi xử lý nghiệm vụ tại `action` của `IPN` bạn cần phải trả về dữ liệu
cho OnePay biết là bạn đã cập nhật đơn hàng, giúp cho OnePay đồng bộ
trạng thái với hệ thống của bạn.

Bảng thông tin cần trả về:

| Gía trị | Mô tả |
| :-------: | ----- |
| responsecode | Trạng thái xử lý tại hệ thống của bạn. Có 2 giá trị là `1` mọi thứ tốt đẹp, `0` có lỗi xảy ra. |
| desc | Mô tả lỗi xảy ra cho OnePay biết khi `responsecode` là `0` có lỗi xảy ra. |

Kiểu dữ liệu trả về có định dạng: `form-format-urlencoded`

> Bạn có thể sử dụng `VerifyFilter` behavior để đảm nhiệm việc xác minh tính hợp lệ của dữ liệu trước
> khi action trong controller diễn ra nhằm đơn giản hóa nghiệp vụ xử lý. Kham khảo tài liệu tại [đây](verifyfilter.md)

## Câu hỏi thương gặp

+ Câu hỏi: Vì sao có đến 2 phương thức nhận và xác minh dữ liệu 
(`verifyRequestPurchaseSuccess`, `verifyRequestIPN`)?
    - Trả lời: vì cổng thanh toán muốn tăng sử đảm bảo cho giao dịch,
    do nếu chỉ cung cấp phương thức `verifyRequestPurchaseSuccess` thì sẽ có
    trường hợp khách hàng rớt mạng không thể `redirect` về `ReturnURL` được cho
    nên phương thức `verifyRequestIPN` được cung cấp để đảm bảo hơn do lúc này
    connection sẽ là OnePay với máy chủ của bạn tính ổn định sẽ là `99.99%`.
    
+ Câu hỏi: Vậy thì luồn xử lý sẽ ra sao nếu như có đến 2 điểm nhận thông báo 
(IPN và ReturnURL)?
    - Trả lời: với chúng tôi `action` của `ReturnURL` chỉ dùng để xác minh tính 
    hợp lệ của dữ liệu OnePay từ đó hiển thị thanh toán thành công hoặc thất bại
    KHÔNG đụng đến phần cập nhật database và các nghiệp vụ liên quan đến cập nhật
    trạng thái đơn hàng. Phần cập nhật trạng thái và xử lý nghiệp vụ liên quan sẽ
    nằm ở `action` của `IPN`.
    
+ Câu hỏi: `IPN` là viết tắt của cụm từ gì?
    - Trả lời: `Instance Payment Notification`.
    
