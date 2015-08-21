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
    $Conditions = GetFromFile($_PATH->get('Conditions'), false);
    $conditionNumbers = array();
    foreach ($Conditions as $cond) {
        if ($cond['Condition Description'][0] === '#') { continue; }
        if (isset($conditionNumbers[$cond['Number']])) {
            exit('Error: Multiple Conditions use the same number. Please check your "'.$_PATH->conditions.'" file and make sure each number in the "Number" column is unique.');
        } else {
            $conditionNumbers[$cond['Number']] = true;
            if (!file_exists($_PATH->get('Stimuli Dir') . '/' . $cond['Stimuli'])) {
                exit('Error: The stimuli file "'   . $cond['Stimuli']   . '" could not be found in the ' . $_PATH->get('Stimuli Dir') . ' folder, for Condition ' . $cond['Number'] . ': ' . $cond['Condition Description'] . '. Either rename a file to "' . $cond['Stimuli']   . '" or change this entry in the "'.$_PATH->get('Conditions').'" file to match an existing file.');
            }
            if (!file_exists($_PATH->get('Procedure Dir') . '/' . $cond['Procedure'])) {
                exit('Error: The procedure file "' . $cond['Procedure'] . '" could not be found in the ' . $_PATH->get('Procedure Dir') . ' folder, for Condition ' . $cond['Number'] . ': ' . $cond['Condition Description'] . '. Either rename a file to "' . $cond['Procedure'] . '" or change this entry in the "'.$_PATH->get('Conditions').'" file to match an existing file.');
            }
        }
    }
    $tempCond   = SortByKey($Conditions, 'Number');
    
    // load page header
    $title = 'Experiment Login Page';  
    require $_PATH->get('Header');
    $action = $_PATH->get('Login', 'url');
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
    <h1 class="textcenter"><?= $_CONFIG->welcome ?></h1>
    <?= $_CONFIG->exp_description ?>
    
    <section id="indexLogin" class="flexVert">
        <div class="textcenter flexChild">
            <?= 'Please enter your ' . $_CONFIG->ask_for_login;  ?>
        </div>
        <div class="flexChild">
            <input name="Username" type="text" value="" autocomplete="off" class="collectorInput" placeholder="<?= $_CONFIG->ask_for_login ?>">
            
            <!-- Condition selector -->
        <?php if ($_CONFIG->show_condition_selector == true): ?>
            <select name="Condition" class="collectorInput">
        <?php else: ?>
            <select class="hidden" name="Condition">
        <?php endif; ?>
                <option default selected value="Auto">Auto</option>
        <?php  // Display conditions as options
                foreach ($Conditions as $i => $cond) {
                    if ($_CONFIG->hide_flagged_conditions AND $cond['Condition Description'][0] === '#') { continue; }
                    // showing condition description on hover
                    if ($_CONFIG->use_condition_names) {
                        $name = $cond['Number'] . '. ' . $cond['Condition Description'];
                    } else {
                        $name = $cond['Number'];
                    }
                    // showing Stimuli + Procedure files for each condition
                    if ($_CONFIG->show_condition_info) {
                        $title = ' title="' . $_PATH->stimuli_dir.'/' . $cond['Stimuli'] . ' - ' . $_PATH->procedure_dir.'/' . $cond['Procedure'] . '"';
                    } else {
                        $title = '';
                    }
                    // make flagged conditions grey
                    if ($cond['Condition Description'][0] === '#') {
                        $style = ' style="color: grey;"';
                    } else {
                        $style = '';
                    }
                    // put this condition in the dropdown selector
                    echo '<option value="' . $i . '"'. $title . $style . '>' . $name . '</option>';
                }
        ?>
            </select>
            <button class="collectorButton" type="submit">Login</button>
        </div>
    </section>
</form>
<?php 
    require $_PATH->get('Footer');
