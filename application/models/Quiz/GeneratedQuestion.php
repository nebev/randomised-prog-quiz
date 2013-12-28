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
* CLASSNAME:        Model_Quiz_GeneratedQuestion
* CORRESPONDING MYSQL TABLE:  generated_questions
* FOR MYSQL DB:     quiz_db
* -------------------------------------------------------
* Class Description:
*/


// **********************
// CLASS DECLARATION (GENERIC)
// **********************

class Model_Quiz_GeneratedQuestion
{
	
	// **********************
	// ATTRIBUTE DECLARATION (GENERIC)
	// **********************

	var $generated_id;   // KEY ATTR. WITH AUTOINCREMENT

	var $instructions;   // (normal Attribute)
	var $question_data;   // (normal Attribute)
	var $correct_answer;   // (normal Attribute)
	var $alt_ans_1;   // (normal Attribute)
	var $alt_desc_1;   // (normal Attribute)
	var $alt_ans_2;   // (normal Attribute)
	var $alt_desc_2;   // (normal Attribute)
	var $alt_ans_3;   // (normal Attribute)
	var $alt_desc_3;   // (normal Attribute)
	var $question_basequestion_id;   // (normal Attribute)


	// **********************
	// CONSTRUCTORS (GENERIC)
	// **********************
	
	public static function fromID($vID){
		//Start by making sure the appropriate record exists
		$db = Zend_Registry::get("db");
		$result = $db->query("SELECT * FROM generated_questions where generated_id=".$db->quote($vID));
		$row = $result->fetch();
		if($row['generated_id']==null){
			return null; //No corresponding record found in database
		}
		
		//Assuming we have the appropriate records
		$vReturn = new Model_Quiz_GeneratedQuestion();
		$vReturn->generated_id = $row['generated_id'];
		$vReturn->instructions = $row['instructions'];
		$vReturn->question_data = $row['question_data'];
		$vReturn->correct_answer = $row['correct_answer'];
		$vReturn->alt_ans_1 = $row['alt_ans_1'];
		$vReturn->alt_desc_1 = $row['alt_desc_1'];
		$vReturn->alt_ans_2 = $row['alt_ans_2'];
		$vReturn->alt_desc_2 = $row['alt_desc_2'];
		$vReturn->alt_ans_3 = $row['alt_ans_3'];
		$vReturn->alt_desc_3 = $row['alt_desc_3'];
		$vReturn->question_basequestion_id = Model_Quiz_QuestionBase::fromID($row['question_basequestion_id']);

		return $vReturn;		//Return the result
	}
	
	/**
	 * Writes a brand new Question to the database
	 *
	 * @param ? $instructions 
	 * @param ? $question_data 
	 * @param ? $correct_answer 
	 * @param ? $vQuestion 
	 * @return null|Model_Quiz_GeneratedQuestion
	 */
	public static function fromScratch($instructions,$question_data,$correct_answer,$vQuestion){
		
		Model_Shell_Debug::getInstance()->log("Writing question to the database");
		
		$db = Zend_Registry::get("db");
		$sql = "INSERT INTO generated_questions(generated_id,instructions,question_data,correct_answer,question_basequestion_id) VALUES(NULL, ".$db->quote($instructions).",".$db->quote($question_data).",".$db->quote($correct_answer).",".$db->quote($vQuestion->getID()).")";
		Model_Shell_Debug::getInstance()->log($sql);
		$db->beginTransaction();
		$db->query($sql);
		
		//Now find the appropriate entry in the database
		//	A safe (default) assumption for this is a query that looks for everything you just put in.
		
		$last_id = $db->lastInsertId();
		$db->commit();
		return Model_Quiz_GeneratedQuestion::fromID($last_id);
		
		
		$sql = "SELECT generated_id FROM generated_questions WHERE instructions=".$db->quote($instructions)." AND question_data=".$db->quote($question_data)." AND correct_answer=".$db->quote($correct_answer)." AND question_basequestion_id=".$db->quote($vQuestion->getID());
		$result = $db->query($sql);
		$row = $result->fetch();
		if($row['generated_id']!=null){
			return Model_Quiz_GeneratedQuestion::fromID($row['generated_id']);
		}else{
			return null; //Something didn't happen
		}
	}
	
