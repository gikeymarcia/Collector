<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';
	
    $title = 'Basic Information';
    require $_codeF . 'Header.php';
?>

<section class="basicinfo content clearfix">
<form class="collector-form" name="Demographics" action="BasicInfoData.php" method="post" autocomplete="off">
  <fieldset>
    <legend>Basic Information</legend>
    <div class="collector-form-element">
      <label>Gender</label>
      <div class="radio">
        <input id="male" name="Gender" type="radio" value="Male" required>
        <label for="male" class="clearfix">Male</label>
          
        <input id="female" name="Gender" type="radio" value="Female" required>
        <label for="female" class="clearfix">Female</label>
          
        <input id="other" name="Gender" type="radio" value="Other" required>
        <label for="other">Other</label>  
      </div>
    </div>
      
    <div class="collector-form-element">
      <label for="age">Age</label>
      <input id="age" name="Age" type="text" pattern="[0-9][0-9]" value="" autocomplete="off" required>
    </div>
          
    <div class="collector-form-element">
      <label for="education">Highest level of education</label>
      <select id="education" name="Education" required>
        <option value="" default selected>Select Level</option>
        <option>Some High School
        <option>High School Graduate
        <option>Some college, no degree
        <option>Associates degree
        <option>Bachelors degree
        <option>Graduate degree (Masters, Doctorate, etc.)
      </select>
    </div>  
    
    <div class="collector-form-element">
      <label>Are you Hispanic?</label>
      <div class="radio">
        <input id="hispanic_yes" name="Hispanic" type="radio" value="Yes" required>
        <label for="hispanic_yes" class="clearfix">Yes</label>
        
        <input id="hispanic_no" name="Hispanic" type="radio" value="No" required>
        <label for="hispanic_no">No</label>
      </div>
    </div>
    
    <div class="collector-form-element">
      <label for="Race">Ethnicity</label>
      <select name="Race" required>
        <option value="" default selected>Select one</option>
        <option>American Indian/Alaskan Native
        <option>Asian/Pacific Islander
        <option>Black
        <option>White
        <option>Other/unknown
      </select>
    </div>  
    
    <div class="collector-form-element">
      <label>Do you speak English fluently?</label>
      <div class="radio">
        <input id="fluent_yes" name="Fluent" type="radio" value="Yes" required>
        <label for="fluent_yes" class="clearfix">Yes</label>
        
        <input id="fluent_no" name="Fluent" type="radio" value="No" required>
        <label for="fluent_no">No</label>
      </div>  
    </div>
    
    <div class="collector-form-element">
      <label for="ageeng">At what age did you start learning English?</label>
      <input id="ageeng" name="AgeEnglish" type="text" pattern="[0-9][0-9]?" value="" autocomplete="off" required>
      <p class="labelsub">If English is your first language please enter 0.
    </div>    
    
    <div class="collector-form-element">
      <label for="country">Country</label>
      <input id="country" name="Country" type="text" autocomplete="off" required>  
    </div>

    <div class="collector-form-element">
      <label for="State">If you are living in the US, which state?</label>
      <select name="State">
        <option value="" default selected>Select State
        <option>Alabama
        <option>Alaska
        <option>Arizona
        <option>Arkansas
        <option>California
        <option>Colorado
        <option>Connecticut
        <option>Delaware
        <option>Florida
        <option>Georgia
        <option>Hawaii
        <option>Idaho
        <option>Illinois
        <option>Indiana
        <option>Iowa
        <option>Kansas
        <option>Kentucky
        <option>Louisiana
        <option>Maine
        <option>Maryland
        <option>Massachusetts
        <option>Michigan
        <option>Minnesota
        <option>Mississippi
        <option>Missouri
        <option>Montana
        <option>Nebraska
        <option>Nevada
        <option>New Hampshire
        <option>New Jersey
        <option>New Mexico
        <option>New York
        <option>North Carolina
        <option>North Dakota
        <option>Ohio
        <option>Oklahoma
        <option>Oregon
        <option>Pennsylvania
        <option>Rhode Island
        <option>South Carolina
        <option>South Dakota
        <option>Tennessee
        <option>Texas
        <option>Utah
        <option>Vermont
        <option>Virginia
        <option>Washington
        <option>West Virginia
        <option>Wisconsin
        <option>Wyoming
      </select>
    </div>
  </fieldset>
  
  <!-- ## SET ## Use this area to provide the equivalent of an informed consent form -->
  <fieldset>
    <legend>Informed Consent</legend>
    <div class="collector-form-element consent">
      <h1>Learning Words and Remembering Facts</h1>
      <h2>Section 1</h2>
      <p>This is the informed consent form. You can put whatever you want here.
      <h2>Section 2</h2>
      <h3>Part A</h3>
      <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.
      <h3>Part B</h3>
      <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.
      <h2>Section 3</h2>
      <p>Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur? Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?
      <p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere
    </div>
  </fieldset>
  
  <div class="collector-form-element consent-check">
    <input id="consent" name="Consent" type="checkbox">
    <label for="consent">Check this box if you have read, understand, and agree to the Informed Consent above.</label>
  </div>
  
  <div class="collector-form-element textcenter">
    <input class="collector-button" type="submit" value="Submit Basic Info">
  </div>
</form>    
</section>

<?php
    require $_codeF . 'Footer.php';