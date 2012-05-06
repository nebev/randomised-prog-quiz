<?php
/*
*  Randoimised Model_Quiz_Quiz Generation System
*		Ben Evans 2010
*		ITEC810 - Proof of Concept for Masters Major Project
*		Macquarie University
*		Email: ben@nebev.net
* -------------------------------------------------------
* CLASSNAME:        Model_Quiz_Quiz
* CORRESPONDING MYSQL TABLE:  quiz
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

class Model_Quiz_Quiz
{
	
	const QUIZ_COMPLETED = 1;
	const QUIZ_AVAILABLE = 2;
	const QUIZ_INPROGRESS = 3;
	
	
	
	// **********************
	// ATTRIBUTE DECLARATION (GENERIC)
	// **********************

	var $quiz_id;   // KEY ATTR. WITH AUTOINCREMENT

	var $quiz_name;   // (normal Attribute)
	var $permissions_group;   // (normal Attribute)
	var $open_date;   // (normal Attribute)
	var $close_date;   // (normal Attribute)
	var $max_attempts;   // (normal Attribute)
	var $percentage_pass;   // (normal Attribute)


	// **********************
	// CONSTRUCTORS (GENERIC)
	// **********************
	
	public static function fromID($vID){
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM quiz where quiz_id=".$db->quote($vID));
		$row = $result->fetch();
		if($row['quiz_id']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new Model_Quiz_Quiz();
		$vReturn->quiz_id = $row['quiz_id'];
		$vReturn->quiz_name = $row['quiz_name'];
		$vReturn->permissions_group = $row['permissions_group'];
		$vReturn->open_date = strtotime($row['open_date']);
		$vReturn->close_date = strtotime($row['close_date']);
		$vReturn->max_attempts = $row['max_attempts'];
		$vReturn->percentage_pass = $row['percentage_pass'];

		return $vReturn;		//Return the result
	}
	

	public static function fromScratch($quiz_name,$permissions_group,$open_date,$close_date,$max_attempts,$percentage_pass){
		$db = Zend_Registry::get("db");
		$sql = "INSERT INTO quiz(quiz_id,quiz_name,permissions_group,open_date,close_date,max_attempts,percentage_pass) VALUES(NULL, ".$db->quote($quiz_name).",".$db->quote($permissions_group).",".$db->quote($open_date).",".$db->quote($close_date).",".$db->quote($max_attempts).",".$db->quote($percentage_pass).")";
		//echo $sql; die();
		$db->query($sql);
		
		//Now find the appropriate entry in the database
		//	A safe (default) assumption for this is a query that looks for everything you just put in.
		
		
		return Model_Quiz_Quiz::fromID($db->lastInsertId()); 
	}

	// **********************
	// GETTER METHODS (GENERIC)
	// **********************

	public function getID(){ return $this->quiz_id; }

	public function getQuiz_id(){	return $this->quiz_id;}
	public function getQuiz_name(){	return stripslashes($this->quiz_name);}
	public function getName(){ return stripslashes($this->quiz_name); }
	public function getPermissions_group(){	return $this->permissions_group;}
	public function getOpen_date(){	return $this->open_date;}
	public function getClose_date(){	return $this->close_date;}
	public function getMax_attempts(){	return $this->max_attempts;}
	public function getPercentage_pass(){	return $this->percentage_pass;}

	// **********************
	// SETTER METHODS (GENERIC)
	// **********************


	public function setQuiz_name($val){
		$db = Zend_Registry::get("db");
		$this->quiz_name =  $val;
		$db->query("UPDATE quiz SET quiz_name=".$db->quote($val)." WHERE quiz_id=".$db->quote($this->quiz_id)."");
	}

	public function setPermissions_group($val){
		$db = Zend_Registry::get("db");
		$this->permissions_group =  $val;
		$db->query("UPDATE quiz SET permissions_group=".$db->quote($val)." WHERE quiz_id=".$db->quote($this->quiz_id)."");
	}

	public function setOpen_date($val){
		$db = Zend_Registry::get("db");
		$this->open_date =  $val;
		$db->query("UPDATE quiz SET open_date='".date("Y-m-d",$val)."' WHERE quiz_id=".$db->quote($this->quiz_id)."");
	}

	public function setClose_date($val){
		$db = Zend_Registry::get("db");
		$this->close_date =  $val;
		$db->query("UPDATE quiz SET close_date='".date("Y-m-d",$val)."' WHERE quiz_id=".$db->quote($this->quiz_id)."");
	}

	public function setMax_attempts($val){
		$db = Zend_Registry::get("db");
		$this->max_attempts =  $val;
		$db->query("UPDATE quiz SET max_attempts=".$db->quote($val)." WHERE quiz_id=".$db->quote($this->quiz_id)."");
	}

	public function setPercentage_pass($val){
		$db = Zend_Registry::get("db");
		$this->percentage_pass =  $val;
		$db->query("UPDATE quiz SET percentage_pass=".$db->quote($val)." WHERE quiz_id=".$db->quote($this->quiz_id)."");
	}


	// **********************
	// OTHER METHODS (SPEIFIC)
	// **********************
	public static function getAll($vOrder=false){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT * FROM quiz";
		if($vOrder){
			$sql.=" ORDER BY close_date";
		}
		//echo "SQL: $sql<br/>";
		$result = $db->query($sql);
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_Quiz::fromID($row['quiz_id']);
		}
		return $vReturn;
	}

	public function getTestedConcepts(){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT * FROM concepts_tested WHERE quizquiz_id=".$db->quote($this->quiz_id);
		//echo "SQL: $sql";
		$result = $db->query($sql);
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_TestedConcept::fromID($row['ctest_id']);
		}
		return $vReturn;
	}
	
	public function getTotalQuestions(){
		$vTotalQuestions = 0;
		$vTCs = $this->getTestedConcepts();
		foreach($vTCs as $vTC){
			$vTotalQuestions = $vTotalQuestions + $vTC->getNumber_tested();
		}
		return $vTotalQuestions;
	}
	
	
	public function remove(){
		//This SHOULD cascade delete in the database...
		$db = Zend_Registry::get("db");
		$db->query("DELETE FROM quiz WHERE quiz_id=".$db->quote($this->quiz_id)." LIMIT 1");
	}
	
	public function getQuizAttempts(){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$result = $db->query("SELECT * FROM quiz_attempt WHERE quizquiz_id=".$db->quote($this->quiz_id));
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_QuizAttempt::fromID($row['quiz_attempt_id']);
		}
		return $vReturn;
	}
	


} // class Model_Quiz_Quiz : end

?>
