Automatic Condition Selection
	Leaving the condiiton selector on "Auto" will let the program cycle through the "Conditions.txt" file to determine each subject's condition assignment

Trial Types
	Study
			shows a word pair in the format of "cue:target"
	StudyPic
			shows a picture with it's label below.
			In the cue column you should include the path to and filename of the image (e.g., images/filename.jpg).  The program will automatically add HTML tags for png, jpg, and gif images
			In label of the picture is whatever you put in the target column
	Passage
			Designed to display a long text passage.  Uses the cue field as the passage and accepts html in the cue (e.g., <p>paragraph</p>, <b>bold</b>, <br />, etc.)  
	Test
			Tests a word pair in the format of "cue:_____"
	TestPic
			INCOMPLETE INFO
	MCpic
			INCOMPLETE INFO
	Copy
			INCOMPLETE INFO
	FreeRecall
			INCOMPLETE INFO
	JOL
			INCOMPLETE INFO
	StepOut
			INCOMPLETE INFO
	Instruct
			Uses study timing; INCOMPLETE INFO

How does block shuffling work?
	Only consecutive items with the same shuffle code are shuffled. Basically the program goes line by line and looks to see if the shuffle column changes.  If there is no change then it adds the line to the current holding block.  When the shuffle column value changes it shuffles the current holding block and adds it back into the output.  If there is only one item in the holding block shuffle does nothing.