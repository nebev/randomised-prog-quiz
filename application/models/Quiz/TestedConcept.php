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
*		Email: ben@nebev.net
* -------------------------------------------------------
* CLASSNAME:        Model_Quiz_TestedConcept
* CORRESPONDING MYSQL TABLE:  concepts_tested
* FOR MYSQL DB:     quiz_db
* -------------------------------------------------------
* Class Description:
* This is the Model_Quiz_TestedConcept class. It essentially contains info
* about what concept is tested, and it relates to a quiz.
* 
*/


// **********************
// CLASS DECLARATION (GENERIC)
// **********************

class Model_Quiz_TestedConcept {
	
	// **********************
	// ATTRIBUTE DECLARATION (GENERIC)
	// **********************

	var $ctest_id;   // KEY ATTR. WITH AUTOINCREMENT

	var $lower_difficulty;   // (normal Attribute)
	var $higher_difficulty;   // (normal Attribute)
	var $number_tested;   // (normal Attribute)
	var $conceptsconcept_name;   // (normal Attribute)
	var $quizquiz_id;   // (normal Attribute)


	// **********************
	// CONSTRUCTORS (GENERIC)
	// **********************
	
	public static function fromID($vID){
		
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM concepts_tested where ctest_id=".$db->quote($vID));
		$row = $result->fetch();
		if($row['ctest_id']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new Model_Quiz_TestedConcept();
		$vReturn->ctest_id = $row['ctest_id'];
		$vReturn->lower_difficulty = $row['lower_difficulty'];
		$vReturn->higher_difficulty = $row['higher_difficulty'];
		$vReturn->number_tested = $row['number_tested'];
		$vReturn->conceptsconcept_name = $row['conceptsconcept_name'];
		$vReturn->quizquiz_id = $row['quizquiz_id'];

		return $vReturn;		//Return the result
	}
	

	public static function fromScratch($lower_difficulty,$higher_difficulty,$number_tested,$vConcept,$vQuiz){
		$db = Zend_Registry::get("db");
		$sql = "INSERT INTO concepts_tested(ctest_id,lower_difficulty,higher_difficulty,number_tested,conceptsconcept_name,quizquiz_id) VALUES(NULL, ".$db->quote($lower_difficulty).",".$db->quote($higher_difficulty).",".$db->quote($number_tested).",".$db->quote($vConcept->getID()).",".$db->quote($vQuiz->getID()).")";
		$db->query($sql);
		
		//Now find the appropriate entry in the database
		//	A safe (default) assumption for this is a query that looks for everything you just put in.
		
		$sql = "SELECT ctest_id FROM concepts_tested WHERE lower_difficulty=".$db->quote($lower_difficulty)." AND higher_difficulty=".$db->quote($higher_difficulty)." AND number_tested=".$db->quote($number_tested)." AND conceptsconcept_name=".$db->quote($conceptsconcept_name)." AND quizquiz_id=".$db->quote($quizquiz_id);
		$result = $db->query($sql);
		$row = $result->fetch();
		if($row['ctest_id']!=null){
			return Model_Quiz_TestedConcept::fromID($row['ctest_id']);
		}else{
			return null; //Something didn't happen
		}
	}

	// **********************
	// GETTER METHODS (GENERIC)
	// **********************

	public function getID(){ return $this->ctest_id; }
	public function getLower_difficulty(){	return $this->lower_difficulty;}
	public function getHigher_difficulty(){	return $this->higher_difficulty;}
	public function getNumber_tested(){	return $this->number_tested;}
	public function getConcept(){	return Model_Quiz_Concept::fromID($this->conceptsconcept_name);}
	public function getQuiz(){	return Model_Quiz_Quiz::fromID($this->quizquiz_id);}

	/**
	 * Update the Concept tested to that of the object passed
	 * @param Model_Quiz_Concept $concept
	 */
	public function updateConcept( Model_Quiz_Concept $concept ) {
		$this->updateField("conceptsconcept_name", $concept->getName());	// NAME? REALLY? NAME?
	}
	
	/**
	 * Updates the lower difficulty for this Tested Concept
	 * @param int $lower_difficulty
	 */
	public function updateLowerDifficulty($lower_difficulty) {
		$this->updateField("lower_difficulty", intval($lower_difficulty));
	}
	
	/**
	 * Updates the higher difficulty for this Tested Concept
	 * @param int $higher_difficulty
	 */
	public function updateHigherDifficulty($higher_difficulty) {
		$this->updateField("higher_difficulty", intval($higher_difficulty));
	}
	
	/**
	 * Updates the Number of questions to be tested
	 * @param int $number_tested
	 */
	public function updateNumberTested( $number_tested ) {
		$this->updateField("number_tested", intval($number_tested));
	}
	
	
	/**
	 * Update a Database Field for the specified Row to the value provided
	 * @param string $field
	 * @param string $value
	 */
	protected function updateField( $field, $value ) {
		$db = Zend_Registry::get("db");	// Really should be using DI
		/* @var $db Zend_Db_Adapter_Abstract */
		$db->update("concepts_tested", array($field => $value), "ctest_id = " . $db->quote($this->getID()));
	}
	

	// **********************
	// OTHER METHODS (SPECIFIC)
	// **********************
	public function remove(){
		$db = Zend_Registry::get("db");
		$db->query("DELETE FROM concepts_tested WHERE ctest_id=".$db->quote($this->ctest_id)." LIMIT 1");
	}




} // class Model_Quiz_TestedConcept : end

?>
