<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/30
 * Time: 11:24
 */

namespace model;
use libs\Exception\ErrorCode;
use libs\ZP;

/**
 * Class RenderObject 用于返回给api调用的类
 * @package model
 */
class RenderObject {

    public $status;
    public $msg;
    public $data;

    public function __construct($status=0, $msg='', $data=array()) {
        $this->status =$status;
        $this->msg = $msg;
        $this->data = $data;
    }

    public function setData($data) {
        $this->data = $data;
    }

    /**
     * 需要data字段是数组类型才可以追加数据，否则不做处理
     * @param $data
     * @return $this
     */
    public function append($data)
    {
        if (is_array($this->data)) {
            if (is_array($data)){
                foreach ($data as $k => $v) {
                    $this->data[$k] = $v;
                }
            } else {
                $this->data[] = $data;
            }
        }
        return $this;
    }

    /**
     * @param bool $exit 是否输出内容后退出程序,默认json后会退出
     * @param int $exitCode
     */
    public function json($exit = true, $exitCode=0) {
        echo json_encode(array(
            'status' => $this->status,
            'msg'    => $this->msg,
            'data'   => $this->data
        ));

        if (true === $exit) {
            // log
            //global $startTime;
            ZP::end($exitCode);
        }
    }

    /**
     * 通过 ErrorCode 设置返回对象的内容，如果设置了exit参数为true，则直接调用json方法并退出程序
     * 否则应该在后面手动调用json()方法退出
     * @param $errorCode int
     * @param bool $exit
     * @return $this
     */
    public function error($errorCode, $exit=false) {
        if (isset(ErrorCode::$errorMsg[$errorCode])) {
            $this->status = $errorCode;
            $this->msg = ErrorCode::$errorMsg[$errorCode];
        }
        $this->status = $errorCode;

        if (true === $exit) {
            $this->json(true, 1);
        }

        return $this;
    }

}