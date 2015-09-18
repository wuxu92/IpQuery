<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/30
 * Time: 10:17
 */

namespace libs;


use model\RenderObject;

class Category {

    /**
     * @var RenderObject
     */
    public $retObj;

    public function __construct() {
        $this->retObj = new RenderObject();
    }

    public function postParam($idx, $default=null)
    {
        if (empty($_POST[$idx])) return $default;
        return $_POST[$idx];
    }

    public function getParam($idx, $default = null)
    {
        if (empty($_GET[$idx])) {
            return $default;
        }
        return $_GET[$idx];
    }

    /**
     * RenderObject 的json方法代理
     * @return $this
     */
    public function json() {
        $this->retObj->json(true, 0);
        return $this;
    }

    /**
     * RenderObject 的error方法代理
     * @param $code
     * @param bool $exit
     * @return $this
     */
    public function error($code, $exit = false)
    {
        $this->retObj->error($code, $exit);
        return $this;
    }
}