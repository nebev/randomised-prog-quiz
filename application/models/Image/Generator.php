<?php
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