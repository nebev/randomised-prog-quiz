If $CmdLine[0] = 0 Then
	ConsoleWrite("===Limit Executon Time of Windows Process. Designed for Windows Systems using Randomised Quiz System=="&@CRLF)
	ConsoleWrite("Usage: limitexec.exe timeInSeconds exe/bat name"&@CRLF&"     eg. limitexec.exe 5 16445"&@CRLF)
	Exit
EndIf

;Take the first parameter - make sure its a Number

if IsNumber(int($CmdLine[1])) Then
	$secondsRunning = int($CmdLine[1])
	logText("Running: " & @ScriptDir & "\" & $CmdLine[2]&".bat for " & $secondsRunning & " seconds")
	
	Run(@ScriptDir & "\" & $CmdLine[2]&".bat")
	
	sleep(1000)	
	logText("Waiting for " & $CmdLine[2]&".exe")
	ProcessWaitClose($CmdLine[2]&".exe", $secondsRunning)
	logText("Finished Waiting for " & $CmdLine[2]&".exe")
	logText("Checking to see if the process still exists")
	
	If ProcessExists($CmdLine[2]&".exe") Then
		logText("Process "&$CmdLine[2]&".exe still exists - killing")
		ProcessClose($CmdLine[2]&".exe")
	Else
		logText("Process "&$CmdLine[2]&".exe does not exist. No need to kill")
	EndIf
EndIf
logText("Exiting")


Func logText($vText)
	FileWriteLine(@ScriptDir&"\limitExec.log",@HOUR&":"&@MIN&":"&@SEC&" "&$vText)
EndFunc
