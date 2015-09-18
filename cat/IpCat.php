<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/30
 * Time: 10:15
 */

namespace cat;

use libs\Category;
use model\IpComplex;
use model\IpSimple;

class IpCat extends Category {

    public function indexAction() {
        echo "<h3>hello cat index</h3>";
    }

    public function queryAction() {
        $ip = $this->getParam('ip');
        if (empty($ip)) {
            $this->retObj->append('参数不正确')
                ->json(true);
        }

        $type = $this->getParam('type', 'simple');

        if ($type == 'simple') $ip = new IpSimple($ip);
        else $ip = new IpComplex($ip);
        // $ip = new IpSimple($ip);

        //var_dump($ip); exit;
        $data = $ip->getIpInfo();

        $this->retObj->append($data)
            ->json(true);

        // ignore below
        return array(
            'request' => 'ipQuery'
        );
    }

    public function qqwryAction() {

        $ip = $this->getParam('ip');
        if (empty($ip)) {
            $this->retObj->append('参数不正确')
                ->json(true);
        }

        if ( !filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->retObj->append('ip参数格式不正确')
                ->json(true);
        }

        $qqwry = new \qqwry(APP_ROOT . '/ext/qqwry.dat');
        //var_dump(serialize($qqwry));
        list($addr1,$addr2)=$qqwry->q($ip);
        $addr1=iconv('GB2312','UTF-8',$addr1);
        $addr2=iconv('GB2312','UTF-8',$addr2);

        $provinces = array(
            '北京' => '北京市',
            '上海' => '上海市',
            '天津' => '天津市',
            '重庆' => '重庆市',
            '台湾' => '台湾省',
            '河北' => '河北省',
            '湖北' => '湖北省',
            '河南' => '河南省',
            '海南' => '海南省',
            '广东' => '广东省',
            '甘肃' => '甘肃省',
            '黑龙' => '黑龙江省',
            '青海' => '青海省',
            '湖南' => '湖南省',
            '吉林' => '吉林省',
            '江苏' => '江苏省',
            '江西' => '江西省',
            '辽宁' => '辽宁省',
            '云南' => '云南省',
            '山东' => '山东省',
            '山西' => '山西省',
            '陕西' => '陕西省',
            '四川' => '四川省',
            '浙江' => '浙江省',
            '安徽' => '安徽省',
            '贵州' => '贵州省',
            '福建' => '福建省',
            '新疆' =>'新疆维吾尔自治区',
            '内蒙' => '内蒙古自治区',
            '宁夏' => '内蒙古自治区',
            '广西' => '广西壮族自治区',
            '西藏' => '西藏自治区',
            '澳门' => '澳门特别行政区',
            '香港' => '香港特别行政区'
        );

        $prov = '';
        $city = '';
        $area = '';
        $country = '中国';

        $cityStr = $addr1;
        // 判断是否有“省”， “自治区”， “行政区”
        if ($pp = strpos($addr1, '省') !== false) {
            $tmp = explode('省', $addr1);
            $prov = $tmp[0] . '省';
            $cityStr = $tmp[1];
        }
        else if ($pp = strpos($addr1, '自治区') !== false) {
            $tmp = explode('自治区', $addr1);
            $prov = $tmp[0] . '自治区';
            $cityStr = $tmp[1];
        }
        else if ($pp = strpos($addr1, '行政区') !== false) {
            $tmp = explode('行政区', $addr1);
            $prov = $tmp[0] . '行政区';
            $cityStr = $tmp[1];
        }

        if ($prov === '') {
            $pStrLen = strlen('广西');
            $pStr = substr($addr1, 0, $pStrLen);
            //echo $pStr;
            if (array_key_exists($pStr,$provinces)) {
                $prov = $provinces[$pStr];
                if ($pStr == '黑龙') $pStrLen = strlen('黑龙江');
                $cityStr = substr($addr1, $pStrLen);
            } else {
                $country = $addr1;
            }
        }
        if (strpos($cityStr, '市') > 0) {
            $cityAndArea = explode('市', $cityStr);
            if (is_array($cityAndArea)) $city = $cityAndArea[0] . '市';
            if (isset($cityAndArea[1])) $area = $cityAndArea[1];
        }

        $this->retObj->append(array(
                'country' => $country,
                'province' => $prov,
                'city' => $city,
                'area' => $area,
                'isp'  => $addr2,
                'addr' => $addr1
            )
        )
            ->json(true);

        //$arr=$qqwry->q('64.233.187.99');
        //$arr[0]=iconv('GB2312','UTF-8',$arr[0]);
        //$arr[1]=iconv('GB2312','UTF-8',$arr[1]);
        //echo $arr[0],'|',$arr[1],"<br/>";
    }
}