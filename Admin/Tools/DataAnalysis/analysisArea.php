<style>
  #column_list > div { cursor: pointer; }
  #column_list > div.selected_column { font-weight: bold; }
  .column_edit_quick_analysis { display: none; }
  .column_edit_quick_analysis.allow_quick_analysis { display: block; }
  .column_name { color : #600; font-weight: bold; }
</style>

<div id="analysis_area">
  <div id="variable_list_area">
    <h3>Columns</h3>
    <div id="column_list"></div>
    <h3>Local Variables</h3>
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
      <div id="console_area"></div>
      
    </div>
    
  </div>
  
  <script src="//code.jquery.com/ui/1.12.0/jquery-ui.js"></script><!-- for jquery highlighting !-->

  <script>
    $("#column_list").on("click", "div", function() {
      $(".selected_column").removeClass("selected_column");
      $(this).addClass("selected_column");
      create_column_edit_menu(this);
    });
    
    $("body").on("click", function(e) {
      if ($(e.target).closest(".column_menu").length < 1) {
        $(".column_menu:not(.appearing)").hide(100, function() {
          $(".selected_column").removeClass("selected_column");
          $(this).remove();
        });
      };
    });
    
    function get_offset(elem) {
      var body_coords = document.body.getBoundingClientRect();
      var elem_coords = elem.getBoundingClientRect();
      
      return {
        top:    elem_coords.top    - body_coords.top,
        left:   elem_coords.left   - body_coords.left,
        right:  elem_coords.right  - body_coords.left,
        bottom: elem_coords.bottom - body_coords.top,
      }
    }
    
    function create_column_edit_menu(column) {
      $(".column_menu").remove();
      
      var column_name = column.innerHTML;
      
      if (typeof data_by_columns[column_name][0] === "number") {
        var type = "number";
      } else {
        var type = "string";
      }
      
      var offset = get_offset(column);
      
      var menu = $("<div>");
      
      menu.addClass("column_menu").addClass("appearing");
      
      menu.css({
        backgroundColor: "#CCC",
        border: "2px solid #444",
        position: "absolute",
        top:  offset.top,
        left: offset.right + 10,
        padding: "10px",
        width: "350px",
        height: "300px",
        display: "none"
      });
      
      menu.html(get_column_menu_contents(type));
      
      $("body").append(menu);
      
      menu.show(150, function() {
        $(this).removeClass("appearing");
      });
    }
    
    function get_column_menu_contents(type) {
      if (type === "number") {
        var format_number_val = " checked";
        var format_string_val = "";
        var quick_analysis_class = "allow_quick_analysis";
      } else {
        var format_number_val = "";
        var format_string_val = " checked";
        var quick_analysis_class = "";
      }
      
      var contents = "";
      contents += "Column: " + $(".selected_column").html();
      
      var format_choice = "<div class='column_edit_choose_format'>";
      format_choice += "<div>Column type:</div>";
      var choice_start = "<div><label><input type='radio' name='column_format' value='";
      format_choice += choice_start+"number' "+format_number_val+"> Number</label></div>";
      format_choice += choice_start+"string' "+format_string_val+"> String</label></div>";
      format_choice += "</div>";
      
      contents += format_choice;
      var quick_analysis = "<div class='column_edit_quick_analysis "+quick_analysis_class+"'>";
      quick_analysis += "<button type='button' class='column_quick_mean'>Calculate Mean</button>";
      quick_analysis += "<button type='button' class='column_quick_std' >Calculate St.Dev</button>";
      quick_analysis += "</div>";
      
      contents += quick_analysis;
      
      return contents;
    }
    
    $("body").on("change", "input[name='column_format']", function() {
      var column_name = $(".selected_column").html();
      
      if (this.value === "number") {
        $(".column_edit_quick_analysis").addClass("allow_quick_analysis");
        
        data_by_columns[column_name] = data_by_columns[column_name].map(function(val) {
          return parseFloat(val);
        });
      } else {
        $(".column_edit_quick_analysis").removeClass("allow_quick_analysis");
        
        data_by_columns[column_name] = data_by_columns[column_name].map(function(val) {
          return "" + val;
        });
      }
    });
    
    $("body").on("click", ".column_quick_mean", function() {
        var col_name = $(".selected_column").html();
        var col_vals = data_by_columns[col_name];
        
        $("#output_area").append(
          '<div><span class="column_name">'
          + col_name
          + '</span> mean: ' 
          + jStat.mean(col_vals)
          + '</div>'
        );
    });
    
    $("body").on("click", ".column_quick_std", function() {
        var col_name = $(".selected_column").html();
        var col_vals = data_by_columns[col_name];
        $("#output_area").append(
          '<div><span class="column_name">'
          + col_name
          + '</span> st.dev: ' 
          + jStat.stdev(col_vals, true)
          + '</div>'
        );
    });
  
    var Defined_Vars = [];
    
    function save_var(name, val) {
        window["Defined_Vars"][name] = val;
    }
    
    function report(val) {
        $("#output_area").append("<div>" + val + "</div><hr style='background-color:black'></hr>");
    }
    
    function update_var_list() {
        var var_list = [];
        
        for (var defined_var in window["Defined_Vars"]) {
            var_list.push(defined_var);
        }
        
        $("#variable_list").html(
            "<div>" + var_list.join("</div><div>") + "</div>"
        );
    }
    
    // running javascript_script
    console_log = '';
    $("#javascript_script_run_button").on("click",function(){
      this_script = $("#javascript_script").val();
      
      try{
        eval(this_script);
        
        this_script_split=this_script.split("\n");
        
        console_log+="<pre class='succesfull_code'>"+this_script+"</pre>";

      
        update_var_list();
            
      } catch(err){

        console_log+="<pre class='error_code'>"+err+"</pre>";

      }
      
      $("#console_area").html(console_log);
      
      return;
      
      // get the existing variables
      
      var existing_vars = [];
      
      for (var vars in window) {
          
      }
      
      // run the script
      
      
      
      
      // check for new vars
      
      this_script_split=this_script.split("\n");// break up script by ";" and then run each line through console
      
        
      for(i=0;i<this_script_split.length;i++){
        $("#console_area").html("");
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
      $("#console_area").html(console_log);
      
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
    <div id="output_area">
      <h2>Output</h2>
      
    </div>
    <div id="toolbox_area">
    
    <?php require("Toolboxes.php"); ?>
    
    </div>      

  </div>
  
</div>
