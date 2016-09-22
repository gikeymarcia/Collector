<?php

//storing selections made from index page

//var_dump($_POST);

// identify 

  //list files in Analysis folders
  
  $analyses_files = glob('Analyses/*js_script.txt');


  if(isset($_POST['analysis_name'])){
    $analysis_name = $_POST['analysis_name'];
  } else {
    
    $title_selected = false;
    $analysis_no=0;
    while ($title_selected == false){
      if(in_array("Analyses/Analysis$analysis_no"."_js_script.txt",$analyses_files)){
        $analysis_no++;
      } else {
        $title_selected = true;
      }
    }
    $analysis_name = "analysis$analysis_no";
    
    // more code for ensuring a unique filename
    
    
    
  }
  
  if(file_exists("Analyses/$analysis_name"."_manuscript.html")){
    $manuscript = file_get_contents("Analyses/$analysis_name"."_manuscript.html");
    $js_script  = file_get_contents("Analyses/$analysis_name"."_js_script.txt");
    $gui_script = file_get_contents("Analyses/$analysis_name"."_gui_script.txt");
    
  } else {
    $manuscript = '';
    $js_script  = '';
    $gui_script = '';
  }
  
  // saving template
  
  echo "<br><br>";
  
//  var_dump($_POST);
  
  $template = [];
  
  $template['files']              =   $_POST['files'];
  $template['trialTypes']         =   $_POST['trialTypes'];
  $template['Output_cols']        =   $_POST['Output_cols'];

  if(isset($_POST['Status_Begin_cols'])){
    $template['Status_Begin_cols']  =   $_POST['Status_Begin_cols'];
  }

  if(isset($_POST['Status_End_cols'])){
    $template['Status_End_cols']    =   $_POST['Status_End_cols'];
  }

  $template['u']                  =   $_POST['u'];
  
  
  /*
  
  //needs to store the right info from the first post and then store it in the same format in a second post
  
    $template_name = "$analysis_name.json"; // this obviously needs to change
    
    $template_contents = json_encode($_POST);
    
    file_put_contents("Analyses/$template_name", $file_contents);

  */


// Anthony's data analysis //

$json_columns = json_encode($columns);
$stats_options = [];

if ($handle = opendir('Stats')) { //found on http://stackoverflow.com/questions/6497833/php-opendir-to-list-folders-only by Jason McCreary
  $blacklist = array('.', '..', 'somedir', 'somefile.php');
  while (false !== ($file = readdir($handle))) {
      if (!in_array($file, $blacklist)) {
          array_push($stats_options,$file);
      }
  }
  closedir($handle);
}
$json_stats_options = json_encode($stats_options);

?>

        
<script>
  getdata_columns  = <?= $json_columns ?>;
  stats_options   = <?= $json_stats_options ?>;
  
  data_by_cols = {};
  
  function row_to_cols(data_by_cols,data,getdata_columns){ //col table,row table, columns

    for (var j=0; j<getdata_columns.length; ++j) {
      data_by_cols[getdata_columns[j]] = [];
    }
    
    for (var i=1; i<data.length; i++) {
      for (var j=0; j<getdata_columns.length; ++j) {
        var col = getdata_columns[j];
        data_by_cols[col][i-1] = data[i][j];
      }
    }  
  }
  
  row_to_cols(data_by_cols,data,getdata_columns);
  
  function col_to_rows(data_by_cols,columns_map){
    data_by_rows = [];
    
    var columns = columns_map;

    data_by_rows[0] = columns;

    for (var i=0; i<data_by_cols[columns[0]].length; i++) {
      var row=[];
      for (var j=0; j<columns.length; j++) {
        row[j] = data_by_cols[columns[j]][i];
      }
      data_by_rows[i+1] = row;
    }
        
    return data_by_rows;    
  }

  
  $(document).ready(function() {
    
    load_table(data);
    
  });

  table_rows_displayed = 100;  
    
  data[0] = getdata_columns;
  
  function load_table(loaded_data){ // only writing first 100 rows
    var local_data = loaded_data;
    
    var columns = local_data[0];
    
    var table_html = '<table><thead><tr><th>' + columns.join('</th><th>') + '</th></tr></thead><tbody>';
  
    for (var i=1; i<table_rows_displayed; i++) { // if you want the full length: local_data.length
      table_html += '<tr><td>' + local_data[i].join('</td><td>') + '</td></tr>';
    }
  
    table_html += '</tbody></table>';
    
    $("#Table_area").html(table_html);
    }
