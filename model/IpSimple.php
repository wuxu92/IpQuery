<?php 

namespace model;

use libs\Exception\InvalidException;
use libs\Model;
use libs\ZP;

class IpSimple extends Model{
	// ip版本
    public $version;

	// ip的值, 初始化使用的值
	public $ip;

    public $ipLong;
    public $ipStr;

	// 对应国家
	public $country;

	// 对应的省份
	public $prov;

	// 对应的城市
	public $city;

    public $tableName = 'ip_lookup';

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

	public function getIpString() {
		return $this->ipStr;
	}

    public function getIpLong() {
        return $this->ipLong;
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

    public function getIpInfo() {
        $ip = $this->ip;

        if (!empty($this->country)) {
            return array(
                'ip' => $this->ipStr,
                'country' => $this->country,
                'prov' => $this->prov,
                'city' => $this->city,
                'version' => $this->version,
            );
        }

        $sql = "select * from {$this->tableName} where ip_start < :ip order by ip_start desc limit 1";
        $ipInfo = ZP::app()->db->createSql($sql, array(':ip' => $this->ipStr))
        ->queryOne();

        ZP::info('bind ip: ' . $this->ipStr, 'zp');

        if (!empty($ipInfo)) {
            $this->country = $ipInfo['country'];
            $this->prov = $ipInfo['stateprov'];
            $this->city = $ipInfo['city'];
            $this->version = $ipInfo['ipversion'];
        }

        unset($ipInfo['ip_start']);
        unset($ipInfo['ip_end']);
        $ipInfo['ip'] = $this->ipStr;
        return $ipInfo;
    }

}