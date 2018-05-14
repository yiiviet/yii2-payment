# Cổng thanh toán Bảo Kim

**Nếu như bạn đang muốn test thử thì xin vui lòng bỏ qua bước bên dưới và đi tới phần thiết lập.**

Đầu tiên mời bạn đăng ký tích hợp tại cổng thanh toán [Bảo Kim](https://www.baokim.vn/developers/).
Sau khi tích hợp xong bạn sẽ được Bảo Kim cấp các dữ liệu sau đây.

| Tên dữ liệu | Kiểu |
| :-----------: | :----: |
| Merchant Email | string |
| Merchant Id | string |
| Secure Password | string |
| Api User| string |
| Api Password | string |
| Private Certificate | string |

Với `private certificate` bạn sẽ được cấp khi đăng ký tích hợp `pro` và nó cũng chỉ cần
khi bạn có nhu cầu sử dụng. Nếu không có thì bạn không cần quan tâm.

## Thiết lập

Thiết lập vào mảng `components` ở file `web.php` trong thư mục `config` của app với các cấu hình sau:

* Cấu hình dành cho test:

```php
'components' => [
    'BKGateway' => [
        'class' => 'yiiviet\payment\baokim\PaymentGateway',
        'sandbox' => true
    ]
]

```

* Cấu hình khi chạy chính thức:

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
| **getMerchantData** | Tạo lệnh yêu cầu bảo kim cấp thông tin merchant


## Phương thức `purchase`

* Cách sử dụng cơ bản để yêu cầu Bảo Kim tạo thanh toán:

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
| order_id | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
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

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| :-----------: | :----: | :------: | ---- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| redirect_url | **có** | string | Đường dẫn thanh toán Bảo Kim, bạn sẽ dãn khách di chuyển đến nó để khách thực hiện thanh toán. |


* Code hoàn chỉnh:

```php
    $result = Yii::$app->BKGateway->purchase([
        'order_id' => 2, 
        'total_amount' => 500000, 
        'url_success' => '/'
    ]);

    if ($result->isOk) {
        Yii::$app->response->redirect($result->redirect_url);
    }
``` 

## Phương thức `purchasePro`

* Cách sử dụng cơ bản để yêu cầu bảo kim tạo thanh toán với phương thức PRO:

```php
    $result = Yii::$app->BKGateway->purchasePro([
        'bank_payment_method_id' => 128,
        'payer_name' => 'vxm',
        'payer_email' => 'vxm@gmail.com',
        'payer_phone_no' => '0909113911',
        'order_id' => microtime(),
        'total_amount' => 500000,
        'url_success' => '/'
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| order_id | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| bank_payment_method_id | **có** | int | Mã ngân hàng khách sẽ thanh toán, kham khảo dữ liệu trả về từ `[[getMerchantData()]]` |
| total_amount | **có** | int | Số tiền của đơn hàng. |
| payer_name | **có** | string | Tên của khách mua hàng. |
| payer_email | **có** | string | Email của khách mua hàng. |
| payer_phone_no | **có** | string | Số điện thoại của khách. |
| url_success | **có** | string | Đường dẫn Bảo Kim sẽ dãn khách về sau khi thanh toán thành công. Bạn có thể sử dụng `\yii\helpers\Url` để giúp bạn tạo đường dẫn. |
| payer_address | không | string | Địa chỉ của khách mua hàng. |
| message | không | string | Thông điệp của khách mua hàng để lại. |
| shipping_fee | không | int | Phí vận chuyển. |
| tax_fee | không | int | Tiền thuế. |
| business | không | string | Email tài khoản Bảo Kim nhận tiền sau khi khách giao dịch thành công. Mặc định là `merchantEmail`. |
| order_description | không | string | Mô tả đơn hàng. |
| url_cancel | không | string | Đường dẫn Bảo Kim sẽ dẫn khách về khi khách hàng hủy thanh toán. Bạn có thể sử dụng `\yii\helpers\Url` để giúp bạn tạo đường dẫn. |
| url_detail | không | string | Đường dẫn thông tin đơn hàng (món hàng) mà khách bạn đang thanh toán. Bạn có thể sử dụng `\yii\helpers\Url` để giúp bạn tạo đường dẫn. |
| transaction_mode_id | không | int | Hình thức giao dịch. `1` là trực tiếp, `2` là an toàn tạm giữ. |
| escrow_timeout | không | int | Số ngày tạm giữ nếu như `transaction_mode_id` có già trị là `2`. |
| mui | không | int | Giao diện thanh toán bạn muốn Bảo Kim xây dựng. Gồm 3 loại: `charge`, `base`, `iframe`, mặc định là `charge`. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| next_action | **có** | string | Hướng dẫn từ Bảo Kim phản hồi. Nó sẽ là `redirect` hoặc `display_guide` |
| rv_id | **có** | int | Mã phiếu thu của Bảo Kim. Bạn nên lưu lại mã này cho đơn hàng của bạn để đối soát sau này. |
| redirect_url | không | string | Đường dẫn thanh toán Bảo Kim, bạn sẽ dãn khách di chuyển đến nó để khách thực hiện thanh toán. Thuộc tính này chỉ tồn tại khi `next_action` có giá trị là `redirect`.|
| guide_url | không | string | Đường dẫn hướng dẫn thanh toán Bảo Kim, bạn sẽ dãn khách di chuyển đến nó để khách thực hiện thanh toán. Thuộc tính này chỉ tồn tại khi `next_action` có giá trị là `display_guide`.|


* Code hoàn chỉnh:

```php
    $result = Yii::$app->BKGateway->purchasePro([
        'bank_payment_method_id' => xxx,
        'payer_name' => 'vxm',
        'payer_email' => 'vxm@gmail.com',
        'payer_phone_no' => '0909113911',
        'order_id' => microtime(),
        'total_amount' => 500000,
        'url_success' => '/'
    ]);

    if ($result->isOk) {
        $url = $result->next_action === 'redirect' ? $result->redirect_url : $result->guide_url;
        Yii::$app->response->redirect($url);
    }
``` 

## Phương thức `verifyRequestPurchaseSuccess`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
Bảo Kim gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `url_success` trong `purchase` và `purchasePro`, sau
khi phương thức này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra
trạng thái giao dịch, từ đó hiển thị thông báo thành công hoặc thất bại...

Cách sử dụng:

```php
    if ($verifiedData = Yii::$app->BKGateway->verifyRequestPurchaseSuccess()) {
        $order = Order::findOne($verifiedData->order_id);
        
        if ($verifiedData->transaction_status == 4) {
            return $this->render('order_completed', [
              'order' => $order
            ]);
         } else {
            return $this->render('order_error', [
              'order' => $order
            ]);         
         }
    
    }
``` 

Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải Bảo Kim)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ Bảo Kim,
bảng thuộc tính:

* Đối với đơn hàng tạo bằng phương thức `purchase`:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| order_id | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| created_on | **có** | timestamp | Thời gian tạo giao dịch trên Bảo Kim. |
| payment_type | **có** | int | Hình thức khách giao dịch (`1` Trực tiếp, `2` Tạm giữ). |
| transaction_status | **có** | int | Trạng thái giao dịch. |
| total_amount | **có** | int | Tổng số tiền khách trả. |
| net_amount | **có** | int | Số tiền bạn thực nhận. |
| fee_amount | **có** | int | Phí dịch vụ của Bảo Kim. |
| merchant_id | **có** | int | Mã website tích hợp. |
| transaction_id | **có** | string | Mã đơn hàng trên Bảo Kim |
| payer_name | **có** | string | Tên người mua. |
| payer_email | **có** | string | Email người mua. |
| payer_phone_no | **có** | string | Số điện thoại người mua. |
| shipping_address | không | string | Địa chỉ giao hàng. |

* Đối với đơn hàng tạo bằng phương thức `purchasePro`:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| order_id | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| created_on | **có** | timestamp | Thời gian tạo giao dịch trên Bảo Kim. |
| payment_type | **có** | int | Hình thức khách giao dịch (`1` Trực tiếp, `2` Tạm giữ). |
| transaction_status | **có** | int | Trạng thái giao dịch. |
| total_amount | **có** | int | Tổng số tiền khách trả. |
| net_amount | **có** | int | Số tiền bạn thực nhận. |
| fee_amount | **có** | int | Phí dịch vụ của Bảo Kim. |
| merchant_id | **có** | int | Mã website tích hợp. |
| transaction_id | **có** | string | Mã đơn hàng trên Bảo Kim |
| customer_name | **có** | string | Tên người mua. |
| customer_email | **có** | string | Email người mua. |
| customer_phone | **có** | string | Số điện thoại người mua. |
| customer_address | không | string | Địa chỉ giao hàng. |


* Bảng trạng thái giao dịch:

| Gía trị | Mô tả |
| :-------: | ----- |
| 1 | giao dịch chưa xác minh OTP |
| 2 | giao dịch đã xác minh OTP |
| **4** | giao dịch hoàn thành |
| 5 | giao dịch bị hủy |
| 6 | giao dịch bị từ chối nhận tiền |
| 7 | giao dịch hết hạn |
| 8 | giao dịch thất bại |
| 12 | giao dịch bị đóng băng |
| **13** | giao dịch bị tạm giữ (thanh toán an toàn) |
| X | các trạng thái giao dịch khác |

Như bạn thấy thì chúng ta chỉ quan tâm đến `4` và `13` vì 2 trạng thái này cho ta biết
 khách đã thanh toán thành công.

## Phương thức `verifyRequestIPN`

Phương thức này cho phép bạn kiểm tra tính hợp lệ của các dữ liệu từ
Bảo Kim gửi sang, tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở Bảo Kim mục `BPN`, sau khi phương thức này kiểm tra dữ liệu 
hợp lệ  thì bạn mới kiểm tra giao dịch có hoàn thành hay không, nếu hợp lệ thì lưu trạng
 thái trên DB.

Cách sử dụng:

```php
    if ($verifiedData = Yii::$app->BKGateway->verifyRequestIPN()) {
        $order = Order::findOne($verifiedData->order_id);
        
        if ($verifiedData->transaction_status == 4 && $order->status == 0) {
        
            $order->status = 1;
            $order->save();
        } 
    
    }
``` 

Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải Bảo Kim)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ Bảo Kim,
bảng thuộc tính:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| order_id | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| created_on | **có** | timestamp | Thời gian tạo giao dịch trên Bảo Kim. |
| payment_type | **có** | int | Hình thức khách giao dịch (`1` Trực tiếp, `2` Tạm giữ). |
| transaction_status | **có** | int | Trạng thái giao dịch. |
| total_amount | **có** | int | Tổng số tiền khách trả. |
| net_amount | **có** | int | Số tiền bạn thực nhận. |
| fee_amount | **có** | int | Phí dịch vụ của Bảo Kim. |
| merchant_id | **có** | int | Mã website tích hợp. |
| transaction_id | **có** | string | Mã đơn hàng trên Bảo Kim |
| customer_name | **có** | string | Tên người mua. |
| customer_email | **có** | string | Email người mua. |
| customer_phone | **có** | string | Số điện thoại người mua. |
| customer_address | không | string | Địa chỉ giao hàng. |

* Bảng trạng thái giao dịch:

| Gía trị | Mô tả |
| :-------: | ----- |
| 1 | giao dịch chưa xác minh OTP |
| 2 | giao dịch đã xác minh OTP |
| **4** | giao dịch hoàn thành |
| 5 | giao dịch bị hủy |
| 6 | giao dịch bị từ chối nhận tiền |
| 7 | giao dịch hết hạn |
| 8 | giao dịch thất bại |
| 12 | giao dịch bị đóng băng |
| **13** | giao dịch bị tạm giữ (thanh toán an toàn) |
| X | các trạng thái giao dịch khác |

Như bạn thấy thì chúng ta chỉ quan tâm đến `4` và `13` vì 2 trạng thái này cho ta biết
 khách đã thanh toán thành công.
 
## Phương thức `queryDR`

Phương thức này cho bạn truy vấn thông tin giao dịch từ Bảo Kim thông qua 
`transaction_id` mà bạn nhận được từ `verifyRequestPurchaseSuccess` hoặc 
`verifyRequestIPN` ở trên.

Cách truy vấn thông tin:

```php

    $responseData = Yii::$app->BKGateway->queryDR([
        'transaction_id' => 'abc'
    ]);    

    if ($responseData->isOk) {
        // code thêm vào đây tùy theo mục đích của bạn.
    }
    
```

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| ----------- | :----: | :----: | ------ |
| transaction_id | **có** | string | Mã đơn hàng trên hệ thống Bảo Kim |


* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| ----------- | :----: | :------: | ----- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| order_id | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| created_on | **có** | timestamp | Thời gian tạo giao dịch trên Bảo Kim. |
| payment_type | **có** | int | Hình thức khách giao dịch (`1` Trực tiếp, `2` Tạm giữ). |
| transaction_status | **có** | int | Trạng thái giao dịch. |
| total_amount | **có** | int | Tổng số tiền khách trả. |
| net_amount | **có** | int | Số tiền bạn thực nhận. |
| fee_amount | **có** | int | Phí dịch vụ của Bảo Kim. |
| merchant_id | **có** | int | Mã website tích hợp. |
| transaction_id | **có** | string | Mã đơn hàng trên Bảo Kim |
| customer_name | **có** | string | Tên người mua. |
| customer_email | **có** | string | Email người mua. |
| customer_phone | **có** | string | Số điện thoại người mua. |
| customer_address | không | string | Địa chỉ giao hàng. |

* Bảng trạng thái giao dịch:

| Gía trị | Mô tả |
| :-------: | ----- |
| 1 | giao dịch chưa xác minh OTP |
| 2 | giao dịch đã xác minh OTP |
| **4** | giao dịch hoàn thành |
| 5 | giao dịch bị hủy |
| 6 | giao dịch bị từ chối nhận tiền |
| 7 | giao dịch hết hạn |
| 8 | giao dịch thất bại |
| 12 | giao dịch bị đóng băng |
| **13** | giao dịch bị tạm giữ (thanh toán an toàn) |
| X | các trạng thái giao dịch khác |

Như bạn thấy thì chúng ta chỉ quan tâm đến `4` và `13` vì 2 trạng thái này cho ta biết
 khách đã thanh toán thành công.


## Câu hỏi thương gặp

+ Câu hỏi: Vì sao có đến 2 phương thức nhận và xác minh dữ liệu 
(`verifyRequestPurchaseSuccess`, `verifyRequestIPN`)?
    - Trả lời: vì cổng thanh toán muốn tăng sử đảm bảo cho giao dịch,
    do nếu chỉ cung cấp phương thức `verifyRequestPurchaseSuccess` thì sẽ có
    trường hợp khách hàng rớt mạng không thể `redirect` về `success_url` được cho
    nên phương thức `verifyRequestIPN` được cung cấp để đảm bảo hơn do lúc này
    connection sẽ là Bảo Kim với máy chủ của bạn tính ổn định sẽ là `99.99%`.
    
+ Câu hỏi: Vậy thì luồn xử lý sẽ ra sao nếu như có đến 2 điểm nhận thông báo 
(BPN và success_url)?
    - Trả lời: với chúng tôi `action` của `success_url` chỉ dùng để xác minh tính 
    hợp lệ của dữ liệu Bảo Kim từ đó hiển thị thanh toán thành công hoặc thất bại
    KHÔNG đụng đến phần cập nhật database và các nghiệp vụ liên quan đến cập nhật
    trạng thái đơn hàng. Phần cập nhật trạng thái và xử lý nghiệp vụ liên quan sẽ
    nằm ở `action` của `BPN`.
    
+ Câu hỏi: `BPN` là viết tắt của cụm từ gì?
    - Trả lời: `Bảo Kim Payment Notification`, nó đóng vai trò như các `IPN` của các
    cổng thanh toán khác.
    


