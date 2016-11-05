<span id="Descriptives_area"  class="GUI_type">
    
  <script>
  
  var columns = Object.keys(data_by_columns);
  descriptive_html ='';
  for(i=0;i<columns.length;i++){
    descriptive_html+="<div><label>"+columns[i]+"<input type='checkbox' name='columns_descriptive' value='"+columns[i]+"' </label></div>";
  }
  
  $("#Descriptives_area").html(descriptive_html);
  
  </script>
  
  <button type="button" id="descriptive_button" class="collectorButton">Do descriptive!</button>
  
  <script>
  
  var descriptive_no=0;
  
  script_array=[];
  
  function add_to_script(script_no){
    if($("#javascript_script").val()==''){
      new_line='';
    } else {
      new_line='\n';
    }
    var new_script = $("#javascript_script").val() + new_line + script_array[script_no];
    $("#javascript_script").val(new_script);
  }
  
  function create_descriptive_output (selected_columns){
    
    descriptive_no++;
    selected_array = "[\'";
    for(i=0; i<selected_columns.length;i++){
      selected_array = selected_array + selected_columns[i] +"\',\'";
    }
    selected_array = selected_array + "\']";
    
  
    var this_script = "selected_array = " + selected_array + ";\n"+
                  "create_descriptive_output(selected_array);\n";
                  
    script_array.push(this_script);              
                  
                  
    new_content_for_output_area = // $("#output_area").html() +
    
    '<div id="'+        
    'descriptive_table'+descriptive_no+"_div"+        
    '"><table id="descriptive_table'+descriptive_no+'"></table>' +
      "<button type='button' id='descriptive_table_"+descriptive_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
      "<button type='button' onclick='remove_from_output(\"descriptive_table"+descriptive_no+"_div\")'>Remove from output</button>"+
      "<hr style='background-color:black'></hr></div>";

  $("#output_area").append(new_content_for_output_area);

    
    var table = document.getElementById("descriptive_table"+descriptive_no);

    $("#descriptive_table"+descriptive_no).addClass("descriptive_tables_class");

      // Create a header row
      
      table_cells_array=[];

      var row = table.insertRow(0);
      
        table_cells_array[0]           = row.insertCell(0);
        table_cells_array[0].innerHTML = "<b>Column Name</b>";          

      for(i=0;i<descriptive_attributes.length;i++){
        
        table_cells_array[i+1]           = row.insertCell(1);
        table_cells_array[i+1].innerHTML = "<b>"+descriptive_attributes[i]+"</b>";
        
      }
     
      var row = table.insertRow(1);
      
      for(i=0; i<selected_columns.length;i++){
        
        table_cells_array[0]           = row.insertCell(0);
        table_cells_array[0].innerHTML = selected_columns[i];
        
        for(j=0; j<descriptive_attributes_calculations.length;j++){

          var function_name     = "calculate_"+descriptive_attributes_calculations[j];
          output_from_test  = window[function_name](data_by_columns[selected_columns[i]]);
        
          this["cell"+1] = row.insertCell(1);
          this["cell"+1].innerHTML = (output_from_test).toFixed(3);
          
        }
        var row = table.insertRow(i+2);
        
      }
  
  }
  
  $("#descriptive_button").on("click",function(){
    
      var selected_columns=[];

      $("input[name='columns_descriptive']:checked").each(function(){
        selected_columns.push(this.value);
      });

      create_descriptive_output (selected_columns);
    
  });
  
  function clean_array (input_array){
    for(var i=0;i<input_array.length;i++){
      input_array[i]  = parseFloat(input_array[i]);
    }
    return input_array;
  }
  
  descriptive_attributes=["Number of responses","Empty responses","Mean","Stdev","SE","Maximum value","Minimum value"];
  
  descriptive_attributes_calculations=["no_present_in_array","no_empty_in_array","mean","sd","se","max","min"];

  for(i=0;i<descriptive_attributes_calculations.length; i++){
  
    load_script('Stats/'+descriptive_attributes_calculations[i]+'/stats.js');
    
  }
  
  </script>

</span>