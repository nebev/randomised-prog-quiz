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
* CLASSNAME:        Model_Quiz_QuestionAttempt
* CORRESPONDING MYSQL TABLE:  question_attempt
* FOR MYSQL DB:     quiz_db
* -------------------------------------------------------
* Class Description:
* 
* 
* 
*/


// **********************
// CLASS DECLARATION (GENERIC)
// **********************

class Model_Quiz_QuestionAttempt
{
	
	// **********************
	// ATTRIBUTE DECLARATION (GENERIC)
	// **********************

	var $attempt_id;   // KEY ATTR. WITH AUTOINCREMENT

	var $initial_result;   // (normal Attribute)
	var $secondary_result;   // (normal Attribute)
	var $question_basequestion_id;   // (normal Attribute)
	var $attempted_on;   // (normal Attribute)
	var $time_started;   // (normal Attribute)
	var $time_finished;   // (normal Attribute)
	var $quiz_attemptquiz_attempt_id;   // (normal Attribute)
	var $generated_questionsgenerated_id;   // (normal Attribute)


	// **********************
	// CONSTRUCTORS (GENERIC)
	// **********************
	
	public static function fromID($vID){
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM question_attempt where attempt_id='".addslashes($vID)."'");
		$row = $result->fetch();
		if($row['attempt_id']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new Model_Quiz_QuestionAttempt();
		$vReturn->attempt_id = $row['attempt_id'];
		$vReturn->initial_result = $row['initial_result'];
		$vReturn->secondary_result = $row['secondary_result'];
		$vReturn->question_basequestion_id = Model_Quiz_QuestionBase::fromID($row['question_basequestion_id']);
		$vReturn->attempted_on = strtotime($row['attempted_on']);
		$vReturn->time_started = strtotime($row['time_started']);
		$vReturn->time_finished = strtotime($row['time_finished']);
		$vReturn->quiz_attemptquiz_attempt_id = Model_Quiz_QuizAttempt::fromID($row['quiz_attemptquiz_attempt_id']);
		$vReturn->generated_questionsgenerated_id = Model_Quiz_GeneratedQuestion::fromID($row['generated_questionsgenerated_id']);


		return $vReturn;		//Return the result
	}
	

	public static function fromScratch($vQuestionBase,$attempted_on,$time_started,$vQuizAttempt,$vGeneratedQuestion){
		$db = Zend_Registry::get("db");
		$sql = "INSERT INTO question_attempt(attempt_id,question_basequestion_id,attempted_on,time_started,quiz_attemptquiz_attempt_id,generated_questionsgenerated_id) VALUES(NULL, ".$db->quote($vQuestionBase->getID()).",'".date("Y-m-d H:i:s",$attempted_on)."','".date("Y-m-d H:i:s",$time_started)."',".$db->quote($vQuizAttempt->getID()).",".$db->quote($vGeneratedQuestion->getID()).")";
		$db->query($sql);
		
		//Now find the appropriate entry in the database
		//	A safe (default) assumption for this is a query that looks for everything you just put in.
		
		$sql = "SELECT attempt_id FROM question_attempt WHERE question_basequestion_id=".$db->quote($vQuestionBase->getID())." AND attempted_on='".date("Y-m-d H:i:s",$attempted_on)."' AND time_started='".date("Y-m-d H:i:s",$time_started)."' AND quiz_attemptquiz_attempt_id=".$db->quote($vQuizAttempt->getID())." AND generated_questionsgenerated_id=".$db->quote($vGeneratedQuestion->getID());

		$result = $db->query($sql);
		$row = $result->fetch();
		if($row['attempt_id']!=null){
			return Model_Quiz_QuestionAttempt::fromID($row['attempt_id']);
		}else{
			return null; //Something didn't happen
		}
	}

	// **********************
	// GETTER METHODS (GENERIC)
	// **********************

	public function getID(){ return $this->attempt_id; }
	public function getInitial_result(){	return $this->initial_result;}
	public function getSecondary_result(){	return $this->secondary_result;}
	public function getQuestion_base(){	return $this->question_basequestion_id;}
	public function getAttempted_on(){	return $this->attempted_on;}
	public function getTime_started(){	return $this->time_started;}
	public function getTime_finished(){	return $this->time_finished;}
	public function getQuiz_attemptquiz_attempt_id(){	return $this->quiz_attemptquiz_attempt_id;}
	public function getGeneratedQuestion(){	return $this->generated_questionsgenerated_id;}

	// **********************
	// SETTER METHODS (GENERIC)
	// **********************


	public function setAttempt_id($val){
		$db = Zend_Registry::get("db");
		$this->attempt_id =  $val;
		$sql = "UPDATE question_attempt SET attempt_id=".$db->quote($val)." WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setInitial_result($val){
		$db = Zend_Registry::get("db");
		$this->initial_result =  $val;
		$sql = "UPDATE question_attempt SET initial_result=".$db->quote($val)." WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setSecondary_result($val){
		$db = Zend_Registry::get("db");
		$this->secondary_result =  $val;
		$sql = "UPDATE question_attempt SET secondary_result=".$db->quote($val)." WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setQuestion_basequestion_id($val){
		$db = Zend_Registry::get("db");
		$this->question_basequestion_id =  $val;
		$sql = "UPDATE question_attempt SET question_basequestion_id=".$db->quote($val)." WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setAttempted_on($val){
		$db = Zend_Registry::get("db");
		$this->attempted_on =  $val;
		$sql = "UPDATE question_attempt SET attempted_on=".$db->quote($val)." WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setTime_started($val){
		$db = Zend_Registry::get("db");
		$this->time_started =  $val;
		$sql = "UPDATE question_attempt SET time_started=".$db->quote($val)." WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setTime_finished($val){
		$db = Zend_Registry::get("db");
		$this->time_finished =  $val;
		$sql = "UPDATE question_attempt SET time_finished='".date("Y-m-d H:i:s",$val)."' WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setQuiz_attemptquiz_attempt_id($val){
		$db = Zend_Registry::get("db");
		$this->quiz_attemptquiz_attempt_id =  $val;
		$sql = "UPDATE question_attempt SET quiz_attemptquiz_attempt_id=".$db->quote($val)." WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setGenerated_questionsgenerated_id($val){
		$db = Zend_Registry::get("db");
		$this->generated_questionsgenerated_id =  $val;
		$sql = "UPDATE question_attempt SET generated_questionsgenerated_id=".$db->quote($val)." WHERE attempt_id=".$db->quote($this->attempt_id)." LIMIT 1";
		$db->query($sql);
	}



	// **********************
	// OTHER METHODS (SPECIFIC)
	// **********************
	public static function getAllFromQuizAttemptAndConcept($vQuizAttempt, $vConcept){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT qa.attempt_id AS attempt_id FROM question_attempt qa, question_concepts qc, concepts c WHERE qc.conceptsconcept_name=c.concept_name AND qc.question_basequestion_id=qa.question_basequestion_id AND qa.quiz_attemptquiz_attempt_id=".$db->quote($vQuizAttempt->getID())." AND c.concept_name=".$db->quote($vConcept->getID());
		//echo $sql;
		$result = $db->query($sql);
		$rows = $result->fetchAll();

		foreach($rows as $row){
			$vReturn[] = Model_Quiz_QuestionAttempt::fromID($row['attempt_id']);
		}
		
		return $vReturn;
	}

	/**
	 * Gets all Question attempts for a given question
	 *
	 * @param string $vQB 
	 * @return void
	 * @author Ben Evans
	 */
	public static function getAllFromQuestionBase(Model_Quiz_QuestionBase $vQB){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT attempt_id FROM question_attempt WHERE question_basequestion_id=".$db->quote($vQB->getID());
		//echo $sql;
		$result = $db->query($sql);
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_QuestionAttempt::fromID($row['attempt_id']);
		}
		return $vReturn;
	}



} // class Model_Quiz_QuestionAttempt : end

?>
