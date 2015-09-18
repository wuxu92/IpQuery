<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/11
 * Time: 16:42
 */

namespace model;


use libs\Model;
use libs\ZP;

class Mobile extends Model {
    public $tableName = 'mobile_lookup';

    public $id;
    public $number;
    public $city;
    public $type;
    public $areacode;
    public $zipcode;

    public function tableName() {
        return $this->tableName;
    }

    /**
     * 根据手机号码获取信息
     * @param $number
     * @return Mobile
     */
    public static function getByNumber($number) {
        $m = new Mobile();
        if (!is_numeric($number)) return $m;

        $sql = "select * from {$m->tableName()} where number=:number";
        $row = ZP::app()->db->createSql($sql, array(
            'number' => $number
        ))->queryOne();

        if (empty($row)) return $m;

        //var_dump(defined('DEBUG') && DEBUG); exit();

        $m->id = $row['id'];
        $m->number = $row['number'];
        $m->city = $row['city'];
        $m->type = $row['type'];
        $m->areacode = $row['areacode'];
        $m->zipcode  = $row['zipcode'];

        ZP::trace("mobile @" . $m->id);
        return $m;
    }
}