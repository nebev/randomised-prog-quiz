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

/**
 * A class that assists in logging information about exceptions
 * @author Ben Evans
 */
class Model_Shell_Debug {
	
	protected $filename;
	protected $log_contents;
	protected static $instance = null;

	/**
	 * This isn't really good practice, but since Zend 1 lacks any real concepts of Dependency Injection...
	 */
	protected function __construct() {
		if( Zend_Auth::getInstance()->hasIdentity() ) {
			$filename = preg_replace("/^[A-Za-z0-9]+$/", "", Zend_Auth::getInstance()->getIdentity()->username);
			$filename = time() . "_" . $filename;
		}else{
			$filename = time() . "_unknown";
		}
		
		$filename .= ".log";
		$this->filename = $filename;
		$this->log_contents = "";
	}
	
	/**
	 * Get the instance of the debug class
	 *
	 * @return Model_Shell_Debug
	 */
	public static function getInstance() {
		if( is_null(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Add the text to the log
	 *
	 * @param string $text 
	 * @return void
	 */
	public function log($text) {
		$backtrace = debug_backtrace();

		if( is_array($backtrace) && sizeof($backtrace > 1) ) {
			if( array_key_exists("function", $backtrace[1]) ) {
				$text = $backtrace[1]['class'] . ":" . $backtrace[1]['function'] . ":" . $backtrace[0]['line'] . ": " . $text;
			}
		}
		
		$this->log_contents .= date("Y-m-d H:i:s") . " - " . $text . "\n";
	}
	
	/**
	 * Writes the log to disk
	 *
	 * @return void
	 */
	public function saveToDisk() {
		$file_path = realpath(APPLICATION_PATH . "/../tmp");
		if( is_writable($file_path) && strlen($this->log_contents) > 0 ) {
			file_put_contents($file_path . DIRECTORY_SEPARATOR . $this->filename, $this->log_contents);
		}
	}
	
	/**
	 * Gets the log contents as a string
	 *
	 * @return string
	 */
	public function getLog() {
		return $this->log_contents;
	}
	
	
}