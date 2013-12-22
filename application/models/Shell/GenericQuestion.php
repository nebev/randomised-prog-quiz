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


	class Model_Shell_GenericQuestion{
		
		private $mFileName;
		private $mFileContents;
		private $mSubstitutions;
		private $mSubstitutions_tmp; 	//Purely as an extra variable in generating fake multiple choice answers
		private $mProblem;
		private $mProblem_tmp; 			//Purely as an extra variable in generating fake multiple choice answers
		private $mProblemFill; 			//For the Fill-in questions only
		private $mAltAnswers;
		private $mActualAnswer;
		
		/**
		 * Create a new Question from a passed XML File
		 * @param string $vFileName
		 * @throws Exception
		 */
		public function __construct($vFileName){
			Model_Shell_Debug::getInstance()->log("Attempting to create generic question from XML File $vFileName");
			$file_contents = Model_XML_Parser::xml2array($vFileName);
			if( !is_array($file_contents) || sizeof($file_contents) == 0 ) {
				throw new Exception("Could not parse XML File " . $vFileName);
			}
			$this->mFileContents = $file_contents;
			$this->mFileName = $vFileName;
			$this->mSubstitutions = array();
			$this->mAltAnswers = array();
			//print_r($this->mFileContents);
		}
		
		public function getConcepts(){
			$vReturn = array();
			foreach($this->mFileContents['question']['concepts'] as $i){
				//Check to see if $i is an array first
				if(is_array($i)){
					foreach($i as $j){
						$vReturn[] = $j;
					}
				}else
					$vReturn[] = $i;
			}
			return $vReturn;
		}
		
		public function getFriendlyType(){
			return $this->mFileContents['question_attr']['type'];
		}
		
		public function getEstimatedTime(){
			return $this->mFileContents['question']['estimated_time'];
		}
		
		
		public function getDifficulty(){
			return $this->mFileContents['question']['difficulty'];
		}
		
		/**
		 * Gets the instructions for this question.
		 * Substitutions will be loaded at this point
		 * @return mixed
		 */
		public function getInstructions(){
			$vInstr = $this->mFileContents['question']['instructions'];
			
			//Make sure all the substitutions have been generated
			if(!$this->substitutionsPopulated()){
				$this->getProblem();
				$this->getCorrectOutput();
			}
			
			$vInstr = $this->substitutePercentages($vInstr);
			
			return str_replace("\n","",str_replace("\t","",$vInstr));
		}
		
		private function substitutionsPopulated(){
			if(sizeof($this->mSubstitutions)>0){ return true; }
			return false;
		}
		
		/**
		 * Generates the Problem
		 * @return string
		 */
		public function getProblem(){
			/*
				Return the generated problem to be displayed
			*/
			
			
			//Don't go substituting and generating if we've already done it
			if(sizeof($this->mProblemFill)>1)
				return $this->mProblemFill;
			elseif(sizeof($this->mProblem)>1)
				return $this->mProblem;
			
			
			//Make sure all the substitutions have been generated
			if(!$this->substitutionsPopulated()){
				$this->populateSubstitutions();
			}
			
			//Get the problem from the XML data
			$this->mProblem = $this->mFileContents['question']['problem'];
			
			
			//Replace what needs to be replaced
			foreach($this->mSubstitutions as $mSubKey => $mSub){
				$this->mProblem = str_replace("`".$mSubKey."`",$mSub,$this->mProblem);
			}
			
			//Model_Shell_Debug::getInstance()->log("The Base Question problem (with substitutions) is: " . $this->mProblem);
			
			//Now, if it's a fill-in question, we need to take care of that. mProblemFill should be
			//	populated with the inputboxes, while mProblem should be populated with OUR solution
			if($this->getFriendlyType()=="fill-in"){
				$this->populateFillIns();
				return $this->mProblemFill;
			}
			
			
			return $this->mProblem;
		}//End getProblem
		
		
		
		private function populateSubstitutions(){
			/*
				This essentially goes through and evaluates the data
				in the <substitutions> part of the XML file. It consists of
				2 parts:
					- Firstly changing %value%'s that are present in <substitution> keys to previously computed values
					- Evaluating the PHP code inside the <substitution> keys and saving the results to $mSubstitutions
			*/
			
			Model_Shell_Debug::getInstance()->log("Populating ". sizeof($this->mFileContents['question']['substitutions']['substitution']) ."/2 Substitutions");
			
			//There's essentially 2x the amount of array keys in this array because the XML parser puts both the VALUE and ATTRIBUTE in
			for($vCounter = 0; $vCounter<(sizeof($this->mFileContents['question']['substitutions']['substitution'])/2); $vCounter++ ){

				
				//Firstly we look at the XML value
				$toGen = $this->mFileContents['question']['substitutions']['substitution'][$vCounter];
				if(strstr($toGen,";")==false){
					//Assuming its just a function without ; and return
					$toGen = "return " . $toGen . ";";
				}
				
				$toGen = $this->substitutePercentages($toGen);
						
				$this->mSubstitutions[$this->mFileContents['question']['substitutions']['substitution'][$vCounter."_attr"]['val']] = eval($toGen);

			}//End FOR
			
			//print_r($this->mSubstitutions);
			
		}//End populateSubstitutions
		
		
		
		
		private function substitutePercentages($toGen){
			//TODO: We need to make clear (or fix) that the equations generated MUST BE IN ORDER

			//Now we Substitute any needed things eg: replace %s1% with the value of s1 (previously stored in the array)
			preg_match_all("/\%\w+\%/", $toGen, $matches);
			$matches = $matches[0]; //Why does this need to be done?
			
			if(sizeof($matches)>0){
				//Iterate through and substitute all matches
				foreach($matches as $match){
					$vSearchText = str_replace("%","",$match);
					if(array_key_exists($vSearchText,$this->mSubstitutions)){
						//Go ahead and replace it
						$toGen = str_replace($match,$this->mSubstitutions[$vSearchText],$toGen);
					}
				}
			}
			
			return $toGen;
		}
		
		
		
		/**
		 * Compiles the question and returns the correct output
		 * @return string
		 */
		public function getCorrectOutput(){
			if(isset($this->mActualAnswer)){
				return $this->mActualAnswer;
			}

			Model_Shell_Debug::getInstance()->log("Attempting to generate correct answer for Question.");

			$this->mActualAnswer = Model_Shell_Compiler::compileAndReturn(time() . rand(1,99999),$this->mProblem);
			$this->mSubstitutions['ans'] = $this->mActualAnswer;
			return $this->mActualAnswer;
		}
		
		
		
		/**
		 * Gets Multiple choice answers.
		 * If the question is not multiple choice, NULL is returned
		 * @return NULL|multitype:string|multitype:multitype:string Results will be an array. Each array element may either be a string, or another array ([0] => Answer, [1] => Why the answer is wrong)
		 */
		public function getAnswers(){
			if($this->getFriendlyType()!="multiple"){
				return null;
			}
			
			//So we have a multiple choice quiz
			//Firstly make sure that we HAVE generated the CORRECT output and stored it somewhere
			//	otherwise this could get messy
			
			$vTemp = $this->getCorrectOutput();
			
			
			//Have we already generated answers (in case someone wants to call this more than once)
			if(sizeof($this->mAltAnswers)>0){
				return $this->mAltAnswers;
			}
			
			//Answers haven't been generated yet. Lets DO IT!
			$vAnswersXML = $this->mFileContents['question']['answers']['answer'];
			foreach($vAnswersXML as $i){
				
				//Make sure we return the answer description if available
				if(array_key_exists('description',$i)){
					$this->mAltAnswers[] = array($this->generateAnswer($i), $i['description']);
				}else{
					$this->mAltAnswers[] = $this->generateAnswer($i);
				}
			}
			return $this->mAltAnswers;
		}
		
		
		/*
			Generate an individual alternate answer.
		*/
		private function generateAnswer($vArray){
			
			if($vArray['substitute_attr']['val']=="ans"){
				//We're substituting ONLY the answer. There is no need to recompile the program
				$vToReturn = $this->substitutePercentages($vArray['substitute']);
				if(strstr($vToReturn,";")==false){$vToReturn = "return " . $vToReturn . ";";}
				return eval($vToReturn);
			}
			
			//Ok. Looks like we're going to have to recompile the program with some new values. This could get interesting.
			
			//Due to the way this shitty XML parser works, we have to do some basic checks. If there's more than one "substitute" key in the answer
			//	the parser puts it into an array, otherwise it doesn't
			
			$vToProcess = array();
			if(is_array($vArray['substitute'])){
				for($i=0;$i<(sizeof($vArray['substitute'])/2);$i++){
					$vToProcess[] = array("attr" => $vArray['substitute'][$i."_attr"]['val'], "code" => $vArray['substitute'][$i]);
				}
			}else{
				$vToProcess[] = array("attr" => $vArray['substitute_attr']['val'], "code" => $vArray['substitute']);
			}
			
			
			//OK. So now we do a whole NEW set of substitutions for this fake answer.
			//We'll start by copying over the REAL substitutions and replacing them as we go with fake ones
			//From here on in, this function will look a lot like populateSubstitutions
			$this->mSubstitutions_tmp = $this->mSubstitutions;
			foreach($vToProcess as $vTP){
				
				$toGen = $vTP['code'];
				if(strstr($toGen,";")==false){
					//Assuming its just a function without ; and return
					$toGen = "return " . $toGen . ";";
				}
				
				$toGen = $this->substitutePercentages($toGen);
				//echo "TOGEN: " . $toGen;
				$this->mSubstitutions_tmp[$vTP['attr']] = eval($toGen);
				
			}
			
			//Ok. All the new 'fake' substitutions are done. Time to generate the entire new 'fake' problem!
			$this->mProblem_tmp = $this->mFileContents['question']['problem'];
			
			//Replace what needs to be replaced
			foreach($this->mSubstitutions_tmp as $mSubKey => $mSub){
				$this->mProblem_tmp = str_replace("`".$mSubKey."`",$mSub,$this->mProblem_tmp);
			}
			
			//Now compile this fake problem into a fake solution and return the output
			return Model_Shell_Compiler::compileAndReturn(rand(1,99999)."_fake",$this->mProblem_tmp);
			
		}
		
		
	/*
		Populate the fill-in's for fill-in questions
		Essentially makes mQuestion the full question with OUR output, and makes
		mQuestionFill the question (including HTML form elements) that the student
		will see
	*/
	private function populateFillIns(){
		$vFills = array();
		
		if(is_array($this->mFileContents['question']['inputboxes']['inputbox'])){
			//Multiple Fill-ins. Do some messy workarounds because this XML parser is crap
			$vCounter = 0;
			foreach($this->mFileContents['question']['inputboxes']['inputbox'] as $key=>$i){
				if($key==$vCounter){
					$vFills[] = array('val'=>$this->mFileContents['question']['inputboxes']['inputbox'][$vCounter.'_attr']['val'] ,'lines'=>$this->mFileContents['question']['inputboxes']['inputbox'][$vCounter.'_attr']['lines'] , 'solution'=>$i);
				}
				$vCounter++;
			}
			
		}else{
			$vFills[] = array('val' => $this->mFileContents['question']['inputboxes']['inputbox_attr']['val'], 'lines' => $this->mFileContents['question']['inputboxes']['inputbox_attr']['lines'], 'solution' => $this->mFileContents['question']['inputboxes']['inputbox']);
		}
		
		//Now we've got all the fills, we need to change the %'s in the problem to the substituted values
		foreach($vFills as &$vFill){
			$vFill['solution'] = $this->substitutePercentages($vFill['solution']);
			//Now find that in the problem
			$this->mProblemFill = $this->mProblem;
			$this->mProblemFill = str_replace("`".$vFill['val']."`", "`textarea name='sub_".$vFill['val']."' style='margin-left:45px;' cols='56' rows='".$vFill['lines']."'``/textarea`", $this->mProblemFill);
			$this->mProblem = str_replace("`".$vFill['val']."`", $vFill['solution'] , $this->mProblem);
			
		}
	}
	
	public function getDebugProblem(){
		return $this->mProblem;
	}
		
		
		
		
				
		
	}//End Class

?>