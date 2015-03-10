<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2015 Mikey Garcia & Nate Kornell


	This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
 */
    require 'Code/initiateCollector.php';

    // reset session so it doesn't contain any information from a previous login attempt
    session_destroy();
    $_SESSION = array();                             
    
    // load and sort conditions
	$Conditions = GetFromFile($expFiles.$conditionsFileName, FALSE);
    $conditionNumbers = array();
    foreach ($Conditions as $cond) {
        if ($cond['Condition Description'][0] === '#') { continue; }
        if (isset($conditionNumbers[$cond['Number']])) {
            exit('Error: Multiple Conditions use the same number. Please check your "'.$conditionsFileName.'" file and make sure each number in the "Number" column is unique.');
        } else {
            $conditionNumbers[$cond['Number']] = TRUE;
            if (!file_exists($expFiles . $stimF . $cond['Stimuli'])) {
                exit('Error: The stimuli file "'   . $cond['Stimuli']   . '" could not be found in the ' . $stimF . ' subfolder of the ' . $expFiles . ' folder, for Condition ' . $cond['Number'] . ': ' . $cond['Condition Description'] . '. Either rename a file to "' . $cond['Stimuli']   . '" or change this entry in the "'.$conditionsFileName.'" file to match an existing file.');
            }
            if (!file_exists($expFiles . $procF . $cond['Procedure'])) {
                exit('Error: The procedure file "' . $cond['Procedure'] . '" could not be found in the ' . $procF . ' subfolder of the ' . $expFiles . ' folder, for Condition ' . $cond['Number'] . ': ' . $cond['Condition Description'] . '. Either rename a file to "' . $cond['Procedure'] . '" or change this entry in the "'.$conditionsFileName.'" file to match an existing file.');
            }
        }
    }
    $tempCond   = SortByKey($Conditions, 'Number');
	
    // load page header
    $title = 'Experiment Login Page';  
    require $_codeF . 'Header.php';
?>

<section class="vcenter">
  <h1 class="textcenter"><?php echo $welcome;?></h1>
  <?php echo $expDescription; ?>
  
  <br>
  
  <form action="<?php echo $codeF . 'login.php' ?>" 
        autocomplete="off" 
        class="collector-form inline textcenter" 
        method="get" 
        name="Login">
    <?php echo $askForLogin;?>
    
    <div class="collector-form-element">
      <input name="Username" type="text" value="" autocomplete="off">
    </div>
  
    <div class="collector-form-element">
    <!-- Condition selector -->
    <?php if ($showConditionSelector == TRUE): ?>
      <select name="Condition">
    <?php else: ?>
      <select class="hidden" name="Condition">
    <?php endif; ?>
        <option default selected value="Auto">Auto</option>
      <?php  // Display conditions as options
                foreach ($Conditions as $i => $cond) {
                    if ($hideFlaggedConditions AND $cond['Condition Description'][0] === '#') { continue; }
                    
                    $name  = $useConditionNames ? $cond['Number'] . '. ' . $cond['Condition Description']                            : $cond['Number'];
                    $title = $showConditionInfo ? ' title="' . $stimF . $cond['Stimuli'] . ' - ' . $procF . $cond['Procedure'] . '"' : '';
                    
                    $style = ($cond['Condition Description'][0] === '#') ? ' style="color: grey;"' : '';
                    
					echo '<option value="' . $i . '"'. $title . $style . '>' . $name . '</option>';
        }
      ?>
      </select>
    <div class="collector-form-element">
      <input class="collector-button" type="submit" value="Login">
    </div>

  </form>
</section>
<?php 
    require $_codeF . 'Footer.php';