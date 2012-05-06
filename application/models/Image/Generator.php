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

	class Model_Image_Generator{
		
		public function echoi($vText){
				global $mImageText;
				if(!isset($mImageText)){
					$mImageText = $vText;
				}else{
					$mImageText = $mImageText . $vText;
				}
			}
	
	
		public function makeImage($mImageText){
			$rows = explode("\n", $mImageText);

			// This ensues any lines marked with //HIDE get hidden
			$new_rows = array();
			for( $i = 0; $i < sizeof($rows); $i++ ) {
				if( strpos($rows[$i], "//HIDE") === false && strlen( str_replace("\t", "", $rows[$i]) ) > 2 ) {
					$new_rows[] = $rows[$i];
				}
			}

			$rows = $new_rows;
			unset($new_rows);
			
			$im = imagecreate(600, 30*sizeof($rows) + 50);

			$bg = imagecolorallocate($im, 255, 255, 255);
			$textcolor = imagecolorallocate($im, 0, 0, 255);
		
			$rowNum = 0;
			foreach($rows as $row){
				
				//Interpret TABs correctly (this is WAAAAAY Beta)
				$row = str_replace("\t","      ",$row);
				imagettftext($im, 12, 0, 5, ($rowNum*23)+50, $textcolor, APPLICATION_PATH . "/../resources/couri.ttf", $row);

				//imagestring($im, 5, 0, $rowNum*30, $row, $textcolor);
				$rowNum++;

			}
		

			header('Content-type: image/png');

			imagepng($im);
			imagedestroy($im);		
		}
	
	
	}

?>