<?php
/**
 * @link https://github.com/yii2-vn/payment
 * @copyright Copyright (c) 2017 Yii2VN
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2vn\payment\baokim;

use yii2vn\payment\BaseRsaDataSignature;

/**
 * Class RsaDataSignature
 *
 * @author Vuong Minh <vuongxuongminh@gmail.com>
 * @since 1.0
 */
class RsaDataSignature extends BaseRsaDataSignature
{

    const HTTP_METHOD_POST = 'POST';

    const HTTP_METHOD_GET = 'GET';

    /**
     * @var string
     */
    public $urlPath;

    /**
     * @var string
     */
    public $httpMethod;

    /**
     * @return string
     */
    protected function getDataString(): string
    {
        $data = $this->getData();
        ksort($data);
        $httpMethod = strtoupper($this->httpMethod);
        $str = $httpMethod . '&' . urlencode($this->urlPath);
        $str .= '&' . urlencode(http_build_query(self::HTTP_METHOD_GET === $httpMethod ? $data : [])) . '&' . urlencode(http_build_query(self::HTTP_METHOD_POST === $httpMethod ? $data : []));

        return $str;
    }

}