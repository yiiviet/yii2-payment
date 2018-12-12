<?php
/**
 * @link https://github.com/yiiviet/yii2-payment
 * @copyright Copyright (c) 2017 Yii Viet
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */


namespace yiiviet\payment\baokim;

use Yii;

use yii\base\InvalidCallException;

use yiiviet\payment\BankProvider as BaseBankProvider;

/**
 * Lớp BankProvider
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0.3
 */
class BankProvider extends BaseBankProvider
{
    /**
     * Thẻ nội địa.
     */
    const TYPE_LOCAL_CARD = 1;

    /**
     * Thẻ tín dụng quốc tế.
     */
    const TYPE_CREDIT_CARD = 2;

    /**
     * Chuyển khoản online của các ngân hàng.
     */
    const TYPE_INTERNET_BANKING = 3;

    /**
     * Chuyển khoản ATM.
     */
    const TYPE_ATM_TRANSFER = 4;

    /**
     * Chuyển khoản truyền thống giữa các ngân hàng.
     */
    const TYPE_BANK_TRANSFER = 5;

    /**
     * @var int loại ngân hàng muốn lấy, ví dụ atm online, offline, giao dịch tại quầy...
     */
    public $type = self::TYPE_LOCAL_CARD;

    /**
     * @var mixed mã client dùng để truy xuất thông tin, nếu không chỉ định hệ thống sẽ tự động lấy theo client.
     */
    public $emailBusiness;

    /**
     * @var mixed mã client dùng để truy xuất thông tin, nếu không chỉ định hệ thống sẽ tự động lấy.
     */
    public $clientId;

    /**
     * @var PaymentGateway
     * @inheritdoc
     */
    protected $gateway;

    /**
     * @var null|array danh sách ngân hàng
     * @see [[banks()]]
     */
    private $_banks;

    /**
     * @return array
     * @throws \ReflectionException
     * @throws \yii\base\InvalidConfigException
     */
    public function banks(): array
    {
        if ($this->_banks === null) {
            $merchantData = $this->gateway->getMerchantData($this->emailBusiness, $this->clientId);

            if ($merchantData->isOk) {
                $banks = [];

                foreach ($merchantData['bank_payment_methods'] as $bank) {
                    if ($bank['payment_method_type'] == $this->type) {
                        $banks[$bank['id']] = $bank['name'];
                    }
                }

                natsort($banks);

                return $this->_banks = $banks;
            } else {
                throw new InvalidCallException('Can not get bank list!');
            }
        } else {
            return $this->_banks;
        }
    }


}
