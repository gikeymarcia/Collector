<style>
  .GUI_headers:hover{
    color:green;
  }
  .GUI_headers{
    width:200px;
  }
  .GUI_divs{
    display:none;
  }
</style>

<head>
    <title>Tests</title>
    <meta charset="utf-8">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>    
    <link rel="stylesheet" type="text/css" href="GUI/GuiStyle.css" media="screen" />               

</head>
<body>

<script src="GUI/GuiFunctions.js"></script>

<table id="gui_table">
  <tr>
    <td id="current_trial_type_name">
      <h3 id="gui_trialtype_header">[Select a trialtype to edit or create a trialtype]</h3>
      <input type="button" value="Create" class="collectorButton" id="gui_create_trialtype_button">
      <input type="button" value="Apply" class="collectorButton" id="gui_to_trialtype_button">
      <input type="button" value="Apply and Save" class="collectorButton" id="gui_to_trialtype_save_button">    
    </td>
  </tr>
  <tr id="entire_gui_interface" style="display:none">
    <td>
      <div id="canvas">
        <iframe id="canvas_iframe">          
        </iframe>
      </div>
    </td>
    <td>
      <div id="gui_interface">
        
        <table>
          <tr>
            <td><h3 id="gui_add_element_header" class="GUI_headers"> Add Element </h3></td>
            <td><h3 id="gui_edit_script_header" class="GUI_headers"> Interactive </h3></td>
            <td><h3 id="gui_code_preview_header" class="GUI_headers">Code Preview</h3></td>            
          </tr>
        </table>
        
        <div id="gui_add_element_div" class="GUI_divs" style="display:none">                  
          <?php require("AddElement.php") ?>   
        </div>
        
        <div id="gui_interface_edit_element">
          <?php require("EditElement.php") ?> <!-- better place to put this? -->
        </div>

          
        <div id="gui_edit_script_div" class="GUI_divs">                  
          <?php require("Interactive.php") ?>
        </div>
        <div id="gui_code_preview_div" class="GUI_divs">
          <div id="temp_trial_type_template"></div>
          <br>
          
        </div>        
      </div>
    </td>
  </tr>
</table>

</body>
<script>        

function add_buttons_reset(){
    $(".new_element_button").removeClass("gui_button_clicked");
    $(".new_element_button").addClass("gui_button_unclicked");
}
  
</script>