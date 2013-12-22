<?php
/**
 *  Randomised Programming Quiz System - A quiz system that develops random programming questions from defined templates
 *  Copyright (C) 2010-2013 Ben Evans <ben@nebev.net>
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 **/

// All errors should be on for the CLI
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);

if (!defined('APPLICATION_PATH')) {
    define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
}
if (!defined('APPLICATION_ROOT')) {
    define('APPLICATION_ROOT', realpath(dirname(__FILE__) . '/..'));
}

// Define application environment
if(!defined('APPLICATION_ENV')) {
    define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
}
    
//Define config path
if (!defined('CONFIG_PATH')) {
    define('CONFIG_PATH', APPLICATION_PATH . '/configs/application.ini');
}

// Get other configs
require_once APPLICATION_PATH . '/configs/general.php';


set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_ROOT . '/library',
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

chdir(APPLICATION_PATH);

// Ensure Dates are setup, or default to my hometown!
@$timezone = date_default_timezone_get();
if( $timezone == "UTC" ) {
	date_default_timezone_set("Australia/Sydney");
	cronlog("Your timezone wasn't set, so I set it to Australia/Sydney");
}



// Check to see if Zend is in the PATH
if( !stream_resolve_include_path("Zend/Loader/Autoloader.php") ) {
	cronlog("Zend Framework is not in the current PHP Include Path. Cannot start CLI Environment", 9);
	exit();
}


require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setDefaultAutoloader(create_function('$class',
    "include str_replace('_', '/', \$class) . '.php';"
));

$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        'config' => array(
            APPLICATION_PATH . '/configs/application.ini'
        )
    )
);
// Bootstrap our application
$bootstrap = $application->bootstrap();

$opts = new Zend_Console_Getopt(array(
        'help|h' => 'Displays this help',
        'action|a=s' => 'Specifies the action to perform. The action must be in the form module:controller:action or controller:action',
        'params|p-s' => 'Specifies any params to pass along with the quest in usual HTTP key1=val1&key2=val2&key3=val3 format.'
    ));
$opts->parse();

// Jump out when the request is for help
if(isset($opts->h) || sizeof($opts->getOptions()) == 0 ) {
    echo $opts->getUsageMessage();
    exit(0);
}

// Prepare our custom router
require_once ('Zend/Controller/Router/Interface.php');
require_once ('Zend/Controller/Router/Abstract.php');
class XSync_Controller_Router_Cli extends Zend_Controller_Router_Abstract implements Zend_Controller_Router_Interface {

    public function assemble($userParams, $name = null, $reset = false, $encode = true) { }
    public function route(Zend_Controller_Request_Abstract $dispatcher) {}
}

set_exception_handler('cli_exception_handler');

// Process our action
if(isset($opts->a)) {
    
    // Load up our route and get our variables
    $request_actions = explode(':', $opts->a);
    
    // Reverse our request actions in order to easily skip checking for a default module
    // if it is not present, as the $module variable will be null and Zend will assume 
    // the default module.
    $request_route = array_reverse($request_actions);
    @list($action, $controller, $module) = $request_route;
    $request = new Zend_Controller_Request_Simple($action, $controller, $module);

    // Parse and add any other params into the request
    if(isset($opts->p)) {
        $output = array();
        parse_str($opts->p, $output);
        $request->setParams($output);
    }

    $front = Zend_Controller_Front::getInstance();
    $front->setRequest($request);
    $front->addModuleDirectory(APPLICATION_PATH);
    $front->setRouter(new XSync_Controller_Router_Cli());
    $front->setResponse(new Zend_Controller_Response_Cli());
    $front->throwExceptions(true);
    $front->dispatch();
    
    exit(0);
}

/**
 * Function for logging cron operations.
 * Relies on the constant CLI_CURRENT_LOG_PATH to be defined
 * @param string $message
 * @param int $severity
 */
function cronlog($message, $severity = 0) {
	$log_message = "[".date("Y-m-d H:i:s")."]\t" . $message . "\r\n";
	if( defined("CLI_CURRENT_LOG_PATH") ) {
		file_put_contents(CLI_CURRENT_LOG_PATH, $log_message, FILE_APPEND);
		echo "*" . $log_message;
	}else{
		echo $log_message;
	}
}

function cli_exception_handler($exception) {
	cronlog("ERROR!! - " . $exception->getMessage(), 1);
	cronlog( $exception->getTraceAsString() );
}