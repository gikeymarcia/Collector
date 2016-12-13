<span id="Regressions_area"  class="GUI_type">

  <div id="Regressions">
    <h2> Correlations </h2>
  
    <h3>Variables</h3>
    
    <div id="correlation_variables"></div>
    
    <h3>Coefficients</h3>
    <label>Pearson's R<input type="checkbox" value="Pearson_R" checked name="coefficients_correlation"></label><br>
    <label>Spearman's Rho<input type="checkbox" value="Spearman" name="coefficients_correlation"></label><br>
    <label>Kendall's Tau-B<input type="checkbox" value="Kendall" name="coefficients_correlation"></label><br>
   
    <input id="correlation_button" type="button" class="collectorButton" value="Correlate!">
  </div>

  <script>
    // populate variables
    column_variables = Object.keys(data_by_columns);
    variables_list = '';
    for(i=0;i<column_variables.length; i++){
      console.dir(column_variables[i]);
      variables_list += "<label>"+column_variables[i]+"<input type='checkbox' value='"+column_variables[i]+"' name='variables_correlation'></label><br>";
    }
    $("#correlation_variables").html(variables_list);
    
    
    coefficients=['Pearson_R'];// Spearman, Kendall
    
    for(i=0;i<coefficients.length;i++){
      scrpt = document.createElement('script');
      scrpt.src='Stats/'+coefficients[i]+'/stats.js';
      document.head.appendChild(scrpt);
    }
    
    correlation_no = -1;
    
    function create_correlational_output (selected_columns,selected_coefficients){
    
      correlation_no++;
    
      selected_array = "[\'";
        
      for(i=0; i<selected_columns.length;i++){
        selected_array = selected_array + selected_columns[i] +"\',\'";
      }
      selected_array = selected_array + "\']";
    
      
      var this_script = "selected_array = " + selected_array + ";\n"+
                    "create_correlational_output(selected_array);\n";
                    
      script_array.push(this_script);              
                    
                    
      new_content_for_output_area = // $("#output_area").html() +
      
      '<div id="'+        
      'correlation_table'+correlation_no+"_div"+        
      '"><table id="correlation_table'+correlation_no+'"></table>' +
        "<button type='button' id='descriptive_table_"+correlation_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
        "<button type='button' onclick='remove_from_output(\"correlation_table"+correlation_no+"_div\")'>Remove from output</button>"+
        "<hr style='background-color:black'></hr></div>";

      $("#output_area").append(new_content_for_output_area);

      
      var table = document.getElementById("correlation_table"+correlation_no);

      $("#correlation_table"+correlation_no).addClass("correlation_tables_class");

        // Create a header row
        
        table_cells_array=[];

        var row = table.insertRow(0);
        
          table_cells_array[0]           = row.insertCell(0);
          table_cells_array[0].innerHTML = "<b>Column Name</b>";          

        for(i=0;i<selected_columns.length;i++){
          
          table_cells_array[i+1]           = row.insertCell(1);
          table_cells_array[i+1].innerHTML = "<b>"+selected_columns[i]+"</b>";
          
        }
       
       var row = table.insertRow(1);
              
        for(i=0; i<selected_columns.length;i++){
          
          table_cells_array[0]           = row.insertCell(0);
          table_cells_array[0].innerHTML = selected_columns[i];
          
          for(j=0; j<selected_columns.length;j++){
            for(k=0;k<selected_coefficients.length;k++){
              if(data_by_columns[selected_columns[i]]==data_by_columns[selected_columns[j]]){
                report_from_test="";
              } else {
                output_from_test = calculate_Pearson_R(data_by_columns[selected_columns[i]],data_by_columns[selected_columns[j]]);
                report_from_test =  "<em>Pearson R</em><br>"+
                                    "r("+output_from_test[1]+") = "+
                                       output_from_test[0] +
                                    "; p="+output_from_test[2];
              }
              this["cell"+1] = row.insertCell(1);
              this["cell"+1].innerHTML = (report_from_test);
            }
        }
        var row = table.insertRow(i+2);
      } 
    }
  
  $("#correlation_button").on("click",function(){
    
      var selected_columns=[];

      $("input[name='variables_correlation']:checked").each(function(){
        selected_columns.push(this.value);
      });
      
      var selected_coefficients=[];

      $("input[name='coefficients_correlation']:checked").each(function(){
        selected_coefficients.push(this.value);
      });
    
      create_correlational_output(selected_columns,selected_coefficients);
  });
    
    
    
    
  </script>

</span>