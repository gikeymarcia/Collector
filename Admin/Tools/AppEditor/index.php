<?php
    require '../../initiateTool.php';

?>
<style type="text/css" media="screen">
        #ACE_editor { 
            height:500px;
        }
    </style>
<iframe id="canvas_iframe"></iframe>

<div id="ACE_editor"></div>
<script src="https://cdn.jsdelivr.net/ace/1.2.6/min/ace.js" type="text/javascript" charset="utf-8"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.2.7/ext-language_tools.js" type="text/javascript" charset="utf-8"></script>

<script>
var editor = ace.edit("ACE_editor");
    editor.setTheme("ace/theme/chrome");       
    editor.getSession().setMode("ace/mode/html");
</script>