</script>

<link rel="stylesheet" type="text/css" href="GetDataStyle.css">
  <h1> Online Analyses </h1>
  <h2> Using javascript from jStat (https://github.com/jstat/jstat) </h2>
  <h3><input id="analysis_name" type="text" placeholder="name of analysis" value= "<?= $analysis_name?>" ></h3>
  
<script>
  analysis_name_old = $("#analysis_name").val();
  $("#analysis_name").on("change",function(){
    update_gui_script($("#gui_output_json").val(),$("#script_area_input").val(),analysis_name_old,$("#analysis_name").val(),$("#manuscript_edit").val());analysis_name_old = $("#analysis_name").val();
  });
  
</script>
  
  <script src = "//cdn.jsdelivr.net/jstat/latest/jstat.min.js" ></script>

<span id="left_col">
<span id="table_selector">  
  <input type="button" class="collectorButton data_script" value="Data-Selected">
  <input type="button" class="collectorButton data_script" value="Table">
  <input type="button" class="collectorButton data_script" value="Script">  
  <input type="button" class="collectorButton data_script" value="Notes">
  <span id="saving_area">No changes saved yet</span>
  
  <!--
    <input type="button" class="collectorButton data_script" value="Collect-R">
  -->
  </span>

  
<div id="table_script_area"> 
  <div class="script_table_select"  id="Data-Selected_area">Tyson and I will put code to allow the user to change data selected here. Maybe getData should be here anyway...? So this area will take up almost the whole screen ... ??? when selected</div>  
  <div class="script_table_select"  id="Table_area"></div>
  <div class="script_table_select"  id="Script_area">
    <h3>Script</h3>
    
    <textarea id="script_area_input" ><?=$js_script?></textarea>
    
    <script>
//      columns = Object.keys(data_by_cols);
      $("#script_area_input").on("keyup",function(){
        
        // code for making it easier to use the script
//        script_area_input.selectionStart
// 
      });
    </script>
    
    <input id="script_button_main" type="button" class = "collector" value="run script" onclick="report_script(script_area_input.value)">

  </div>

  <div class="script_table_select" id="Notes_area">
    <h3>Notes</h3>
    <span>
      <input type="button" class="collectorButton manscript_button" value="edit">
      <input type="button" class="collectorButton manscript_button" value="preview">     
    </span>
    <textarea id="manuscript_edit" class="manuscript_class"><?=$manuscript?></textarea>
    <div id="manuscript_preview" class="manuscript_class"></div>
  </div>
  
  <!--
  <div class="script_table_select" id="manuscript_area">
    this is where access to the Collect-R journal will go. This will be a journal with a wikipedia format... Exciting, right?
  </div>
  -->
  
</div>

<script>
  function report_script(input){
    console.dir(input);

    new Function (input)();    
    
    gui_output_area.innerHTML += "<br>" + input ;
  }
  
  $(".data_script").on("click", function(){
    $(".script_table_select").hide();
    $("#"+this.value+"_area").show();

  });
  
  $(".manscript_button").on("click", function(){
    $(".manuscript_class").hide();
    $("#manuscript_"+this.value).show();

  });
 
  
  
  $("#script_area").on("change",function(){
    update_gui_script($("#gui_output_json").val(),$("#script_area_input").val(),analysis_name_old,$("#analysis_name").val(),$("#manuscript_edit").val());
  });
  
  
  $("#manuscript_edit").on("change",function(){
    $("#manuscript_preview").html($("#manuscript_edit").val());
    update_gui_script($("#gui_output_json").val(),$("#script_area_input").val(),analysis_name_old,$("#analysis_name").val(),$("#manuscript_edit").val());
  });
  
  

</script>
 
  
  <br><br>
  
  
<span id="analysis_area">
  <span> 
      <input class="interface_button collectorButton" type="button" id="gui_interface_button"       value="GUI">
      <input class="interface_button collectorButton" type="button" id="custom_interface_button"    value="Custom">
      <input class="interface_button collectorButton" type="button" id="custom_interface_button"    value="Display">
  </span> 
  <div id="analysis_interface">
     
    <div class="interface" id="Display_interface">
      <h3>  Display </h3>
      <div> Rename variable 
        <select id="rename_list">
          <option>- Choose which variable you would like to RENAME</option>
        </select>
        <input type="text" id="variable_new_name">
        <input type="button" class="collectorButton" id="rename_variable_button" value="Rename">
      </div>
      <div> Remove variable 
        <select id="remove_list">
          <option>- Choose which variable you would like to REMOVE</option>
        </select>
        <input type="button" class="collectorButton" id="remove_variable_button" value="Remove">
      </div>
    </div>
    
    <script>
    
      
      function update_selects(columns_map){
        for(j=0; j<selects_to_populate.length;j++){
          $("#" +selects_to_populate[j]).empty();
          
          var x = document.getElementById(selects_to_populate[j]);
            
            var option = document.createElement("option");
            option.text = "-select an option-";
            x.add(option);
          
          for(i=0; i<columns_map.length; i++){
            var option = document.createElement("option");
            option.text = columns_map[i];
            x.add(option);    
          }        
        }      
      }
      
      
      $("#rename_variable_button").click(function(){

        // creating map in order to preserve order of columns
        columns_map = Object.keys(data_by_cols);
        columns_map[columns_map.indexOf($("#rename_list").val())] = $("#variable_new_name").val(); // renaming map
        
        data_by_cols[$("#variable_new_name").val()] = data_by_cols[$("#rename_list").val()];
        delete data_by_cols[$("#rename_list").val() ];
        
        //preserve columns order
        temp_object = {};
        for(i=0; i<columns_map.length;i++){
          temp_object[columns_map[i]]=data_by_cols[columns_map[i]];
        }
        data_by_cols= temp_object;
        
        data_by_rows = col_to_rows(data_by_cols,columns_map);
        update_selects(columns_map);
        load_table(data_by_rows);
      });
      
      $("#remove_variable_button").click(function(){

        delete data_by_cols[$("#remove_list").val() ];
        columns_map = Object.keys(data_by_cols);
              
        data_by_rows = col_to_rows(data_by_cols,columns_map);
        update_selects(columns_map);
        load_table(data_by_rows);
      });
      
    
    </script>
    
    <div class="interface" id="GUI_interface">
      
      <span>
        <input class="GUI_type_button collectorButton" type="button"    value="Descriptives">
        <input class="GUI_type_button collectorButton" type="button"    value="T-Tests">
        <input class="GUI_type_button collectorButton" type="button"    value="ANOVA">
        <input class="GUI_type_button collectorButton" type="button"    value="Regression">
        <input class="GUI_type_button collectorButton" type="button"    value="Frequencies">
        <input class="GUI_type_button collectorButton" type="button"    value="Table" title="To manipulate data in the table, add columns, etc.">
      </span>  

      <div id="Descriptives"  class="GUI_type">
      descriptives: <br>
      <select id="descriptive_variable">
        <option>select variable</option>
      </select>
      - update this to allow selection of multiple variables -
      
      <script>

      function clean_array (input_array){
        for(var i=0;i<input_array.length;i++){
          input_array[i]  = parseInt(input_array[i]);
        }
        return input_array;

      }

      
      // populate descriptive list
      
        var x = document.getElementById('descriptive_variable');
        for(i=0; i<getdata_columns.length; i++){
          var option = document.createElement("option");
          option.text = getdata_columns[i];
          x.add(option);    
        }    
        
        descriptive_attributes=["Number of responses","Empty responses","Mean","Stdev","SE","Maximum value","Minimum value"];
        
        descriptive_attributes_calculations=["no_present_in_array","no_empty_in_array","mean","sd","se","max","min"];
        
        for(i=0;i<descriptive_attributes.length; i++){
        
          var stats_scrpt = document.createElement('script');    
          stats_scrpt.src='Stats/'+descriptive_attributes_calculations[i]+'/stats.js';
          document.head.appendChild(stats_scrpt);
          
          
        }

        descriptive_no = -1;
        
        
        $("#descriptive_variable").change(function(){
          descriptive_no++;      
          data_by_cols[descriptive_variable.value] = clean_array(data_by_cols[descriptive_variable.value]);      
                
          // Find a <table> element with id="myTable":
          
          $("#gui_output_area").html($("#gui_output_area").html() + '<table id="descriptive_table'+descriptive_no+'"></table>' ); 
      
          
          var table = document.getElementById("descriptive_table"+descriptive_no);

          // clear table - not done
          

          // Create a header row
          var row = table.insertRow(0);
          
          cell0           = row.insertCell(0);
          cell0.innerHTML = "<b>Variable Name</b>";
          
          cell1           = row.insertCell(1);
          cell1.innerHTML = "<b>"+descriptive_variable.value+"</b>";
         

          for(i=0;i<descriptive_attributes.length; i++){
          
          
            var row = table.insertRow(i+1);
            this["cell"+0] = row.insertCell(0);
            this["cell"+0].innerHTML = descriptive_attributes[i];
            

            var function_name     = "calculate_"+descriptive_attributes_calculations[i];
            output_from_test  = window[function_name](data_by_cols[descriptive_variable.value]);

            this["cell"+1] = row.insertCell(1);
            this["cell"+1].innerHTML = output_from_test; 

          }
        });
      
      </script>
      
      </div>
      <div id="T-Tests"       class="GUI_type">
        <select id="t_test_selection">
          <option value="select">Select an option</option>
          <option value="independent">Independent Samples</option>
          <option value="paired">Paired Samples</option>
          <option value="one_sample">One-Sample</option>
        </select>
      </div>
      
      <div class="t_test_div" id="t_test_independent"><?php require ("Stats/t_test_independent/display.html"); ?></div>
      <div class="t_test_div" id="t_test_paired"><?php require ("Stats/t_test_paired/display.html"); ?></div>
      <div class="t_test_div" id="t_test_one_sample"><?php require ("Stats/t_test_one_sample/display.html"); ?></div>
      
      
      <script>
        //call js files for each t-test
        
        var t_tests=['t_test_independent','t_test_paired','t_test_one_sample'];
        
        for(i=0;i<t_tests.length;i++){
          var display_scrpt = document.createElement('script');    
          display_scrpt.src='Stats/'+t_tests[i]+'/display.js';
          document.head.appendChild(display_scrpt);

          var stats_scrpt = document.createElement('script');    
          stats_scrpt.src='Stats/'+t_tests[i]+'/stats.js';
          document.head.appendChild(stats_scrpt);        
        }
        
        $(".t_test_div").hide();
        $("#t_test_selection").change(function(){
          $(".t_test_div").hide();
          $("#t_test_"+$("#t_test_selection").val()).show();
        });
      </script>
      
      <div id="ANOVA"         class="GUI_type">
      ANOVA
      </div>
      <div id="Regression"    class="GUI_type">
      regression
      </div>
      <div id="Frequencies"   class="GUI_type">

      
      NEED TO EMBED SUMMARY FUNCTIONS in here!!!
      
      
      <script src="summaryFunctions.js"></script>
      
      </div>
      <div id="Table"  class="GUI_type">
        <span>
          <input type="button" class="column_row_select collectorButton" value="Columns">
          <input type="button" class="column_row_select collectorButton" value="Rows">
        </span>
        <div class="gui_table_div" id="table_Columns">
          New Column: 
            Name<input type="text" id="new_col_name">
            Formula:
              <select id="new_col_variable1">
              </select>
              <select id="new_col_computation_type">
                <option>+</option>
                <option>-</option>
                <option>*</option>
                <option>/</option>
              </select>
              <input type="number" id="new_col_number">
              
              <select id="new_col_variable2">                
              </select>
              <!-- <input type="text" placeholder="formula" id="new_col_formula"> -->
            <input type="button" class="collectorButton" value="Create" id="new_col_button">
        
        <script>
          
          $("#new_col_button").on("click", function(){
            // this all needs to be added to GUI script!!!
            
            var new_col_name = $("#new_col_name").val();
            
            //var formula      = $("#new_col_formula").val();
              
            var result = jStat(data_by_cols[$("#new_col_variable1").val()], function( x ) {
              if($("#new_col_variable2").val()!== "-select an option-"){
                
                var this_script='var_by_var = true;'+
                                'var variable_1_data = data_by_cols[$("#new_col_variable1").val()];'+
                                'var variable_2_data = data_by_cols[$("#new_col_variable2").val()];'


                
                var_by_var = true;
                
                var variable_1_data = data_by_cols[$("#new_col_variable1").val()];
                var variable_2_data = data_by_cols[$("#new_col_variable2").val()];
                
                
                switch ($("#new_col_computation_type").val()){
                  case "+":
                    script += 'return jStat([variable_1_data]).add([variable_2_data]);';
                    return jStat([variable_1_data]).add([variable_2_data]);

//                  return jStat([variable_1_data,variable_2_data]).sum();
                  break;
                  case "-":
                    script += 'return jStat([variable_1_data]).subtract([variable_2_data]);';

                    /// resume adding stats and output here! to submit to process_stats
                    
                    return jStat([variable_1_data]).subtract([variable_2_data]);
                  break;
                  case "*":
                    return jStat([variable_1_data]).multiply([variable_2_data]);

//                    return jStat([variable_1_data,variable_2_data]).product();
                  break;
                  case "/":
                    return jStat([variable_1_data]).divide([variable_2_data]);
                  
                  break;
                }
                
              } else {
                var_by_var = false;
                
                switch ($("#new_col_computation_type").val()){
                  case "+": 
                    return x + $("#new_col_number").val();
                  break;
                  case "-": 
                    return x - $("#new_col_number").val();
                  break;
                  case "*": 
                    return x * $("#new_col_number").val();
                  break;
                  case "/":
                    return x / $("#new_col_number").val();
                  break;
                  
                }              
              }
              
            });
            
            
            if(var_by_var == true){
              data_by_cols[$("#new_col_name").val()]=result[0][0][0];  
            } else {
              data_by_cols[$("#new_col_name").val()]=result[0];  
            }
//            data_by_cols[$("#new_col_name").val()]=data_by_cols[$("#new_col_name").val()][0];  // this shouldn't be necessary!!!
            
            columns_map = Object.keys(data_by_cols);
            data_by_rows = col_to_rows(data_by_cols,columns_map);
            update_selects(columns_map);
            load_table(data_by_rows); 
            
            
          });
  
          $(".gui_table_div").hide();
          $(".column_row_select").on("click",function(){
          
            $(".gui_table_div").hide();
            $("#table_"+this.value).show();
            
          });
          
        </script>
        
        
        
      </div>
        <div class="gui_table_div" id="table_Rows">
          remove outliers<br>
          Within participant<br>
          Between participant

        </div>        

      
    </div>
    
    <div class="interface" id="Custom_interface">
      <h3>Custom</h3>

      <input id="stats_select" type="text" placeholder="what stats test do you want to run?">
      <div id="ajax_stats">
        <!-- here's where display files will be presented !-->
      </div>


    </div>
    
  </div>


  <script>
    $(".interface").hide();
    $(".interface_button").click(function(){
      $(".interface").hide();
      $("#"+this.value+"_interface").show();
    });
    
    $(".GUI_type").hide();
    $(".GUI_type_button").click(function(){
      $(".GUI_type").hide();
      $("#"+this.value).show();
    });
  </script>
  
</span>

</span>
<span id="right_col">
  
  <h3> Output </h3>
  Which view of the output do you want?
  <br>
  output only<input type="radio" name="simple_script_view" value="output_only" checked>
  script only<input type="radio" name="simple_script_view" value="script_only" checked>
  show all (including javascript)<input type="radio" name="simple_script_view" value="show_all">
  <br>
  <div id="gui_output_area"></div>
  <textarea id="gui_output_json"><?=$gui_script?></textarea>
</span>
  <div> List of functions we want:
<br>allow users to add custom scripts. Needs HTML part and javascript component. 
<br>allow custom version of our analyses: and comparisons of whether the custom version is quicker. Test if they get the same results 1000 times. If so, then e-mail us to investigate implementing it   
<br>allow users to use script to rerun analysis (so it's bidirectional)
<br>create new columns in data - that are presented on the screen
<br>allow languages to be used, e.g. R syntax?
</div>

<script>

var returned_script;

$("#stats_select").on('input',function(){ // ajax in Stats tool
  var stats_selected = $("#stats_select").val().toLowerCase();
  if(stats_options.indexOf(stats_selected) !=-1){
    $.get(
      'Stats/'+stats_selected+'/display.html',
      {
        first: 'first param',
        second: 'second param'
      },
      function(returned_data) {
        $("#ajax_stats").html(returned_data);
        
      }
    );
    
    var display_scrpt = document.createElement('script');    
    display_scrpt.src='Stats/'+stats_selected+'/display.js';
    document.head.appendChild(display_scrpt);
    
    var stats_scrpt = document.createElement('script');    
    stats_scrpt.src='Stats/'+stats_selected+'/stats.js';
    document.head.appendChild(stats_scrpt);



  }
});
</script>

<?php


  if(file_exists("blah.txt")){
    $gui_script = file_get_contents("blah.txt");
    $gui_script = str_ireplace('"','\"',$gui_script);
  } 
    
  
?>

<script>
  // ajax save

  // try to load script first
  
  var current_file_location = 'blah.txt';
  
  var original_content = "<?= $gui_script?>";
  
  gui_output_area.innerHTML = original_content;
  

  
  function update_gui_script(gui_script_content,js_script_content,old_analysis_name,new_analysis_name,manuscript){

    $.get(
      'AjaxSave.php',
      { file                : current_file_location,
        gui_content         : gui_script_content,
        js_content          : js_script_content,
        old_name            : old_analysis_name,
        new_name            : new_analysis_name,
        manuscript_content  : manuscript
      } , 
      function(returned_data) {
        $("#saving_area").html("GUI Script and Output Saved");  
      }
    );
  
  }
  
  // if not available create one
  
  
  
  script_output_graph_line  =-1;
  
  gui_script_json = {};
  
  function process_stats(script,output,graph){
    script_output_graph_line++;
    // script processing
    
    json_script={};  
    json_script['script']=script;  
    console.dir(script);
    console.dir(json_script['script']);
    json_script['output']=output;  
    json_script['graph']=graph;  
    gui_script_json[script_output_graph_line]=json_script;
    $("#gui_output_json").val(JSON.stringify(gui_script_json));
    
    script =  "<div class='script_line' id='script_line_"+script_output_graph_line+"'>"+script+"<br>"+
              "<input type='button' value='inject into:' class='collectorButton' onclick='inject_script(\"script_line_"+script_output_graph_line+"\")'><br>" +
              "[detect which script and line - if neither, replace button with (need to have a script open to inject]</div>"; // need it read which scripts there are
    
    // output processing

    output =  "<span class='output_line' id='output_line_"+script_output_graph_line+"'>"+output+"</span><br>";
             
    // graph processing
    
    if (typeof graph == "undefined"){
      graph = '[No code submitted for figure]';
    }    

    var comment_area         = "<br><input type='button' id='user_comment_"+script_output_graph_line+"_button' value='Comment' onclick='show_comment(\""+script_output_graph_line+"\")'>"+
                                "<textarea class='user_comment' style='display:none' id='user_comment_"+script_output_graph_line+"'></textarea>";

    gui_output_area.innerHTML += "<hr></hr>" +script+output+graph+comment_area;     
    //var this_content = gui_output_area.innerHTML;
    update_gui_script($("#gui_output_json").val(),$("#script_area_input").val(),analysis_name_old,$("#analysis_name").val(),$("#manuscript_edit").val());
  }

  function show_comment(comment_no){
    $("#user_comment_"+comment_no).show();
    $("#user_comment_"+comment_no+"_button").hide();
    
  }
  
  function inject_script(this_script_line){
    alert(this_script_line);
  }

      var selects_to_populate = ['rename_list','remove_list','new_col_variable1','new_col_variable2'];
      update_selects(getdata_columns);

  
</script>
