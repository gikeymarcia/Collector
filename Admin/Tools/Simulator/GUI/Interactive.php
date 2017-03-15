<div id="interactive_gui"></div>
<input id="add_interactive_button" type='button' class='collectorButton' value='Add'>          
<textarea id="raw_script" style="display:none"></textarea> <!-- perhaps can remove this and related code -->
<div id="preprocessing_trialType" style="position:relative;display:none"></div>

  
<script>

$("#add_interactive_button").on("click",function(){
  $("#select_interactive_function").val("--- select a function ---");
  $("#select_interactive_function").show();
  $(".interactive_divs").hide();
  // create a new button below the lowest;
  // buttons should be highlighted depending on which script is being edited!
  
});

</script>
<select id='select_interactive_function' style="display:none">
<div id="gui_script_editor">
  <?php 
    $dir = "GUI/Interactive";
    $interactive_functions = array_diff(scandir($dir), array('.', '..'));
    
    print_r($interactive_functions);
  echo "<option>--- select a function ---</option>";
  foreach ($interactive_functions as $interactive_function){
    $interactive_function = str_ireplace(".php","",$interactive_function);
    echo "<option>$interactive_function</option>";
  }
  ?>
</select>            
  
  <?php
  foreach ($interactive_functions as $interactive_function){
    $this_div_name = str_ireplace(".php","",$interactive_function);
    echo "<div id='interactive_$this_div_name' class='interactive_divs'>";
      require("GUI/Interactive/$interactive_function");
    echo "</div>";
  }
   
  ?>
</div>