<?php
/*
*  Randoimised Quiz Generation System
*		Ben Evans 2010
*		ITEC810 - Proof of Concept for Masters Major Project
*		Macquarie University
*		Email: ben@nebev.net
* -------------------------------------------------------
* CLASSNAME:        Model_Quiz_QuestionBase
* CORRESPONDING MYSQL TABLE:  question_base
* FOR MYSQL DB:     quiz_db
* -------------------------------------------------------
* Class Description:
* The Model_Quiz_QuestionBase class is here to keep a record of what each
* XML question has in it - eg. Difficulty and Concepts (another class)
* It's used primarily in selecting a question for a student to attempt
* 	eg. System asks "Quiz x is instructing me to give student a question on variables @ difficulty 1"
*		In this case, something like $vConcept->getQuestions(1) would be executed, returning an array
*		of one or more Model_Quiz_QuestionBase objects. From here, the system can query the XML file (or a previously
*		generated question), and give it to the student
*/


// **********************
// CLASS DECLARATION (GENERIC)
// **********************

class Model_Quiz_QuestionBase
{
	
	// **********************
	// ATTRIBUTE DECLARATION (GENERIC)
	// **********************

	var $question_id;   // KEY ATTR. WITH AUTOINCREMENT

	var $xml;   // (normal Attribute)
	var $difficulty;   // (normal Attribute)
	var $question_type;
	var $estimated_time;
	var $added_on;   // (date Attribute)

	// **********************
	// CONSTRUCTORS (GENERIC)
	// **********************
	
	public static function fromID($vID){
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM question_base where question_id=".$db->quote($vID));
		$row = $result->fetch();
		if($row['question_id']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new Model_Quiz_QuestionBase();
		$vReturn->question_id = $row['question_id'];
		$vReturn->xml = $row['xml'];
		$vReturn->difficulty = $row['difficulty'];
		$vReturn->added_on = strtotime($row['added_on']);
		$vReturn->question_type = $row['question_type'];
		$vReturn->estimated_time = $row['estimated_time'];
		$vReturn->question_type = $row['question_type'];

		return $vReturn;		//Return the result
	}
	
	
	public static function fromXml($vID){
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM question_base where xml=".$db->quote($vID));
		$row = $result->fetch();
		if($row['question_id']==null){
			return null; //No corresponding record found in database
		}
		return Model_Quiz_QuestionBase::fromID($row['question_id']);
	}	
	
	

	public static function fromScratch($xml,$difficulty,$estimated_time,$question_type,$added_on){
		$db = Zend_Registry::get("db");
		$sql = "INSERT INTO question_base(question_id,xml,question_type,difficulty,estimated_time,added_on) VALUES(NULL, ".$db->quote($xml).",".$db->quote($question_type).",".$db->quote($difficulty).",".$db->quote($estimated_time).",'".date("Y-m-d",$added_on)."')";
		$db->query($sql);
		
		//Now find the appropriate entry in the database
		//	A safe (default) assumption for this is a query that looks for everything you just put in.
		
		$sql = "SELECT question_id FROM question_base WHERE xml='".$db->quote($xml)."'";
		
		$result = $db->query($sql);
		$row = $result->fetch();
		if($row['question_id']!=null){
			return Model_Quiz_QuestionBase::fromID($row['question_id']);
		}else{
			return null; //Something didn't happen
		}
	}

	// **********************
	// GETTER METHODS (GENERIC)
	// **********************

	public function getID(){ return $this->question_id; }
	public function getXml(){	return $this->xml;}
	public function getDifficulty(){	return $this->difficulty;}
	public function getAdded_on(){	return $this->added_on;}
	public function getQuestion_type(){	return strtolower($this->question_type);}
	public function getEstimated_time(){ return $this->estimated_time; }
	public function getType(){return $this->question_type;}

	// **********************
	// SETTER METHODS (GENERIC)
	// **********************


	public function setDifficulty($val){
		$db = Zend_Registry::get("db");
		$this->difficulty =  $val;
		$sql = "UPDATE question_base SET difficulty=".$db->quote($val)." WHERE question_id=".$this->question_id." LIMIT 1";
		$db->query($sql);
	}
	
	public function setEstimated_time($val){
		$db = Zend_Registry::get("db");
		$this->estimated_time =  $val;
		$sql = "UPDATE question_base SET estimated_time=".$db->quote($val)." WHERE question_id=".$this->question_id." LIMIT 1";
		$db->query($sql);
	}
	

	public function setAdded_on($val){
		$db = Zend_Registry::get("db");
		$this->added_on =  $val;
		$sql = "UPDATE question_base SET added_on=".$db->quote($val)." WHERE question_id=".$this->question_id." LIMIT 1";
		$db->query($sql);
	}



	// **********************
	// OTHER METHODS (SPECIFIC)
	// **********************
	public function getConcepts(){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT * FROM question_concepts WHERE question_basequestion_id=".$db->quote($this->question_id);
		$result = $db->query($sql);
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_Concept::fromID($row['conceptsconcept_name']);
		}
		return $vReturn;
	}
	
