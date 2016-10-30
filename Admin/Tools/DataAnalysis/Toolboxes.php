<h3>Toolboxes</h3>
<div id="Verified_toolboxes_area">
    <h4>Verified toolboxes</h4>
    <div id="verified_toolbox_list"></div>
</div>
<div id="User_toolboxes_area">
    <h4>User toolboxes</h4>
    <span><em>Beware - we have not verified whether these toolboxes are safe or not. Use at your own peril</em></span>
    <div id="unverified_toolbox_list"></div>
</div>

<?php
    $validated_tools = glob('Toolboxes/Validated/*.js');
?>

<script>
    function get_script_loader() {
        var loaded_scripts = [];
        
        return function(script_src) {
            if (loaded_scripts.indexOf(script_src) === -1) {
                var script = document.createElement('script');
                script.src = script_src;
                document.head.appendChild(script);
                
                loaded_scripts.push(script_src);
            }
        }
    }
    
    var load_script = get_script_loader();

    var validated_tools = <?= json_encode($validated_tools) ?>;
    
    validated_tools.forEach(function(tool_path) {
      var name = tool_path
                .replace('Toolboxes/Validated/', '')
                .replace(/\.js$/, '');
      $("#verified_toolbox_list").append(
        "<div><label><input type='checkbox' value='" + tool_path + "'>" + name + "</label></div>"
      );
    });
    
    $("#verified_toolbox_list input").on("change", function() {
        if (this.checked) {
            load_script(this.value);
        } else {
            console.dir("deactivate: " + this.value);
        }
    });
</script>

<?php
    return;
?>

    <?php
    
      //read folders within "Toolboxes"
      
      // this is where we have jStat embedded
      
//      $toolboxes = ['jStat.txt'];
      
      $toolboxes = glob('Toolboxes/Validated/*.js');
      
      $toolbox_location_array = [];
      
      echo "<h4>Verified toolboxes</h4>";
      echo "<br>";
      
      foreach($toolboxes as $toolbox){
        $this_location  = file_get_contents("$toolbox");
        $this_label     = str_ireplace("Toolboxes/Validated/","",$toolbox);
        $this_label     = str_ireplace(".txt","",$this_label);
        echo "<label id='toolbox_$this_label'>$this_label<input type='checkbox' id='toolbox_check_$this_label' onclick='activate_deactivate_toolbox(\"$this_label\")'></label><br>";
        array_push($toolbox_location_array,$this_location); 
      }
      
      ?>
      <br><br>
      <h4>User chosen toolboxes</h4>
      <span><em>Beware - we have not verified whether these toolboxes are safe or not. Use at your own peril</em></span>
      <br><br>
    
        <?php

      // code for user toolboxes
      
      $user_toolboxes = glob('Toolboxes/User/*.txt');
      
      $user_toolbox_location_array = [];
      
      echo "<div id='user_toolboxes_div'>";
      
      foreach($user_toolboxes as $usertoolbox){
        $this_location  = file_get_contents("$usertoolbox");
        $this_label     = str_ireplace("Toolboxes/User/","",$usertoolbox);
        $this_label     = str_ireplace(".txt","",$this_label);
        echo "<label id='toolbox_$this_label'>$this_label<input type='checkbox' id='toolbox_check_$this_label' onclick='activate_deactivate_toolbox(\"$this_label\")'></label><br>";
        array_push($user_toolbox_location_array,$this_location); 
      }
      
    ?>
    </div>
    <br><br>
    <input type="text" placeholder="web address of toolbox" id="add_toolbox_address">
    <input type="text" placeholder="name of toolbox" id="add_toolbox_name">
    <input type="button" class="collectorButton" value="Add toolbox" id="add_toolbox_button">
  
  <script>
    
    var toolboxes_array = <?= json_encode($toolboxes); ?>;
    var toolboxes_location_array = <?= json_encode($toolbox_location_array) ?>;
    var user_toolboxes_array = <?= json_encode($user_toolboxes); ?>;
    var user_toolboxes_location_array = <?= json_encode($user_toolbox_location_array) ?>;
    
  function activate_deactivate_toolbox(this_label){
    console.dir(this_label);
    if(document.getElementById('toolbox_check_'+this_label).checked==true){
      var toolbox_position = toolboxes_array.indexOf("Toolboxes/"+this_label+".txt");
      var script_location = toolboxes_location_array[toolbox_position];
      

      var scrpt = document.createElement('script');
      scrpt.src=script_location;
      document.head.appendChild(scrpt);
         
      // code for adding this toolbox to the start of the script
      
      
    } else {
      alert ("this toolbox will not be deactivated, but will not load next time you load this page");
            
      // code for removing this from the start of the GUI
      
    }

//  <script src = "//cdn.jsdelivr.net/jstat/latest/jstat.min.js" >

    
    //script for loading or unloading a toolbox
    //if($("#toolbox_"+this_label).checked
    
  }
  
  
  $("#add_toolbox_button").on("click", function(){
    var toolbox_web_address = $("#add_toolbox_address").val();
    var toolbox_name        = $("#add_toolbox_name").val();
    
    // ajax in new file with this info
    
    $.get(
      'AjaxUserToolbox.php',
      { web_address         : toolbox_web_address,
        filename            : toolbox_name
      } , 
      function(returned_data) {
        $("#saving_area").html("GUI Script and Output Saved");  
      
        //update list of toolboxes_array
        user_toolboxes_array[user_toolboxes_array.length]               = toolbox_name;
        user_toolboxes_location_array[user_toolboxes_location_array.length] = toolbox_web_address;
        
        // update the list of toolboxes, and automatically check the new added one?
        
        var user_toolboxes_div_input = '';

        for(i = 0; i<user_toolboxes_array.length; i++){
          user_toolboxes_div_input += "<label id='"+user_toolboxes_array[i]+"'>"+
                                    user_toolboxes_array[i]+
                                    "<input type='checkbox' id='toolbox_check_"+user_toolboxes_array[i]+"'"+
                                    " onclick='activate_deactivate_toolbox(\""+user_toolboxes_array[i]+"\")'></label><br>";
        }
        
        $("#user_toolboxes_div").html(user_toolboxes_div_input); 
        
        
        // maybe ask what the users preference is for activating new toolboxes
        
        // clear space for inputting new user toolbox
        $("#add_toolbox_address").val("");
        $("#add_toolbox_name").val("");
      
      }
      
      
      
    );


  });
  </script>
      
      </div>