# Cổng thanh toán Bảo Kim

Đầu tiên mời bạn đăng ký tích hợp tại cổng thanh toán [Bảo Kim](https://www.baokim.vn/developers/).
Sau khi tích hợp xong bạn sẽ được Bảo Kim cấp các dữ liệu sau đây.

| Tên dữ liệu | kiểu |
| ----------- | ---- |
| Merchant Email | string |
| Merchant Id | string |
| Secure Password | string |
| Api User| string |
| Api Password | string |
| *Private Certificate | string |

Với `private certificate` bạn sẽ được cấp khi đăng ký tích hợp `pro` và nó cũng chỉ cần
khi bạn có nhu cầu sử dụng. Nếu không có thì bạn không cần quan tâm.

## Thiết lập

Thiết lập vào mảng `components` ở file `web.php` trong thư mục `config` của app với thông số sau:

```php
'components' => [
    'BKGateway' => [
        'class' => 'yiiviet\payment\baokim\PaymentGateway',
        'client' => [
            'merchantId' => 'Mã merchant bạn vừa đăng ký',
            'merchantEmail' => 'Email tài khoản bảo kim của bạn',
            'securePassword' => 'Secure password bạn vừa đăng ký',
            'apiUser' => 'Api user bạn vừa đăng ký',
            'apiPassword' => 'Api password bạn vừa đăng ký',
            'privateCertificate' => 'Private certificate bạn vừa đăng ký, 
            không cần thiết lập nếu như bạn không xài phương thức PRO',
        ]
    ]
]

```

Khi đã thiết lập xong ngay lập tức bạn đã có thể truy xuất đến cổng thanh toán Bảo Kim
bằng cú pháp `Yii::$app->BKGateway`.

## Tổng quan các phương thức (methods)

| Tên phương thức | Mục đích |
| :-----------:  | :----: |
| **purchase** | Tạo lệnh thanh toán thông qua Bảo Kim (di chuyển đến website Bảo Kim để chọn hình thức thanh toán).|
| **purchasePro** | Tạo lệnh thanh toán PRO (di chuyển trực tiếp đến ngân hàng). |
| **queryDR** | Tạo lệnh yêu cầu truy vấn thông tin giao dịch. |
| **verifyRequestPurchaseSuccess** | Kiểm tra tính hợp lệ của dữ liệu mà Bảo Kim gửi sang khi khách hàng thanh toán thành công (Client to Server). |
| **verifyRequestIPN** | Kiểm tra tính hợp lệ của dữ liệu mà Bảo Kim gửi sang khi khách hàng thanh toán thành công (Server to Server). |


## Phương thức `purchase`

* Cách sử dụng cơ bản:

```php
    $result = Yii::$app->BKGateway->purchase([
        'order_id' => 2, 
        'total_amount' => 500000, 
        'url_success' => \yii\helpers\Url::to(['payment/success]');
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| ----------- | :----: | :----: | ------ |
| order_id | **có** | string | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| url_success | **có** | string | Đường dẫn Bảo Kim sẽ dãn khách về sau khi thanh toán thành công. Bạn có thể sử dụng `\yii\helpers\Url` để giúp bạn tạo đường dẫn. |
| total_amount | **có** | int | Số tiền của đơn hàng. |
| shipping_fee | không | int | Phí vận chuyển. |
| tax_fee | không | int | Tiền thuế. |
| business | không | string | Email tài khoản Bảo Kim nhận tiền sau khi khách giao dịch thành công. Mặc định là `merchantEmail`. |
| order_description | không | string | Mô tả đơn hàng. |
| url_cancel | không | string | Đường dẫn Bảo Kim sẽ dẫn khách về khi khách hàng hủy thanh toán. Bạn có thể sử dụng `\yii\helpers\Url` để giúp bạn tạo đường dẫn. |
| url_detail | không | string | Đường dẫn thông tin đơn hàng (món hàng) mà khách bạn đang thanh toán. Bạn có thể sử dụng `\yii\helpers\Url` để giúp bạn tạo đường dẫn. |
| currency | không | string | Đơn vị tiền tệ. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Kiểu | Mô tả |
| ----------- | :----: | ------ |
| isOk | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại.
| location | string | Đường dẫn thanh toán Bảo Kim, bạn sẽ dãn khách di chuyển đến nó để khách thực hiện thanh toán.


* Code hoàn chỉnh:

```php
    $result = Yii::$app->BKGateway->purchase([
        'order_id' => 2, 
        'total_amount' => 500000, 
        'url_success' => '/'
    ]);

    if ($result->isOk) {
        Yii::$app->response->redirect($result->location);
    }
``` 

## Phương thức `purchasePro`