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
    $_SESSION = array();                                // reset session so it doesn't contain any information from a previous login attempt

	// load and sort conditions
	$Conditions = GetFromFile($expFiles.$conditionsFileName);
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
				for ($i=2; $i<count($tempCond); $i++) {
					echo '<option value="' . $tempCond[$i]['Number'] . '">' . $tempCond[$i]['Number'] . '</option>';
				}
				?>
			</select>

		<input class="button" type="submit" value="Login" />
	</form>
</div>

<?php
    require $_codeF . 'Footer.php';