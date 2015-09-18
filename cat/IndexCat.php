<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/31
 * Time: 15:49
 */

namespace cat;


use libs\Exception\ErrorCode;

class IndexCat {

    /**
     * 用来测试一些语言特性的接口
     */
    public function testAction() {
        var_dump(ErrorCode::INVALID_PARAM);
        echo isset(ErrorCode::$errorMsg[10301]);
        //var_dump(ErrorCode::$errorMsg);
        exit();
    }
}