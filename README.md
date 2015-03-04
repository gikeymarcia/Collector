# Collector
A program for running experiments on the web
Copyright 2012-2015 Mikey Garcia & Nate Kornell

## FAQ
#### How do I get started?
The best way to get acquainted with Collector is to follow along on the [`StartingUp.html`](http://cogfog.com/Collector/Documentation/StartingUp.html) page.
#### Can I use Collector if I am not a programmer?
YES!  This program was designed to allow researchers to work with a format they're very familiar with (spreadsheets) to create interactive experiments.  As you will see in the tutorial videos, Collector is almost a completely programming free solution.  Of course there are times when being able to code will make your life easier but we have tried to minimize user coding wherever possible.
#### What language is Collector written in?
Collector is written mostly in PHP although some of the functionality is achieved through javascript/Jquery.  Formatting of the presentations is controlled mostly by HTML5 and CSS2.
The breakdown is roughtly ~75% PHP, 10% HTML, 10% CSS, 5% javascript/JQuery
#### Do I need to ask anyone if I want to use Collector?
No.  Collector is distributed under the GNU GPLv3 license (full text can be found in the `/Documentation/` folder).  You are free to use, modify, and distribute this program as you wish.  If you distribute a modified version of this program you are required to make your modified version available to the public under the same GNU GPLv3 license of the original program.
If you are publishing papers that have used Collector experiments we ask that you acknowledge its use somewhere.  This isn't a requirement of using this software but we'd like to get the word out to as many people as possible about it's availability.  Hopefully soon there will be a method's journal writeup that you can cite when using Collector.  If you build trial types that you think others would find useful we encourage you fork the repository, add your new trial type into the `/TrialTypes/` folder, then send us a pull request.
#### How does block shuffling work?
Shuffling is explained in detail during the Hangout 1 video on the `StartingUp` page.


## Included Trial Types
#### Study
Shows a word pair in the format of `Cue:Answer`
#### StudyPic
Shows a picture with it's label below.
In the `Cue` column you should include the filename and path from within the `/Experiment/` folder (e.g., `images/filename.jpg`).  The program will automatically add HTML tags for png, jpg, and gif images.
Whatever you put in the `Answer` column will show up below the image
#### Passage
Designed to display a long text passage.  Uses the `Cue` field as the passage and accepts html markup (e.g., `<p>paragraph</p>`, `<b>bold</b>`, `<br />`, etc.)
#### Test
Cued recall of a word pair in the form of `Cue:_____`
#### TestPic
Show an image with a textbox below where users can type a response.  The image comes from the `Cue` column (e.g., `images/bringing it all back home.jpg`).  Responses are recorded and automatically scored for % match with the text in the `Answer` column.
#### MCpic
INCOMPLETE INFO
#### Copy
INCOMPLETE INFO
#### FreeRecall
INCOMPLETE INFO
#### JOL
INCOMPLETE INFO
#### StepOut
INCOMPLETE INFO
#### Instruct
Allows you to give participants instructions during the experiment.
Instruct trials are special because they do not have to correspond to a line in the stimuli file.
To call an instruct trial you insert `Item` (0), `Trial Type` (Instruct), and `Procedure Notes` (instructions and/or HTML code).
If you are using instructions that are in your stimuli file you insert the stimuli row as `Item` and in the stimuli file you place your instructions in the `Cue` column.
See the Order file used in Condition 1 for an example.
#### Audio
Allows you to use audio clips as stimuli.  When you use this trial type you need to put the path from the `/Experiment/` folder to your audio file in the `Cue` column (e.g., if your audio file is in a folder called sounds you would use  `sounds/random.mp3` ).  Audio uses the HTML5 <audio> tag so it will only work on a modern web browser.  To be safe I would recommend using Chrome or Firefox v10+



## Troubleshooting
#### My changes aren't being reflected in the experiment.  Why?
1.  Save all your files before running the experiment.  90% of the time when you are having this problem it is because you forgot to save the changes you've made.  Seems silly but I run into this all the time.
2.  Clear your cache.  There are extensions for chrome (http://goo.gl/r961j) and firefox (http://goo.gl/r3Zky) that make this process very quick
3.  Are you sure you're editing the right experiment/folder?  The best way to test this is to rename index.php to missing.php then try to login to your experiment.  If you're editing the right folder then renaming index.php would break the login process.  If you can still login then you're editing the wrong folder / launching the wrong experiment.  Make sure to change missing.php back to index.php after you've found the correct folder.
		
		
## Collaborators/Thanks
#### Tyson Kerr
Many of the best ideas/solutions in Collector have come from the mind of Tyson.  Tyson's contributions are so wide that it is hard to think of a piece of the code he hasn't been involved with at this point.  Despite his broad contributions, I think Tyson would agree with me that his real baby is the getdata functionality in `/Data/`.  Every time you use those slick menus to check participant completion, exclude flagged users, or download all your precious data into one clean sheet you have Tyson to thank.
#### Adam Blake
Completely reorganized the collector.js code to be object oriented.  Is responsible for the current look of Collector because he redid nearly all of the CSS to make it much prettier than I initially could.
#### Victor Sungkhasettee
Figured out how to implement the Audio trial type
#### Nate Kornell
Without Nate there would be no Collector.  Many years ago Nate taught me how to use the tool he had created for himself, Generic, and that code inspired me to write Collector.  Most of the core ideas and design decision at the heart of this project are either directly lifted from Nate's program or were based on adaptations of what he had created.