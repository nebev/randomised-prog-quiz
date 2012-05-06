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
class ShellController extends Zend_Controller_Action{
	
    public function init(){
        $this->_auth = Zend_Auth::getInstance();
		
		if( $this->_auth->hasIdentity() ) {
			$identity = Zend_Auth::getInstance()->getIdentity();
			if( !isset($identity->username) ) {
				// Don't know how you got here... But you're not authenticated
				$this->_helper->redirector("login", "auth");	//Must Log in before accessing anything
			}
		
			$this->view->username = $identity->username;
		
			// Determine what sidebars this person has access to
			// (Determined at this point by defined groups)
			$auth_model = Model_Auth_General::getAuthModel();
			if( $auth_model->userInGroup( $identity->username, QUIZ_ADMINISTRATORS ) ) {
				$this->view->is_admin = true;
			}else{
				$this->view->is_admin = false;
			}
		
		
		}else{
			$this->_helper->redirector("login", "auth");	//Must Log in before accessing anything
		}
    }
	
	
	
	/**
	 * This action is the quiz shell.
	 * It ensures permissions, creates new attempts etc etc.
	 *
	 * @author Ben Evans
	 */
	public function attemptAction() {
		
		$debug = true;
		
		$identity = Zend_Auth::getInstance()->getIdentity();
		$username = $identity->username;
		$auth_model = Model_Auth_General::getAuthModel();
		
	
		/*	Before we do anything, test to make sure we've passed a VALID QUIZ which WE ARE ENTITLED to sit.	*/
		if($_GET['quiz']==null && $_POST['quiz']==null){
			throw new Exception("No quiz was passed. Cannot continue.");
		}
			
		if($_GET['quiz']!=null)
			$vQuiz = Model_Quiz_Quiz::fromID($_GET['quiz']);
		else	
			$vQuiz = Model_Quiz_Quiz::fromID($_POST['quiz']);
	
		
		if($vQuiz==null){
			throw new Exception("Quiz ID passed was invalid. Cannot continue.");
		}
	
	
		$mFinished = false;
		$mMarking = false;
	
	
		//Permissions
		if($auth_model->userInGroup($username, $vQuiz->getPermissions_group()) && $vQuiz->getOpen_date()<=strtotime("now")){
		
		
			//Have we run out of attempts?
			$vAttempts = Model_Quiz_QuizAttempt::getAllFromUser($username, $vQuiz);
			if(sizeof($vAttempts) >= $vQuiz->getMax_attempts()){
			
				//It is possible that we're on our last attempt, and that it's "in progress"...check
				$bInProgress = false;
				foreach($vAttempts as $vAttempt){
					if($vAttempt->getDate_finished()==null){
						$bInProgress=true;
					}
				}
				if(!$bInProgress){
					throw new Exception("You've exceeded your maximum attempts for this quiz. Cannot continue");
				}
			}		
		
		}else{
			if(!$this->view->is_admin){
				throw new Exception("Insufficient Permissions to take this quiz / Quiz not open yet");
			}
				
			$vAttempts = Model_Quiz_QuizAttempt::getAllFromUser($username, $vQuiz);
		}
	



		/*	Ok. We're allowed to TAKE the quiz. Are we resuming, or starting a new one? */
		$mQuizAttempt = null;
		if(is_array($vAttempts)){
			foreach($vAttempts as $vAttempt){
				if($vAttempt->getDate_finished()==null){
					$mQuizAttempt = $vAttempt;
					break;
				}
			}//End Foreach
		}//End If


		if($mQuizAttempt==null){
			$mQuizAttempt = Model_Quiz_QuizAttempt::fromScratch(strtotime("now"), $vQuiz, $username);
		}


		/* Calculate the total questions needed for this quiz */
		$vTCs = $vQuiz->getTestedConcepts();
		$vTotalQuestions = 0;
		foreach($vTCs as $vTC){
			$vTotalQuestions = $vTotalQuestions + $vTC->getNumber_tested();
		}




		/*	We have our quizAttempt ready to go. Now we look to see if we're resuming a question or not */
	
		$mQuestionAttempt = $mQuizAttempt->getLastIncompleteQuestion();
		if($mQuestionAttempt!=null){
		
			/*	Are we getting an ANSWER for this question? */
			if(array_key_exists("marking", $_POST) && $_POST['marking']=="1"){
				/*	Mark it */
				$mMarking=true;			
			}		
		
			/* If we reach here, the page has probably been refreshed. We just re-display the last question */	
		
		}else{
		
			/* Have we finished this quiz? */

		
			if($mQuizAttempt->getQuestionAttemptCount() >= $vTotalQuestions){
		
				//Close this attempt and display a result later on down the page
				$mQuizAttempt->setDate_finished(strtotime("now"));
		
				//Calculate and store the final score
				$mQuizAttempt->setTotal_score($mQuizAttempt->getTotal_score());
				$mFinished = true;
			}else{
				
				/*	QuizAttempt isn't finished... Fetch a questionBase */
				$vQuestionBase =  Model_Shell_QuestionChooser::select_next_question($mQuizAttempt, $debug);
		
				/* Make a GeneratedQuestion */
				$vCounter = 0; //Make sure we don't get any fluke no-text answers
					while($vCounter<3){
						
						if( $debug ) {
							echo "Generating... from " . $vQuestionBase->getXml() . "\n";
						}
						
						$vGen = Model_Quiz_GeneratedQuestion::fromQuestionBase($vQuestionBase);
				
						if($vGen->getCorrect_answer()!="" && $vGen->getCorrect_answer()!= "\r\n"){
							break;
						}else{
							$vGen->remove();
						}
						$vCounter++;
					}
			
					if($vGen->getCorrect_answer()=="" || $vGen->getCorrect_answer()== "\r\n"){
						throw new Exception("Error. While generating a question for you, blank answers appeared > 3 times. This should never happen. Either try to refresh this page, or consult your lecturer...");
					}
		
		
					/* Make a QuestionAttempt */
					$mQuestionAttempt = Model_Quiz_QuestionAttempt::fromScratch($vQuestionBase, strtotime("now"), strtotime("now"), $mQuizAttempt, $vGen);
			
				}//End-if_finished_quizAttempt
			}
	
	
			// Pass all relevant information to the view
			$this->view->quiz = $vQuiz;
			$this->view->question_attempt = $mQuestionAttempt;
			$this->view->finished = $mFinished;
			$this->view->marking = $mMarking;
			$this->view->mQuizAttempt = $mQuizAttempt;
			$this->view->vTotalQuestions = $vTotalQuestions;
	}
	
	
	
	public function imagegenAction() {
	
		if($_GET['gid']==null)
			die();
		else{
			$gc = Model_Quiz_GeneratedQuestion::fromID($_GET['gid']);
			if($gc==null){
				die();
			}
		}
	
	
		//TODO: Some more auth here...
		$image_generator = new Model_Image_Generator();
	
		$image_generator->makeImage($gc->getQuestion_data());
		die();
	}
	
	
	
}







