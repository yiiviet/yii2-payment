# Bank widget & validator

Bank widget và validator là cặp đôi hổ trợ cho bạn trong việc xuất danh sách ngân hàng và
kiểm tra tính hợp lệ của mã ngân hàng khi người dùng cuối gửi đến.

Chỉ hổ trợ cho các cổng thanh toán sau:

* [Bảo Kim](https://baokim.vn)
* [Ngân Lượng](https://nganluong.vn)
* [VnPayment](https://vnpayment.vn)

Vì những cổng thanh toán còn lại không yêu cầu `bank id` nên nếu như bạn không thuộc
`merchant` của các cổng thanh toán nêu trên thì `không` cần xem tài liệu này.

## Widget

Hổ việc `render` danh sách ngân hàng tại tầng `view` thông qua lớp 
`yiiviet\payment\BankWidget` bạn chỉ cần gắn widget vào `input bank id`

Ví dụ:

+ App components:

```php
'components' => [
    'BKGateway' => [
        'class' => 'nhuluc\payment\baokim\PaymentGateway',
        'sandbox' => true,
        'pro' => true, // Sử dụng phương thức PRO bạn sẽ `redirect` khách trực tiếp đến bank không thông qua Bảo Kim. Ngược lại `FALSE` thì thanh toán thông qua Bảo Kim.
    ]
]

```

+ Tại tầng view:

```php

use yii\widgets\ActiveForm;
use nhuluc\payment\BankWidget;

$form = ActiveForm::begin();

$form->field($model, 'bank_id')->widget(BankWidget::class, [
    'gateway' => 'BKGateway'
]);

ActiveForm::end()

```

> Tham trị `gateway` khi khai báo widget là đối tượng cổng thanh toán có thể là id trong component hoặc là object riêng của bạn.

## Validator

Sau khi `render` tại tầng `view` chúng ta cần kiểm tra tính trọn vẹn của `bank id`
khi gửi về lại máy chủ nên tại đây validator `bankvn` sẽ hổ trợ cho bạn vấn đề này

Ví dụ:

+ App components:

```php
'components' => [
    'BKGateway' => [
        'class' => 'nhuluc\payment\baokim\PaymentGateway',
        'sandbox' => true,
        'pro' => true, // Sử dụng phương thức PRO bạn sẽ `redirect` khách trực tiếp đến bank không thông qua Bảo Kim. Ngược lại `FALSE` thì thanh toán thông qua Bảo Kim.
    ]
]

```

+ Tại Model hay Active Record:

```php

class Payment extends Model {

    public function rules() {
    
        return [
            ['bank_id', 'bankvn', 'gateway' => 'BKGateway']
        ];
    
    }
}
```

> Tham trị `gateway` khi khai báo rule là đối tượng cổng thanh toán có thể là id trong component hoặc là object riêng của bạn.
