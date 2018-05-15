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
        'class' => 'yiiviet\payment\onepay\PaymentGateway',
        'international' => false, //Thiết lập `FALSE` để sử dụng cổng nội địa và ngược lại là cổng quốc tế. Mặc định là `FALSE`.
        'sandbox' => true
    ]
]

```

* Cấu hình khi chạy chính thức:

```php
'components' => [
    'OPGateway' => [
        'class' => 'yiiviet\payment\baokim\PaymentGateway',
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
    $result = Yii::$app->OPGateway->purchase([
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
    $result = Yii::$app->OPGateway->purchase([
        'ReturnURL' => 'http://localhost/',
        'OrderInfo' => time(),
        'Amount' => 500000,
        'TicketNo' => '127.0.0.1',
        'AgainLink' => 'http://localhost/',
        'Title' => 'Hello World',
        'MerchTxnRef' => time()
    ]);

    if ($result->isOk) {
        Yii::$app->response->redirect($result->redirect_url);
    } 
    
``` 

## Phương thức `purchase` cổng quốc tế (`international` = `TRUE`)

* Cách sử dụng cơ bản:

```php
    $result = Yii::$app->OPGateway->purchase([
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
    $result = Yii::$app->OPGateway->purchase([
        'ReturnURL' => 'http://localhost/',
        'OrderInfo' => time(),
        'Amount' => 500000,
        'TicketNo' => '127.0.0.1',
        'AgainLink' => 'http://localhost/',
        'Title' => 'Hello World',
        'MerchTxnRef' => time()
    ]);

    if ($result->isOk) {
        Yii::$app->response->redirect($result->redirect_url);
    } 
    
``` 

## Phương thức `queryDR`

Phương thức này cho bạn truy vấn thông tin giao dịch từ OnePay thông qua `MerchTxnRef` mà bạn tạo ra ở
 phương thức `purchase` phía trên. Lưu ý `MerchTxnRef` của cổng quốc tế và cổng nội địa không thể dùng chung,
 tất là nếu giao dịch tạo bằng cổng nội địa thì khi check ở cổng quốc tế giá trị sẽ không tồn tại.

Cách truy vấn thông tin:

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
| error_code | **có** | string | Mã báo lỗi `00` nghĩa là giao dịch thành công. |
| token | **có** | string | Token của đơn hàng nó sẽ giống như token dùng để truy vấn. |
| order_code | không | string | Mã đơn hàng trên hệ thống của bạn. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| total_amount | không | string |Tổng số tiền của đơn hàng. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| payment_method | không | string | Phương thức thanh toán. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| bank_code | không | string | Mã ngân hàng khách dùng để thanh toán. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| payment_type | không | int | Hình thức thanh toán `1` là trực tiếp, `2` là tạm giữ an toàn. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| order_description | không | string | Mô tả đơn hàng. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| tax_amount | không | int | Tiền thuế. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| discount_amount | không | int | Tiền khuyến mãi, giảm giá. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| fee_shipping | không | int | Tiền thuế. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| return_url | không | string | Đường dẫn OnePay `redirect` khách về sau khi họ thực hiện thanh toán, được thiết lập ở phương thức `purchase`. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| cancel_url | không | string | Đường dẫn OnePay `redirect` khách về khi họ thực hiện hủy đơn hàng, được thiết lập ở phương thức `purchase`. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| notify_url | không | string | Đường dẫn OnePay gọi về sau khi khách thực hiện thanh toán được thiết lập ở phương thức `purchase`. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| time_limit | không | int | Số phút còn lại để khách thực hiện giao dịch. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| buyer_fullname | không | string | Tên người mua. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| buyer_email | không | string | Email người mua. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| buyer_mobile | không | string | Số điện thoại người mua. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| buyer_address | không | string | Địa chỉ người mua. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| affiliate_code | không | string | Mã đối tác của OnePay. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| transaction_status | không | string | Trạng thái đơn hàng. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| transaction_id | không | string | Mã giao dịch tại hệ thống OnePay. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| description | không | string | Mô tả đơn hàng. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |

* Bảng trạng thái giao dịch:

| Gía trị | Mô tả |
| :-------: | ----- |
| **00** | giao dịch thành công|
| 01 | đã thanh toán, chờ xử lý |
| 02 | giao dịch chưa thanh toán |

Như bạn thấy thì chúng ta chỉ quan tâm đến `00` vì trạng thái này cho ta biết
 khách đã thanh toán thành công.
 
## Phương thức `verifyRequestPurchaseSuccess`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
OnePay gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `url_success` trong `purchase` và `purchasePro`, sau
khi phương thức này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra
trạng thái giao dịch, từ đó hiển thị thông báo thành công hoặc thất bại...

Cách sử dụng:

```php
    if ($verifiedData = Yii::$app->OPGateway->verifyRequestPurchaseSuccess()) {
        $token = $verifiedData->token;
        $result =  Yii::$app->OPGateway->queryDR(['token' => $token]);
        
        if ($result->isOk && $result->transaction_status == '00') {
            // processing update database...
            
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
| token | **có** | mixed | Mã token dùng để lấy thông tin đơn hàng thông qua phương thức `queryDR`. |


## Câu hỏi thương gặp

+ Câu hỏi: OnePay không có hổ trợ `verifyRequestIPN`?
    - Trả lời: Đúng! OnePay không hổ trợ. Cập nhật trạng thái và xử lý nghiệp vụ
    khi đơn hàng thanh toán thành công đều nằm ở `action` mà bạn thiết lập `return_url` 
    trong phương thức `purchase`.
    
+ Câu hỏi: Vậy thì luồng xử lý sẽ ra sao khách rớt mạng?
    - Trả lời: với chúng tôi `action` của `return_url` chỉ dùng để xác minh tính 
    hợp lệ của dữ liệu OnePay từ đó hiển thị thanh toán thành công hoặc thất bại
    KHÔNG đụng đến phần cập nhật database và các nghiệp vụ liên quan đến cập nhật
    trạng thái đơn hàng. Phần cập nhật trạng thái và xử lý nghiệp vụ liên quan sẽ
    nằm ở `cron task`, `cron task` sẽ gọi `queryDR` để cập nhật trạng thái và xử lý
    nghiệp vụ.
    
