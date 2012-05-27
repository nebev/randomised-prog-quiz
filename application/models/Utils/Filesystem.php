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


class Model_Utils_Filesystem{
	
	/**
	 * Removes a Directory
	 *
	 * @param string $dir 
	 * @return void
	 * @author Ben Evans
	 */
	public static function delete_directory( $dir ) {
	    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),  RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($iterator as $path) {
			
			if( substr( $path, strlen($path) - 3, 3 ) != "/.." && substr( $path, strlen($path) - 2, 2 ) != "/." ) {
				if ($path->isDir()) {
					rmdir($path->__toString());
				} else {
					unlink($path->__toString());
				}
			}
			
			
		}
		rmdir($dir);
	}
	
}

?>