<?php
/**
 * Created by PhpStorm.
 * User: wuxu@zplay.com
 * Date: 2015/7/2
 * Time: 9:52
 */
namespace libs;

use \PDO;
use \PDOStatement;
use \Exception;

class DBConnector {

    /**
     * @var string the database source name. the information required to connect to the database
     */
    public $dsn;

    public $username;

    public $password;

    public $attributes;

    /**
     * @var PDO
     */
    public $pdo;

    public $sql;

    /**
     * @var PDOStatement
     */
    public $pdoStatement;

    public $defaultFetchMode = PDO::FETCH_ASSOC;

    public $charset = 'utf8';

    public $pdoClass;

    public $enableSlaves;

    public $slaves = array();

    public $slaveConfig = array();

    Public $masters = array();

    public $masterConfig = array();

    public $_bindParam = array();

    /**
     * @var DBConnector the current slave connection
     * $_slave 本身是一个DBConnector
     */
    public $_slave = false;

	public function __construct($config) 
	{
        if (empty($config)) {
            $config = require(APP_ROOT . '/config/main.php');
            $config = $config['db'];
        }

        if (is_array($config)) {
            foreach ($config as $name => $value) {
                $this->$name = $value;
            }
        }
	}

    public function open()
    {
        if ($this->pdo !== null) {
            return;
        }

        /**
         * if has not established a connection
         */
        // echo "open\r\n";
        if (!empty($this->masters)) {
            $dbc = $this->openDBFromPool($this->masters, $this->masterConfig);
            if ($dbc !== null) {
                $this->pdo= $dbc->pdo;
                $this->username = $dbc->username;
                $this->password = $dbc->password;
                return;
            } else {
                throw new Exception("no master db server if available.");
            }
        }

        if (empty($this->dsn)) {
            throw new Exception("connect source dsn cannot be empty");
        }


        // crate pdo instance
        try {
            $this->pdo = $this->newPDOInstance();
            $this->initPDO();
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }

    /**
     * @param array $pool
     * @param array $baseConfig
     * @return DBConnector
     * @throws Exception
     */
    public function openDBFromPool($pool, $baseConfig)
    {
        //ZP::$logger->info('open from pool', 'uc');

        if (empty($pool)) {
            return null;
        }

        shuffle($pool);

        foreach ($pool as $config) {
            $config = array_merge($baseConfig, $config);
            if (empty($config['dsn'])) {
                throw new Exception("dsn config is null");
            }

            $dbc = new self($config);

            try {
                $dbc->open();
                return $dbc;
            } catch (Exception $e) {
                return null;
            }
        }

        return null;

    }

    public function newPDOInstance()
    {
        return new PDO($this->dsn, $this->username, $this->password, $this->attributes);
    }

    protected function initPDO()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // init charset
        if ($this->charset !== null) {
            $this->pdo->exec('set NAMES ' . $this->pdo->quote($this->charset));
        }
    }

    /**
     * @param bool $fallback
     * @return DBConnector|null
     */
    public function getSlave($fallback=true)
    {
        if (!$this->enableSlaves) {
            return $fallback ? $this : null;
        }

        if ($this->_slave === false) {
            $this->_slave = $this->openDBFromPool($this->slaves, $this->slaveConfig);
        }

        return ($this->_slave === null && $fallback) ? $this : $this->_slave;
    }

    /**
     * 获取当前slave connector的pdo实例
     * @param bool $fallback 使用slave失败后是否回退到master
     * @return null|PDO
     */
    public function getSlavePdo($fallback=true)
    {
        $dbc = $this->getSlave(false);

        //ZP::info("query at slave:" . $this->dsn, 'uc');
        // var_dump($dbc);
        
        if ($dbc === null) {
            return $fallback ? $this->getMasterPdo() : null;
        } else {
            return $dbc->pdo;
        }
    }

    /**
     * 获取主的PDO实例
     */
    public function getMasterPdo()
    {
        $this->open();
        //ZP::info('execute at master:' . $this->dsn, 'uc');
        return $this->pdo;
    }

    public function getLastInsertID()
    {

    }

    /**
     * 判断一个sql查询是不是读查询
     * 如果是读查询则使用slave实例
     * @param $sql
     * @return bool
     */
    public function isReadQuery($sql)
    {
        $pattern = '/^\s*(SELECT|SHOW|DESCRIBE)\b/i';
        return preg_match($pattern, $sql) > 0;
    }

