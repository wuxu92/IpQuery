<?php 

if ( !class_exists('\SplClassLoader')) {
	require(__DIR__ . '/spl-class-loader/SplClassLoader.php');
}

$classLoader = new \SplClassLoader('model', dirname(__DIR__) . '/');
$classLoader->register();

$classLoader = new \SplClassLoader('libs', APP_ROOT );
$classLoader->register();

$classLoader = new \SplClassLoader('cat', APP_ROOT );
$classLoader->register();
