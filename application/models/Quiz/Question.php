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

/*
* -------------------------------------------------------
* CLASSNAME:        Model_Quiz_Question
* CORRESPONDING MYSQL TABLE:  question_base
* FOR MYSQL DB:     quiz_db
* -------------------------------------------------------
* Class Description:
* 
*/


// **********************
// CLASS DECLARATION (GENERIC)
// **********************

class Model_Quiz_Question
{
	
	// **********************
	// ATTRIBUTE DECLARATION (GENERIC)
	// **********************

	var $question_id;   // KEY ATTR. WITH AUTOINCREMENT

	var $xml;   // (normal Attribute)
	var $difficulty;   // (normal Attribute)
	var $added_on;   // (normal Attribute)


	// **********************
	// CONSTRUCTORS (GENERIC)
	// **********************
	/**
	 * Generates a question from the numeric identifier passed
	 * @param int $vID
	 * @return NULL|Model_Quiz_Question
	 */
	public static function fromID($vID){
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		
		$result = $db->query("SELECT * FROM question_base where question_id=".$db->quote($vID));
		$row = $result->fetch();
		if($row['question_id']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new self();
		$vReturn->question_id = $row['question_id'];
		$vReturn->xml = $row['xml'];
		$vReturn->difficulty = $row['difficulty'];
		$vReturn->added_on = $row['added_on'];

		return $vReturn;		//Return the result
	}
	

	/**
	 * Adds a new question from scratch
	 * @param string $xml
	 * @param int $difficulty
	 * @param string $added_on should be in YYYY-MM-DD Format
	 * @return Question|Null
	 */
	public static function fromScratch($xml,$difficulty,$added_on){
		$db = Zend_Registry::get("db"); /* @var $db Zend_DB_Adapter_Abstract */
		$sql = "INSERT INTO question_base(question_id,xml,difficulty,added_on) VALUES(NULL, ".$db->quote($xml).",".$db->quote($difficulty).",".$db->quote($added_on).")";
		
		$db->beginTransaction();
		$db->query($sql);
		$last_insert_id = $db->lastInsertId();
		$db->commit();
		return Model_Quiz_Question::fromID($last_insert_id);
	}

	// **********************
	// GETTER METHODS (GENERIC)
	// **********************

	public function getID(){ return $this->question_id; }

	public function getQuestion_id(){	return $this->question_id;}
	public function getXml(){	return $this->xml;}
	public function getDifficulty(){	return $this->difficulty;}
	public function getAdded_on(){	return $this->added_on;}

	// **********************
	// SETTER METHODS (GENERIC)
	// **********************


	private function setQuestion_id($val){
		$this->question_id =  $val;
	}

	private function setXml($val){
		$this->xml =  $val;
	}

	private function setDifficulty($val){
		$this->difficulty =  $val;
	}

	private function setAdded_on($val){
		$this->added_on =  $val;
	}

	// **********************
	// UPDATE (GENERIC)
	// **********************

	public function update($id){
		$db = Zend_Registry::get("db");
		$sql = " UPDATE question_base SET  xml = '$this->xml',difficulty = '$this->difficulty',added_on = '$this->added_on' WHERE question_id = $id ";
		$result = $db->query($sql);
	}


} // class Model_Quiz_Question : end

?>
