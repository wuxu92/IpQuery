<?php 
// 
return array(
    'db' => array(
        'enableSlaves' => true,
        'masterConfig' => array(
            'username' => 'developer',
            'password' => '&2015',
            'attributes' => array(
                PDO::ATTR_TIMEOUT => 10,
            )
        ),
        'masters' => array(
            array('dsn' => 'mysql:host=192.168.0.111;dbname=ip2location'),
            array('dsn' => 'mysql:host=192.168.0.111;dbname=ip2location')
        ),
        'slaveConfig' => array(
            'username' => 'developer',
            'password' => '&2015',
            'attributes' => array(
                PDO::ATTR_TIMEOUT => 10,
            )
        ),
        'slaves' => array(
            array('dsn' => 'mysql:host=192.168.0.111;dbname=ip2location'),
            array('dsn' => 'mysql:host=192.168.0.111;dbname=ip2location'),
            array('dsn' => 'mysql:host=192.168.0.111;dbname=ip2location')
        )
    ),
);
