<?php

	class ReportsController extends Zend_Controller_Action{
		
		public function init() {
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
		 * List all available reports
		 */
		public function listAction() {
			$reports = array(
				array("name" => "Pass / Fail Results for Class", "action" => "passfail"),
				array("name" => "Time Taken Per Quiz", "action" => "timetaken"),
				array("name" => "Question Analysis", "action" => "questionanalysis"),
			);
			$this->view->reports = $reports;
		}
		
		/**
		 * Shows Pass/Fail for a given class [Group]
		 */
		public function passfailAction() {
			$group = $this->_getParam("group");
			$this->view->group = $group;
			
			
			// Pass ALL the groups to the view (to select)
			$all_groups = array();
			$all_quizzes = Model_Quiz_Quiz::getAll();
			foreach( $all_quizzes as $quiz ) {
				$all_groups[] = $quiz->getPermissions_group();
			}
			$all_groups = array_unique($all_groups);
			sort($all_groups);
			$this->view->all_groups = $all_groups;
			
			
			// If we've SELECTED a group...
			if( !is_null($group) ) {
				$group = strtolower($group);
				
				// Group Members
				$members = Model_Auth_ActiveDirectory::getUsersFromGroup( $group );
				$keyed_members = array();
				foreach( $members as $member ) {
					$username = $member;
					$member = Model_Auth_ActiveDirectory::getUserDetails($username);
					$member['username'] = strtolower($username);
					$keyed_members[ $username ] = $member;
				}
				
				
				// Find all Quizzes that are part of this group
				$all_quizzes = Model_Quiz_Quiz::getAll();
				$valid_quizzes = array();
				foreach( $all_quizzes as $quiz ) {
					if( strtolower($quiz->getPermissions_group()) == $group ) {
						$valid_quizzes[] = $quiz;
					}
				}
				
				// Now go and find all the results for each quiz
				$quiz_results = array();	// Key is the quiz ID
				foreach( $valid_quizzes as $quiz ) {
					$set_result = array();
					foreach( $keyed_members as $member ) {

						//Did they pass?
						$highest_result = Model_Quiz_QuizAttempt::getHighestMarkQuiz($member['username'], $quiz);
						if( is_null($highest_result) ) {
							$set_result[ $member['username'] ] = "NA";
						}else{
							if( ($highest_result->getTotal_score() / $quiz->getTotalQuestions()) * 100 >= $quiz->getPercentage_pass()){
								$set_result[ $member['username'] ] = "<span class='green'>P</span>";
							}else{
								$set_result[ $member['username'] ] = "<span class='red'>F</span>";
							}
						}
					}
					$quiz_results[ $quiz->getID() ] = $set_result;
				}
				
				// Pass all info to the view
				$this->view->members = $keyed_members;
				$this->view->quizzes = $valid_quizzes;
				$this->view->quiz_results = $quiz_results;
				
			}
			
			
			
		}
		
		
		/**
		 * Shows the time taken for each quiz
		 */
		public function timetakenAction() {
			
		}
		
		
		/**
		 * Shows the Question Analysis
		 */
		public function questionanalysisAction() {
			$filename = $this->_getParam("file");
			$this->view->file = $filename;
			
			// Get All Question Bases
			$this->view->all_question_bases = Model_Quiz_QuestionBase::getAll();
			
			if( !is_null($filename) && isset($filename) ) {
				$question_base = Model_Quiz_QuestionBase::fromID( $filename );
				$attempts = Model_Quiz_QuestionAttempt::getAllFromQuestionBase($question_base);
				
				// Generate a sample question
				$sample_question = new Model_Shell_GenericQuestion(APPLICATION_PATH . "/../xml/questions/" . $question_base->getXml());
				
				
				$this->view->question_base = $question_base;
				$this->view->attempts = $attempts;
				$this->view->sample_question = $sample_question;
			}
			
		}
		
	}


?>