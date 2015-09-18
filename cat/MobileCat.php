<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/11
 * Time: 17:14
 */

namespace cat;


use libs\Category;
use libs\Exception\ErrorCode;
use libs\Exception\InvalidException;
use model\Mobile;

class MobileCat extends Category {

    /**
     * @method GET
     * @throws InvalidException
     * @internal param 手机号码 $number
     */
    public function queryAction() {
        $number = $this->getParam('number', null);

        /**
         * number 必须是数字类型并且不能少于7位
         */
        if (!is_numeric($number) || strlen($number) < 7 ) {
            $this->error(ErrorCode::INVALID_MOBILE, true);
        }

        $number = intval(substr($number, 0, 7));
        $m = Mobile::getByNumber($number);
        $this->retObj->append(array(
            'city' => $m->city,
            'type' => $m->type,
            'areacode' => $m->areacode,
            'zipcode'  => $m->zipcode
        ))->json();

        // 忽略，下面的代码不会执行
        return $m;
    }
}