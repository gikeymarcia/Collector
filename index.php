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
require 'Code/initiateCollector.php';

// get possible experiments to choose from
$experiments = array();
$exp_folder  = $_FILES->get_path('Experiments');
foreach (get_Collector_experiments($_FILES) as $expName) {
    $experiments[$expName] = "$exp_folder/$expName";
}

$title = 'Collector Homepage';
require $_PATH->get('Header');
?>

<style>
  .inlineUL { display: inline-block; margin: auto; text-align: left; }
</style>
<div class="collectorRoot">
    <h1>Collector</h1>
    <h2>A program for running experiments on the web</h2>

    <p>Welcome to the Collector. If you would like to begin an experiment,
       click on one of the links below.
    </p>

    <ul class="inlineUL">
      <?php foreach ($experiments as $name => $path): ?>
      <li><a href='<?= $path ?>'><?= $name ?></a></li>
      <?php endforeach; ?>
    </ul>

    <p>Otherwise, you can access one of the other tools
       <a href="<?= $_PATH->get('Admin') ?>">here</a>.
    </p>
</div>

<?php
require $_PATH->get('Footer');
