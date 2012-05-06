<?php

	class View_Helper_Sort{
		
		
		public static function sort_by_last_name( $a, $b ) {
			if ($a['last_name'] == $b['last_name']) {
			        return 0;
			}
			return ($a['last_name'] < $b['last_name']) ? -1 : 1;
		}
		
		
		
	}


?>