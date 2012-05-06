
<?php
/*
*  Randoimised Quiz Generation System
*		Ben Evans 2010
*		ITEC810 - Proof of Concept for Masters Major Project
*		Macquarie University
*		Email: ben@nebev.net
* -------------------------------------------------------
* CLASSNAME:        Model_Quiz_Question
* CORRESPONDING MYSQL TABLE:  question_base
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
	
	public static function fromID($vID){
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		
		$result = $db->query("SELECT * FROM Question where question_id=".$db->quote($vID));
		$row = $result->fetch();
		if($row['question_id']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new Question();
		$vReturn->question_id = $row['question_id'];
		$vReturn->xml = $row['xml'];
		$vReturn->difficulty = $row['difficulty'];
		$vReturn->added_on = $row['added_on'];

		return $vReturn;		//Return the result
	}
	

	public static function fromScratch($xml,$difficulty,$added_on){
		$db = Zend_Registry::get("db");
		$sql = "INSERT INTO Question(question_id,xml,difficulty,added_on) VALUES(NULL, ".$db->quote($xml).",".$db->quote($difficulty).",".$db->quote($added_on).")";
		$db->query($sql);
		
		//Now find the appropriate entry in the database
		//	A safe (default) assumption for this is a query that looks for everything you just put in.
		
		$sql = "SELECT question_id FROM Question WHERE xml=".$db->quote($xml)." AND difficulty=".$db->quote($difficulty)." AND added_on=".$db->quote($added_on);
		$result = $db->query($sql);
		$row = $result->fetch();
		if($row['question_id']!=null){
			return Model_Quiz_Question::fromID($row['question_id']);
		}else{
			return null; //Something didn't happen
		}
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



	// **********************
	// OTHER METHODS (SPEIFIC)
	// **********************





} // class Model_Quiz_Question : end

?>
