# Cổng thanh toán Ngân Lượng

**Nếu như bạn đang muốn test thử thì xin vui lòng bỏ qua bước bên dưới và đi tới phần thiết lập.**

Đầu tiên mời bạn đăng ký tích hợp tại cổng thanh toán [Ngân Lượng](https://www.nganluong.vn).
Sau khi tích hợp xong bạn sẽ được Ngân Lượng cấp các dữ liệu sau đây.

| Tên dữ liệu | Kiểu |
| :-----------: | :----: |
| Email | string |
| Merchant Id | string |
| Merchant Password | string |

## Thiết lập

Thiết lập vào mảng `components` ở file `web.php` trong thư mục `config` của app với các cấu hình sau:

* Cấu hình dành cho test:

```php
'components' => [
    'NLGateway' => [
        'class' => 'yiiviet\payment\nganluong\PaymentGateway',
        'seamless' => FALSE, // Sử dụng phương thức thanh toán redirect về Ngân Lượng (FALSE) hoặc khách thanh toán trực tiếp trên trang của bạn không cần `redirect` (TRUE).
        'sandbox' => true
    ]
]

```

* Cấu hình khi chạy chính thức:

```php
'components' => [
    'NLGateway' => [
        'class' => 'yiiviet\payment\nganluong\PaymentGateway',
        'seamless' => FALSE, // Sử dụng phương thức thanh toán redirect về Ngân Lượng (FALSE) hoặc khách thanh toán trực tiếp trên trang của bạn không cần `redirect` (TRUE).
        'client' => [
            'email' => 'Email tài khoản ngân lượng của bạn',
            'merchantId' => 'Mã merchant bạn vừa đăng ký',
            'merchantPassword' => 'Merchant password bạn vừa đăng ký'
        ]
    ]
]

```

Khi đã thiết lập xong ngay lập tức bạn đã có thể truy xuất đến cổng thanh toán Ngân Lượng
bằng cú pháp `Yii::$app->NLGateway`.

## Tổng quan các phương thức (methods)

| Tên phương thức | Mục đích |
| :-----------:  | :----: |
| **purchase** | Tạo lệnh thanh toán thông qua Ngân Lượng.|
| **queryDR** | Tạo lệnh yêu cầu truy vấn thông tin giao dịch. |
| **verifyRequestPurchaseSuccess** | Kiểm tra tính hợp lệ của dữ liệu mà Ngân Lượng gửi sang khi khách hàng thanh toán thành công (Client to Server). |
| **authenticate** | Tạo lệnh yêu cầu Ngân Lượng kiểm tra tính hợp lệ của `OTP` nó được sử dụng với `seamless checkout` version `3.2`.


## Phương thức `purchase` (`seamless` = FALSE)

Phương thức này khi `seamless = FALSE` thanh toán đơn hàng dựa trên hệ thống của Ngân Lượng
bạn sẽ phải `redirect` khách hàng sang Ngân Lượng để thực hiện thanh toán.

* Cách sử dụng cơ bản:

```php
    $result = Yii::$app->NLGateway->purchase([
        'bank_code' => 'VCB',
        'buyer_fullname' => 'vxm',
        'buyer_email' => 'admin@test.app',
        'buyer_mobile' => '0909113911',
        'total_amount' => 10000000,
        'order_code' => microtime(),
        'return_url' => \yii\helpers\Url::to(['order/success'])
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| order_code | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| total_amount | **có** | int | Số tiền của đơn hàng. |
| buyer_fullname | **có** | string | Họ và tên người mua hàng. |
| buyer_email | **có** | string | Email người mua hàng. |
| buyer_mobile | **có** | string | Số điện thoại người mua hàng. |
| bank_code | **có** | string | Mã ngân hàng khách sử dụng để thanh toán. |
| return_url | **có** | string | Đường dẫn Ngân Lượng sẽ dẫn khách về hệ thống của bạn khi giao dịch kết thúc. |
| buyer_address | không | string | Địa chỉ người mua hàng. |
| receiver_email | không | string | Email tài khoản nhận tiền, nếu không thiết lập hệ thống sẽ dùng `email` của client. |
| payment_method | không | string | Hình thức thanh toán. Đối với `seamless` thì mặc định là `ATM_ONLINE` và ngược lại là `NL`. |
| payment_type | không | int | Hình thức giao dịch. `1` trực tiếp, `2` tạm giữ thanh toán an toàn. |
| cur_code | không | string | Đơn vị tiền tệ `vnd` hoặc `usd`. Mặc định `vnd`. |
| lang_code | không | string | Ngôn ngữ hiển thị tại trang thanh toán `vi` hoặc `en`. Mặc định `vi`. |
| order_description | không | string | Mô tả đơn hàng. |
| tax_amount | không | int | Số tiền thuế. |
| discount_amount | không | int | Số tiền giảm giá. |
| fee_shipping | không | int | Phí vận chuyển. |
| cancel_url | không | int | Đường dẫn khi khách hủy thanh toán. |
| time_limit | không | int | Thời gian tối đa bạn cho phép khách thực hiện thanh toán. Tính theo phút, mặc định `1440` phút (24 giờ). |
| total_item | không | int | Số lượng `item` trong đơn hàng. |
| item_name1 | không | string | Tên `item` đầu tiên trong đơn hàng. |
| item_quantity1 | không | string | Số lượng `item` đầu tiên trong đơn hàng. |
| item_amount1 | không | string | Giá tiền `item` đầu tiên trong đơn hàng. |
| item_url1 | không | string | Đường dẫn website của `item` đầu tiên trong đơn hàng (trang chi tiết). |
| affiliate_code | không | string | Mã đối tác của Ngân Lượng. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| :-----------: | :----: | :------: | ---- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| error_code | **có** | string | Mã lỗi từ Ngân Lượng. `00` nghĩa là thành công không lỗi. |
| token | không | string | Token của đơn hàng trên Ngân Lượng. Bạn phải lưu lại mã này để đối soát với Ngân Lượng sau này. Giá trị này chỉ tồn tại khi `isOk` là TRUE. |
| checkout_url | không | string | Đường dẫn bạn sẽ `redirect` khách đến để thực hiện thanh toán. Giá trị này chỉ tồn tại khi `isOk` là TRUE. |
| time_limit | không | int | Thời gian còn lại để khách thực hiện thanh toán. Giá trị này chỉ tồn tại khi `isOk` là TRUE. |
| description | không | string | Mô tả lỗi. Giá trị này chỉ tồn tại khi `isOk` là FALSE. |


* Code hoàn chỉnh:

```php
    $result = Yii::$app->NLGateway->purchase([
        'bank_code' => 'VCB',
        'buyer_fullname' => 'vxm',
        'buyer_email' => 'admin@test.app',
        'buyer_mobile' => '0909113911',
        'total_amount' => 10000000,
        'order_code' => microtime(),
        'return_url' => \yii\helpers\Url::to(['order/success'])
    ]);

    if ($result->isOk) {
        Yii::$app->response->redirect($result->checkout_url);
    } else {
        return $result->description;
    }
``` 

## Phương thức `purchase` (`seamless` = TRUE)

Phương thức này khi `seamless = TRUE` thanh toán đơn hàng sẽ không dựa trên hệ thống của Ngân Lượng (`seamless checkout`),
bạn sẽ không cần phải `redirect` khách hàng sang Ngân Lượng để thực hiện thanh toán mà nghiệp vụ thanh
toán bạn sẽ phải tự xây dựng. Ngân Lượng cấp phương thức `authenticate` để bạn xác minh mã `OTP` của khách.

* Cách sử dụng cơ bản:

```php
    $result = Yii::$app->NLGateway->purchase([
        'bank_code' => 'VCB',
        'buyer_fullname' => 'vxm',
        'buyer_email' => 'admin@test.app',
        'buyer_mobile' => '0909113911',
        'total_amount' => 10000000,
        'order_code' => microtime(),
        'return_url' => \yii\helpers\Url::to(['order/success']),
        'card_fullname' => 'vxm',
        'card_number' => '123123123123',
        'card_month' => 12,
        'card_year' => 2012
    ]);
``` 

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| order_code | **có** | mixed | Mã đơn hàng do website bạn sinh ra thường thì nó chính là `primary key` của `order row`. Dùng để đối chứng khi khách hàng giao dịch thành công. |
| total_amount | **có** | int | Số tiền của đơn hàng. |
| buyer_fullname | **có** | string | Họ và tên người mua hàng. |
| buyer_email | **có** | string | Email người mua hàng. |
| buyer_mobile | **có** | string | Số điện thoại người mua hàng. |
| bank_code | **có** | string | Mã ngân hàng khách sử dụng để thanh toán. |
| return_url | **có** | string | Đường dẫn Ngân Lượng sẽ dẫn khách về hệ thống của bạn khi giao dịch kết thúc. |
| card_fullname | **có** | string | Họ và tên ghi trên thẻ atm/tín dụng của khách. |
| card_number | **có** | string | Số thẻ atm/tín dụng của khách. |
| card_month | **có** | string | Tháng khởi tạo hoặc kết thúc của thẻ atm/tín dụng của khách. |
| card_year | **có** | string | Năm khởi tạo hoặc kết thúc của thẻ atm/tín dụng của khách. |
| buyer_address | không | string | Địa chỉ người mua hàng. |
| receiver_email | không | string | Email tài khoản nhận tiền, nếu không thiết lập hệ thống sẽ dùng `email` của client. |
| payment_method | không | string | Hình thức thanh toán. Đối với `seamless` thì mặc định là `ATM_ONLINE` và ngược lại là `NL`. |
| payment_type | không | int | Hình thức giao dịch. `1` trực tiếp, `2` tạm giữ thanh toán an toàn. |
| cur_code | không | string | Đơn vị tiền tệ `vnd` hoặc `usd`. Mặc định `vnd`. |
| lang_code | không | string | Ngôn ngữ hiển thị tại trang thanh toán `vi` hoặc `en`. Mặc định `vi`. |
| order_description | không | string | Mô tả đơn hàng. |
| tax_amount | không | int | Số tiền thuế. |
| discount_amount | không | int | Số tiền giảm giá. |
| fee_shipping | không | int | Phí vận chuyển. |
| notify_url | không | int | Đường dẫn nhận thông báo khi giao dịch thành công. |
| cancel_url | không | int | Đường dẫn khi khách hủy thanh toán. |
| time_limit | không | int | Thời gian tối đa bạn cho phép khách thực hiện thanh toán. |
| total_item | không | int | Số lượng `item` trong đơn hàng. |
| item_name1 | không | string | Tên `item` đầu tiên trong đơn hàng. |
| item_quantity1 | không | string | Số lượng `item` đầu tiên trong đơn hàng. |
| item_amount1 | không | string | Giá tiền `item` đầu tiên trong đơn hàng. |
| item_url1 | không | string | Đường dẫn website của `item` đầu tiên trong đơn hàng (trang chi tiết). |
| affiliate_code | không | string | Mã đối tác của Ngân Lượng. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Thuộc tính | Bắt buộc | Kiểu | Mô tả |
| :-----------: | :----: | :------: | ---- |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| error_code | **có** | string | Mã lỗi từ Ngân Lượng. `00` nghĩa là thành công không lỗi. |
| token | không | string | Token của đơn hàng trên Ngân Lượng. Bạn phải lưu lại mã này để đối soát với Ngân Lượng sau này. Giá trị này chỉ tồn tại khi `isOk` là TRUE. |
| auth_url | không | string | Đường dẫn xác thực mã `OTP`. Giá trị này chỉ tồn tại khi `isOk` là TRUE. |
| auth_site | không | string | Hệ thống xác thực mã `OTP` sẽ có 2 giá trị `NL` hoặc `BANK`. Nếu là `NL` thì bạn sẽ sử dụng phương thức `authenticate` để xác minh, ngược lại thì bạn phải `redirect` khách đến `auth_url` để xác minh. |
| time_limit | không | int | Thời gian còn lại để khách thực hiện thanh toán. Giá trị này chỉ tồn tại khi `isOk` là TRUE. |
| description | không | string | Mô tả lỗi. Giá trị này chỉ tồn tại khi `isOk` là FALSE. |


* Code hoàn chỉnh:

```php
    Yii::$app->NLGateway->setVersion('3.2');
    $result = Yii::$app->NLGateway->purchase([
        'bank_code' => 'VCB',
        'buyer_fullname' => 'vxm',
        'buyer_email' => 'admin@test.app',
        'buyer_mobile' => '0909113911',
        'total_amount' => 10000000,
        'order_code' => microtime(),
        'return_url' => \yii\helpers\Url::to(['order/success']),
        'card_fullname' => 'vxm',
        'card_number' => '123123123123',
        'card_month' => 12,
        'card_year' => 2012
    ]);

    if ($result->isOk && $result->auth_site === 'BANK') {
        Yii::$app->response->redirect($result->auth_url);
    } else {
        // Lưu lại thông tin `result` và hiển thị form cho khách nhập OTP.
        sau khi khách nhập OTP sử dụng mã OTP truyền vào phương thức `authenticate` để xác minh tính
        hợp lệ.
    }
``` 

## Phương thức `authenticate` (version 3.2)

Phương thức này dùng để xác minh `OTP` thông qua `token` mà bạn nhận được từ
 phương thức `purchase` ở trên và mã `OTP` của khách nhập đối với các ngân hàng mà `auth_site` có giá trị
 là `NL`.

Cách kiểm tra:

```php

    Yii::$app->NLGateway->setVersion('3.2');
    $result = Yii::$app->NLGateway->purchase([
        'bank_code' => 'VCB',
        'buyer_fullname' => 'vxm',
        'buyer_email' => 'admin@test.app',
        'buyer_mobile' => '0909113911',
        'total_amount' => 10000000,
        'order_code' => microtime(),
        'return_url' => \yii\helpers\Url::to(['order/success']),
        'card_fullname' => 'vxm',
        'card_number' => '123123123123',
        'card_month' => 12,
        'card_year' => 2012
    ]);
    
    if ($result->isOk && $result->auth_site === 'NL') {
        $authResponse = Yii::$app->NLGateway->authenticate([
            'token' => $result->token,
            'otp' => '123123',
            'auth_url' => $result->auth_url
        ]);    
    }
```

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| ----------- | :----: | :----: | ------ |
| token | **có** | string | Token của đơn hàng trên hệ thống Ngân Lượng |
| otp | **có** | string | Mã `otp` do khách hàng nhập cần xác minh tính hợp lệ. |
| auth_url | **có** | string | Đường dẫn xác minh `otp` giúp Ngân Lượng trong việc xác minh. |

* Sau khi gọi phương thức với các tham trị được yêu cầu nó sẽ trả về đối
tượng `response` với các thuộc tính sau:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| ----------- | :----: | :----: | ------ |
| isOk | **có** | bool | Thuộc tính cho biết tiến trình yêu cầu diễn ra tốt đẹp hay không. Nếu có là `TRUE` và ngược lại. |
| error_code | **có** | string | Mã báo lỗi `00` nghĩa là giao dịch thành công. `OTP` hợp lệ. |
| token | **có** | string | Mã token giống với `token` gửi lên yêu cầu xác minh. |


## Phương thức `queryDR`

Phương thức này cho bạn truy vấn thông tin giao dịch từ Ngân Lượng thông qua `token` mà bạn nhận được từ
 phương thức `purchase` ở trên hoặc phương thức `verifyRequestPurchaseSuccess` ở phía dưới.

Cách truy vấn thông tin:

```php

    $responseData = Yii::$app->NLGateway->queryDR([
        'token' => 'abc'
    ]);    

    if ($responseData->isOk) {
        // code thêm vào đây tùy theo mục đích của bạn.
    }
    
```

* Giới thiệu các thành phần trong mảng khi tạo lệnh:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| ----------- | :----: | :----: | ------ |
| token | **có** | string | Token của đơn hàng trên hệ thống Ngân Lượng |


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
| return_url | không | string | Đường dẫn Ngân Lượng `redirect` khách về sau khi họ thực hiện thanh toán, được thiết lập ở phương thức `purchase`. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| cancel_url | không | string | Đường dẫn Ngân Lượng `redirect` khách về khi họ thực hiện hủy đơn hàng, được thiết lập ở phương thức `purchase`. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| notify_url | không | string | Đường dẫn Ngân Lượng gọi về sau khi khách thực hiện thanh toán được thiết lập ở phương thức `purchase`. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| time_limit | không | int | Số phút còn lại để khách thực hiện giao dịch. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| buyer_fullname | không | string | Tên người mua. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| buyer_email | không | string | Email người mua. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| buyer_mobile | không | string | Số điện thoại người mua. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| buyer_address | không | string | Địa chỉ người mua. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| affiliate_code | không | string | Mã đối tác của Ngân Lượng. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| transaction_status | không | string | Trạng thái đơn hàng. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
| transaction_id | không | string | Mã giao dịch tại hệ thống Ngân Lượng. Thuộc tính này chỉ tồn tại khi `isOk` là TRUE. |
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
Ngân Lượng gửi sang tránh trường hợp giả mạo. Nó phải được gọi trong `action`
mà bạn đã thiết lập ở `return_url` trong `purchase`, sau
khi phương thức này kiểm tra dữ liệu hợp lệ thì bạn mới tiến hành kiểm tra
trạng thái giao dịch, từ đó hiển thị thông báo thành công hoặc thất bại...

Cách sử dụng:

```php
    if ($verifiedData = Yii::$app->NLGateway->verifyRequestPurchaseSuccess()) {
        $token = $verifiedData->token;
        $result =  Yii::$app->NLGateway->queryDR(['token' => $token]);
        
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

Khi gọi phương thức sẽ trả về `FALSE` nếu như dữ liệu không hợp lệ (không phải Ngân Lượng)
và ngược lại sẽ là một đối tượng chứa các thuộc tính dữ liệu hợp lệ gửi từ Ngân Lượng,
bảng thuộc tính:

| Khóa | Bắt buộc | Kiểu | Chi tiết |
| :-----------: | :----: | :----: | ------ |
| token | **có** | mixed | Mã token dùng để lấy thông tin đơn hàng thông qua phương thức `queryDR`. |


## Câu hỏi thương gặp

+ Câu hỏi: Ngân Lượng không có hổ trợ `verifyRequestIPN`?
    - Trả lời: Đúng! Ngân Lượng không hổ trợ. Cập nhật trạng thái và xử lý nghiệp vụ
    khi đơn hàng thanh toán thành công đều nằm ở `action` mà bạn thiết lập `return_url` 
    trong phương thức `purchase`.
    
+ Câu hỏi: Vậy thì luồng xử lý sẽ ra sao khách rớt mạng?
    - Trả lời: với chúng tôi `action` của `return_url` chỉ dùng để xác minh tính 
    hợp lệ của dữ liệu Ngân Lượng từ đó hiển thị thanh toán thành công hoặc thất bại
    KHÔNG đụng đến phần cập nhật database và các nghiệp vụ liên quan đến cập nhật
    trạng thái đơn hàng. Phần cập nhật trạng thái và xử lý nghiệp vụ liên quan sẽ
    nằm ở `cron task`, `cron task` sẽ gọi `queryDR` để cập nhật trạng thái và xử lý
    nghiệp vụ.
    
