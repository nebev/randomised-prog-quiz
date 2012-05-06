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

//This file effectively provides a function which will return a QuestionBase with a particlar difficulty
//	based on a student and a quizAttempt that is passed to it. It takes into account all the questions the student
//	has PREVIOUSLY done for this quizAttempt - including whether or not the answer was right, and the time it took.
//It ATTEMPTS to give the student a question that is relevant to THEM, and within quiz guidelines

/*
	============
	How it works
	============
	For each concept tested in a quiz, a number of questions and difficulty levels are allocated to it.
		eg. Test 20 questions on "Algebraic Equations" with difficulties ranging from 1-3
		
	This function will automatically put a 20% BUFFER in the question bank, and equally allocate
	the other difficulties accordingly. In this case, the entire 10 questions would look something like this:

	 =================================================================
	|     26.67%       |    26.67%      |     26.67%      |    20%    |
	|   Difficulty 1   | Difficulty 2   |   Difficulty 3  |   Buffer  |
	 =================================================================

	The idea now is that the student will have to do at LEAST 26.67% of questions in each difficulty level.
	However, depending on the correctness of their previous answers, and their time take on questions, they 
	will have extra questions in a particular category.
	
	Eg. If a student is taking a long time to answer questions in "Difficulty 1", more questions from the "buffer"
	will be allocated to "Difficulty 1".
	
	Eg. A student starts out well, and finishes all the "Difficulty 1" questions correctly and within reasonable
	time. They struggle with some "Difficulty 2" questions. Buffer Questions will be allocated to "Difficulty 2"
	questions.
	
	Eg. A very smart student completes all questions in all 3 difficulties correctly and within reasonable
	time. Their "buffer" questions are allocated to the highest difficulty - in this case, "Difficulty 3".


*/


/*TEST
	Small nums of Q's
	Q's that say difficulties that don't exist (both too far down & up)
	
*/

class Model_Shell_QuestionChooser{

