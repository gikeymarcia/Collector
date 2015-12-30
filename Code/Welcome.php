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
    require 'initiateCollector.php';
    // load page header
    $title = 'Experiment Login Page';  
    require $_PATH->get('Header');
    $action = $_PATH->get('Login', 'url');


    $currentExp = explode('/', $_SERVER['SCRIPT_NAME']);    // get something like array(... , "Collector", "Experiments", "Demo", "index.php")
    $currentExp = $currentExp[count($currentExp)-2];        // take directory name (from above example, take "Demo")
    
    $_PATH->setDefault('Current Experiment', $currentExp);
    $_PATH->setDefault('Data Sub Dir', '');                 // the conditions class will automatically create a login counter dir, and it needs this value
    
    $_SETTINGS->up_to_date($_PATH);         // refresh settings to read from correct experiment settings
    
    #### login objects
    $errors = new errorController();

    $cond = new conditionController(
        $_PATH->get('Conditions'),
        $_PATH->get('Counter'),
        $_SETTINGS->hide_flagged_conditions,
        $errors
    );
    
    $cond->checkConditionsFile($_PATH->get('Procedure Dir'), $_PATH->get('Stimuli Dir'));
    if ($errors->arePresent()) {
        $errors->printErrors();
        exit;
    }
    $conditions = $cond->getAllConditions();
?>
<!-- Page specific styling tweaks -->
<style>
    #indexLogin {
        margin-top: 2em;
    }
    #indexLogin div:first-of-type{
        margin-bottom: .5em;
    }
    #indexLogin input[type="text"] {
        width: 250px;
    }
    #indexLogin  select {
        width: 150px;
    }
</style>

<form   id="content"            name="Login"
        action="<?=$action?>"   method="get"
        autocomplete="off"      class="index"   >
    <h1 class="textcenter"><?= $_SETTINGS->welcome ?></h1>
    <?= $_SETTINGS->exp_description ?>
    
    <section id="indexLogin" class="flexVert">
        <div class="textcenter flexChild">
            <?= "Please enter your $_SETTINGS->ask_for_login"  ?>
        </div>
        <div class="flexChild">
            <input name="Username" type="text" value="" class="collectorInput" placeholder="<?= $_SETTINGS->ask_for_login ?>">
            
            <!-- Condition selector -->
        <?php if ($_SETTINGS->show_condition_selector == true): ?>
            <select name="Condition" class="collectorInput">
        <?php else: ?>
            <select class="hidden" name="Condition">
        <?php endif; ?>
                <option default selected value="Auto">Auto</option>
        <?php  // Display conditions as options
                foreach ($conditions as $i => $cond) {
                    if ($_SETTINGS->use_condition_names) {
                        $name = $cond['Description'];
                    } else {
                        $name = $i+1;
                    }
                    // showing Stimuli + Procedure files for each condition
                    if ($_SETTINGS->show_condition_info) {
                        $title = "title='{$cond['Stimuli']} - {$cond['Procedure']}'";
                    } else {
                        $title = '';
                    }
                    // make flagged conditions grey
                    if (substr($cond['Description'],0,1) === '#') {
                        $style = 'style="color: grey;"';
                    } else {
                        $style = '';
                    }
                    // put this condition in the dropdown selector
                    echo "<option value='$i' $title $style>$name</option>";
                }
        ?>
            </select>
            <input type="hidden" name="CurrentExp" value="<?= $currentExp ?>">
            <button class="collectorButton" type="submit">Login</button>
        </div>
    </section>
</form>
<?php 
    require $_PATH->get('Footer');
