<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';
	
    $title = 'Basic Information';
    require $_codeF . 'Header.php';
?>
<div class="main-contain">
	<h2 class="textcenter">Basic Information</h2>

	<form name="Demographics" class="collector-form collector-form-extra" action="BasicInfoData.php" method="post" autocomplete="off">

		<div class="field">
			<legend>What is your gender?</legend>
			<input type="radio"   value="Male"     class="radio"    name="Gender"    />    Male     <br />
			<input type="radio"   value="Female"   class="radio"    name="Gender"    />    Female   <br />
		</div>

		<div class="field">
			<p>What is your age?</p>
			<input type="text" value="" name="Age" autocomplete="off" class="forceNumeric" />
		</div>

		<div class="field">
			<p>Which of the following best describes your highest achieved education level?</p>
			<select name="Education">
				<option selected="selected">Select Level</option>
				<option>    Some High School                            </option>
				<option>    High School Graduate                        </option>
				<option>    Some college, no degree                     </option>
				<option>    Associates degree                           </option>
				<option>    Bachelors degree                            </option>
				<option>    Graduate degree (Masters, Doctorate, etc.)  </option>
			</select>
		</div>

		<div class="field">
			<p>Are you Hispanic?</p>
			<input    type="radio"    name="Hispanic"    value="Yes" />    Yes    <br />
			<input    type="radio"    name="Hispanic"    value="No"  />    No     <br />
		</div>

		<div class="field">
			<p>Which of the following best describes your ethnicity?</p>
			<select name="Race">
				<option selected="selected">Select Level</option>
				<option>	American Indian/Alaskan Native		</option>
				<option>	Asian/Pacific Islander				</option>
				<option>	Black           					</option>
				<option>	White                       		</option>
				<option>	Other/unknwon						</option>
			</select>
		</div>

		<div class="field">
			<p>Do you speak English fluently?</p>
			<input    type="radio"    name="English"    value="Fluent"        />    Yes, I am fluent in English     <br />
			<input    type="radio"    name="English"    value="Non-Fluent"    />    No, I am not fluent in English  <br />
		</div>
        
		<div class="field">
			<p>What age did you start learning English? If English is your first language, please put 0.</p>
			<input type="text" value="" name="AgeEnglish" autocomplete="off"/>
		</div>

		<div class="field">
			<p>In what country do you live?</p>
			<input type="text" value="" name="Country" size="30"    autocomplete="off" />
		</div>
		
		<div class="field">
			<p>If you live in the United States of America, which state do you live in?</p>
			<select name="State">
				<option selected="selected">Select State</option>
				<option>Alabama			</option>
				<option>Alaska			</option>
				<option>Arizona			</option>
				<option>Arkansas		</option>
				<option>California		</option>
				<option>Colorado		</option>
				<option>Connecticut		</option>
				<option>Delaware		</option>
				<option>Florida			</option>
				<option>Georgia			</option>
				<option>Hawaii			</option>
				<option>Idaho			</option>
				<option>Illinois		</option>
				<option>Indiana			</option>
				<option>Iowa			</option>
				<option>Kansas			</option>
				<option>Kentucky		</option>
				<option>Louisiana		</option>
				<option>Maine			</option>
				<option>Maryland		</option>
				<option>Massachusetts	</option>
				<option>Michigan		</option>
				<option>Minnesota		</option>
				<option>Mississippi		</option>
				<option>Missouri		</option>
				<option>Montana			</option>
				<option>Nebraska		</option>
				<option>Nevada			</option>
				<option>New Hampshire	</option>
				<option>New Jersey		</option>
				<option>New Mexico		</option>
				<option>New York		</option>
				<option>North Carolina	</option>
				<option>North Dakota	</option>
				<option>Ohio			</option>
				<option>Oklahoma		</option>
				<option>Oregon			</option>
				<option>Pennsylvania	</option>
				<option>Rhode Island	</option>
				<option>South Carolina	</option>
				<option>South Dakota	</option>
				<option>Tennessee		</option>
				<option>Texas			</option>
				<option>Utah			</option>
				<option>Vermont			</option>
				<option>Virginia		</option>
				<option>Washington		</option>
				<option>West Virginia	</option>
				<option>Wisconsin		</option>
				<option>Wyoming			</option>
			</select>
		</div>

		<!-- ## SET ##  Use this area to provide the equivalent of an informed consent form -->
		<div class="consent">
			<h3 class="consent-legend">Informed Consent:</h3>
			<h3 class="consent-legend textcenter">Learning Words and Remembering Facts</h3>
			<textarea rows="20" cols="45" wrap="physical">This is the informed consent form.  You can put whatever you want here.</textarea>
		</div>

		<div class="consent textcenter">
			<input class="button" type="submit" value="Submit Basic Info" />
		</div>
	</form>
</div>
<?php
    require $_codeF . 'Footer.php';