    public function getSql()
    {
        return $this->sql;
    }

    /**
     * 创建一个sql查询，使用占位符设置参数
     * @param string $sql
     * @param array $param 占位符对应到值的数组
     * @return $this
     */
    public function createSql($sql, $param=array())
    {
        $this->sql = $sql;
        $this->_bindParam = array();
        $this->pdoStatement = null;
        
        foreach ($param as $name => $value) {
            $this->bindParam($name, $value);
        }
        ZP::info("create sql:" . $sql . ' with param: ' . var_export($param, true), 'uc');
        return $this;
    }

    public function bindParamArray($param=array())
    {
        // $this->_bindParam = [];
        foreach ($param as $name => $value) {
            $this->bindParam($name, $value);
        }

        //ZP::info("bind param count: " . count($this->_bindParam), 'uc');
        return $this;
    }

    public function bindParam($name, $value, $type=PDO::PARAM_STR)
    {
        $this->_bindParam[$name] = array($value, $type);
        return $this;
    }

    /**
     * bind params to pdo statement
     */
    public function bindPendingParams()
    {
        if ($this->pdoStatement) {
            foreach ($this->_bindParam as $name => $value) {
                // echo "bind $value to $name \r\n";
                $this->pdoStatement->bindValue($name, $value[0], $value[1]);
            }
            $this->_bindParam = array();
        }
    }

    public function prepareSql($readOnly = false)
    {
        if ($this->pdoStatement) {
            $this->bindPendingParams();
            return $this;
        }

        $sql = $this->getSql();

        // check if this sql is a read query
        if ($readOnly || $this->isReadQuery($sql)) {
            // echo 'read sql ' . $sql;
            $pdo = $this->getSlavePdo();
        } else {
            // echo 'not ' . $sql;
            $pdo = $this->getMasterPdo();
        }

        if ($pdo === null) {
        }
        try {
            $this->pdoStatement = $pdo->prepare($sql);
            $this->bindPendingParams();
        } catch (Exception $e) {
            throw new Exception($e->getMessage() . "\nFailed to prepare sql: $sql", "", $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * 查询一条记录
     * @param null $fetchMode
     * @return mixed
     */
    public function queryOne($fetchMode = null)
    {
        return $this->internalQuery('fetch', $fetchMode);
    }

    /**
     * 查询所有符合条件的记录
     * @param null $fetchMode
     * @return mixed
     */
    public function queryAll($fetchMode = null)
    {
        return $this->internalQuery('fetchAll', $fetchMode);
    }

    /**
     * 获取第一条记录的某一列的值
     * @param int $index
     * @return mixed|string
     */
    public function queryScalar($index = 0)
    {
        $result = $this->internalQuery('fetchColumn', $index);

        if (is_resource($result) && get_resource_type($result) === 'stream') {
            return stream_get_contents($result);
        } else {
            return $result;
        }
    }

    /**
     * @return int
     * @throws Exception
     */
    public function execute()
    {
        $sql = $this->getSql();

        // Logger::debug("sql: $sql", 'uc');

        if ($sql == '') {
            return 0;
        }

        $this->prepareSql();

        try {
            $this->pdoStatement->execute();
            $n = $this->pdoStatement->rowCount();
            return $n;
        } catch (Exception $e) {
            ZP::info("query exception" . $e->getMessage() , 'uc');
            return false;
        }
    }

    public function exec()
    {
        return $this->execute();
    }

    /**
     * execute a query/readonly sql
     * @param $method
     * @param null $fetchMode
     * @return mixed
     * @throws Exception
     */
    protected function internalQuery($method, $fetchMode = null)
    {
        $this->prepareSql(true);

        //ZP::info("pdo statement: " . var_export($this->pdoStatement, true), 'zp');
        try {
            $this->pdoStatement->execute();

            if ($fetchMode === null) {
                $fetchMode = $this->defaultFetchMode;
            }

            $result = call_user_func_array(array($this->pdoStatement, $method), (array)$fetchMode);
            $this->pdoStatement->closeCursor();

        } catch (Exception $e) {
            $result = null;
        }

        return $result;
    }

}