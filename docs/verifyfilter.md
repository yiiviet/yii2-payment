# VerifyFilter behavior

Nó là một lớp đối tượng dùng để kiểm tra tính hợp lệ của dữ liệu đầu vào đối với các `action` của `controller`
đảm nhiệm việc handle `request` từ cổng thanh toán, giúp cho bạn giảm bớt nghiệp vụ kiểm tra tính xác thực.

## Cấu hình

Bạn hãy cấu hình nó vào behavior của `controller` chứa `action` đảm nhiệm việc handle. Ví dụ
`action` đảm nhiệm việc handle `request IPN` của Bảo Kim là `ipn` thì ta sẽ thiết lập như sau.

```php

public function behavior() {

    return [
        'verifyIPN' => [
        
        ]
    
    ]
}

```
