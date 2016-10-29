<?php
    require '../../initiateTool.php';
    
    $analyses_files = glob('Analyses/*.txt');
    
  
    foreach($analyses_files as &$analyses_file){
      $analyses_file=str_ireplace("Analyses/","",$analyses_file);
    }
    
    
    // default not to load any data
?>

<style>


  #analysis_name_block{
    margin-right:30px;
  }
  #left_col{
    width:60%;
  }
  #right_col{
    width:40%;    
  }
  #table_script_area{
    background-color:grey;
    width:100%;
    height:60%;
  }
</style>

<div id="analysis_area">
  <span id="analysis_name_block"> 

    <input type="text" id="analysis_name" placeholder="Current Analysis name">
    <select id="analyses_list">
      <option>-Select Analysis</option>
      <?php
        foreach($analyses_files as $analysis_file){
          $this_analysis = str_replace('.txt','',$analysis_file);
          echo "<option>$this_analysis</option>"; 
        }
      ?>
    </select>
    <button type="button" class="collectorButton" id="load_button">Load</button>
    <button type="button" class="collectorButton" id="delete_button">Delete</button>
    <span id="saving_area" style="display:none">Saved</span>

  </span>
  
  <button type="button" id="data_button" class="collectorButton">Data</button>
  <button type="button" id="analysis_button" class="collectorButton">Analysis</button>
  
</div>

  <script>
    function update_analysis(){
      var newName   = $("#analysis_name").val();
      var json_text = $("#analysis_json_textarea").val();      
      $.post(
        'AjaxSave.php',
        { filename   : newName,
          filetext   : json_text
        } , 
        function(returned_data) {
          $("#saving_area").html("GUI Script and Output Saved").fadeIn(400).fadeOut(2000);  
        }
      );    
    }

    $("#analysis_name").on("blur",function(){
      update_analysis();
      // add option to analyses list
      
      // check if option is on list or not yet.
    });
    
    $("#load_button").on("click",function(){
      analysis_to_load = $("#analyses_list").val();
      $.post(
        'AjaxLoad.php',
        {
          filename  : analysis_to_load
        },
        function(returned_data){
          analysis_json = JSON.parse(returned_data);
          $("#analysis_name").val(analysis_to_load);
          update_json();
          
          
        }
      
      )
    });
  
  </script>

  <textarea id="analysis_json_textarea"></textarea>
  
  <div id="data_area"></div>
  
  <div id="analysis_area">
    <div id="variable_list"></div>
    <div id="left_col"> 
      <span>
        <button type="button" class="collectorButton" id="gui_button">GUI</button>
        <button type="button" class="collectorButton" id="script_button">Script</button>
      </span>
      <div id="gui_area"></div>
      
       
      <div id="table_script_area">
        <div id="table_area"></div>
        <div id="script_area">
          <textarea id="javascript_script"></textarea>
          <button type="button">run</button>
        </div>
     
        
      </div>
      
    </div>
    
    <script>
      var analysis_json = {
        javascript_script:''
      };
      
      $("#javascript_script").on("input",function(){
        analysis_json['javascript_script']=$("#javascript_script").val();
        update_json();
      });
      
      function update_json(){
        $("#analysis_json_textarea").val(JSON.stringify(analysis_json));
      }
      
    </script>
    
    <div id="right_col">
      <div id="output_area"></div>
      <div id="toolbox_area"></div>      
    </div>
    
  </div>