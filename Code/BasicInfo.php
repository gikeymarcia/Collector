<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell
 */
    require 'initiateCollector.php';
	
    $title = 'Basic Information';
    require $_codeF . 'Header.php';
?>

<section class="basicinfo">
  <form class="collector-form aligned" name="Demographics" action="BasicInfoData.php" method="post" autocomplete="off">
    <fieldset>
      <legend>Basic Information</legend>
      
      <div class="collector-form-element inline-radio">
        <span class="radio-label">Gender</span>
        
        <div>
          <input id="male" name="Gender" type="radio" value="Male" required>
          <label for="male" class="">Male</label>

          <input id="female" name="Gender" type="radio" value="Female" required>
          <label for="female">Female</label>

          <input id="other" name="Gender" type="radio" value="Other" required>
          <label for="other">Other</label>
        </div>
      </div>
      
      <div class="collector-form-element number">
        <label for="age">Age</label>
        <input id="age" name="Age" type="text" pattern="[0-9][0-9]" value="" autocomplete="off" required>
      </div>
          
      <div class="collector-form-element">
        <label for="education">Education</label>
        <select id="education" name="Education" required>
          <option value="" default selected>Select Level
          <option>Some High School
          <option>High School Graduate
          <option>Some college, no degree
          <option>Associates degree
          <option>Bachelors degree
          <option>Graduate degree (Masters, Doctorate, etc.)
        </select>
      </div>  
    
      <div class="collector-form-element inline-radio">
        <span class="radio-label">Are you Hispanic?</span>
        
        <div>
          <input id="hispanic_yes" name="Hispanic" type="radio" value="Yes" required>
          <label for="hispanic_yes">Yes</label>

          <input id="hispanic_no" name="Hispanic" type="radio" value="No" required>
          <label for="hispanic_no">No</label>
        </div>
      </div>
    
      <div class="collector-form-element">
        <label for="Race">Ethnicity</label>
        <select name="Race" required>
          <option value="" default selected>Select one
          <option>American Indian/Alaskan Native
          <option>Asian/Pacific Islander
          <option>Black
          <option>White
          <option>Other/unknown
        </select>
      </div>  
    
      <div class="collector-form-element inline-radio">
        <span class="radio-label">Do you speak English fluently?</span>
        
        <div>
          <input id="fluent_yes" name="Fluent" type="radio" value="Yes" required>
          <label for="fluent_yes">Yes</label>

          <input id="fluent_no" name="Fluent" type="radio" value="No" required>
          <label for="fluent_no">No</label>          
        </div>
      </div>
    
      <div class="collector-form-element number">
        <label for="ageeng">At what age did you start learning English?</label>
        <input id="ageeng" name="AgeEnglish" type="text" pattern="[0-9][0-9]?" value="" autocomplete="off" required>
        <div class="helptext">If English is your first language please enter 0.</div>
      </div>    
    
      <div class="collector-form-element">
        <label for="country">Country</label>
        <select id="country" name="Country" required>
          <option value="AF">Afghanistan
          <option value="AX">Åland Islands
          <option value="AL">Albania
          <option value="DZ">Algeria
          <option value="AS">American Samoa
          <option value="AD">Andorra
          <option value="AO">Angola
          <option value="AI">Anguilla
          <option value="AQ">Antarctica
          <option value="AG">Antigua and Barbuda
          <option value="AR">Argentina
          <option value="AM">Armenia
          <option value="AW">Aruba
          <option value="AU">Australia
          <option value="AT">Austria
          <option value="AZ">Azerbaijan
          <option value="BS">Bahamas
          <option value="BH">Bahrain
          <option value="BD">Bangladesh
          <option value="BB">Barbados
          <option value="BY">Belarus
          <option value="BE">Belgium
          <option value="BZ">Belize
          <option value="BJ">Benin
          <option value="BM">Bermuda
          <option value="BT">Bhutan
          <option value="BO">Bolivia
          <option value="BA">Bosnia and Herzegovina
          <option value="BW">Botswana
          <option value="BV">Bouvet Island
          <option value="BR">Brazil
          <option value="IO">British Indian Ocean Territory
          <option value="BN">Brunei Darussalam
          <option value="BG">Bulgaria
          <option value="BF">Burkina Faso
          <option value="BI">Burundi
          <option value="KH">Cambodia
          <option value="CM">Cameroon
          <option value="CA">Canada
          <option value="CV">Cape Verde
          <option value="KY">Cayman Islands
          <option value="CF">Central African Republic
          <option value="TD">Chad
          <option value="CL">Chile
          <option value="CN">China
          <option value="CX">Christmas Island
          <option value="CC">Cocos (Keeling) Islands
          <option value="CO">Colombia
          <option value="KM">Comoros
          <option value="CG">Congo
          <option value="CD">Congo, The Democratic Republic of The
          <option value="CK">Cook Islands
          <option value="CR">Costa Rica
          <option value="CI">Cote D'ivoire
          <option value="HR">Croatia
          <option value="CU">Cuba
          <option value="CY">Cyprus
          <option value="CZ">Czech Republic
          <option value="DK">Denmark
          <option value="DJ">Djibouti
          <option value="DM">Dominica
          <option value="DO">Dominican Republic
          <option value="EC">Ecuador
          <option value="EG">Egypt
          <option value="SV">El Salvador
          <option value="GQ">Equatorial Guinea
          <option value="ER">Eritrea
          <option value="EE">Estonia
          <option value="ET">Ethiopia
          <option value="FK">Falkland Islands (Malvinas)
          <option value="FO">Faroe Islands
          <option value="FJ">Fiji
          <option value="FI">Finland
          <option value="FR">France
          <option value="GF">French Guiana
          <option value="PF">French Polynesia
          <option value="TF">French Southern Territories
          <option value="GA">Gabon
          <option value="GM">Gambia
          <option value="GE">Georgia
          <option value="DE">Germany
          <option value="GH">Ghana
          <option value="GI">Gibraltar
          <option value="GR">Greece
          <option value="GL">Greenland
          <option value="GD">Grenada
          <option value="GP">Guadeloupe
          <option value="GU">Guam
          <option value="GT">Guatemala
          <option value="GG">Guernsey
          <option value="GN">Guinea
          <option value="GW">Guinea-bissau
          <option value="GY">Guyana
          <option value="HT">Haiti
          <option value="HM">Heard Island and Mcdonald Islands
          <option value="VA">Holy See (Vatican City State)
          <option value="HN">Honduras
          <option value="HK">Hong Kong
          <option value="HU">Hungary
          <option value="IS">Iceland
          <option value="IN">India
          <option value="ID">Indonesia
          <option value="IR">Iran, Islamic Republic of
          <option value="IQ">Iraq
          <option value="IE">Ireland
          <option value="IM">Isle of Man
          <option value="IL">Israel
          <option value="IT">Italy
          <option value="JM">Jamaica
          <option value="JP">Japan
          <option value="JE">Jersey
          <option value="JO">Jordan
          <option value="KZ">Kazakhstan
          <option value="KE">Kenya
          <option value="KI">Kiribati
          <option value="KP">Korea, Democratic People's Republic of
          <option value="KR">Korea, Republic of
          <option value="KW">Kuwait
          <option value="KG">Kyrgyzstan
          <option value="LA">Lao People's Democratic Republic
          <option value="LV">Latvia
          <option value="LB">Lebanon
          <option value="LS">Lesotho
          <option value="LR">Liberia
          <option value="LY">Libyan Arab Jamahiriya
          <option value="LI">Liechtenstein
          <option value="LT">Lithuania
          <option value="LU">Luxembourg
          <option value="MO">Macao
          <option value="MK">Macedonia, The Former Yugoslav Republic of
          <option value="MG">Madagascar
          <option value="MW">Malawi
          <option value="MY">Malaysia
          <option value="MV">Maldives
          <option value="ML">Mali
          <option value="MT">Malta
          <option value="MH">Marshall Islands
          <option value="MQ">Martinique
          <option value="MR">Mauritania
          <option value="MU">Mauritius
          <option value="YT">Mayotte
          <option value="MX">Mexico
          <option value="FM">Micronesia, Federated States of
          <option value="MD">Moldova, Republic of
          <option value="MC">Monaco
          <option value="MN">Mongolia
          <option value="ME">Montenegro
          <option value="MS">Montserrat
          <option value="MA">Morocco
          <option value="MZ">Mozambique
          <option value="MM">Myanmar
          <option value="NA">Namibia
          <option value="NR">Nauru
          <option value="NP">Nepal
          <option value="NL">Netherlands
          <option value="AN">Netherlands Antilles
          <option value="NC">New Caledonia
          <option value="NZ">New Zealand
          <option value="NI">Nicaragua
          <option value="NE">Niger
          <option value="NG">Nigeria
          <option value="NU">Niue
          <option value="NF">Norfolk Island
          <option value="MP">Northern Mariana Islands
          <option value="NO">Norway
          <option value="OM">Oman
          <option value="PK">Pakistan
          <option value="PW">Palau
          <option value="PS">Palestinian Territory, Occupied
          <option value="PA">Panama
          <option value="PG">Papua New Guinea
          <option value="PY">Paraguay
          <option value="PE">Peru
          <option value="PH">Philippines
          <option value="PN">Pitcairn
          <option value="PL">Poland
          <option value="PT">Portugal
          <option value="PR">Puerto Rico
          <option value="QA">Qatar
          <option value="RE">Reunion
          <option value="RO">Romania
          <option value="RU">Russian Federation
          <option value="RW">Rwanda
          <option value="SH">Saint Helena
          <option value="KN">Saint Kitts and Nevis
          <option value="LC">Saint Lucia
          <option value="PM">Saint Pierre and Miquelon
          <option value="VC">Saint Vincent and The Grenadines
          <option value="WS">Samoa
          <option value="SM">San Marino
          <option value="ST">Sao Tome and Principe
          <option value="SA">Saudi Arabia
          <option value="SN">Senegal
          <option value="RS">Serbia
          <option value="SC">Seychelles
          <option value="SL">Sierra Leone
          <option value="SG">Singapore
          <option value="SK">Slovakia
          <option value="SI">Slovenia
          <option value="SB">Solomon Islands
          <option value="SO">Somalia
          <option value="ZA">South Africa
          <option value="GS">South Georgia and The South Sandwich Islands
          <option value="ES">Spain
          <option value="LK">Sri Lanka
          <option value="SD">Sudan
          <option value="SR">Suriname
          <option value="SJ">Svalbard and Jan Mayen
          <option value="SZ">Swaziland
          <option value="SE">Sweden
          <option value="CH">Switzerland
          <option value="SY">Syrian Arab Republic
          <option value="TW">Taiwan, Province of China
          <option value="TJ">Tajikistan
          <option value="TZ">Tanzania, United Republic of
          <option value="TH">Thailand
          <option value="TL">Timor-leste
          <option value="TG">Togo
          <option value="TK">Tokelau
          <option value="TO">Tonga
          <option value="TT">Trinidad and Tobago
          <option value="TN">Tunisia
          <option value="TR">Turkey
          <option value="TM">Turkmenistan
          <option value="TC">Turks and Caicos Islands
          <option value="TV">Tuvalu
          <option value="UG">Uganda
          <option value="UA">Ukraine
          <option value="AE">United Arab Emirates
          <option value="GB">United Kingdom
          <option value="US" default selected>United States
          <option value="UM">United States Minor Outlying Islands
          <option value="UY">Uruguay
          <option value="UZ">Uzbekistan
          <option value="VU">Vanuatu
          <option value="VE">Venezuela
          <option value="VN">Viet Nam
          <option value="VG">Virgin Islands, British
          <option value="VI">Virgin Islands, U.S.
          <option value="WF">Wallis and Futuna
          <option value="EH">Western Sahara
          <option value="YE">Yemen
          <option value="ZM">Zambia
          <option value="ZW">Zimbabwe
        </select>
      </div>

      <div class="collector-form-element">
        <label for="State">State</label>
        <select name="State">
          <option value="" default selected>Select State
          <option value="AL">Alabama
          <option value="AK">Alaska
          <option value="AZ">Arizona
          <option value="AR">Arkansas
          <option value="CA">California
          <option value="CO">Colorado
          <option value="CT">Connecticut
          <option value="DE">Delaware
          <option value="DC">District Of Columbia
          <option value="FL">Florida
          <option value="GA">Georgia
          <option value="HI">Hawaii
          <option value="ID">Idaho
          <option value="IL">Illinois
          <option value="IN">Indiana
          <option value="IA">Iowa
          <option value="KS">Kansas
          <option value="KY">Kentucky
          <option value="LA">Louisiana
          <option value="ME">Maine
          <option value="MD">Maryland
          <option value="MA">Massachusetts
          <option value="MI">Michigan
          <option value="MN">Minnesota
          <option value="MS">Mississippi
          <option value="MO">Missouri
          <option value="MT">Montana
          <option value="NE">Nebraska
          <option value="NV">Nevada
          <option value="NH">New Hampshire
          <option value="NJ">New Jersey
          <option value="NM">New Mexico
          <option value="NY">New York
          <option value="NC">North Carolina
          <option value="ND">North Dakota
          <option value="OH">Ohio
          <option value="OK">Oklahoma
          <option value="OR">Oregon
          <option value="PA">Pennsylvania
          <option value="RI">Rhode Island
          <option value="SC">South Carolina
          <option value="SD">South Dakota
          <option value="TN">Tennessee
          <option value="TX">Texas
          <option value="UT">Utah
          <option value="VT">Vermont
          <option value="VA">Virginia
          <option value="WA">Washington
          <option value="WV">West Virginia
          <option value="WI">Wisconsin
          <option value="WY">Wyoming
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
      
      <div class="collector-form-element">
        <input id="consent" name="Consent" type="checkbox" required>
        <label for="consent">Check this box if you have read, understand, and agree to the Informed Consent above.</label>
      </div>
    </fieldset>

    <div class="collector-form-element">
      <input class="collector-button" type="submit" value="Submit Basic Info">
    </div>
  </form>    
</section>

<?php
    require $_codeF . 'Footer.php';