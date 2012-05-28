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



class AdminController extends Zend_Controller_Action
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
		
		$this->view->baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		
		
		
		if( !$this->view->is_admin ) {
			throw new Ecxeption("Unauthorised.", 3005);
		}

    }


	/**
	 * Essentially just give a list of quizzes to edit
	 */
	public function manageAction() {
		 $this->view->quizzes = Model_Quiz_Quiz::getAll();
	}


	public function addeditAction() {
		
		// The Form
		$form = new Form_AddQuiz();
		$this->view->form = $form;
		
		
		// Editing? Or new Quiz?
		$editing = null;
		$editing = $this->_getParam("id");
		if( !is_null( $editing ) ) {			
			$editing = Model_Quiz_Quiz::fromID( $editing );
			$this->view->editing = $editing;
			
			// Populate Form
			$id = new Zend_Form_Element_Hidden('id');
			$id->setValue( $editing->getID() );
			$form->addElement($id);
			
			$form->getElement("name")->setValue($editing->getName());
			$form->getElement("permissions")->setValue($editing->getPermissions_group());
			$form->getElement("opendate")->setValue(date("Y-m-d",$editing->getOpen_date()));
			$form->getElement("closedate")->setValue(date("Y-m-d",$editing->getClose_date()));
			$form->getElement("attempts")->setValue($editing->getMax_attempts());
			$form->getElement("percentage")->setValue($editing->getPercentage_pass());

			
			
		}
		
	
		// Submitting?
		if( $this->getRequest()->isPost() ) {
			
			$formData = $_POST;
			
			if (!$form->isValid($_POST)) {
				// Failed validation; redisplay form
				$this->view->form = $form;
				return;
			}else{
				
				if( is_null($editing) ) {
					// New Quiz
					$vQuiz = Model_Quiz_Quiz::fromScratch($formData['name'],$formData['permissions'],$formData['opendate'],$formData['closedate'],$formData['attempts'],$formData['percentage']);
				}else{
					// Editing Quiz
					$editing->setQuiz_name($formData['name']);
					$editing->setPermissions_group($formData['permissions']);
					$editing->setOpen_date(strtotime($formData['opendate']));
					$editing->setClose_date(strtotime($formData['closedate']));
					$editing->setMax_attempts($formData['attempts']);
					$editing->setPercentage_pass($formData['percentage']);
				}
				
				// Redirect to the Manage Quiz Pages
				$this->_helper->redirector("manage", "admin");
			}
		}

		
		
	}



	public function deletequizAction() {
		
		$quiz_id = $this->_getParam("id");
		
		if( isset($quiz_id) ) {
			$vQuiz = Model_Quiz_Quiz::fromID($quiz_id);
			$vQuiz->remove();
		}
		
		// Redirect to the Manage Quiz Pages
		$this->_helper->redirector("manage", "admin");
	}



	public function showconceptsAction() {
		
		//Grab the Quiz
		$quiz_id = $this->_getParam("id");
		if( !isset($quiz_id) ) {
			throw new Exception("No Quiz Identifier Passed", 3000);
		}
		
		$quiz = Model_Quiz_Quiz::fromID($quiz_id);
		$this->view->quiz = $quiz;

		$this->view->concepts = Model_Quiz_Concept::getAll();
		
	}


	/**
	 * Add a Concept (Migrated Code Essentially)
	 */
	public function addconceptAction() {
		
		$vQuiz = $this->_getParam("id");
		$vQuiz = Model_Quiz_Quiz::fromID($vQuiz);
		
		/*	Process the ADD CONCEPT TO QUIZ Form */
		/*	Start by checking for errors */

		$vError = array();
		if($_POST['num']==null)
			$vError[] = "Num of questions was left blank";
		if($_POST['concept']==null)
			$vError[] = "Concept was left blank (how did you do that?)";
		if($_POST['from']==null)
			$vError[] = "Difficulty(From) was left blank";
		if($_POST['to']==null)
			$vError[] = "Difficulty (to) was left blank";

		if(sizeof($vError) == 0){
			
			/*	Assuming everything is ok... */
			$vConcept = Model_Quiz_Concept::fromID($_POST['concept']);
			$vTestedConcept = Model_Quiz_TestedConcept::fromScratch($_POST['from'],$_POST['to'],$_POST['num'],$vConcept,$vQuiz);
		}
		
		//Redirect to the concept page
		$params = array('id' => $_REQUEST['id']);
		$this->_helper->redirector("showconcepts", "admin", null, $params);
	}


	public function deleteconceptAction() {
		$concept_id = $this->_getParam("concept_id");
		if( !isset($concept_id) ) {
			throw new Exception("Count not delete concept. No identifier passed", 3000);
		}
		
		$vTestedConcept = Model_Quiz_TestedConcept::fromID( $concept_id );
		if($vTestedConcept == null)
			throw new Exception("ID passed did not correspond to a valid TestedConcept");
	
		$vQuiz = $vTestedConcept->getQuiz(); //For the return page

		$vTestedConcept->remove();

		//Redirect to the concept page
		$params = array('id' => $vQuiz->getID() );
		$this->_helper->redirector("showconcepts", "admin", null, $params);
		
	}


	/**
	 * Shows a list of Quizzes, the total number of
	 * attempts, and the date they are due (Summary Screen)
	 * @author Ben Evans
	 */
	public function resultsoverviewAction() {
		// Get all Quizzes
		$quizzes = Model_Quiz_Quiz::getAll(true);
		
		// Reverse Order
		$quizzes = array_reverse( $quizzes );
		
		$this->view->quizzes = $quizzes;
	}

	/**
	 * This shows the results of an individual quiz,
	 * It works by going through all the People in the Quizzes
	 * primary Active Directory group, and then seeing if their
	 * account has an attempt associated with it.
	 */
	public function resultsquizAction() {
		$quiz_id = $this->_getParam("quiz_id");
		if( !isset($quiz_id) ) {
			throw new Exception("No Quiz Identifier Passed", 3000);
		}
		$quiz = Model_Quiz_Quiz::fromID( $quiz_id );
		if( is_null($quiz) || $quiz === false ) {
			throw new Exception("Invalid Quiz Identifier", 3000);
		}
		
		// Pass the quiz (for general information)
		$this->view->quiz = $quiz;
		
		// Start By Populating an array with the Group information
		$results = array();
		$group_members = Model_Auth_ActiveDirectory::getUsersFromGroup( $quiz->getPermissions_group() );
		foreach( $group_members as $gm ) {
			$results[ $gm ] = array();
		}
		unset($group_members);
		
		// At this point, we have an array with keys being the username
		foreach( $results as $name => &$result ) {
			
			// Get the User's First and Last Name
			$details = Model_Auth_ActiveDirectory::getUserDetails( $name );
			$result['first_name'] = $details['first_name'];
			$result['last_name'] = $details['last_name'];
			$result['username'] = $name;
			
			//Get the verdict / best score...
			$vHighest = Model_Quiz_QuizAttempt::getHighestMarkQuiz($name, $quiz); // Will be null if not completed, Model_Quiz_QuizAttempt otherwise
			
			if( !is_null($vHighest) ) {
				
				// Get their finish date
				$result['completion_date'] = $vHighest->getDate_finished();
				
				//Is this 'highest' attempt still in progress?
				if( $vHighest->getDate_finished()==null ) {
					$result['verdict'] = "<span class='orange'>In Progress</span>";
				}else{
					// Completed
					//Did they pass/fail?
					if(($vHighest->getTotal_score() / $quiz->getTotalQuestions())*100 >= $quiz->getPercentage_pass()){
						$result['verdict'] = "<span class='green'>PASS</span>";
					}else{
						$result['verdict'] = "<span class='red'>FAIL</span>";
					}
				}
				
				
				
				// Best Score
				$result['best_score'] = $vHighest->getTotal_score();
				
				// Attempts
				$result['attempts'] = sizeof(Model_Quiz_QuizAttempt::getAllFromUser($name, $quiz));
			}
			
			
		}
		
		$this->view->results = $results;
		
	}


	/**
	 * This function rebuilds XML files
	 */
	public function rebuildxmlAction() {
		$process = $this->_getParam("process");
		if( is_null($process) || $process !== "1" ) {
			
			// Get the amount of files in the Questions Dir
			$counter = 0;
				if ($handle = opendir(APPLICATION_PATH . '/../xml/questions')) {
				    while (false !== ($file = readdir($handle))) {
				        if(strtolower(substr($file,-3))=="xml"){
							$counter = $counter + 1;
						}
				    }
					closedir($handle);
				}
			$this->view->count = $counter;		
		}else{
			
			// Process the XML Files
			if ($handle = opendir(APPLICATION_PATH . '/../xml/questions')) {
			    while (false !== ($file = readdir($handle))) {
			        if(strtolower(substr($file,-3))=="xml"){
						echo "Parsing file: $file <br/>\nConcepts: ";
						
						//Begin Processing
						$vQuestion = new Model_Shell_GenericQuestion(APPLICATION_PATH . '/../xml/questions/' . $file);

						//Add to questionBase (if not already there)
						$vQuestionBase = Model_Quiz_QuestionBase::fromXml($file);
						if($vQuestionBase == null){
							$vQuestionBase = Model_Quiz_QuestionBase::fromScratch($file, $vQuestion->getDifficulty(), $vQuestion->getEstimatedTime(), $vQuestion->getFriendlyType(), strtotime("today"));
						}


						//Now look at the concepts
						$vConcepts = $vQuestion->getConcepts();
						foreach($vConcepts as $vConcept){
							//Make sure this concept exists in the database
							$vConceptObj = Model_Quiz_Concept::fromID($vConcept);
							if($vConceptObj==null){
								//Doesn't exist... we should make a record
								$vConceptObj = Model_Quiz_Concept::fromScratch($vConcept);
							}
							echo $vConcept . "; ";

							//Now we need to make sure that this question has this concept associated with it
							$vQuestionBase->addConcept($vConceptObj);					

						}

						//Update the questionBase's Difficulty & EstimatedTime (these are the things most likely to change)
						$vQuestionBase->setDifficulty($vQuestion->getDifficulty());
						$vQuestionBase->setEstimated_time($vQuestion->getEstimatedTime());

						echo "<br/>Difficulty: " . $vQuestion->getDifficulty();
						echo "<br/>Estimated time to complete: " . $vQuestion->getEstimatedTime();



						echo "<br/><br/>\n";

					}
			    }
				closedir($handle);
			}
			
			
			
		}
				
		
	}

 
	/**
	 * This function Caches the local Usernames (and First/Last names)
	 *  from the designated Authentication source (eg. LDAP)
	 */
	public function syncusernamesAction() {

		$vGroups = array();
		$vCounter = 0;

		//Get all the quizzes
		$vQuizzes = Model_Quiz_Quiz::getAll();
		foreach($vQuizzes as $vQuiz){
			if(!in_array($vQuiz->getPermissions_group(),$vGroups)){
				$vGroups[] = $vQuiz->getPermissions_group();
			}
		}

		//So we have all groups now in the system
		foreach($vGroups as $vGroup){
			//Get the members of this group
			$vMembers = Model_Auth_ActiveDirectory::getUsersFromGroup( $vGroup );
			if( is_array($vMembers) && sizeof($vMembers) > 0 ) {
				foreach($vMembers as $vMember){
					Model_Auth_ActiveDirectory::updateUser($vMember);
					$vCounter++;
				}
			}
		}
		
		$this->view->counter = $vCounter;
		
	}

}
?>