<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2014 Mikey Garcia & Nate Kornell


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
    $Conditions = GetFromFile($expFiles.$conditionsFileName);
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
        for ($i=2; $i<count($tempCond); $i++) {
          $name  = $useConditionNames ? $tempCond[$i]['Condition Description'] : $tempCond[$i]['Number'];
          $title = $showConditionInfo ? $tempCond[$i]['Stimuli'] . ' - ' . $tempCond[$i]['Procedure'] : '';
          echo '<option value="' . $tempCond[$i]['Number'] . '"  title="' . $title . '">' . $name . '</option>';
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