<?php
/* The intent of this page is to provide either a prompt for the password or a
   menu of tools. */
/* When we log in, we will set a $_SESSION variable indicating our success, so
   that we know not to ask for the password again. */

require 'initiateTool.php';

// we have logged in, present menu of tools
$tools = getTools();

?>



<div class="toolWidth">
    <h2>Welcome to the Admin page for the Collector</h2>
    <p>To use one of our tools, select from the options below.</p>
    <?php
    foreach ($tools as $tool) {
        echo "<a href='Tools/$tool/'>$tool</a><br>";
    }
    ?>
    <br><br>
    <h5> Some functionality is still on the way. To be contacted when it's arrived, please insert your contact information below:</h5>
    <iframe width="800px" height="400px" src="https://docs.google.com/forms/d/e/1FAIpQLSetp3TdnRzJZ1cvhDIcex_ILqbB0faT2ZuWqXqJjmRThqoJoA/viewform?usp=sf_link">    
</div>
