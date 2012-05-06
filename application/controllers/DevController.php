<?php

class DevController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $auth_model = Model_Auth_General::getAuthModel();
		$result = $auth_model->authenticate("administrator", "fgkjh");
		var_dump($result);
		die("finished");
    }

	public function testAction() {
		

		die();
	}


}







