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
$isReturningUser = isset($isReturningUser) ? $isReturningUser : false;

// get the directory of the currently running script (should be Experiment name)
$scriptName = filter_input_fix(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_STRING);
$pathParts = explode('/', $scriptName);
$currentExp = $pathParts[count($pathParts) - 2];

// Set default path for the Current Experiment
$_PATH->setDefault('Current Experiment', $currentExp);

// Set default path for the Data directory
// the conditions class will automatically create a login counter dir, and it needs this value
$_PATH->setDefault('Data Sub Dir', '');

// refresh settings to read from correct experiment settings
$_SETTINGS->upToDate($_PATH);

/*
 * Login objects
 */
$errors = new Collector\ErrorController();

$cond = new Collector\ConditionController(
    $_PATH->get('Conditions'),
    $_PATH->get('Counter'),
    $errors,
    $_SETTINGS->hide_flagged_conditions
);

$cond->checkConditionsFile($_PATH->get('Procedure Dir'), $_PATH->get('Stimuli Dir'));
if ($errors->arePresent()) {
    $errors->printErrors();
    exit;
}
$conditions = $cond->getAllConditions();

/*
 * Display
 */
// load page header
$title = 'Experiment Login Page';
require $_PATH->get('Header');
$action = $_PATH->get('Login', 'url');

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
$loginText = "Please enter your {$_SETTINGS->ask_for_login}";
if ($isReturningUser) {
    $loginText .= ' and make sure it is the same one you used last time';
}

// only show the select box if enabled and this is not a returning user
$selectClass = 'hidden';
if ($_SETTINGS->show_condition_selector == true && !$isReturningUser) {
    $selectClass = 'collectorInput';
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
      <?= $loginText ?>
    </div>

    <div class="flexChild">
      <input name="Username" type="text" value="" class="collectorInput" placeholder="<?= $_SETTINGS->ask_for_login ?>">

      <!-- Condition selector -->
      <select name="Condition" class="<?= $selectClass ?>">
        <option default selected value="Auto">Auto</option>

        <?php foreach ($options as $o): ?>
        <option value="<?= $o['value'] ?>" title="<?= $o['title'] ?>" style="<?= $o['style'] ?>">
            <?= $o['name'] ?>
        </option>
        <?php endforeach; ?>

      </select>

      <!-- Submit button -->
      <?php if ($isReturningUser): ?>
      <input type="hidden" name="returning" value="1">
      <?php endif; ?>
      <input type="hidden" name="CurrentExp" value="<?= $currentExp ?>">
      <button class="collectorButton" type="submit">Login</button>

    </div>
  </section>
</form>

<?php
require $_PATH->get('Footer');
