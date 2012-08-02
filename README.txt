Collector
A program for running experiments on the web
Copyright 2012 Mikey Garcia & Nate Kornell

What is Collector?
	Collector is a program designed to run psychology experiments on the web.
How do I run this program?
	To run locally (on your machine) you should install WAMP/MAMP/LAMP if you're using Windows/Mac/Linux and then copy the collector files into your localhost directory.  From there use a modern web browser to navigate to index.php (where the experiments begin)
What language is Collector written in?
	Collector is written mostly in PHP although some of the functionality is achieved through javascript/Jquery.  Formatting of the presentations is controlled mostly by HTML4 and CSS2
	The breakdown is roughtly ~70% PHP, 15% HTML, 10% CSS, 5% javascript/JQuery
Do I need to ask anyone if I want to use Collector?
	No.  Collector is distributed under the GNU GPLv3 license (full text can be found in the documentation folder).  You are free to use, modify, and distribute this program as you wish.  If distribute a modified version of this program you are required to make your modified version available to the public under the same GNU GPLv3 license of the original program.
How does block shuffling work?
	Only consecutive items with the same shuffle code are shuffled. Basically the program goes line by line and looks to see if the shuffle column changes.  If there is no change then it adds the line to the current holding block.  When the shuffle column value changes it shuffles the current holding block and adds it back into the output.  If there is only one item in the holding block shuffle does nothing.
	Shuffling of the stimuili and order/info files is done randomly for each new participant.

	
	
Automatic Condition Selection
	Leaving the condiiton selector on "Auto" will let the program cycle through the "Conditions.txt" file to determine each subject's condition assignment

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
	Audio
			Allows you to use audio clips as stimuli.  When you use this trial type you need to put HTML markup that points to the audio file in the 'Cue' column (e.g., '<source src="random.ogg">' ).  Audio uses the HTML5 <audio> tag so it will only work on a modern web browser.  To be safe I would recommend using Chrome or Firefox v10+

Post Trial Types
	JOL
			Ask the question "How likely are you to correctly remember this item on a later test? -- Type your response on a scale from 0-100 using the entire range of the scale" then gives participants a textbox where they can type whatever they want
	Feedback
			If the trial was StudyPic, TestPic, or MCpic the Cue image and Target label will be shown
			If the trial was anything else then Feedback will be shown as "The corrct answer is:   Cue:Target"\
	No
			If the post trial is not feedback or JOL then the experiment will skip post trial and proceed to the next trial in the experiment.
		

		
Collaborators/Thanks
	Victor Sungkhasettee - figured out how to implement the Audio trial type