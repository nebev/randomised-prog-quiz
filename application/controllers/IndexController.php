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
class IndexController extends Zend_Controller_Action
{

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
	 * This is the default page that people see
	 * Generally speaking, it shows the welcome page, and some sidebard
	 *
	 * @return void
	 * @author Ben Evans
	 */
    public function indexAction(){
        
		$this->view->title = "Welcome to the Randomised Quiz System (RQS)";
		$this->view->headTitle("Welcome");
		
    }
	
	
	
	
	public function availableAction() {
		$this->view->headTitle("Available Quizzes");
		
		$outstanding = $this->_getParam("outstanding");
		$vQuizzes = Model_Quiz_Quiz::getAll(true);
		$vAvailable = array();
		$auth_model = Model_Auth_General::getAuthModel();
		$identity = Zend_Auth::getInstance()->getIdentity();
		

		/*	Make sure you have permission	*/
		foreach($vQuizzes as $vQuiz){
			if($this->view->is_admin){
				$vAvailable[] = $vQuiz;
			}else if($auth_model->userInGroup($this->view->username, $vQuiz->getPermissions_group()) && $vQuiz->getOpen_date()<=strtotime("now")){
				$vAvailable[] = $vQuiz;
			}
		}


		if( isset($outstanding) ){
			$this->view->title = "Outstanding Quizzes";
	
			$vOutstanding = array();
			foreach($vAvailable as $vQuiz){
				$vQuizAttempt = Model_Quiz_QuizAttempt::fromQuizAndUser($vQuiz, $identity->username);
				if($vQuizAttempt==null){
					$vOutstanding[] = $vQuiz;
				}else{
					//Have an end time?
					if($vQuizAttempt->getDate_finished()==null){
						$vOutstanding[] = $vQuiz;
					}
				}
			}
	
			//Make the new 'available quizzes' the quizzes that aren't complete yet
			$vAvailable = $vOutstanding;
	
		}else{
			$this->view->title = "Available Quizzes";
		}

		$this->view->available = $vAvailable;

	}
	
	
	public function halloffameAction() {
		$this->view->title = "Hall of Fame";
		$this->view->headTitle("Hall of Fame");
		$identity = Zend_Auth::getInstance()->getIdentity();
		$auth_model = Model_Auth_General::getAuthModel();
		
		//Firstly, we have to get the quizzes that we have access to
		$vQuizzes = Model_Quiz_Quiz::getAll(true);
		$vAvailable = array();

		/*	Make sure you have permission	*/
		foreach($vQuizzes as $vQuiz){
			if($this->view->is_admin){
				$vAvailable[] = $vQuiz;
			}else if($auth_model->userInGroup($identity->username, $vQuiz->getPermissions_group()) && $vQuiz->getOpen_date()<=strtotime("now")){
				$vAvailable[] = $vQuiz;
			}
		}
		
		
		/*	Any Quizzes available? */
		$quiz_rows = array();
		if(sizeof($vAvailable)!=0){
			
			foreach($vAvailable as $vQuiz){
				$quiz_row = array();
				$quiz_row['name'] = $vQuiz->getName();
				
				$vAttempts = $vQuiz->getQuizAttempts();
				$vPassed = array();
				if(sizeof($vAttempts) > 0){
					foreach($vAttempts as $vAttempt){
						//Make sure we're only looking at people who've passed

						$vTotalScore = $vAttempt->getTotal_score();
						//echo "Comparing " . $vTotalScore . "/". $vQuiz->getTotalQuestions() ."  with " . $vQuiz->getPercentage_pass() . "%  (".$vAttempt->getID().")<br/>";  
						if(($vTotalScore/$vQuiz->getTotalQuestions())*100 >= $vQuiz->getPercentage_pass() && $vAttempt->getDate_finished()!=null){
							//echo "Added.<br/>";
							$vPassed[] = $vAttempt;
						}
					}
				}
	
	
				
				//Did anyone pass?
				if(sizeof($vPassed)>0){
			
					//Sort results first
					usort($vPassed,"sort_by_score");
		
					//Truncate the array if necessary
					if(sizeof($vPassed) > MAX_HALLOFFAME){
						array_splice($vPassed,$mTotalTopScores);
					}
			
					// And now record the scores
					$quiz_row['scores'] = array();
					
			
					//Now output the scores
					foreach($vPassed as $vp){
						$score_row = array(
							"name"	=> $vp->getAd_user_cachesamaccountname(),
							"score" => $vp->getTotal_score(),
							"time"	=> ($vp->getDate_finished() - $vp->getDate_started())
						);
	
						$quiz_row['scores'][] = $score_row;
					}
				}

				$quiz_rows[] = $quiz_row;
			}//End_foreach_quiz
		}
		
		$this->view->quiz_rows = $quiz_rows;
		
		
	}
	
	
	
	
	public function testquestiongenerationAction() {
		if( !$this->view->is_admin ) {
			throw new Exception("Access Denied");
		}
		
		$this->_helper->layout->disableLayout();
		
		
		/* Get the appropriate files and show them in a nice little combobox */
		$xml_path = APPLICATION_PATH . '/../xml/questions';
		$available_selects = array();
		if ($handle = opendir($xml_path)) {
		    while (false !== ($file = readdir($handle))) {
		        if(strtolower(substr($file,-3))=="xml"){
					$entity = "<option value='".substr($file,0,-4)."'";
						if((array_key_exists("q", $_GET)) && substr($file,0,-4) == $_GET['q']){
							$entity .= " selected='yes' ";
						}
					$entity .= ">$file</option>\n";
					$available_selects[] = $entity;
				}
				
		    }
			closedir($handle);
		}

		$this->view->available_selects = $available_selects;

		// See what Question we're looking at...
		$selected_xml = $this->_getParam("q");
		if( isset($selected_xml) && !is_null($selected_xml) ) {
			
			// Get the Question XML
			$mQuestion = new Model_Shell_GenericQuestion($xml_path . "/" . $selected_xml .".xml");
			$this->view->question = $mQuestion;
			
			// Just make a new random question, so we get access to functions like Randset
			$temp = new Model_Quiz_GeneratedQuestion();
			
		}
	}
	
	
	
	
	
	


}// End Class

function sort_by_score(&$a, &$b){
	if($a->getTotal_score()>$b->getTotal_score())
		return -1;
	elseif($a->getTotal_score()==$b->getTotal_score()){
		//Take into account the time taken
		//a less time (better) -> -1
		if(($a->getDate_finished()-$a->getDate_started()) <  ($b->getDate_finished()-$b->getDate_started()) )
			return -1;
		else
			return 1;
	}
	else
		return 1;
}







