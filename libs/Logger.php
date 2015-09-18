<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/29
 * Time: 16:17
 */

namespace libs;
use libs\Exception\InvalidException;

/**
 * 日志记录，只提供记录到文件的功能。
 * 对于大量日志提供rotate file
 * Class Logger
 * @property  traceLevel
 * @package base
 */
class Logger {

    /**
     * log levels
     */
    const LEVEL_ERROR = 0x01;
    const LEVEL_WARNING = 0x02;
    const LEVEL_INFO    = 0x04;
    const LEVEL_TRACE   = 0x08;
    const LEVEL_PROFILE = 0x16;

    public $enableRotateFile = true;
    public $logFile;
    public $logLevel;
    public $maxFileSize = 1;//10240; // 10M
    public $maxLogFiles = 5;
    public $rotateByCopy= true;

    public $flushInterval = 1;
    public $traceLevel = 3;
    public $oldTraceLevel = 0;

    /**
     * 暂存日志记录
     * @var array
     */
    public $messages = array();

    public function __construct() {
        $this->logFile = APP_ROOT . '/logs/main.log';
        $this->logLevel = self::LEVEL_TRACE;
    }

    public function error($msg, $cate=null) {
        $this->log($msg, self::LEVEL_ERROR, $cate);
    }

    public function info($msg, $cate=null) {
        $this->log($msg, self::LEVEL_INFO, $cate);
    }

    public function trace($msg, $cate=null) {
        $this->log($msg, self::LEVEL_TRACE, $cate);
    }

    public function warning($msg, $cate=null) {
        $this->log($msg, self::LEVEL_WARNING, $cate);
    }

    public function profile($msg, $cate=null) {
        $this->log($msg, self::LEVEL_PROFILE, $cate);
    }

    public function log($message, $level, $cate = 'app', $trace=true) {
        $time = microtime(true);

        //var_dump($time);exit;

        // get one trace level
        $ts = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_pop($ts);
        //$lastTrace = $ts[1];

        $traces = [];
        if ($this->traceLevel > 0 && $trace) {
            $count = 0;
            $ts = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_pop($ts); // remove the last trace since it would be the entry script, not very useful
            foreach ($ts as $trace) {
                if (isset($trace['file'], $trace['line'])) {
                    unset($trace['object'], $trace['args']);
                    $traces[] = $trace;
                    if (++$count >= $this->traceLevel) {
                        break;
                    }
                }
            }
        }


        //if ($lastTrace && isset($lastTrace['file'], $lastTrace['line'])) {
        //    unset($lastTrace['object'], $lastTrace['args']);
        //}

        $this->messages[] = array($message, $level, $cate, $time, $traces);

        // if need flush
        if ($this->flushInterval > 0 && count($this->messages) > $this->flushInterval) {
            $this->export();
        }
    }

    /**
     * write log messages to file
     */
    public function export() {
        //var_dump("export"); exit;

        $text = implode("\n", array_map(array($this, 'formatMessage'), $this->messages));

        // empty the old array
        $this->messages = array();

        if (($fp = @fopen($this->logFile, 'a')) === false) {
            throw new InvalidException("不能打开日志文件 {$this->logFile}");
        }

        @flock($fp, LOCK_EX);

        if ($this->enableRotateFile) {
            clearstatcache();
        }

        if ($this->enableRotateFile && @filesize($this->logFile) > $this->maxFileSize * 1024) {
            //echo "rotate"; exit;
            $this->rotateFile();
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($this->logFile, $text, FILE_APPEND | LOCK_EX);
        } else {
            @fwrite($fp, $text);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
        // todo file mode @chmod
    }

    protected function formatMessage($msg) {
        list($text, $level, $cate, $time) = $msg;
        $level = $this->getLevelName($level);

        // 只有一个trace
        $trace = '';
        $ts = [];
        if (isset($msg[4])) {
            foreach ($msg[4] as $trace) {
                $ts[] = 'file: ' . $trace['file'] . ' line: ' . $trace['line'];
            }
        }
        if (!empty($ts)) $trace = implode("\n ", $ts);
        $sessionId = session_id();

        return date('Y-m-d H:i:s', $time) . " {$sessionId}[$level][$cate] $text\n$trace";
    }

    /**
     * 日志文件轮转，使用拷贝或者重命名
     * 重命名速度更快，但是在Windows下可能存在问题
     */
    protected function rotateFile() {
        $file = $this->logFile;
        for ($i = $this->maxLogFiles; $i > 0; --$i) {
            $rotateFile = $file . ($i === 0 ? '' : '.' . $i);
            // echo $rotateFile;

            if (is_file($rotateFile)) {
                if ($i === $this->maxLogFiles) {
                    // delete old file
                    @unlink($rotateFile);
                } else {
                    if ($this->rotateByCopy) {
                        // 原来编号为i的写道i+1
                        if (!copy($rotateFile, $file . '.' . ($i + 1))) {
                            $errors = error_get_last();
                            var_dump($errors);
                            exit;
                        };
                        if ($fp = @fopen($rotateFile, 'a')) {
                            @ftruncate($fp, 0);
                            @fclose($fp);
                        }
                    } else {
                        @rename(@$rotateFile, $file . '.' . ($i + 1));
                    }
                }
            }
        }
    }

    private function getLevelName($level) {
        static $levels = array(
            self::LEVEL_ERROR => 'error',
            self::LEVEL_WARNING => 'warning',
            self::LEVEL_INFO => 'info',
            self::LEVEL_TRACE => 'trace',
        );

        return isset($levels[$level]) ? $levels[$level] : 'unknown';
    }

    private function flush() {
        $msgs = $this->messages;
        $this->messages = array();

        $this->export();
    }

    public function closeTrace() {
        $this->oldTraceLevel = $this->traceLevel;
        $this->traceLevel = 0;
    }

    public function restoreTrace() {
        $this->traceLevel = $this->oldTraceLevel;
    }

}