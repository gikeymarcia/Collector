  var x = document.getElementById('lone_variable');
  for(i=0; i<getdata_columns.length; i++){
    var option = document.createElement("option");
    option.text = getdata_columns[i];
    x.add(option);    
  }    
  
  if(selects_to_populate.indexOf('lone_variable') == -1){
    selects_to_populate[selects_to_populate.length]='lone_variable';
  }

  function report_t_test_one_sample(input_variable,baseline){
  
    var script    = "var sum_array = data_by_cols["+input_variable+"]; //extracting the array <br>" +
                    "t_test_results = calculate_t_test_one_sample(sum_array,"+baseline+");";
  
    var sum_array = data_by_cols[input_variable];
    t_test_results = calculate_t_test_one_sample(sum_array,baseline);
    var output = "<br> t_test_one_sample("+ input_variable +") <br> t("+ t_test_results[1] +") = " + t_test_results[0] +
                            ", p = "+t_test_results[2];
    
    var graph='[ figure not coded yet!]';
    
    process_stats(script,output,graph);
    
  }
  
  
