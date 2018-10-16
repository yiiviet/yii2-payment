# Cổng thanh toán VTCPay

**Nếu như bạn đang muốn test thử thì xin vui lòng bỏ qua bước bên dưới và đi tới phần thiết lập.**

Đầu tiên mời bạn đăng ký tích hợp tại cổng thanh toán [VTCPay](https://vtcpay.vn).
Sau khi tích hợp xong bạn sẽ được VTCPay cấp các dữ liệu sau đây.

| Tên dữ liệu | Kiểu |
| :-----------: | :----: |
| Account (tài khoản của bạn) | string |
| Secure Code (mã bảo vệ bạn đã điền) | string |
| Website Id (mã website) | string |

## Thiết lập

Thiết lập vào mảng `components` ở file `web.php` trong thư mục `config` của app với các cấu hình sau:

* Cấu hình dành cho test:

```php
'components' => [
    'VTCGateway' => [
        'class' => 'yiiviet\payment\vtcpay\PaymentGateway',
        'sandbox' => true
    ]
]

```

* Cấu hình khi chạy chính thức:

```php
'components' => [
    'VTCGateway' => [
        'class' => 'yiiviet\payment\VTCPay\PaymentGateway',
        'client' => [
            'business' => 'Account VTCPay bạn vừa đăng ký',
            'merchantId' => 'Website Id bạn vừa đăng ký',
            'secureCode' => 'Secure Code bạn vừa đăng ký'
        ]
    ]
]

```

Khi đã thiết lập xong ngay lập tức bạn đã có thể truy xuất đến cổng thanh toán VTCPay
bằng cú pháp `Yii::$app->VTCGateway`.

## Tổng quan các phương thức (methods)

| Tên phương thức | Mục đích |
| :-----------:  | :----: |
| **purchase** | Tạo lệnh thanh toán thông qua VTCPay.|
| **verifyRequestIPN** | Kiểm tra tính hợp lệ của dữ liệu mà VTCPay gửi sang khi khách hàng thanh toán thành công (VTCPay to Server). |
| **verifyRequestPurchaseSuccess** | Kiểm tra tính hợp lệ của dữ liệu mà VTCPay gửi sang khi khách hàng thanh toán thành công (Client to Server). |


## Phương thức `purchase`

* Cách sử dụng cơ bản:

```php
    $result = Yii::$app->VTCGateway->purchase([
       'amount' => 100000,
       'reference_number' => time()
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| reference_number | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| amount | **có** | double | Số tiền của đơn hàng. |
| receiver_account | không | string | Tài khoản nhận tiền, mặc định nếu không thiết lập sẽ lấy tài khoản bạn đã thiết lập. |
| currency | không | string | Tiền tệ có 2 loại `VND` và `USD` mặc định là `VND`. |
| bill_to_address | không | string | Địa chỉ khách hàng. |
| bill_to_address_city | không | string | Thành phố khách hàng cư trú. |
| bill_to_email | không | string | Email khách hàng. |
| bill_to_surname | không | string | Họ và tên đệm khách hàng. |
| bill_to_forename | không | string | Tên khách hàng. |
| bill_to_phone | không | string | Số điện thoại khách hàng. |
| url_return | không | string | Đường dẫn trả về hệ thống khi giao dịch thành công hay thất bại. |
| language | không | string | Ngôn ngữ chỉ định cho VTCPay hiển thị có 2 kiểu `vi` và `en` mặc định là `vi`. |
| payment_type | không | string | Phương thức thanh toán có 3 dạng `VTCPay` thanh toán bằng số dư ví VTCPay, `DomesticBank` thanh toán thông qua ngân hàng trong nước, `InternationalCard` thanh toán qua thẻ visa/master. Mặc định nếu không điền sẽ để khách chọn. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| :-----------: | :----: | :------: | ---- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| redirect_url | **có** | string | Đường dẫn thanh toán VTCPay. Bạn sẽ `redirect` khách đến đường dẫn để khách thực hiện thanh toán. |

* Code hoàn chỉnh:

```php
    $result = Yii::$app->VTCGateway->purchase([
       'amount' => 100000,
       'reference_number' => time()
    ]);

    if ($result->isOk) {
        Yii::$app->response->redirect($result->redirect_url);
    } 
    
``` 

## Phương thức `verifyRequestPurchaseSuccess`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
VTCPay gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `return_url` trong `purchase`, sau khi phương thức
 này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra trạng thái 
 giao dịch, từ đó hiển thị thông báo thành công hoặc thất bại...

* Cách sử dụng:

```php
    if ($verifiedData = Yii::$app->VTCGateway->verifyRequestPurchaseSuccess()) {
        
        if ($result->status == 1) {            
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

* Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải VTCPay)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ VTCPay,
bảng thuộc tính:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| status | **có** | int | Trạng thái. |
| reference_number | **có** | mixed | Mã đơn hàng trên hệ thống của bạn. |
| amount | **có** | double | Số tiền của đơn hàng. |
| trans_ref_no | không | string | Mã giao dịch tại VTCPay. Nó chỉ tồn tại khi `status` khác `1` |
| payment_type | không | string | Phương thức thanh toán. Nó chỉ tồn tại khi `status` khác `1` |

> Bạn có thể sử dụng `VerifyFilter` behavior để đảm nhiệm việc xác minh tính hợp lệ của dữ liệu trước
> khi action trong controller diễn ra nhằm đơn giản hóa nghiệp vụ xử lý. Kham khảo tài liệu tại [đây](verifyfilter.md)

## Phương thức `verifyRequestIPN`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
VTCPay gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `IPN` trên hệ thống VTCPay, sau khi phương thức
 này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra trạng thái 
 giao dịch, từ đó cập nhật database và xử lý nghiệp vụ...

* Cách sử dụng:

```php
    
    if ($verifiedData = Yii::$app->VNPGateway->verifyRequestIPN()) {
        
        if ($verifiedData->status == 1) {  
        
            // update database            
         } 
    }
    

``` 

Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải VTCPay)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ VTCPay,
bảng thuộc tính:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| status | **có** | int | Trạng thái. |
| reference_number | **có** | mixed | Mã đơn hàng trên hệ thống của bạn. |
| amount | **có** | double | Số tiền của đơn hàng. |
| trans_ref_no | không | string | Mã giao dịch tại VTCPay. Nó chỉ tồn tại khi `status` khác `1` |
| payment_type | không | string | Phương thức thanh toán. Nó chỉ tồn tại khi `status` khác `1` |

> Bạn có thể sử dụng `VerifyFilter` behavior để đảm nhiệm việc xác minh tính hợp lệ của dữ liệu trước
> khi action trong controller diễn ra nhằm đơn giản hóa nghiệp vụ xử lý. Kham khảo tài liệu tại [đây](verifyfilter.md)

## Câu hỏi thương gặp

+ Câu hỏi: Vì sao có đến 2 phương thức nhận và xác minh dữ liệu 
(`verifyRequestPurchaseSuccess`, `verifyRequestIPN`)?
    - Trả lời: vì cổng thanh toán muốn tăng sử đảm bảo cho giao dịch,
    do nếu chỉ cung cấp phương thức `verifyRequestPurchaseSuccess` thì sẽ có
    trường hợp khách hàng rớt mạng không thể `redirect` về `return_url` được cho
    nên phương thức `verifyRequestIPN` được cung cấp để đảm bảo hơn do lúc này
    connection sẽ là VTCPay với máy chủ của bạn tính ổn định sẽ là `99.99%`.
    
+ Câu hỏi: Vậy thì luồn xử lý sẽ ra sao nếu như có đến 2 điểm nhận thông báo 
(IPN và return_url)?
    - Trả lời: với chúng tôi `action` của `return_url` chỉ dùng để xác minh tính 
    hợp lệ của dữ liệu VTCPay từ đó hiển thị thanh toán thành công hoặc thất bại
    KHÔNG đụng đến phần cập nhật database và các nghiệp vụ liên quan đến cập nhật
    trạng thái đơn hàng. Phần cập nhật trạng thái và xử lý nghiệp vụ liên quan sẽ
    nằm ở `action` của `IPN`.
    
+ Câu hỏi: `IPN` là viết tắt của cụm từ gì?
    - Trả lời: `Instance Payment Notification`.
    
