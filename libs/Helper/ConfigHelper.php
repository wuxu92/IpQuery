<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/8/10
 * Time: 10:15
 */

namespace libs\Helper;

/**
 * 用来读取配置文件的辅助类
 * Class ConfigHelper
 * @package libs\Helper
 */
class ConfigHelper {

    public static $instance;

    /**
     * @var string
     */
    public $configPath;
    /**
     * @var array
     */
    public $config;

    public function __construct($path = 'config/main.php') {
        $this->configPath = APP_ROOT . '/' . $path;
        if (is_file($this->configPath)) {
            $this->config = require($this->configPath);
        } else {
            $this->config = array();
        }
    }

}