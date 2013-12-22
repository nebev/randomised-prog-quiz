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
* CLASSNAME:        Model_Quiz_QuizAttempt
* CORRESPONDING MYSQL TABLE:  quiz_attempt
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

class Model_Quiz_QuizAttempt
{
	
	// **********************
	// ATTRIBUTE DECLARATION (GENERIC)
	// **********************

	var $quiz_attempt_id;   // KEY ATTR. WITH AUTOINCREMENT

	var $date_started;   // (normal Attribute)
	var $date_finished;   // (normal Attribute)
	var $total_score;   // (normal Attribute)
	var $quizquiz_id;   // (normal Attribute)
	var $ad_user_cachesamaccountname;   // (normal Attribute)
	var $last_question;


	// **********************
	// CONSTRUCTORS (GENERIC)
	// **********************
	
	public static function fromID($vID){
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM quiz_attempt where quiz_attempt_id=".$db->quote($vID));
		$row =$result->fetch();
		if($row['quiz_attempt_id']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new Model_Quiz_QuizAttempt();
		$vReturn->quiz_attempt_id = $row['quiz_attempt_id'];
		$vReturn->date_started = strtotime($row['date_started']);
		$vReturn->date_finished = strtotime($row['date_finished']);
		$vReturn->total_score = $row['total_score'];
		$vReturn->quizquiz_id = $row['quizquiz_id'];
		$vReturn->ad_user_cachesamaccountname = $row['ad_user_cachesamaccountname'];
		$vReturn->last_question = $row['last_question'];

		return $vReturn;		//Return the result
	}
	
	
	public static function fromQuizAndUser($vQuiz, $vSam){
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM quiz_attempt WHERE ad_user_cachesamaccountname=".$db->quote($vSam)." AND quizquiz_id=".$db->quote($vQuiz->getID()));
		$row =$result->fetch();
		return Model_Quiz_QuizAttempt::fromID($row['quiz_attempt_id']);
	}
	

	public static function fromScratch($date_started,$quizquiz_id,$ad_user_cachesamaccountname){
		$db = Zend_Registry::get("db");
		$sql = "INSERT INTO quiz_attempt(quiz_attempt_id,date_started,quizquiz_id,ad_user_cachesamaccountname) VALUES(NULL, '".date("Y-m-d H:i:s",$date_started)."',".$db->quote($quizquiz_id->getID()).",".$db->quote($ad_user_cachesamaccountname).")";
		$db->query($sql);
		
		//Now find the appropriate entry in the database
		//	A safe (default) assumption for this is a query that looks for everything you just put in.
		
		$sql = "SELECT quiz_attempt_id FROM quiz_attempt WHERE date_started='".date("Y-m-d H:i:s",$date_started)."' AND quizquiz_id=".$db->quote($quizquiz_id->getID())." AND ad_user_cachesamaccountname=".$db->quote($ad_user_cachesamaccountname);
		$result = $db->query($sql);
		$row =$result->fetch();
		if($row['quiz_attempt_id']!=null){
			return Model_Quiz_QuizAttempt::fromID($row['quiz_attempt_id']);
		}else{
			return null; //Something didn't happen
		}
	}

	// **********************
	// GETTER METHODS (GENERIC)
	// **********************

	public function getID(){ return $this->quiz_attempt_id; }
	public function getDate_started(){	return $this->date_started;}
	public function getDate_finished(){	return $this->date_finished;}
	public function getTotal_time(){ return $this->date_finished-$this->date_started; }
	public function getTotal_score(){
		$db = Zend_Registry::get("db");
		
		if($this->total_score!=null)
			return $this->total_score;
		
		//Find out how many you FAILED	
		$sql = "SELECT count(*) as count FROM question_attempt WHERE quiz_attemptquiz_attempt_id=".$db->quote($this->quiz_attempt_id)." AND initial_result='0' AND secondary_result='0'";
		$result=$db->query($sql);
		$row =$result->fetch();
		$vFailed = $row['count'];
		
		//And the total?
		$sql = "SELECT count(*) as count FROM question_attempt WHERE quiz_attemptquiz_attempt_id=".$db->quote($this->quiz_attempt_id);
		$result=$db->query($sql);
		$row =$result->fetch();
		return ($row['count']-$vFailed);
		
	}
	public function getQuiz(){	return Model_Quiz_Quiz::fromID($this->quizquiz_id); }
	public function getAd_user_cachesamaccountname(){	return $this->ad_user_cachesamaccountname;}
	public function getLast_question(){	return GeneratedQuestion::fromID($this->last_question);}

	// **********************
	// SETTER METHODS (GENERIC)
	// **********************


	public function setDate_started($val){
		$db = Zend_Registry::get("db");
		$this->date_started =  $val;
		$sql = "UPDATE quiz_attempt SET date_started='".$db->quote($val)."' WHERE quiz_attempt_id=".$db->quote($this->quiz_attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setDate_finished($val){
		$db = Zend_Registry::get("db");
		$this->date_finished =  $val;
		$sql = "UPDATE quiz_attempt SET date_finished='".date("Y-m-d H:i:s",$val)."' WHERE quiz_attempt_id=".$db->quote($this->quiz_attempt_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setTotal_score($val){
		$db = Zend_Registry::get("db");
		$this->total_score =  $val;
		$sql = "UPDATE quiz_attempt SET total_score=".$db->quote($val)." WHERE quiz_attempt_id=".$db->quote($this->quiz_attempt_id)." LIMIT 1";
		$db->query($sql);
	}




	// **********************
	// OTHER METHODS (SPECIFIC)
	// **********************
	public function remove(){
		$db = Zend_Registry::get("db");
		$db->query("DELETE FROM quiz_attempt WHERE quiz_attempt_id=".$db->quote($this->quiz_attempt_id)." LIMIT 1");
	}

	public static function getAllFromUser($vUser, $vQuiz){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT * FROM quiz_attempt WHERE ad_user_cachesamaccountname=".$db->quote($vUser)." AND quizquiz_id=".$vQuiz->getID();
		//echo "SQL: $sql";
		$result = $db->query($sql);
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_QuizAttempt::fromID($row['quiz_attempt_id']);
		}
		return $vReturn;
	}
	
	public static function getHighestMarkQuiz($vUser, $vQuiz){
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM quiz_attempt WHERE ad_user_cachesamaccountname=".$db->quote($vUser)." AND quizquiz_id=".$vQuiz->getID()." ORDER BY total_score DESC");
		$row =$result->fetch();
		return Model_Quiz_QuizAttempt::fromID($row['quiz_attempt_id']);
	}


	public function getQuestionAttempts($vConcept=null){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT attempt_id FROM question_attempt WHERE quiz_attemptquiz_attempt_id=".$db->quote($this->quiz_attempt_id);

		if($vConcept!=null)
			$sql="SELECT qa.attempt_id FROM question_attempt qa, question_concepts qc WHERE qa.quiz_attemptquiz_attempt_id=".$db->quote($this->quiz_attempt_id)." AND qa.question_basequestion_id=qc.question_basequestion_id AND qc.conceptsconcept_name=".$db->quote($vConcept->getID());
		
		//echo "SQL: $sql<br/>";
		
		$result = $db->query($sql);
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_QuestionAttempt::fromID($row['attempt_id']);
		}
		return $vReturn;
	}

	public function getHighestDifficultyTestedSoFar(){
		$db = Zend_Registry::get("db");
		$sql = "SELECT qb.difficulty FROM question_base qb, question_attempt qa WHERE qa.question_basequestion_id=qb.question_id AND qa.quiz_attemptquiz_attempt_id=".$db->quote($this->quiz_attempt_id)." ORDER BY qb.difficulty DESC";
		$result=$db->query($sql);
		$row =$result->fetch();
		return $row['difficulty'];
	}
	
	public function getAttemptedQuestionBases($vConcept, $vDifficulty){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT DISTINCT qb.question_id FROM question_attempt qa, question_base qb, question_concepts qc WHERE qc.conceptsconcept_name=".$db->quote($vConcept->getID())." AND qb.difficulty=".$db->quote($vDifficulty)." AND qa.question_basequestion_id=qb.question_id AND qc.question_basequestion_id=qb.question_id AND qa.quiz_attemptquiz_attempt_id=".$db->quote($this->quiz_attempt_id);
		//echo $sql;
		$result = $db->query($sql);
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_QuestionBase::fromID($row['question_id']);
		}
		return $vReturn;
	}
	
	/**
	 * Gets the last incomplete Question Attempt for this quiz
	 * @return Model_Quiz_QuestionAttempt|NULL
	 */
	public function getLastIncompleteQuestion(){
		$db = Zend_Registry::get("db");
		$sql = "SELECT attempt_id FROM question_attempt WHERE time_finished IS NULL AND quiz_attemptquiz_attempt_id=".$db->quote($this->quiz_attempt_id)." ORDER BY attempt_id DESC";
		$result = $db->query($sql);
		$row = $result->fetch();
		return Model_Quiz_QuestionAttempt::fromID($row['attempt_id']);
	}
	
	public function getQuestionAttemptCount(){
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT COUNT(*) AS count FROM question_attempt WHERE quiz_attemptquiz_attempt_id=".$db->quote($this->quiz_attempt_id));
		$row =$result->fetch();
		return $row['count'];
	}
	

} // class Model_Quiz_QuizAttempt : end

?>
