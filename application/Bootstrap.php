<?php
/**
 *  Randomised Programming Quiz System - A quiz system that develops random programming questions from defined templates
 *  Copyright (C) 2010-2012 Ben Evans <ben@nebev.net>
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

