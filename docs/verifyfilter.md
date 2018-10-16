# VerifyFilter behavior

Nó là một lớp đối tượng dùng để kiểm tra tính hợp lệ của dữ liệu đầu vào đối với các `action` của `controller`
đảm nhiệm việc handle `request` từ cổng thanh toán, giúp cho bạn giảm bớt nghiệp vụ kiểm tra tính xác thực.

## Cấu hình

Bạn hãy cấu hình nó vào behavior của `controller` chứa `action` đảm nhiệm việc handle. Ví dụ
`action` đảm nhiệm việc handle `request IPN` của Bảo Kim là `ipn` và  `action` `success`
 đảm nhiệm việc handle `request purchase success` thì ta sẽ thiết lập như sau:

```php


class TestController extends \yii\web\Controller {

    public function behaviors() {
    
        return [
            'verifyFilter' => [
                'class' => 'yiiviet\payment\VerifyFilter',
                'gateway' => \Yii::$app->BKGateway,
                'commands' => [
                    'ipn' => 'IPN',
                    'success' => 'purchaseSuccess'
                ]
            ]
        ];
    }
    
    public function actionIpn() {
        /** @var \yiiviet\payment\baokim\VerifiedData $verifiedData */
        $verifiedData = $this->verifiedData;
    
        if ($verifiedData->transaction_status == 4) {
        
            // do business logic
        }
        
    }

    public function actionSuccess() {
        /** @var \yiiviet\payment\baokim\VerifiedData $verifiedData */
        $verifiedData = $this->verifiedData;
    
        if ($verifiedData->transaction_status == 4) {
        
            // do business logic
        }
        
    }    
}

```

> Thuộc tính `commands` là mảng có các phần tử mang khóa là `id` của `action` và giá trị là
lệnh cần thực hiện xác minh.

> Như bạn thấy nếu như action `ipn` được chạy thì bạn có thể truy cập đến property `verifiedData` để lấy dữ liệu đã xác minh 
tính hợp lệ.
 

