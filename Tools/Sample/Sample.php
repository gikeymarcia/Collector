<?php
    // access control
    require_once 'loginFunctions.php';      // This is so we can run the function below
    adminOnly();
    /**
     * The above lines should be at the head of every tool becasue they ensure no one can access your
     * tool unless properly logged in. We have carefully worked on making /Tools/ as secure as possible over HTTP
     * and you can focus on writing useful code for science!
     */
    
    /**
     * This is a skeleton tool that does nothing but gives you a starting place for writing your own custom tools
     *     /Tools/ work by first having /Tool/tools.php scan for sub-folders in /Tools/
     *     Each valid tool is a sub-folder of /Tools/ that contains a php script of the same name as the folder
     *     For example, /Tools/NewThing/NewThing.php is a valid tool configuration
     *
     * All valid tools populate the tool selector dropdown menu
     *
     * When somone picks a tool the primary script (e.g./ `/Tools/NewThing/NewThing.php`) is 
     * included from within /Tool/tools.php
     *
     * Essentially, anything you write on your script runs as if it was running from /Tools/tools.php
     *
     * The code is written so this 'Sample' tool doesn't show up
     * If making a new tool copy the sample directory then rename it and it's corresponding script
     * For example, "/New/New.php"
     *
     * What you have access to in all tools
     *     - $_PATH
     *     - $_CONFIG
     *     - `customFunctions.php`
     *     - $_DATA
     *         -- This is a special holder array that you can use to store information into $_SESSION
     *            I highly recommend you use $_DATA and not make your own holder in $_SESSION
     *            By using $_DATA the session data you make will be wiped when the user logs out
     *            $_DATA is actually an alias for $_SESSION['admin']['(toolname)Data'] = array();
     *
     * 
     * General structure of page where your tool will be included:
     * 
     * <body id="flexbox">
     *     <div id="nav">NavigationBar stuff</div>
     *     
     *     ## ## YOUR TOOL IS INCLUDED HERE ## ##
     *     
     * </body>
     *
     * The body ID is "flexbox" becasuse that is how the rest of the program is configured but for /Tools/ we have
     * changed that ID style so { display: block; height: auto; } If you'd like to override that you can simply add
     * <style></style> tags inside this page and it will apply to the display while your tool is in use
     * You can actually do things like set ID "nav" to width and height of 0 but we advise heavily against it becasue
     * people will not be able to logout or switch tools after choosing yours
     *
     * If you make something that feels like it will be useful for others then make a pull request on the github page
     * https://github.com/gikeymarcia/Collector/
     */