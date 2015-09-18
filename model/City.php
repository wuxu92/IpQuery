<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/31
 * Time: 16:31
 */

namespace model;


use libs\Helper\GeoHelper;
use libs\Model;
use libs\ZP;

class City extends Model {

    public $tableName = 'city_china';
    //public $tableName = 'city_lookup';
    public $globalTable = 'city_lookup';

    public $country;
    public $city;
    public $accentcity;
    public $region;
    public $population;
    public $latitude;
    public $longitude;

    public $originLatitude;
    public $originLongitude;

    public function tableName() {
        return $this->tableName;
    }

    /**
     * @param $longi
     * @param $lati
     */
    public function __construct($longi, $lati) {
        $this->originLongitude = $longi;
        $this->originLatitude  = $lati;
    }


    public function getNearestCity() {
        return $this->getNearNCities(1);
    }

    /**
     * 搜索一定范围内的城市，默认返回最近的一个城市
     * @param $N
     * @return mixed
     */
    public function getNearNCities($N=1) {
        $rangeLati = GeoHelper::calcNearLatitude($this->originLatitude, 100);
        $rangeLongi = GeoHelper::calcNearLongitude($this->originLongitude, 100);

        $fromLati = $this->originLatitude - $rangeLati;
        $toLati   = $this->originLatitude + $rangeLati;
        $fromLongi = $this->originLongitude - $rangeLongi;
        $toLongi    = $this->originLongitude + $rangeLongi;

        $sql = "select * from {$this->tableName} where latitude>:fromLati and latitude<:toLati and longitude>:fromLongi and longitude<:toLongi limit 100";

        $candidates = ZP::app()->db->createSql($sql, array(
            ':fromLati' => $fromLati,
            ':toLati' => $toLati,
            ':fromLongi' => $fromLongi,
            ':toLongi' => $toLongi
        ))->queryAll();

        return $candidates;
    }


}