<?php

	class View_Helper_Question{
		
		/**
		 * Takes Question text, and formats it appropriately
		 */
		public static function format_for_text( $question_text ) {
			
			$rows = explode("\n", $question_text);
			
			// This ensues any lines marked with //HIDE get hidden
			$new_rows = array();
			for( $i = 0; $i < sizeof($rows); $i++ ) {
				$rows[$i] = str_replace("<","&lt;",str_replace(">","&gt;", $rows[$i] ) );
				
				if( strpos($rows[$i], "//HIDE") === false && strlen( str_replace("\t", "", $rows[$i]) ) > 2 ) {
					$new_rows[] = $rows[$i];
				}
			}
			
			return implode("\n", $new_rows);
		}
		
		
	}

?>