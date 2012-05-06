<?php

class AuthController extends Zend_Controller_Action
{

    public function init(){
        $this->_auth = Zend_Auth::getInstance();
		
		if( $this->_auth->hasIdentity() ) {
			$identity = Zend_Auth::getInstance()->getIdentity();
			if( !isset($identity->username) ) {
				// Don't know how you got here... But you're not authenticated
				$this->_forward("login");
			}
		
			$this->view->username = $identity->username;
		
		}else{
			$this->_forward("login");	//Must Log in before accessing anything
		}
    }

	/**
	 * The Login Action
	 * @author Ben Evans
	 */
	public function loginAction() {
		$this->view->headTitle("Login");
		$this->view->title = "Login";
		
		if( $this->getRequest()->isPost() ) {
			
			$username = $this->_getParam("rqz-username");
			$password = $this->_getParam("rqz-password");
			
			if( isset($username) && isset($password) ) {
				// We have some Data. Call the Authentication Model
				$auth_model = Model_Auth_General::getAuthModel();
				$auth_result = $auth_model->authenticate( $username, $password );
				if( $auth_result === true ) {
					
					$auth_storage = $this->_auth->getStorage();
					$auth_object = new stdClass;
					$auth_object->username = $username;
					$auth_object->login_time = time();
					
					
					$auth_storage->write( $auth_object );
					
					$identity = Zend_Auth::getInstance()->getIdentity();
					
					$this->_helper->redirector("index", "index");
				}
			}
			
			$this->view->msg = "There was a problem with your credentials. Check your username and password, and try again.";
		}
		
	}


	public function logoutAction() {
		$this->_auth->clearIdentity();
		$this->_redirect("/");
	}


}







