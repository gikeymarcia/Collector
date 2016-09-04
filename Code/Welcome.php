<?php
/*  Collector
    A program for running experiments on the web
    Copyright 2012-2016 Mikey Garcia & Nate Kornell


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

// determine whether participant is returning or not (see Return.php)
$is_returning_user = isset($is_returning_user) ? $is_returning_user : false;

// get the directory of the currently running script (should be Experiment name)
$script_name = filter_input_fix(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING);
$path_parts = explode('/', $script_name);
$current_exp = $path_parts[count($path_parts) - 2];

// Set default path for the Current Experiment
$_FILES->set_default('Current Experiment', $current_exp);

// Set default path for the Data directory
// the conditions class will automatically create a login counter dir, and it needs this value
$_FILES->set_default('Data Sub Dir', '');

/*
 * Login objects
 */
 /* to be updated, and possibly converted to JS
$errors = new Collector\ErrorController();

$cond = new Collector\ConditionController(
    $_FILES->get_path('Conditions'),
    $_FILES->get_path('Counter'),
    $errors,
    $_SETTINGS->hide_flagged_conditions
);

$cond->checkConditionsFile(
    $_FILES->get_path('Procedure Dir'),
    $_FILES->get_path('Stimuli Dir')
);

if ($errors->arePresent()) {
    $errors->printErrors();
    exit;
}
$conditions = $cond->getAllConditions();
*/

$conditions = $_FILES->read('Conditions');

/*
 * Display
 */
// load page header
$title = 'Experiment Login Page';
require $_FILES->get_path('Header');
$action = $_FILES->get_path('Login');

// modify condition option tag attributes according to Settings
$options = array();
foreach ($conditions as $i => $cond) {
    // default option tag attributes
    $option = array('value' => $i, 'title' => '', 'style' => '', 'name' => $i + 1);

    // Use condition name if set and names are turned on, else number
    if ($_SETTINGS->use_condition_names) {
        $option['name'] = $cond['Description'];
    }

    // show Stimuli + Procedure files for each condition if set
    if ($_SETTINGS->show_condition_info) {
        $option['title'] = "{$cond['Stimuli']} - {$cond['Procedure']}";
    }

    // change style to greyed out if condition is flagged
    if (substr($cond['Description'], 0, 1) === '#') {
        $option['style'] = 'color: grey;';
    }

    $options[$i] = $option;
}

// set the login prompt text based on whether this is a returning user or not
$login_text = "Please enter your {$_SETTINGS->ask_for_login}";
if ($is_returning_user) {
    $login_text .= ' and make sure it is the same one you used last time';
}

// only show the select box if enabled and this is not a returning user
$select_class = 'hidden';
if ($_SETTINGS->show_condition_selector == true && !$is_returning_user) {
    $select_class = 'collectorInput';
}

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

<form id="content"             name="Login"
      action="<?= $action ?>"  method="get"
      autocomplete="off"       class="index" >
  <h1 class="textcenter"><?= $_SETTINGS->welcome ?></h1>
  <?= $_SETTINGS->exp_description ?>

  <section id="indexLogin" class="flexVert">
    <div class="textcenter flexChild">
      <?= $login_text ?>
    </div>

    <div class="flexChild">
      <input name="Username" type="text" value="" class="collectorInput" placeholder="<?= $_SETTINGS->ask_for_login ?>" autofocus>

      <!-- Condition selector -->
      <select name="Condition" class="<?= $select_class ?>">
        <option default selected value="Auto">Auto</option>

        <?php foreach ($options as $o): ?>
        <option value="<?= $o['value'] ?>" title="<?= $o['title'] ?>" style="<?= $o['style'] ?>">
            <?= $o['name'] ?>
        </option>
        <?php endforeach; ?>

      </select>

      <!-- Submit button -->
      <?php if ($is_returning_user): ?>
      <input type="hidden" name="returning" value="1">
      <?php endif; ?>
      <input type="hidden" name="CurrentExp" value="<?= $current_exp ?>">
      <button class="collectorButton" type="submit">Login</button>

    </div>
  </section>
</form>

<?php
require $_FILES->get_path('Footer');
