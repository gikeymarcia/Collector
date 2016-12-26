<h3>Toolboxes</h3>
<div id="Verified_toolboxes_area">
    <h4>Verified toolboxes</h4>
    <div id="verified_toolbox_list"></div>
</div>
<div id="User_toolboxes_area">
    <h4>User toolboxes</h4>
    <span><em>Beware - we have not verified whether these toolboxes are safe or not. Use at your own peril</em></span>
    <div id="user_toolbox_list">
    
    </div>
    <input type="text" id="user_toolbox_web_address" placeholder="url">
    <input type="text" id="user_toolbox_name" placeholder="name">
    <button type="button" class="collectorButton" id="add_toolbox_button">Include</button>
</div>
<div>
  <h3>Toolbox help</h3>
  <div id="toolbox_helper">Click on a toolbox "help" button to activate</div>
</div>

<?php 
  if(!is_dir('Toolboxes/User/')){
    mkdir('Toolboxes/User/',0777,true);
  }
  $user_tools = glob('Toolboxes/User/*.txt'); 

?>

<script>

    var user_tools = <?= json_encode($user_tools) ?>;
    
    user_tools.forEach(function(tool_path) {
      var name = tool_path
                .replace('Toolboxes/User/', '')
                .replace(/\.txt$/, '');
      $("#user_toolbox_list").append(
        "<div><label><input type='checkbox' value='" + tool_path + "'>" + name + "</label></div>"
      );
    });
    
    $("#user_toolbox_list input").on("change", function() {
        this_user_toolbox = this.value;
        this_user_toolbox = this_user_toolbox.replace("Toolboxes/User/","");
        this_user_toolbox = this_user_toolbox.replace(".txt","");
        
        if (this.checked) {
          alert(this_user_toolbox);
          $.get(
              'UserToolboxAjax.php',
            {
              filename: this_user_toolbox
            },
            function(returned){
              load_script(returned);

              
            },
            "text"
          )
            //load_script(this.value);
        } else {
            console.dir("deactivate: " + this.value);
        }
    });


  $("#add_toolbox_button").on("click", function(){
    var toolbox_web_address = $("#user_toolbox_web_address").val();
    var toolbox_name        = $("#user_toolbox_name").val();
  
    $.post(
      'UserToolboxAjax.php',
      { web_address         : toolbox_web_address,
        filename            : toolbox_name
      } , 
      function(returned_data) {
        //$("#saving_area").html("GUI Script and Output Saved"); 
          
          user_tools.push(toolbox_name);
          
          $("#user_toolbox_list").append(
            "<div><label><input type='checkbox' value='" + toolbox_web_address + "' checked>" + toolbox_name + "</label></div>"
          );
          
          load_script(toolbox_web_address);       
         
     }
    );
  });
  

</script>

<?php
    $validated_tools = glob('Toolboxes/Validated/*.js');
?>

<script>

    var validated_tools = <?= json_encode($validated_tools) ?>;
    
    validated_tools.forEach(function(tool_path) {
      var name  = tool_path
                .replace('Toolboxes/Validated/', '')
                .replace(/\.js$/, '');
      var help  = tool_path
                .replace(/\.js$/, '_help.txt');
      $("#verified_toolbox_list").append(
        "<div><label><input type='checkbox' value='" + tool_path + "'>" + name + "</label><button class='toolbox_help_button' value='"+help+"'>Help</button></div>"
      );
    });
    
    $(".toolbox_help_button").on("click",function(){
      //alert(this.value);
      var this_url = this.value;
      $.post(
      'AjaxLoadHelp.php',
      { web_address         : this_url
      } , 
      function(returned_data) {
        //$("#saving_area").html("GUI Script and Output Saved"); 
          
        $("#toolbox_helper").html(returned_data);      
       
     });
      
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

      