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
