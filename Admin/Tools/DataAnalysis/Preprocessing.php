 <div id="Preprocessing_area"  class="GUI_type">
    <span>
      <input type="button" class="column_row_select collectorButton" value="Columns">
      <input type="button" class="column_row_select collectorButton" value="Rows">
    </span>
    <div class="gui_table_div" id="table_Columns">
            <div id="Display_interface">
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
      
      
      columns_map = Object.keys(data_by_columns);
      selects_to_populate=['rename_list','remove_list'];
      update_selects(columns_map);
    
      
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
      
      function rename_variable(data_by_columns,new_var_name,old_var_name){
        
        columns_map = Object.keys(data_by_columns);
        columns_map[columns_map.indexOf(old_var_name)] = new_var_name; // renaming map
        
        data_by_columns[new_var_name] = data_by_columns[old_var_name];
        delete data_by_columns[old_var_name];
        
        //preserve columns order
        temp_object = {};
        for(i=0; i<columns_map.length;i++){
          temp_object[columns_map[i]]=data_by_columns[columns_map[i]];
        }
        data_by_columns= temp_object;
        
        
        
        var script = "rename_variable(data_by_columns,'"+new_var_name+"','"+old_var_name+"');";
        script_array[script_array.length]=script;
        
        
        if(typeof rename_var_no == "undefined"){
          rename_var_no=0;
        } else {
          rename_var_no++;
        }
        
        var container = $("<div>");
        var id="rename_var"+rename_var_no;
        container.attr('id',id);
        
        container.html("Varible <b>"+old_var_name+"</b> renamed to <b>"+new_var_name+"</b><button type='button' id='rename_var_no"+rename_var_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
      "<button type='button' onclick='remove_from_output(\"rename_var"+rename_var_no+"\")'>Remove from output</button>"+
      "<hr style='background-color:black'></hr>");
        
        container.appendTo("#output_area");
        
        
      }  
      
      $("#rename_variable_button").click(function(){
        var new_var_name = $("#variable_new_name").val();
        var old_var_name = $("#rename_list").val();
        // creating map in order to preserve order of columns
        rename_variable(data_by_columns,new_var_name,old_var_name);
        
        

        //data_by_rows = col_to_rows(data_by_columns,columns_map);
        //update_selects(columns_map);
        //load_table(data_by_rows);
      });
      
      $("#remove_variable_button").click(function(){

        delete data_by_columns[$("#remove_list").val() ];
        columns_map = Object.keys(data_by_columns);
              
        /* data_by_rows = col_to_rows(data_by_columns,columns_map);
        update_selects(columns_map);
        load_table(data_by_rows); */
      });
      
    
    </script>

  <h3>New Column</h3>

      Name<input type="text" id="new_col_name">
      <br>
      Variable 1:
        <select id="new_col_variable1">
        </select>
        <br>
        <select id="new_col_computation_type">
          <option>+</option>
          <option>-</option>
          <option>*</option>
          <option>/</option>
        </select>
        <br>
        <input type="button" value="Number" id="new_col_number_button">
        <input type="button" value="Column" id="new_col_variable2_button">

        <input type="number" id="new_col_number" style="width:100px" class="number_variable_2">
        
        <select id="new_col_variable2" class="number_variable_2">                
        </select>
        <!-- <input type="text" placeholder="formula" id="new_col_formula"> -->
        <br>
      <input type="button" class="collectorButton" value="Create" id="new_col_button">

    <script>
    
    selects_to_populate.push('new_col_variable1');
    selects_to_populate.push('new_col_variable2');
    
    update_selects(columns_map);
          
    $(".number_variable_2").hide();
    $("#new_col_number_button").on("click",function(){
      $("#new_col_variable2").val("-select an option-");
      
      $("#new_col_variable2").hide(500);
      
      $("#new_col_number").show(500);
      
    });
    $("#new_col_variable2_button").on("click",function(){

      $("#new_col_variable2").show(500);
      $("#new_col_number").hide(500);
      
      
    });
    
    function create_new_variable (new_col_name,original_variable,operator,second_input,variable_or_number){
      
      var script    = "create_new_variable('"+new_col_name+"','"+original_variable+"','"+operator+"','"+second_input+"','"+variable_or_number+"')";
      script_array[script_array.length]=script;
      
      var result = jStat(data_by_columns[original_variable], function( x ) {
        if(variable_or_number == "variable"){
          var_by_var = true;
          var variable_1_data = data_by_columns[original_variable];
          var variable_2_data = data_by_columns[second_input];

          switch (operator){
            case "+":
              return jStat([variable_1_data]).add([variable_2_data]);
            break;
            
            case "-":
              return jStat([variable_1_data]).subtract([variable_2_data]);
            break;
            
            case "*":
              return jStat([variable_1_data]).multiply([variable_2_data]);
            break;

            case "/":
              return jStat([variable_1_data]).divide([variable_2_data])
            break;
          }
        } else {
          var_by_var = false;
          
          switch (operator){
            case "+": 
              temp_var = x;
              return x + parseFloat(second_input);
            break;
            case "-": 
              return x - parseFloat(second_input);
            break;
            case "*": 
              return x * parseFloat(second_input);
            break;
            case "/":
              return x / parseFloat(second_input);
            break;            
          }              
        }
      });
      

      if(typeof new_variable_no == "undefined"){
        new_variable_no=0;
      } else {
        new_variable_no++;
      }
        
      var container = $("<div>");
      var id="new_variable"+new_variable_no;
      container.attr('id',id);
      
      container.html("Varible <b>"+new_col_name+"</b> created<button type='button' id='new_variable_no"+new_variable_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
      "<button type='button' onclick='remove_from_output(\"new_variable"+new_variable_no+"\")'>Remove from output</button>"+
      "<hr style='background-color:black'></hr>");
        
      container.appendTo("#output_area");
      
      
      if(var_by_var == true){
        data_by_columns[new_col_name]=result[0][0][0];  
      } else {
        data_by_columns[new_col_name]=result[0];  
      }
      
/*       columns_map = Object.keys(data_by_columns);
      data_by_rows = col_to_rows(data_by_columns,columns_map);
      update_selects(columns_map);
      load_table(data_by_rows); 
 */      
      
      
    }
    
    $("#new_col_button").on("click", function(){
      // this all needs to be added to GUI script!!!
      
      var new_col_name      = $("#new_col_name").val();
      var original_variable = $("#new_col_variable1").val();
      var operator          = $("#new_col_computation_type").val();
      var second_input;
      var variable_or_number;
      if($("#new_col_variable2").val()!== "-select an option-"){
        second_input         = $("#new_col_variable2").val();
        variable_or_number  = "variable";
      } else {
        second_input         = $("#new_col_number").val();
        variable_or_number  = "number";
      }
      
      
      create_new_variable(new_col_name,original_variable,operator,second_input,variable_or_number);
      
      //var formula      = $("#new_col_formula").val();
      
      
    });
  
          $(".gui_table_div").hide();
          $(".column_row_select").on("click",function(){
          
            $(".gui_table_div").hide();
            $("#table_"+this.value).show();
            
          });
          
        </script>
        
        
        
      </div>
        <div class="gui_table_div" id="table_Rows">



        
          Remove empty rows with empty cells in <select id="emptying_column"></select>
          <input type="button" value="Remove" id="remove_empty_button" class="collectorButton">
          
          <script>
            $("#remove_empty_button").on("click", function(){
              
              emptying_array = data_by_columns[$("#emptying_column").val()];
              
              empty_rows = [];
              for(i =0; i<emptying_array.length; i++){
                //console.dir(i);
                if(emptying_array[i] == ""){
                  console.dir("empty");
                  empty_rows[empty_rows.length]=i;
                }
              }
              
              empty_rows = empty_rows.reverse(); //to make it easier to loop through
              
              columns_to_loop = Object.keys(data_by_columns);
              
              for(j = 0; j<columns_to_loop.length; j++){
                for(i = 0; i<empty_rows.length; i++){                              
                  data_by_columns[columns_to_loop[j]].splice(empty_rows[i],1);
                }
              }

              var this_script = 'emptying_array = data_by_columns['+$("#emptying_column").val()+'];          '+              
                                'empty_rows = [];                                                         '+
                                'for(i =0; i<emptying_array.length; i++){                                 '+
                                '  if(emptying_array[i] == ""){                                           '+
                                '    console.dir("empty");                                                '+
                                '    empty_rows[empty_rows.length]=i;                                     '+
                                '  }                                                                      '+
                                '}                                                                        '+
                                '                                                                         '+
                                'empty_rows = empty_rows.reverse(); //to make it easier to loop through   '+
                                '                                                                         '+
                                'columns_to_loop = Object.keys(data_by_columns);                             '+
                                '                                                                         '+
                                'for(j = 0; j<columns_to_loop.length; j++){                               '+
                                '  for(i = 0; i<empty_rows.length; i++){                                  '+
                                '    data_by_columns[columns_to_loop[j]].splice(empty_rows[i],1);            '+
                                '  }                                                                      '+
                                '}';
              
              
              var this_output = 'clearing empty rows based on '+$("#emptying_column").val()+' column';
              var this_graph  = '';
              
              process_stats(this_script,this_output,this_graph);
              
              data_by_rows = col_to_rows(data_by_columns,columns_to_loop);
              load_table(data_by_rows);
              
            });
          </script>
          
          <br>

            <h3>Remove Outliers</h3>
          
            Participant Column <select id="participant_column"></select> (must select before you can remove outliers)
          
          
          <div id="participant_dependent_outlier_removal">

            
            Outside of <input type="number" id="outlier_between_SDs" style="width:80px" value="3">SDs in :<select id="between_outlier_variable" class="variable_select"></select>
            <input type="button" value="Between" id="outlier_between_button" class="collectorButton">
            <input type="button" value="Within" id="outlier_within_button" class="collectorButton">
            Participants

            <br><br>
            Reduce Data to 
            <input type="button" value="Means" id="reduce_to_means">
            <input type="button" value="Medians" id="reduce_to_medians">
            by participant

            <br><br>
            
            
          </div>
          <script>
          
            $("#participant_dependent_outlier_removal").hide();
            
            $("#participant_column").on("change",function(){
              if($("#participant_column").val() !== "blah"){
                $("#participant_dependent_outlier_removal").show(500);
              }
            })
            
            function onlyUnique(value, index, self) { // by TLindig on http://stackoverflow.com/questions/1960473/unique-values-in-an-array
              return self.indexOf(value) === index;
            }
            
            
            $("#outlier_within_button").on("click",function(){
              var participant_column_array = data_by_columns[$("#participant_column").val()];

              var unique_participant_column_array = participant_column_array.filter(onlyUnique);
              
              // don't do outlier removal on participant data which has less than three values
              
              if(participant_column_array.length == unique_participant_column_array.length){
                alert ("Only one value per participant - <b>Within</b> participant outlier removal is impossible. You may want to run <b>between</b> participant outlier removal?");
              } else {
                                
                var within_pp_candidates = { }; // based on fcalderan's solution on http://stackoverflow.com/questions/11649255/how-to-count-the-number-of-occurences-of-each-item-in-an-array
                for (var i = 0, j = participant_column_array.length; i < j; i++) {
                  within_pp_candidates[participant_column_array[i]] = (within_pp_candidates[participant_column_array[i]] || 0) + 1;
                }
                
                var within_pp_to_process = [];
                for (var key in within_pp_candidates) { //based on levik's solution on http://stackoverflow.com/questions/684672/how-do-i-loop-through-or-enumerate-a-javascript-object
                  if (within_pp_candidates.hasOwnProperty(key)) {
                    if(within_pp_candidates[key]>2){
                      within_pp_to_process[within_pp_to_process.length]=key;
                    };
                  }
                }
                
                //now let's loop through the data that needs to have outlier removal
                
                
                var user_upper_lower_outlier_limit = {};
                
                for(i=0; i<within_pp_to_process.length;i++){
                  var rows_to_compare=[]; // to create index
                  for(j=0;j<participant_column_array.length;j++){
                    if(within_pp_to_process[i]==participant_column_array[j]){
                      rows_to_compare[rows_to_compare.length]=j;
                    }
                  }
                  var within_subject_outlier_data = [];
                  var outlier_sds = $("#outlier_between_SDs").val();
                  var outlier_data_col = $("#between_outlier_variable").val();
                  
                  for(j=0;j<rows_to_compare.length;j++){
                    within_subject_outlier_data[within_subject_outlier_data.length]=parseFloat(data_by_columns[outlier_data_col][rows_to_compare[i]]);
                  }
                  var upper_limit = jStat.mean(within_subject_outlier_data) + outlier_sds * jStat.stdev(within_subject_outlier_data);
                  var lower_limit = jStat.mean(within_subject_outlier_data) - outlier_sds * jStat.stdev(within_subject_outlier_data);
                  user_upper_lower_outlier_limit[within_pp_to_process[i]]= {  upper_limit:upper_limit,
                                                                              lower_limit:lower_limit};
                  
                }
                
                //now identify which are outliers, and their location!
                
                var outlier_rows = [];

                for(i=0;i<participant_column_array.length;i++){
                  for (var key in user_upper_lower_outlier_limit) { 
                    if(typeof participant_column_array[user_upper_lower_outlier_limit[key]] !== "undefined"){
                      var row_outlier_parameters =  participant_column_array[user_upper_lower_outlier_limit[key]];
                      if( parseFloat(data_by_columns[$("#between_outlier_variable").val()][i])>row_outlier_parameters['upper_limit'] |
                          parseFloat(data_by_columns[$("#between_outlier_variable").val()][i])<row_outlier_parameters['lower_limit']){
                            outlier_rows[outlier_rows.length]=i;
                          }
                    }
                  }
                }
                  
                
                

                //console.log(within_pp_candidates);
              
                //loop through candidates
                
                
              
              
              }
              
              // under developement
              
              
            });
            
            $("#outlier_between_button").on("click",function(){
              var participant_column_array = data_by_columns[$("#participant_column").val()];
            
              var unique_participant_column_array = participant_column_array.filter(onlyUnique);
              
              if(participant_column_array.length != unique_participant_column_array.length){
                alert ("Participant rows have more than one value - please reduce rows by means or medians within participant data");
              } else {
                
                var this_data = data_by_columns[$("#between_outlier_variable").val()];
                                
                for(i=0;i<this_data.length; i++){
                  
                  this_data[i]=parseFloat(this_data[i]);
                  
                }
                
                var group_mean  = jStat.mean(this_data);
                var group_SD    = jStat.stdev(this_data);

                  outlier_rows = [];
                  for(i =0; i<this_data.length; i++){
                    //console.dir(i);
                    if(Math.abs(this_data[i] - group_mean) > 3*group_SD){
                      console.dir("outlier");
                      outlier_rows[outlier_rows.length]=i;
                    }
                  }
                  
                  outlier_rows = outlier_rows.reverse(); //to make it easier to loop through
                  
                  columns_to_loop = Object.keys(data_by_columns);
                  
                  for(j = 0; j<columns_to_loop.length; j++){
                    for(i = 0; i<outlier_rows.length; i++){
                                  
                      data_by_columns[columns_to_loop[j]].splice(outlier_rows[i],1);
                    }
                  }

              var this_script = 'var this_data = data_by_columns['+$("#between_outlier_variable").val()+'];          '+
                                '                                                                                 '+
                                'for(i=0;i<this_data.length; i++){                                                '+
                                '                                                                                 '+
                                '  this_data[i]=parseFloat(this_data[i]);                                         '+
                                '                                                                                 '+
                                '}                                                                                '+
                                '                                                                                 '+
                                'var group_mean  = jStat.mean(this_data);                                         '+
                                'var group_SD    = jStat.stdev(this_data);                                        '+
                                '                                                                                 '+
                                '  outlier_rows = [];                                                             '+
                                '  for(i =0; i<this_data.length; i++){                                            '+
                                '    //console.dir(i);                                                            '+
                                '    if(Math.abs(this_data[i] - group_mean) > 3*group_SD){                        '+
                                '      console.dir("outlier");                                                    '+
                                '      outlier_rows[outlier_rows.length]=i;                                       '+
                                '    }                                                                            '+
                                '  }                                                                              '+
                                '                                                                                 '+
                                '  outlier_rows = outlier_rows.reverse(); //to make it easier to loop through     '+
                                '                                                                                 '+
                                '  columns_to_loop = Object.keys(data_by_columns);                                   '+
                                '                                                                                 '+
                                '  for(j = 0; j<columns_to_loop.length; j++){                                     '+
                                '    for(i = 0; i<outlier_rows.length; i++){                                      '+
                                '                                                                                 '+
                                '      data_by_columns[columns_to_loop[j]].splice(outlier_rows[i],1);                '+
                                '    }                                                                           ';
                               
                                
              
              var this_output = 'clearing outliers rows based on '+$("#between_outlier_variable").val()+' column';
              var this_graph  = '';
              
              process_stats(this_script,this_output,this_graph);

                  
                  
                  
              
                data_by_rows = col_to_rows(data_by_columns,columns_to_loop);
                load_table(data_by_rows);                
              
              }
            
            });
            
            // detect whether there are repetitions within the participant column
            
            
          </script>
          
          <br><br>
        
        </div>        
      </div>
