<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
	APPLICATION_PATH,
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));


// Check to see if all required files exist
if( !file_exists(APPLICATION_PATH . '/configs/application.ini') || !file_exists(APPLICATION_PATH . '/configs/general.php') ) {
	die("Cannot Find application.ini / general.php");
}
if( !is_writable(APPLICATION_PATH . "/../tmp") ) {
	die("Either ". realpath(APPLICATION_PATH . "/../") ."/tmp doesn't exist, or is not writable");
}
if( !extension_loaded('gd') ) {
	die("The GD2 Extension for PHP is not enabled. Please enable it");
}
if( !extension_loaded('ldap') ) {
	die("The LDAP Extension for PHP is not enabled. Please enable it");
}


/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();
