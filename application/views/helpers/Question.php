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
	class View_Helper_Question{
		
		/**
		 * Takes Question text, and formats it appropriately
		 * @param string $question_text - Unescaped Question text
		 * @param boolean $include_explanations - Whether or not you want explanations with this text (if present in the question)
		 * @return mixed - Returns string if $include_explanations == false, array with keys [question] and [explanations] (each being arrays of strings)
		 */
		public static function format_for_text( $question_text, $include_explanations = false ) {
			
			$rows = explode("\n", $question_text);
			
			// This ensues any lines marked with //HIDE get hidden
			$new_rows = array();
			$explanations = array();
			
			for( $i = 0; $i < sizeof($rows); $i++ ) {
				$rows[$i] = str_replace("<","&lt;",str_replace(">","&gt;", $rows[$i] ) );
				
				if( strpos($rows[$i], "//HIDE") === false && strlen( str_replace("\t", "", $rows[$i]) ) >= 1 ) {
			
					$exploded_line = explode("////", $rows[$i]);
					if( sizeof($exploded_line) > 1 ) {
						$explanations[] = $exploded_line[1];
					}else{
						$explanations[] = "";
					}

					$new_rows[] = $exploded_line[0];
				}
			}
			
			if( $include_explanations === true ) {
				return array( "question" => $new_rows, "explanations" => $explanations );
			}
			
			return implode("\n", $new_rows);
		}
		
		/**
		 * Returns HTML Formatted Text for Question Display on the 'answer' page
		 *
		 * @param string $question_text 
		 * @return void
		 * @author Ben Evans
		 */
		public static function output_with_hints( $question_text ) {
			$text_and_hints = View_Helper_Question::format_for_text($question_text, true);
			$question = $text_and_hints['question'];
			$hints = $text_and_hints['explanations'];
			
			$return_text = "";
			
			for( $i = 0; $i < sizeof($question); $i++ ) {
				if( array_key_exists($i, $hints) && strlen($hints[$i]) > 0 ) {
					$return_text .= "<div class='question-explanation-holder' id='question-explanation-holder-$i' onClick='showExplanation($i);'></div>";
					$return_text .= "<div class='question-explanation-details' id='question-explanation-details-$i' >". $hints[$i] ."</div>";
				}else{
					$return_text .= "<div class='question-blank-holder'></div>";
				}
				
				$question[$i] = str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $question[$i]);
				
				$return_text .= "<div class='question-text-output' id='question-text-output-$i'>" . $question[$i] . "</div>\n";
			}
			return $return_text;
		}
		
		
	}

?>