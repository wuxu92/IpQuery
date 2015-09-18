<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/30
 * Time: 11:16
 */

namespace libs;


class ExceptionHandler {

    /**
     * @param $ex \Exception
     */
    public function exceptionHandler($ex) {
        if (function_exists('http_response_code')) http_response_code(500);
        else header("HTTP/1.0 500 error");
        echo json_encode(array(
            'status' => 500,
            'msg' => 'server internal exception',
            'data' => $ex->getMessage()
        ));
        exit(1);
    }

    public function errorHandler($code, $message, $file, $line) {
        if (function_exists('http_response_code')) http_response_code(500);
        else header("HTTP/1.0 500 error");
        echo json_encode(array(
            'status' => 500,
            'msg' => 'server internal error',
            'data' => "error code: $code, message: " . ($message) . ", from file: $file, at line: $line"
        ));
        exit(1);
    }

    public function register() {
        set_exception_handler(array($this, 'exceptionHandler'));
        set_error_handler(array($this, 'errorHandler'));
    }

    public function unregister() {
        restore_error_handler();
        restore_exception_handler();
    }
}