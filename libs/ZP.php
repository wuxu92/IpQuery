<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/30
 * Time: 11:39
 */

namespace libs;

/**
 * Class ZP 各个组件的入口，实现各个静态方法
 * @package libs
 */
class ZP {

    /**
     * @var ZP
     */
    private static  $ins;

    /**
     * @var Logger
     */
    public static $logger;

    /**
     * @var DBConnector
     */
    public $db;

    public static function setLogger() {
        self::$logger = new Logger();
        //var_dump(self::$logger);
    }

    public function setDb($config=null) {
        $this->db = new DBConnector($config);
    }

    public static function init() {
        $i = self::app();
        //var_dump($i);exit;
    }

    public static function app() {
        if (ZP::$ins instanceof ZP) return ZP::$ins;
        else {
            ZP::$ins = new ZP();
            ZP::$ins->setDb();
            ZP::$ins->setLogger();
            return ZP::$ins;
        }
    }

    /**调用$logger记录日志
     * @param $msg
     * @param null $cate
     * @param bool $trace
     */
    public static function error($msg, $cate=null, $trace=true) {
        self::$logger->log($msg, Logger::LEVEL_ERROR, $cate, $trace);
    }

    /**
     * 调用$logger记录日志
     * @param $msg string 要记录的消息
     * @param null $cate string 消息的分类
     * @param bool $trace 是否记录backtrace信息，默认为是
     */
    public static function info($msg, $cate=null, $trace=true) {
        self::$logger->log($msg, Logger::LEVEL_INFO, $cate, $trace);
    }

    /**
     * 调用$logger记录日志
     * @param $msg
     * @param null $cate
     * @param bool $trace
     */
    public static function trace($msg, $cate=null, $trace=true) {
        if (defined('DEBUG') && DEBUG) {
            self::$logger->log($msg, Logger::LEVEL_TRACE, $cate, $trace);
        }
    }

    /**
     * 调用$logger记录日志
     * @param $msg
     * @param null $cate
     * @param bool $trace
     */
    public static function warning($msg, $cate=null, $trace=true) {
        self::$logger->log($msg, Logger::LEVEL_WARNING, $cate, $trace);
    }

    /**
     * 调用$logger记录日志
     * @param $msg
     * @param null $cate
     * @param bool $trace
     */
    public static function profile($msg, $cate=null, $trace=true) {
        if (defined(DEBUG) && DEBUG) {
            self::$logger->log($msg, Logger::LEVEL_PROFILE, $cate, $trace);
        }
    }

    /**
     * 运行的结束函数，理论上所有请求的结束都应该以调用此函数结束
     * @param int $exitCode 退出状态码
     */
    public static function end($exitCode = 0)
    {
        global $startTime;
        //ZP::$logger->closeTrace();
        ZP::info('end: ' . microtime(true) . ' cost time: ' . (microtime(true) - $startTime), null, false);
        //ZP::$logger->restoreTrace();

        exit($exitCode);
    }

}