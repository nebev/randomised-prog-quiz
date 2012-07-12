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
abstract class Model_Auth_General extends Zend_DB_Table_Abstract{
		
	/**
	 * Authenticates the user using the credentials supplied. Returns true
	 * if authentication is successfull, False otherwise
	 *
	 * @param string $username 
	 * @param string $password 
	 * @return boolean
	 * @author Ben Evans
	 */
	abstract public static function authenticate($username, $password);
	
	
	public static function getAuthModel() {
		
		if( AUTH_METHOD == "LDAP" ) {
			return new Model_Auth_ActiveDirectory();
		}
		
		throw new Exception("No Valid Authentication Model specified");
	}
	
	
	abstract public function userInGroup( $username, $group );
	
}

?>