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
    $_SESSION = array();                                // reset session so it doesn't contain any information from a previous login attempt

	// load and sort conditions
	$Conditions = GetFromFile($expFiles.$conditionsFileName, FALSE);
    $conditionNumbers = array();
    foreach ($Conditions as $cond) {
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
	
	$title = 'Experiment Login Page';
    require $_codeF . 'Header.php';
?>
<div class="cframe-content textcenter login-pos">
	<h1><?php echo $welcome;?></h1>

	<?php echo $expDescription; ?>

	<form class="collector-form collector-form-extra" name="Login" autocomplete="off"  action="<?php echo $codeF;?>login.php"  method="get">
		<?php echo $askForLogin;?>
		<input name="Username" type="text" value="" autocomplete="off" />

			<?php if ($showConditionSelector == TRUE): ?>
			<select name="Condition">
			<?php else: ?>
			<select class="hidden" name="Condition">
			<?php endif; ?>

				<option selected value="Auto">Auto</option>

				<?php
				#### Display conditions as choices ####

				// output all possible condition choices
                foreach ($Conditions as $cond) {
                    $name  = $useConditionNames ? $cond['Number']   . '. ' . $cond['Condition Description']         : $cond['Number'];
                    $title = $showConditionInfo ? $stimF . $cond['Stimuli'] . ' - ' . $procF . $cond['Procedure']   : '';
                    
					echo '<option value="' . $cond['Number'] . '" title="' . $title . '">' . $name . '</option>';
                }
				?>
			</select>

		<input class="button" type="submit" value="Login" />
	</form>
</div>

<?php
    require $_codeF . 'Footer.php';