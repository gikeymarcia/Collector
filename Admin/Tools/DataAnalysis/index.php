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
  #data_analysis_block{
    margin-right:50px;
  }
  
  #console_area{
    width:100%;
    height:150px;
  }
  
  #javascript_script{
    width:   100%;
    height:  400px;
  }
  

  #table_script_area{
    width:100%;
    height:60%;
  }
  
  #analysis_area { display:block; margin:0; padding:0; width:100%; }
  #variable_list_area { float:left; display:block; margin:0; padding:0; width:10%; }
  #left_col { float:left; display:block; margin:0; padding:0; width:60%; }
  #right_col { float:left; display:block; margin:0; padding:0; width:30%;   }
  
  #variable_list{
    
  }
  
</style>

<div id="main_options">

  <span id="analysis_name_block"> 

    <input type="text" id="analysis_name" placeholder="Current Analysis name">
    <select id="analyses_list">
      <option>-Select Analysis-</option>
      <?php
        $list_of_analyses = array();
    
        foreach($analyses_files as $analysis_file){
      
        $this_analysis = str_replace('.txt','',$analysis_file);
          echo "<option>$this_analysis</option>"; 
          array_push($list_of_analyses,$this_analysis);
        }
        print_r($list_of_analyses);

      ?>
    </select>
    <button type="button" class="collectorButton" id="load_button">Load</button>
    <button type="button" class="collectorButton" id="delete_button">Delete</button>
    <span id="saving_area" style="display:none">Saved</span>

  </span>
  <span id="data_analysis_block">
    <button type="button" id="data_button" class="collectorButton">Data</button>
    <button type="button" id="analysis_button" class="collectorButton">Analysis</button>
      <textarea id="analysis_json_textarea"></textarea>

  </span>
  
  
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

    list_of_analyses = <?= json_encode($list_of_analyses) ?>;
    
    $("#analysis_name").on("blur",function(){
      update_analysis();
      if(list_of_analyses.indexOf($("#analysis_name").val())== -1){
        list_of_analyses[list_of_analyses.length]=$("#analysis_name").val();
      }
      // add option to analyses list
      update_analysis_list(list_of_analyses);
      // check if option is on list or not yet.
    });
    
//    analyses_list=["optin1","opption2"];
    function update_analysis_list(list_of_analyses){
      $("#analyses_list").empty();
      
      var x = document.getElementById("analyses_list");
        
        var option = document.createElement("option");
        option.text = "-Select Analysis-";
        x.add(option);
      
      for(i=0; i<list_of_analyses.length; i++){
        var option = document.createElement("option");
        option.text = list_of_analyses[i];
        x.add(option);    
      }        
    }      
      
    
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

  <?php
    function get_data() {
      $csv_data = fsDataType_CSV::read('temp/responses.csv');
      $raw_data = array();
      $raw_data[] = array_keys($csv_data[0]);
      
      foreach ($csv_data as $row) {
        $raw_data[] = array_values($row);
      }
      
      return json_encode($raw_data);
    }
  ?>
  <div id="data_area">
    <table id="data_table"></table>
    <script>
      var Collector_data_raw = <?= get_data(); ?>
      
      function raw_table_to_columns(data) {
        var output = {};
        var headers = data[0];
        
        // create empty arrays for each column header
        for (var col_index=0; col_index<headers.length; ++col_index) {
          // for example, set output["Username"] to empty array
          output[headers[col_index]] = [];
        }
        
        for (var row_index=1; row_index<data.length; ++row_index) {
          for (var col_index=0; col_index<headers.length; ++col_index) {
            output[headers[col_index]].push(data[row_index][col_index])
          }
        }
        
        return output;
      }
      
      var data_by_columns = raw_table_to_columns(Collector_data_raw);
      
      console.dir(data_by_columns);
    </script>
  </div>
  
  <div id="analysis_area">
    <div id="variable_list_area">
      <h3>Variables</h3>
      <div id="variable_list">
      </div>
    </div>
    <div id="left_col"> 
      <span>
        <button type="button" class="collectorButton" id="gui_button">GUI</button>
        <button type="button" class="collectorButton" id="script_button">Script</button>
      </span>
      <div id="gui_area">
      

      </div>
      
      <div id="table_script_area">
        <div id="table_area"></div>
        <div id="script_area">
          <textarea id="javascript_script"></textarea>
          <button type="button" class="collectorButton" id="javascript_script_run_button">run</button>
        </div>
        <textarea id="console_area"></textarea>
        
      </div>
      
    </div>
    
    <script src="//code.jquery.com/ui/1.12.0/jquery-ui.js"></script><!-- for jquery highlighting !-->
  
    <script>
      // running javascript_script
      console_log = '';
      $("#javascript_script_run_button").on("click",function(){
        this_script = $("#javascript_script").val();
        
        this_script_split=this_script.split("\n");// break up script by ";" and then run each line through console
        
          
        for(i=0;i<this_script_split.length;i++){
          $("#console_area").val("");
          if(this_script_split[i]!==''){
            try{
              new Function (this_script_split[i])();
              var this_output=process_line(this_script_split[i]);

              console_log+=this_script_split[i]+"\n"+this_output;

              
            } catch(err){
              console_log+="line "+(i+1)+"->"+err+"\n";
              
            }
          }

        }
        $("#console_area").val(console_log);
        
        textarea_in_question = document.getElementById('console_area');
        textarea_in_question.scrollTop = textarea_in_question.scrollHeight; // should find a way to adjust it so they see the last few lines (rather than just the last)!!
        
        $('#console_area').effect("highlight", {}, 3000); 

        
      });  
      function process_line(this_line){
        
        // check if there's anything to evaluate
        if(this_line.indexOf("=")==-1){
          return eval(this_line)+"\n";          
          
        } else {
          new_variable(this_line);
          return "";
        }
      }
      
      variable_list_items=[];
      
      function new_variable(this_input){
        
        // check if this variable already exists!!!!
        // has the == issue been resolved!!
          this_input_split=this_input.split("=");

        if(variable_list_items.indexOf(this_input_split[0])==-1){

        
          variable_list_items[variable_list_items.length]=this_input_split[0];
          variable_list_items = variable_list_items.sort();
          
          $("#variable_list").html(variable_list_items.join("<br>"));
        
        }
      }
      
      
      
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
      <div id="output_area">output area here</div>
      <div id="toolbox_area">
      
      <?php require("Toolboxes.php"); ?>
      
      </div>      

    </div>
    
  </div>