<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/31
 * Time: 15:20
 */

namespace libs\Exception;


class ErrorCode {
    const INVALID_PARAM = 10300;
    const INVALID_MOBILE = 10310;

    static $errorMsg = array(
        self::INVALID_PARAM => '参数不符合接口要求',
        self::INVALID_MOBILE => '手机号码必须是数字类型并且不能少于7位',
    );
}