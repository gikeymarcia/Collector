  var jsInputs = ['paired_variable_1','paired_variable_2'];

  Columns=Object.keys(data_by_columns);
  
  for (j=0;j<jsInputs.length;j++){
    var x = document.getElementById(jsInputs[j]);
    for(i=0; i<Columns.length; i++){
      var option = document.createElement("option");
      option.text = Columns[i];
      x.add(option);    
    }    
  }

  
  function report_t_test_paired(input_variable1,input_variable2,paired_dv_units){
    var input_array1  = data_by_columns[input_variable1];
    var input_array2  = data_by_columns[input_variable2];
    
    var script    = "report_t_test_paired('"+input_variable1+"','"+input_variable2+"','"+paired_dv_units+"')";
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
                  
        
    if(typeof paired_sample_ttest_no == "undefined"){
      paired_sample_ttest_no=0;
    } else {
      paired_sample_ttest_no++;
    }
    
    
    
    
      anObjectName_1 = input_variable1;
      this[anObjectName_1] = {"height":t_test_results[3],
                            "error" :t_test_results[4]}

      anObjectName_2 = input_variable2;
      this[anObjectName_2] = {"height":t_test_results[5],
                            "error" :t_test_results[6]}

      
      col_data = {};
      col_data[anObjectName_1]=this[anObjectName_1];
      col_data[anObjectName_2]=this[anObjectName_2];
      col_data=JSON.stringify(col_data);
                         
      var y_axis = paired_dv_units;
    
      var container = $("<div>");
      var id = 'paired_sample_ttest' + paired_sample_ttest_no;
      
      container.attr('id', id);
            
      container.html( output+"<br><div class='graphArea'></div><br><div class='histArea1'>Histogram 1</div><br><div class='histArea2'>Histogram 2</div><br><button type='button' id='paired_sample_ttest"+paired_sample_ttest_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
      "<button type='button' onclick='remove_from_output(\"paired_sample_ttest"+paired_sample_ttest_no+"\")'>Remove from output</button>"+
      "<hr style='background-color:black'></hr>");     
    
      container.appendTo("#output_area");
      var image_url;
      
      var these_results = {
        p_value : t_test_results[2],
        t_value : t_test_results[0],
        df      : t_test_results[1],
        
      }
      
      $.post(
        'barGenerate.php',
        {
            data: col_data,
            yAxis: y_axis
        },
        function(img_url) {
            if (img_url.substring(0, 5) === 'Error') {
              container.find(".graphArea").html(img_url);
                
            } else {
              container.find(".graphArea").html('<img src="' + img_url + '">');

            }
            window.image_url = img_url;
            these_results['t_test_plot'] = window.image_url;
        },
        'text'
      );
        
       

      var plot_names      = ["hist1","hist2"];
      var histogram_divs  = [".histArea1",".histArea2"];
      var sum_arrays      = [input_array1,input_array2];
      
      create_histogram(sum_arrays,histogram_divs,container,plot_names,these_results);
    
      store_results(these_results);
      
    /*
    
    new_content_for_output_area = 
    
    '<div id="'+        
    'paired_sample_ttest'+paired_sample_ttest_no+"_div"+        
    '">'+output+"<br>"+graph+    
    "<br><button type='button' id='paired_sample_ttest"+paired_sample_ttest_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
      "<button type='button' onclick='remove_from_output(\"paired_sample_ttest"+paired_sample_ttest_no+"_div\")'>Remove from output</button>"+
      "<hr style='background-color:black'></hr></div>";
    
    $("#output_area").append(new_content_for_output_area); */
    
  }