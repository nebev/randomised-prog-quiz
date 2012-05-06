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







