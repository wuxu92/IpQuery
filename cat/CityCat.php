<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/31
 * Time: 15:12
 */

namespace cat;

use libs\Category;
use libs\Exception\ErrorCode;
use model\City;

class CityCat extends Category {

    public $searchDistance = 100; // 找东南西北100km内的城市
    /**
     * 通过传入两个参数找到离他最近的城市
     * @method GET
     */
    public function locateAction() {

        // 查找两个参数
        $longitude = floatval($this->getParam('longitude'));
        $latitude = floatval($this->getParam('latitude'));

        if (null === $longitude || null === $latitude) {
            $this->error(ErrorCode::INVALID_PARAM, true);
        }

        // 查找城市

        $city = new City($longitude, $latitude);
        $this->retObj->append($city->getNearestCity())->json();
    }
}