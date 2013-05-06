Collector
A program for running experiments on the web
Copyright 2012-2013 Mikey Garcia & Nate Kornell

FAQ
What is Collector?
	Collector is a program designed to run psychology experiments on the web.
How do I run this program?
	To run locally (on your machine) you should install WAMP/MAMP/LAMP if you're using Windows/Mac/Linux and then copy the collector files into a folder in your localhost directory.  From there use a modern web browser to navigate to the experiment folder (e.g., on windows this would be 'localhost/myExperiment/').  Remember, in PHP capitalization matters so if you name you folder 'eXp' then 'localhost/EXP/' won't work.
What language is Collector written in?
	Collector is written mostly in PHP although some of the functionality is achieved through javascript/Jquery.  Formatting of the presentations is controlled mostly by HTML4 and CSS2
	The breakdown is roughtly ~70% PHP, 15% HTML, 10% CSS, 5% javascript/JQuery
Do I need to ask anyone if I want to use Collector?
	No.  Collector is distributed under the GNU GPLv3 license (full text can be found in the documentation folder).  You are free to use, modify, and distribute this program as you wish.  If distribute a modified version of this program you are required to make your modified version available to the public under the same GNU GPLv3 license of the original program.
How does block shuffling work?
	Only consecutive items with the same shuffle code are shuffled. Basically the program goes line by line and looks to see if the shuffle column changes.  If there is no change then it adds the line to the current holding block.  When the shuffle column value changes it shuffles the current holding block and adds it back into the output.  If there is only one item in the holding block shuffle does nothing.
	Shuffling of the stimuili and order/info files is done randomly for each new participant.


Automatic Condition Selection
	Leaving the condiiton selector on "Auto" will let the program cycle through the "Conditions.txt" file to determine each subject's condition assignment.  Cycling is based on the 'Number' column of the conditions.txt file.  For this reason it is important that you make sure to number sequentially starting at 1.  If two different conditions (lines) have the same number or if a number is skipped then the experiment will not work as designed.
	If an experiment has 4 conditions and 7 people log in then they would be assigned to conditions in the following order: 1, 2, 3, 4, 1, 2, 3

Trial Types
	Study
			shows a word pair in the format of "cue:target"
	StudyPic
			shows a picture with it's label below.
			In the cue column you should include the path to and filename of the image (e.g., images/filename.jpg).  The program will automatically add HTML tags for png, jpg, and gif images
			Whatever you put in the target column will show up below the image
	Passage
			Designed to display a long text passage.  Uses the cue field as the passage and accepts html markup (e.g., <p>paragraph</p>, <b>bold</b>, <br />, etc.)
	Test
			Tests a word pair in the format of "cue:_____"
	TestPic
			Show an image with a textbox below where users can subimt a response.  The image comes from the Cue column (e.g., 'images/bringing it all back home.jpg').  Responses are recorded and automatically scored for % match with the text in the Answer column.
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
			Allows you to give participants instructions during the experiment.
			Instruct trials are special because they do not correspond to a line in the stimuli file.
			To call an instruct trial you insert item (0), trial type(Instruct), and Order Notes (instructions and/or HTML code).
			See the Order file used in Condition1 for an example.
	Audio
			Allows you to use audio clips as stimuli.  When you use this trial type you need to put the path to your audio file in the 'Cue' column (e.g., if your audio file is in a folder called sounds you would use  'sounds/random.mp3' ).  Audio uses the HTML5 <audio> tag so it will only work on a modern web browser.  To be safe I would recommend using Chrome or Firefox v10+


Post Trial Types
	JOL
			Ask the question "How likely are you to correctly remember this item on a later test? -- Type your response on a scale from 0-100 using the entire range of the scale" and gives participants a textbox where they can type a response
	Feedback
			If the trial was StudyPic, TestPic, or MCpic the Cue image and Target label will be shown
			If the trial was anything else then Feedback will be shown as "The corrct answer is:   Cue:Target"
	No
			If the post trial is not feedback or JOL then the experiment will skip post trial and proceed to the next trial in the experiment.


Timing Options
	Computer
			Computer timing means that participants will see the particular trial for the default amount of time.  Participants are not able to force the trial to end any faster than designated.  The default timing for each trial can be changed in login.php in the '##### Parameters #####' section of the code.
	User
			User timing means that participants can proceed to the next trial at their own pace.  There will be a button on each User timed trial that will either say 'done','next', or 'submit' depending on the trial type.
	#
			Aside from the default time it is also possible to set a specific time for any given trial.  To do this you go to that trial in the order file and designate the number of seconds in the 'Timing' column (e.g., '8' would make that given trial an 8 second computer timed trial)


Troubleshooting
	My changes aren't being reflected in the experiment.  Why?
		1.  Save all your files before running the experiment.  90% of the time when you are having this problem it is because you forgot to save the changes you've made.  Seems silly but I run into this all the time.
		2.  Clear your cache.  There are extensions for chrome (http://goo.gl/r961j) and firefox (http://goo.gl/r3Zky) that make this process very quick
		3.  Are you sure you're editing the right experiment/folder?  The best way to test this is to rename index.php to missing.php then try to login to your experiment.  If you're editing the right folder then renaming index.php would break the login process.  If you can still login then you're editing the wrong folder / launching the wrong experiment.  Make sure to change missing.php back to index.php after you've found the correct folder.
		
		
Collaborators/Thanks
	Victor Sungkhasettee - figured out how to implement the Audio trial type