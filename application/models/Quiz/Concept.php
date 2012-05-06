<?php
/*
*  Randoimised Quiz Generation System
*		Ben Evans 2010
*		ITEC810 - Proof of Concept for Masters Major Project
*		Macquarie University
*		Email: ben@nebev.net
* -------------------------------------------------------
* CLASSNAME:        Concept
* CORRESPONDING MYSQL TABLE:  concepts
* FOR MYSQL DB:     quiz_db
* -------------------------------------------------------
* Class Description:
*  Sort of a pointless class. Just a placeholder to access the
*  concepts table in the database. This table maintains a record of all the
*  concepts that the various XML file contain, and is needed so that we can
*  specify what concepts to test in each quiz.
*/


// **********************
// CLASS DECLARATION (GENERIC)
// **********************

class Model_Quiz_Concept
{
	
	// **********************
	// ATTRIBUTE DECLARATION (GENERIC)
	// **********************

	var $concept_name;   // KEY ATTR. WITH AUTOINCREMENT



	// **********************
	// CONSTRUCTORS (GENERIC)
	// **********************
	
	public static function fromID($vID){
		$db = Zend_Registry::get("db");
				
		//Start by making sure the appropriate record exists
		$stmt = $db->query("SELECT * FROM concepts where concept_name=" . $db->quote($vID));
		$row = $stmt->fetch();
		if($row['concept_name']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new Model_Quiz_Concept();
		$vReturn->concept_name = $row['concept_name'];

		return $vReturn;		//Return the result
	}
	

	public static function fromScratch($vConcept){
		
		//echo "CLASS: VConcept: " . $vConcept;
		$db = Zend_Registry::get("db");
		$query = "INSERT INTO concepts(concept_name) VALUES( ". $db->quote($vConcept) ." )";
		$stmt = $db->query($query);
		
		
		//Now find the appropriate entry in the database
		//	A safe (default) assumption for this is a query that looks for everything you just put in.
		$sql = "SELECT concept_name FROM concepts WHERE concept_name=" . $db->quote($vConcept);
		$stmt = $db->query($sql);
		$row = $stmt->fetch();
		if($row['concept_name']!=null){
			return Concept::fromID($row['concept_name']);
		}else{
			return null; //Something didn't happen
		}
		
	}

	// **********************
	// GETTER METHODS (GENERIC)
	// **********************

	public function getID(){ return $this->concept_name; }

	public function getConcept_name(){	return $this->concept_name;}

	// **********************
	// SETTER METHODS (GENERIC)
	// **********************


	public function setConcept_name($val){
		$this->concept_name =  $val;
		$db = Zend_Registry::get("db");
		$sql = "UPDATE concepts SET concept_name=". $db->quote($val) ." WHERE concept_name=".$db->quote($this->concept_name)." LIMIT 1";
		$db->query($sql);
	}
	
	public static function getAll(){
		$db = Zend_Registry::get("db");
		$vReturn = array();
		$result = $db->query("SELECT * FROM concepts");
		$rows = $result->fetchAll();
		
		foreach($rows as $row){
			$vReturn[] = Model_Quiz_Concept::fromID($row['concept_name']);
		}
		return $vReturn;
	}





} // class Concept : end

?>