	/**
	 * Gets a new Question from the Question Base object provided
	 * @param Model_Quiz_QuestionBase $vQB
	 * @return Model_Quiz_GeneratedQuestion NULL if not defined
	 */
	public static function fromQuestionBase($vQB){
		$db = Zend_Registry::get("db");
		
		//Firstly see if there's any 'spare' Model_Quiz_GeneratedQuestions
		Model_Shell_Debug::getInstance()->log("Seeing if there are any Pregenerated Questions for Question Identifier " . $vQB->getID());
		$result = $db->query("SELECT generated_id FROM generated_questions WHERE question_basequestion_id=".$db->quote($vQB->getID())." AND generated_id NOT IN(SELECT generated_questionsgenerated_id AS generated_id FROM question_attempt)");
		$row = $result->fetch();
		if($row['generated_id']!=null){
			Model_Shell_Debug::getInstance()->log("Found PreGenerated Question with Generated Identifier " . $row['generated_id']);
			return Model_Quiz_GeneratedQuestion::fromID($row['generated_id']);
		}

		return self::generateNewFromQuestionBase($vQB);	//Return a new generated question as there are no spares in the database
	}
	
	
	/**
	 * This will remove ALL pre-generated questions that haven't been used in a quiz
	 */
	public static function removePregeneratedQuestions() {
		$db = Zend_Registry::get("db"); /* @var $db Zend_DB_Adapter_Abstract */
		Model_Shell_Debug::getInstance()->log("Removing all Pre-generated questions");
		$rows = $db->query("SELECT generated_id FROM generated_questions 
			WHERE generated_id NOT IN(SELECT generated_questionsgenerated_id AS generated_id FROM question_attempt)")->fetchAll();
		
		foreach($rows as $row) {
			$db->delete("generated_questions", "generated_id = " . $db->quote($row['generated_id']));
		}
	}
	
	
	/**
	 * This will generate a brand new question from a question base.
	 * Unlike fromQuestionBase(), this method will NOT consult the database for question first
	 * @param Model_Quiz_QuestionBase $vQuestionBase
	 * @return Model_Quiz_GeneratedQuestion
	 */
	public static function generateNewFromQuestionBase( Model_Quiz_QuestionBase $vQuestionBase ) {
		
		$vGenerated = null;
		$error_threshold = 4;
		$error_counter = 0;
		
		while( $error_counter <= $error_threshold && is_null($vGenerated) ) {

			// Start by ensuring the Question is has instructions outputs etc etc
			$vQuestion = new Model_Shell_GenericQuestion(APPLICATION_PATH . "/../xml/questions/" . $vQuestionBase->getXml());
			$problem_string = $vQuestion->getProblem();
			$question_output = $vQuestion->getCorrectOutput();
			
			// We need to make sure that the question has valid output
			if( strlen(trim($question_output)) > 0 ) {
					
				// If the question is multiple choice, we need to ensure that all answers are different
				$alternate_answers = $vQuestion->getAnswers();
				if( !is_null( $alternate_answers ) && sizeof( $alternate_answers ) > 0 ) {
					shuffle($alternate_answers);
			
					// Now, we need to ensure that we have 3 different answers that are ALL different to the actual answer
					$answer_set = array( trim($question_output) );	// Value is the answer
					foreach( $alternate_answers as $aa_key => $alternate_answer ) {
						if( is_array($alternate_answer) ) {
							$alternate_answer = $alternate_answer[0];
						}
						$alternate_answer = trim( $alternate_answer );
						if( in_array($alternate_answer, $answer_set) || strlen( trim($alternate_answer) ) == 0 ) {
							unset($alternate_answers[$aa_key]);	// Answer already exists, or is blank (unusable)
						}else{
							$answer_set[] = $alternate_answer;
						}
					}
			
					if( sizeof($alternate_answers) >= 3 ) {
							
						// All is good. We can add this question, as well as all its alternate answers
						$vGenerated = Model_Quiz_GeneratedQuestion::fromScratch($vQuestion->getInstructions(), $vQuestion->getProblem(), $vQuestion->getCorrectOutput(), $vQuestionBase);
						$vNum = 1;
						foreach($alternate_answers as $vAltAnswer){
							if($vNum>3){
								break; 	//Can't have more than 3 alternates
							}
								
							if(is_array($vAltAnswer))
								$vGenerated->addAlternateAnswer($vNum, $vAltAnswer[0], $vAltAnswer[1]);
							else
								$vGenerated->addAlternateAnswer($vNum, $vAltAnswer, "");
								
							$vNum++;
						}
			
					}else{
						$error_counter++;
					}
				}else {
					// Not a multiple choice question
					$vGenerated = Model_Quiz_GeneratedQuestion::fromScratch($vQuestion->getInstructions(), $vQuestion->getProblem(), $vQuestion->getCorrectOutput(), $vQuestionBase);
				}
					
			}else{
				$error_counter++;	// The question didn't return a result
			}
		}
		
		if( is_null($vGenerated) ) {
			throw new Exception("Attempted to generate a question " . $error_threshold . "  times, but failed. Cannot continue");
		}
		
		
		//If the question is a fill-in question, put the whole solution in the 1st alternate answer column
		if($vQuestion->getFriendlyType()=="fill-in"){
			$vGenerated->setAlt_desc_1($vQuestion->getDebugProblem());
		}
		
		return $vGenerated;
	}
	
	
	
	
	
	
	

	// **********************
	// GETTER METHODS (GENERIC)
	// **********************

	public function getID(){ return $this->generated_id; }

	public function getGenerated_id(){	return $this->generated_id;}
	public function getInstructions(){	return $this->instructions;}
	public function getQuestion_data(){	return $this->question_data;}
	public function getCorrect_answer(){	return $this->correct_answer;}
	public function getBareAltAnswers(){ return array($this->alt_ans_1, $this->alt_ans_2, $this->alt_ans_3); }
	public function getFullAltAnswers(){
		return array(array($this->alt_ans_1, $this->alt_desc_1), array($this->alt_ans_2,$this->alt_desc_2), array($this->alt_ans_3,$this->alt_desc_3));
	}
	public function getQuestion_base(){	return $this->question_basequestion_id;}

	// **********************
	// SETTER METHODS (GENERIC)
	// **********************


	public function setGenerated_id($val){
		$db = Zend_Registry::get("db");
		$this->generated_id =  $val;
		$sql = "UPDATE generated_questions SET generated_id=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setInstructions($val){
		$db = Zend_Registry::get("db");
		$this->instructions =  $val;
		$sql = "UPDATE generated_questions SET instructions=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)."' LIMIT 1";
		$db->query($sql);
	}

	public function setQuestion_data($val){
		$db = Zend_Registry::get("db");
		$this->question_data =  $val;
		$sql = "UPDATE generated_questions SET question_data=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setCorrect_answer($val){
		$db = Zend_Registry::get("db");
		$this->correct_answer =  $val;
		$sql = "UPDATE generated_questions SET correct_answer=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setAlt_ans_1($val){
		$db = Zend_Registry::get("db");
		$this->alt_ans_1 =  $val;
		$sql = "UPDATE generated_questions SET alt_ans_1=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setAlt_desc_1($val){
		$db = Zend_Registry::get("db");
		$this->alt_desc_1 =  $val;
		$sql = "UPDATE generated_questions SET alt_desc_1=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setAlt_ans_2($val){
		$db = Zend_Registry::get("db");
		$this->alt_ans_2 =  $val;
		$sql = "UPDATE generated_questions SET alt_ans_2=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setAlt_desc_2($val){
		$db = Zend_Registry::get("db");
		$this->alt_desc_2 =  $val;
		$sql = "UPDATE generated_questions SET alt_desc_2=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setAlt_ans_3($val){
		$db = Zend_Registry::get("db");
		$this->alt_ans_3 =  $val;
		$sql = "UPDATE generated_questions SET alt_ans_3=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setAlt_desc_3($val){
		$db = Zend_Registry::get("db");
		$this->alt_desc_3 =  $val;
		$sql = "UPDATE generated_questions SET alt_desc_3=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}

	public function setQuestion_basequestion_id($val){
		$db = Zend_Registry::get("db");
		$this->question_basequestion_id =  $val;
		$sql = "UPDATE generated_questions SET question_basequestion_id=".$db->quote($val)." WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		$db->query($sql);
	}



	// **********************
	// OTHER METHODS (SPECIFIC)
	// **********************
	


	/**
	 * Add an alternate (Multiple Choice) answer to this question
	 * @param int $vNum The alternate answer number (1-3)
	 * @param string $vAns The actual answer as a string
	 * @param string $vDesc The reason why this answer is wrong
	 */
	public function addAlternateAnswer($vNum, $vAns, $vDesc){
		$db = Zend_Registry::get("db");
		if($vDesc!="" && $vDesc!=null){
			$pt2 = ", alt_desc_$vNum=".$db->quote($vDesc);
		}else{
			$pt2 = "";
		}
		$sql = "UPDATE generated_questions SET alt_ans_$vNum=".$db->quote($vAns)."$pt2 WHERE generated_id=".$db->quote($this->generated_id)." LIMIT 1";
		//echo $sql;
		$db->query($sql);
		if($vNum==1){
			$this->alt_ans_1 = $vAns;
		}elseif($vNum==2){
			$this->alt_ans_2 = $vAns;
		}else{
			$this->alt_ans_3 = $vAns;
		}
	}//End Function

	public function remove(){
		$db = Zend_Registry::get("db");
		$db->query("DELETE FROM generated_questions WHERE generated_id=".$db->quote($this->generated_id) );
	}




} // class Model_Quiz_GeneratedQuestion : end



function randset($vArray){
	return $vArray[(rand(0,(sizeof($vArray))-1))];
}


?>
