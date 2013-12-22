<?php
/**
 *  Randomised Programming Quiz System - A quiz system that develops random programming questions from defined templates
 *  Copyright (C) 2010-2014 Ben Evans <ben@nebev.net>
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



class CliController extends Zend_Controller_Action {
	
	/**
	 * Ensures that this is ONLY run by CLI
	 * @see Zend_Controller_Action::init()
	 */
	public function init() {
		if(php_sapi_name() != "cli") {
			throw new Exception("This controller is only available using the CLI Interface");
		}
		
		// Disable all Layouts/Views
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(TRUE);
		
		// Just make a new random question, so we get access to functions like Randset
		$temp = new Model_Quiz_GeneratedQuestion();
	}
	
	
	/**
	 * Generates a bank of questions for a question identifier provided
	 * Expects parameters [question_id], [questions_to_generate_num], [max_errors_num]
	 */
	public function generatequestionbankAction() {
		
		// Check to see that all the parameters get passed
		$parameters = $this->_getAllParams();
		$check_errors = array();
		foreach( array("question_id" => "Question Identifier", "questions_to_generate_num" => "Number of Questions to Generate", "max_errors_num" => "Maximum number of Errors") as $check_key => $check_text) {
			if( !array_key_exists($check_key, $parameters) || !is_numeric($parameters[$check_key]) || $parameters[$check_key] < 0 ) {
				$check_errors[] = $check_text . " [". $check_key ."] was not passed, or not a positive number.";
			}
		}
		if( sizeof($check_errors) > 0) {
			cronlog("The following validation errors occured:");
			foreach($check_errors as $ce) {
				cronlog($ce);
			}
			exit();
		}
		
		$question_base_id = intval($parameters['question_id']);
		$number_of_questions = intval($parameters['questions_to_generate_num']);
		$maximum_errors = intval($parameters['max_errors_num']);
		
		cronlog("Generating $number_of_questions questions from Question Base Identifier $question_base_id ; Maximum Error threshold is $maximum_errors");
		$vQuestionBase = Model_Quiz_QuestionBase::fromID( $question_base_id );
		
		if( is_null($vQuestionBase) ) {
			throw new Exception("The question base identifier passed was invalid");
		}

		$question_counter = 0;
		$error_counter = 0;
		
		while( $question_counter <= $number_of_questions && $error_counter <= $maximum_errors ) {
			
			try{
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
							$question_counter++;

						}else{
							cronlog("There were not enough valid reponses for the multiple choice question generated.");
							$error_counter++;
						}
						
						
					}else{
						
						// Not a multiple choice question
						$vGenerated = Model_Quiz_GeneratedQuestion::fromScratch($vQuestion->getInstructions(), $vQuestion->getProblem(), $vQuestion->getCorrectOutput(), $vQuestionBase);
						$question_counter++;
							
					}
					
				}else{
					$error_counter++;
				}
			}catch(Exception $e) {
				cronlog("Could not generate question. " . $e->getMessage() );
				echo Model_Shell_Debug::getInstance()->getLog();
				$error_counter++;
			}
			
		}
		
		cronlog("Finished. Generated " . $question_counter . " questions; " . $error_counter . " errornous questions generated (but discarded)");
	}
	
	
	
	
}