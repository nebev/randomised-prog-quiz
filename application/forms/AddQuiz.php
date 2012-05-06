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
class Form_AddQuiz extends Zend_Form
{

	
    public function init()
    {
        $this->setName('addquiz');


		//Setup some Validators
		$validatorPositive = new Zend_Validate_GreaterThan(0);
		$validatorLessthan = new Zend_Validate_LessThan(101);


        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Quiz Name')
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addErrorMessage("Please Enter a Valid Quiz Name")
			->addValidator('NotEmpty');



		$permissions = new Zend_Form_Element_Text('permissions');
        $permissions->setLabel('Permissions Group')
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('NotEmpty');
		
		
		$open_date = new Zend_Form_Element_Text('opendate');
        $open_date->setLabel('Open Date (YYYY-MM-DD)')
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('date');
	
		$close_date = new Zend_Form_Element_Text('closedate');
        $close_date->setLabel('Close Date (YYYY-MM-DD)')
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('date');
		

		$attempts = new Zend_Form_Element_Text('attempts');
        $attempts->setLabel('Maximum number of Attempts')
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('Int')
			->addValidator($validatorPositive);
			//->setErrors(array("Please Enter a Valid Number of Attempts (at least 1)"))


		$percentage = new Zend_Form_Element_Text('percentage');
        $percentage->setLabel('Pass Percentage (eg. 80)')
			->setRequired(true)
			->addFilter('StripTags')
			->addFilter('StringTrim')
			->addValidator('Int')
			->addValidator($validatorPositive)
			->addValidator($validatorLessthan);
			//->setErrors(array("Please input a number between 1 and 100"))


        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');


		$this->addElements(array( $name, $permissions, $open_date, $close_date, $attempts, $percentage, $submit));


    }
}