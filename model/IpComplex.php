<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/30
 * Time: 15:42
 */

namespace model;

use libs\Exception\InvalidException;
use libs\Model;
use libs\ZP;

/**
 * 对应表ip_lookup_db11,包含经纬度，时区信息，只有ipv4
 * Class IpComplex
 * @package model
 */
class IpComplex extends Model{
    public $ip;

    public $ipLong;
    public $ipStr;

    public $country_code;
    public $country_name;
    public $region_name;
    public $city_name;

    public $latitude;
    public $longitude;
    public $zip_code;
    public $time_zone;

    public $tableName = 'ip_lookup_db11';

    public function __construct($ip) {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->ipStr = $ip;
            $this->ipLong = ip2long($ip);
        } else {
            $this->ipLong = $ip;
            $this->ipStr = $this->_long2ip($ip);
        }

        $this->ip = $ip;
    }

    public function getIpInfo() {

        if (!empty($this->country_code)) {
            return array(
                'ip' => $this->ipStr,
                'country_code' => $this->country_code,
                'country_name' => $this->country_name,
                'region_name' => $this->region_name,
                'city_name' => $this->city_name,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'zip_code' => $this->zip_code,
                'time_zone' => $this->time_zone,
            );
        }

        $sql = "select * from {$this->tableName} where ip_from < :ip order by ip_from desc limit 1";
        $ipInfo = ZP::app()->db->createSql($sql, array(':ip' => $this->ipLong))
            ->queryOne();

        ZP::info('bind ip: ' . $this->ipStr, 'zp');

        if (!empty($ipInfo)) {
            $this->country_code = $ipInfo['country_code'];
            $this->country_name = $ipInfo['country_name'];
            $this->region_name = $ipInfo['region_name'];
            $this->city_name = $ipInfo['city_name'];
            $this->latitude = $ipInfo['latitude'];
            $this->longitude = $ipInfo['longitude'];
            $this->zip_code = $ipInfo['zip_code'];
            $this->time_zone = $ipInfo['time_zone'];
        }
        unset($ipInfo['ip_from']);
        unset($ipInfo['ip_to']);
        $ipInfo['ip'] = $this->ipStr;

        return $ipInfo;
    }


    private function _long2ip($long)
    {
        // 防止在32位系统上发生错误，转换为float
        $long = floatval($long);
        if (0 === $long) {
            throw new InvalidException();
        }

        return long2ip($long);
    }

}