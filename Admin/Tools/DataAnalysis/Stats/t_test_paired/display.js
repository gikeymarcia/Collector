  var jsInputs = ['variable_1','variable_2'];

  Columns=Object.keys(data_by_columns);
  
  for (j=0;j<jsInputs.length;j++){
    var x = document.getElementById(jsInputs[j]);
    for(i=0; i<Columns.length; i++){
      var option = document.createElement("option");
      option.text = Columns[i];
      x.add(option);    
    }    
  }

  
  function report_t_test_paired(input_variable1,input_variable2){
    var input_array1  = data_by_columns[input_variable1];
    var input_array2  = data_by_columns[input_variable2];
    
    var script    = "report_t_test_paired('"+input_variable1+"','"+input_variable2+"')";
    script_array[script_array.length]=script;
 
    t_test_results = calculate_t_test_paired(input_array1,input_array2);
  
    bold_p_value(t_test_results[2]);
    
    var output =  "<br>" +script +
                  "<br> t("+ t_test_results[1] +") = " + 
                  t_test_results[0] +
                  ", p = "+bold_sig_on+t_test_results[2]+bold_sig_off+ " (2-tailed)"+
                  // descriptives
                  "<br> group 1 mean = "+ jStat.mean(input_array1) +"; "+
                  "sd = "+ jStat.stdev(input_array1) +
                  "<br> group 2 mean = "+ jStat.mean(input_array2) +"; "+
                  "sd = "+ jStat.stdev(input_array2);
                  
    
    var graph='[ figure not coded yet!]';
        
    if(typeof paired_sample_ttest_no == "undefined"){
      paired_sample_ttest_no=0;
    } else {
      paired_sample_ttest_no++;
    }
    
    new_content_for_output_area = 
    
    '<div id="'+        
    'paired_sample_ttest'+paired_sample_ttest_no+"_div"+        
    '">'+output+"<br>"+graph+    
    "<br><button type='button' id='paired_sample_ttest"+paired_sample_ttest_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
      "<button type='button' onclick='remove_from_output(\"paired_sample_ttest"+paired_sample_ttest_no+"_div\")'>Remove from output</button>"+
      "<hr style='background-color:black'></hr></div>";
    
    $("#output_area").append(new_content_for_output_area);
    
  }