	public static function select_next_question($vQuizAttempt, $debug = false){

		//Get all the concepts this quiz tests
		$vQuiz = $vQuizAttempt->getQuiz();
		$vTestedConcepts = $vQuiz->getTestedConcepts();
	
		if(sizeof($vTestedConcepts)<1){
			throw new Exception("This quiz is not ready yet - reason: No testedConcepts specified.");
		}
	
	
		//Foreach concept tested, see how many questions we've attempted
		foreach($vTestedConcepts as $vTestedConcept){
			
			if( $debug ) {
				echo "Looking at TestedConcept: " . $vTestedConcept->getConcept()->getConcept_name() . ".\n";
			}
			
	
			$vQuestionAttempts = Model_Quiz_QuestionAttempt::getAllFromQuizAttemptAndConcept($vQuizAttempt, $vTestedConcept->getConcept());

			if( $debug ) {
				echo "Done " . sizeof($vQuestionAttempts) ."/". $vTestedConcept->getNumber_tested().".\n";
			}
			
		
			//If we haven't attempted enough in this concept, we need to choose a question difficulty
			if(sizeof($vQuestionAttempts) < $vTestedConcept->getNumber_tested()){
				
				//What difficulties are there being tested within this concept?
				$vLowest = $vTestedConcept->getLower_difficulty();
				$vHighest = $vTestedConcept->getHigher_difficulty();
			
				//Find out how many questions I should allocate for each difficulty
				$mQuestionsPerDifficultyLevel = floor((0.80/(($vHighest+1)-$vLowest)) * $vTestedConcept->getNumber_tested());
				$mBuffer = $vTestedConcept->getNumber_tested() - ((($vHighest+1)-$vLowest) * $mQuestionsPerDifficultyLevel);
			
			
				//If there's only one difficulty, that settles that
				if($vLowest == $vHighest){
					return Model_Shell_QuestionChooser::select_next_question_2($vQuizAttempt,$vTestedConcept->getConcept(),$vLowest);
				}
			
			
				//Were there any previous attempts at this concept? If not, we need to start a Q at the lowest specified difficulty
				$vQuestionAttempts = $vQuizAttempt->getQuestionAttempts($vTestedConcept->getConcept());
				
				//echo "Size of vQuestionAttempts:".sizeof($vQuestionAttempts)."<br/>";
				if(sizeof($vQuestionAttempts)==0){
					return Model_Shell_QuestionChooser::select_next_question_2($vQuizAttempt,$vTestedConcept->getConcept(),$vLowest);
				}
			

				//Find the highest attempted difficulty done so far
				$vHighestSoFar = $vQuizAttempt->getHighestDifficultyTestedSoFar();
				
				//echo "The highest difficulty so far: $vHighestSoFar<br/>";
			
				if($vHighestSoFar>=$vHighest){
					//Already at the highest difficulty, the next question will be at this difficulty too
					return Model_Shell_QuestionChooser::select_next_question_2($vQuizAttempt,$vTestedConcept->getConcept(),$vHighest);
				}
			
			
				//Look at the last attempts in this difficulty. Were they below-par (use a percentage?)?
				$vTotal=0;
				$vTotalRight=0;
				
				if( $debug ) {
					echo "Checking your previous attempts...Size of vQuestionAttempts:".sizeof($vQuestionAttempts)."\n";
				}
				
				foreach($vQuestionAttempts as $vQA){
					$vQB = $vQA->getQuestion_base();
				
					//echo "Looking at " . $vQB->getDifficulty() . " vs $vHighestSoFar<br/>";
				
					if($vQB->getDifficulty()==$vHighestSoFar){
						$vTotal++;
						if($vQB->getEstimated_time() > ($vQA->getTime_finished() - $vQA->getTime_started()) ){
							//You did this question in a reasonable time... did you get it right first time?
							if($vQA->getInitial_result()=="1"){
								$vTotalRight++;
							}
						}
					}
				}
			
				//Note, a divide by 0 happened here before...
			
				if($vTotalRight/$vTotal > 0.8){
					//OK. If they WERE good attempts, I should move on if I've hit my quota for this difficulty
					if($vTotal>=$mQuestionsPerDifficultyLevel){
						//Reached our minumum quota for this difficulty level. NEXT!
						return Model_Shell_QuestionChooser::select_next_question_2($vQuizAttempt,$vTestedConcept->getConcept(),$vHighestSoFar+1);				
					}else{
						//We've done well so far, but we need to do more in this difficulty
						return Model_Shell_QuestionChooser::select_next_question_2($vQuizAttempt,$vTestedConcept->getConcept(),$vHighestSoFar);
					}
							
				}else{
					//Your previous attempts weren't all that good.
				
					//Figure out how much buffer we have left. Can we afford to give you a question at $vHighestSoFar? Or do we have to move onto something harder?
					$mBuffer = $vTestedConcept->getNumber_tested() - (($vHighest-$vHighestSoFar) * $mQuestionsPerDifficultyLevel) - sizeof($vQuestionAttempts);
					if($mBuffer>1){
						//We can afford to give you another easier question
						return Model_Shell_QuestionChooser::select_next_question_2($vQuizAttempt,$vTestedConcept->getConcept(),$vHighestSoFar);
					}else{
						//Sorry.. buffer is dry. You weren't going too well, but you gotta do the harder stuff
						return Model_Shell_QuestionChooser::select_next_question_2($vQuizAttempt,$vTestedConcept->getConcept(),$vHighestSoFar+1);
					}
	
				}
						
				
			}//End If
		}//End Foreach testedConcept
	
	
		//We shouldn't really ever get here, but in case we do -> null
		return null;
	
	}


/*
		This is the second phase of the question choosing.
		By this stage, we've figured out what concept we're testing,
		and what difficulty it should be.
	
		Now all we have to do is make sure that we get as little
		repetition as possible. If we CAN, we'll try and get a different
		questionBase - if we can't, then we'll pick one at random
		(with a slight bias towards multiple choice and fill-in answers)

*/
	public static function select_next_question_2($vQuizAttempt, $vConcept, $vDifficulty){
		//Firstly get ALL the questionBase's with this difficulty & concept
		$vPossibleQuestions = Model_Quiz_QuestionBase::getAllFromConceptAndDifficulty($vConcept, $vDifficulty);
		//Shuffle it to make it a little random
		shuffle($vPossibleQuestions);
	
		//print_r($vPossibleQuestions);
	
	
		//Now get all the questions the STUDENT has done...
		$vAttemptedQuestions = $vQuizAttempt->getAttemptedQuestionBases($vConcept, $vDifficulty);
	
		//print_r($vAttemptedQuestions);
	
		//There's the chance that we haven't had a questionbase asked.
		foreach($vPossibleQuestions as $vPQ){
			$vUsed = false;
			foreach($vAttemptedQuestions as $vAQ){
				if($vPQ->getID()==$vAQ->getID()){
					$vUsed = true;
					break;
				}
			}
		
			if(!$vUsed){
				return $vPQ;
			}
		}
	
		//OK. So it turns out that we've used all questions. Pick a random one
		$vTemp = rand(0,(sizeof($vPossibleQuestions)-1));
		return $vPossibleQuestions[$vTemp];
	
	}

}




?>