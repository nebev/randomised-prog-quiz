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

	class Model_XML_Menu {

		/**
		 * Load the View Rules XML File
		 * @return SimpleXML Object
		 * @author Ben Evans
		 */
		private static function getXMLRules() {
            
			//Open and retrieve the contents of the rules file
            $filename = APPLICATION_PATH . "/../xml/menu.xml";
            if( !file_exists($filename) ){
                throw new Exception("Menu XML Configuration does not exist!", 3000);
            }
            
            //Now load the XML
            return simplexml_load_file($filename);
        }



		/**
		 * Draws HTML Menu (driven by XML for Menus and Features)
		 *
		 * @param array $blocks 
		 * @param array $elements 
		 * @param array $permission_array 
		 * @param string $module
		 * @return void
		 * @author Ben Evans
		 */
		public static function draw( array $blocks, array $elements, array $permission_array, $module = null ) {
			if( is_null($module) ) {
				$menu_rules = Model_XML_Menu::getXMLRules();
			}else{
				$filename = APPLICATION_PATH . "/" . $module . "/rules/menu.xml";
				if( file_exists($filename) ) {
					$menu_rules = simplexml_load_file($filename);
				}else{
					return;	//No menu for this module
				}
			}
			
			
			foreach( $blocks as $block ) {
				$menu_block = $menu_rules->xml_tree->xpath("//block[@id='".$block."']");
				if( is_array($menu_block) && sizeof($menu_block) > 0 ) {
					
					$menu_block = current($menu_block);
					$display_elements = array();
					
					// OK. At this point, we know what menu items we're "possibly" going to display for this block
					// But we have to determine permissions etc here.
					foreach( $menu_block->item as $menu_item ) {
						
						$menu_display_item = array("name" => (string)$menu_item->name);
						
						// URL
						$url = (string)$menu_item->url;
				        $matches = array();
				        if(preg_match_all("/\{([^}]+)\}/", (string)$menu_item->url, $matches)) {
							// There's some substitution going on here
							
							foreach($matches[1] as $match) {
								$match_ok = true;
								if( !array_key_exists( $match, $elements ) ) {
									$match_ok = false;
									continue;
								}
								$url = str_replace( "{". $match ."}", $elements[$match], $url );
							}
							if( !$match_ok ) {
								continue;
							}
							
				        }	
						
						$menu_display_item['url'] = $url;
						
						
						// Image (if applicable)
						if( isset($menu_item->image)  ) {
							$menu_display_item['image'] = (string)$menu_item->image;
						}
						

						
						// Permission OK?
						if( isset($menu_item->permission) ) {
							$permission = (string)$menu_item->permission;
							
							if( strpos($permission, ",") === false ) {
								// If we have text (eg. state_admin), we look for the text in the $elements array
								if( !array_key_exists( $permission, $permission_array ) || $permission_array[$permission] !== true ) {
									continue;
								}
							}else{
								// A more complex menu item exists. One where we have to invoke the permissions model
								// Syntax is permission_type, {element}
								// eg. view,{member_id}
								$exploded_permission = explode(",", $permission);
								
								if( sizeof($exploded_permission) == 2 ) {
									
									$entity_id = str_replace("{", "", $exploded_permission[1]);
									$entity_id = str_replace("}", "", $entity_id);
									$entity_id = trim($entity_id);
									$permission_type = $exploded_permission[0];
									
									if( !array_key_exists($entity_id, $permission_array) || strlen($permission_array[$entity_id]) < 1 ) {
										continue;
									}
									
									if( substr($permission_type, 0, 1) == "!" ) {
										if( Model_Auth_RuleHelper::canAccess(new Model_BAO_Contact( $permission_array[$entity_id] ), null, $permission_type) ) {
											continue;
										}
									}else{
										if( !Model_Auth_RuleHelper::canAccess(new Model_BAO_Contact( $permission_array[$entity_id] ), null, $permission_type) ) {
											continue;
										}
									}
									
									
									
								}else{
									continue;
								}
							}	
						}
						
						
						// Permissions OK. Add
						$display_elements[] = $menu_display_item;
						
					}
					
					// OK. Render the menu items if they're not blank
					Model_XML_Menu::drawMenuHtml((string)$menu_block->attributes()->name, $display_elements);
					
					
				}else if( is_null($module) && strpos($block, "_") !== false ) {
					// Could be in a module
					$exploded_block = explode("_", $block);
					$block = str_replace( $exploded_block[0] . "_", "", $block );
					Model_XML_Menu::draw( array($block), $elements, $permission_array, $exploded_block[0] );
				}
				
			}
				
		}


		/**
		 * Draws HTML Menu from provided Parameters
		 *
		 * @param string $menu_title 
		 * @param array $menu_elements 
		 * @return void
		 * @author Ben Evans
		 */
		private static function drawMenuHtml($menu_title, array $menu_elements) {
			if( sizeof($menu_elements) == 0 ) {
				return;
			}
			
			$fc = Zend_Controller_Front::getInstance();
			$baseUrl =  $fc->getBaseUrl();
			
			
			echo "\t<h2 class='menu ". preg_replace('/\W+/', '-', strtolower($menu_title) ) ."'>". $menu_title ."</h2>\n";
			echo '<div id="leftNav">
					<ul id="nav_structure">';
			
			foreach( $menu_elements as $element ) {
				$css_class = preg_replace('/\W+/', '-', strtolower($element['name']));
				echo "\t<li class='menu-item menu-item-".$css_class."'><a href='". $baseUrl. "/" . $element['url'] ."' title='". $element['name'] ."'>".$element['name']."</a></li>\n";
			}
			echo "\t</ul>\n";
			echo "</div>\n";
			
			
			
		}



	}
?>