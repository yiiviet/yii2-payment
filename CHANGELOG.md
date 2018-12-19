Change Log
==========================

1.0.3 under development
--------------------------

- Tính năng #1: Hổ trợ widget để xuất thông tin ngân hàng (vuongxuongminh)
- Tính năng #10: Thêm validator `bankvn` hổ trợ kiểm tra mã ngân hàng (vuongxuongminh)
- Tính năng #8: Thêm thuộc tính `autoDisableControllerCsrfValidation` tại `\yiiviet\payment\VerifyFilter` (vuongxuongminh). 
- Lỗi #7: xác minh `checksum` tại phương thức `verifyPurchaseSuccess` của Bảo Kim (vuongxuongminh).
- Lỗi #6: `checksum` vẫn bị yêu cầu đối với phương thức `purchase` pro của Bảo Kim (vuongxuongminh).
- Lỗi #5: Xác minh chữ ký dữ liểu gửi từ VTCPay (vuongxuongminh).

1.0.2
--------------------------

- Tính năng #3: Hổ trợ cổng thanh toán VTCPay (vuongxuongminh).
- Tính năng #2: Thêm `yiiviet\payment\VerifyFilter` hổ trợ việc xác minh tính hợp lệ của dữ liệu
gửi từ cổng thanh toán (vuongxuongminh).

1.0.1
--------------------------

- Nâng cấp: Gói `vxm/yii2-gateway-clients` lên phiên bản `2.0.0` (vuongxuongminh).
