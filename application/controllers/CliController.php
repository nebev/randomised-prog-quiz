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
				$vGeneratedQuestion = Model_Quiz_GeneratedQuestion::generateNewFromQuestionBase($vQuestionBase);
			}catch(Exception $e) {
				cronlog("Could not generate question. " . $e->getMessage() );
				echo Model_Shell_Debug::getInstance()->getLog();
				$error_counter++;
			}
			
		}
		
		cronlog("Finished. Generated " . $question_counter . " questions; " . $error_counter . " errornous questions generated (but discarded)");
	}
	
	
	
	
}