	public function addConcept($vConcept){
		$db = Zend_Registry::get("db");
		if(get_class($vConcept)!="Model_Quiz_Concept"){
			throw new Exception("Error: class.Model_Quiz_QuestionBase->addConcept - Object passed was not a concept (".get_class($vConcept).")", 3000);
		}
		
		
		//Make sure the concept isn't already associated with this question
		$vConcepts = $this->getConcepts();
		$vConceptExists = false;
		foreach($vConcepts as $vC){
			if($vConcept->getID()==$vC->getID()){
				$vConceptExists=true;
				return;
			}
		}
		if(!$vConceptExists){
			$sql = "INSERT INTO question_concepts VALUES(NULL, ".$db->quote($this->question_id).", ".$db->quote($vConcept->getID()).")";
			$db->query($sql);
		}
		
	}
	
	public static function getAllFromConceptAndDifficulty($vConcept, $vDifficulty){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$sql = "SELECT qb.question_id FROM question_base qb, question_concepts qc WHERE qb.question_id=qc.question_basequestion_id AND qc.conceptsconcept_name=".$db->quote($vConcept->getID())." AND qb.difficulty=".$db->quote($vDifficulty);
		$result = $db->query($sql);
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_QuestionBase::fromID($row['question_id']);
		}
		if(sizeof($vReturn)<1){
			//There's a possibility we specified a concept with the incorrect difficulty. Let's try any questions a difficulty lower...
			$sql = "SELECT qb.question_id FROM question_base qb, question_concepts qc WHERE qb.question_id=qc.question_basequestion_id AND qc.conceptsconcept_name=".$db->quote($vConcept->getID())." AND qb.difficulty=".$db->quote($vDifficulty-1);
			$result = $db->query($sql);
			$rows = $result->fetchAll();
			foreach($rows as $row){
				$vReturn[] = Model_Quiz_QuestionBase::fromID($row['question_id']);
			}
		}
		if(sizeof($vReturn)<1){
			//OK. Something is wrong here, but we HAVE to return a question. Take out the difficulty clause
			$sql = "SELECT qb.question_id FROM question_base qb, question_concepts qc WHERE qb.question_id=qc.question_basequestion_id AND qc.conceptsconcept_name=".$db->quote($vConcept->getID());
			$result = $db->query($sql);
			$rows = $result->fetchAll();
			foreach($rows as $row){
				$vReturn[] = Model_Quiz_QuestionBase::fromID($row['question_id']);
			}
		}
		
		return $vReturn;
	}
	
	public static function getAll(){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$result = $db->query("SELECT * FROM question_base");
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_QuestionBase::fromID($row['question_id']);
		}
		return $vReturn;
	}
	
	public function getGeneratedQuestionCount(){
		$db = Zend_Registry::get("db");
		$sql = "SELECT COUNT(*) AS count FROM generated_questions WHERE question_basequestion_id=".$db->quote($this->question_id)." AND generated_id NOT IN (SELECT generated_questionsgenerated_id FROM question_attempt)";
		$result = $db->query($sql);
		$row = $result->fetch();
		return $row['count'];
	}




} // class Model_Quiz_QuestionBase : end

?>
