<?php
/* The intent of this page is to provide either a prompt for the password or a
   menu of tools. */
/* When we log in, we will set a $_SESSION variable indicating our success, so
   that we know not to ask for the password again. */
require 'initiateTool.php';

$title = 'Collector - Tools';
require $_PATH->get('Header');

// we have logged in, present menu of tools
$tools = scandir('Tools');

foreach ($tools as $i => $tool) {
    if (   $tool === '.'
        || $tool === '..'
        || !is_dir("Tools/$tool")
        || !is_file("Tools/$tool/index.php")
    ) {
        unset($tools[$i]);
    }
}

?>
<h2>Welcome to the Admin page for the Collector</h2>
<p>To use one of our tools, select from the options below.</p>
<?php

foreach ($tools as $tool) {
    echo "<a href='Tools/$tool/'>$tool</a>";
}

require $_PATH->get('Footer');
