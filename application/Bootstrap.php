<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initRequest()
	{
		// Get our General Configuration (I'm sure there's a better way to do this, but for now we'll use it)
		include_once( APPLICATION_PATH . '/configs/general.php' );
		
		require_once 'Zend/Loader/PluginLoader.php';
		
		ini_set('display_errors', 1);
		
		
	}


	protected function _initAutoload()
	{
		$moduleLoader = new Zend_Application_Module_Autoloader(array(
			'namespace' => '', 
			'basePath'  => APPLICATION_PATH));

			//Load Config
			$config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);

			//init database        
			$params = $config->resources->db->params->toArray();
			$db = Zend_Db::factory($config->resources->db->adapter, $params);
			Zend_Registry::set('db', $db);

			// Make sure that the appropriate configurations are set
			$this->checkConfig();
			   
			return $moduleLoader;
		}



	/**
	 * Checks that the environment in which this is running is adequate.
	 * Checks configuration variables, writable paths etc.
	 *
	 * @return void
	 * @author Ben Evans
	 */
	private function checkConfig() {
		$defined_variables = array(
			"QUIZ_SYSTEM_NAME"		=>	"The Quiz System Name",
			"COMPILER_TYPE"			=>	"The Quiz System's Compiler Type",
			"COMPILER_PATH"			=>	"The Quiz System's Compiler Path",
			"AUTH_METHOD"			=>	"Authentication Method",
			"QUIZ_ADMINISTRATORS"	=>	"Quiz Administrator Group",
			"MAX_HALLOFFAME"		=>	"Maximum amount of Hall of Fame Scores",
			"DEFAULT_DATE_FORMAT"	=>	"Default Date Format",
		);
	
		foreach($defined_variables as $key=>$dv) {
			if( !defined($key) ) {
				die("Configuration Error. Required Parameter " . $key . "(". $dv .") is not defined.");
			}
		}
	
	}


}

