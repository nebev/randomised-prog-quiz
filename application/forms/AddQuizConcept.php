<?php
/**
 *  Randomised Programming Quiz System - A quiz system that develops random programming questions from defined templates
 *  Copyright (C) 2010-2013 Ben Evans <ben@nebev.net>
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
class Form_AddQuizConcept extends Zend_Form {

	
    public function init(){
        $this->setName('addquizconcept');

		$form_elements = array();
        
		//Setup some Validators
		$validatorPositive = new Zend_Validate_GreaterThan(0);
		$validatorLessthan = new Zend_Validate_LessThan(101);

		// Concepts
		$all_concepts = Model_Quiz_Concept::getAll();
		$all_concepts_array = array();	// Form likes key=>val
		foreach($all_concepts as $concept) {
			$all_concepts_array[ $concept->getID() ] = $concept->getName();
		}
		
		// Form Elements		
		$this->addElement('text', 'number_of_questions', array(
				'label'      => 'Number of Questions',
				'required'   => true,
				'filters'    => array('StringTrim'),
				'validators' => array( $validatorPositive ),
				'placeholder' => "eg. 5"
		));

		$this->addElement('select', 'concept_id', array(
				'label'      => 'Concept',
				'multiOptions' => $all_concepts_array,
				'required'   => true
		));
		
		$this->addElement('text', 'difficulty_from', array(
				'label'      => 'Difficulty (From)',
				'required'   => true,
				'filters'    => array('StringTrim'),
				'validators' => array( $validatorPositive ),
				'placeholder' => "eg. 1"
		));
		
		$this->addElement('text', 'difficulty_to', array(
				'label'      => 'Difficulty (To)',
				'required'   => true,
				'filters'    => array('StringTrim'),
				'validators' => array( $validatorPositive ),
				'placeholder' => "eg. 3"
		));

		$this->addElement('submit', 'submit', array(
				'label'      => 'Add Tested Concept',
		));
    }
    
    /**
     * Populates the form from a TestedConcept Passed
     * @param Model_Quiz_TestedConcept $concept
     */
    public function populateFromConcept( Model_Quiz_TestedConcept $concept ) {
    	$info = array(
    		"number_of_questions" => $concept->getNumber_tested(),
    		"concept_id" => $concept->getConcept()->getID(),
    		"difficulty_from" => $concept->getLower_difficulty(),
    		"difficulty_to" => $concept->getHigher_difficulty()
    	);
    	$this->populate($info);
    }
    
    
}