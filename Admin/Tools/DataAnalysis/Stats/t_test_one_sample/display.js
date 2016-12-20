  var x = document.getElementById('lone_variable');
  
  Columns=Object.keys(data_by_columns);
  
  for(i=0; i<Columns.length; i++){
    var option = document.createElement("option");
    option.text = Columns[i];
    x.add(option);    
  }    
  
  /*
  if(selects_to_populate.indexOf('lone_variable') == -1){
    selects_to_populate[selects_to_populate.length]='lone_variable';
  }
  */

  function report_t_test_one_sample(input_variable,baseline,one_sample_dv_unit){
  
    var script    = "report_t_test_one_sample('"+input_variable+"',"+baseline+",'"+one_sample_dv_unit+"')";
    script_array[script_array.length]=script;
    
    var sum_array = data_by_columns[input_variable];
    t_test_results = calculate_t_test_one_sample(sum_array,baseline);
        
    bold_p_value(t_test_results[2]);
    
    var this_mean = jStat.mean(sum_array)
    var this_sd = jStat.stdev(sum_array)
    var this_se = this_sd/Math.sqrt(sum_array.length-1)
    
    var these_results = {
      p_value : t_test_results[2],
      t_value : t_test_results[0],
      df      : t_test_results[1]
    }

    
    var output = "<br>" +script + 
                  "<br> t("+ t_test_results[1] +") = " + t_test_results[0] +
                  ", p = "+bold_sig_on+t_test_results[2]+bold_sig_off+" (2-tailed)"+
                  // descriptives
                  "<br> mean = "+ this_mean +"; "+
                  "sd = "+ this_sd;
    
    
    anObjectName_1 = input_variable;
    this[anObjectName_1] = {"height":this_mean,
                            "error" :this_se}
    
    col_data = {};
    col_data[anObjectName_1]=this[anObjectName_1];
    col_data=JSON.stringify(col_data);
        
    if(typeof one_sample_ttest_no == "undefined"){
      one_sample_ttest_no=0;
    } else {
      one_sample_ttest_no++;
    }
    
    var y_axis = one_sample_dv_unit;
    
    var container = $("<div>");
      var id = 'one_sample_ttest' + one_sample_ttest_no;
      
      container.attr('id', id);
      
      var this_title = "<h2>Output "+ (Object.keys(report_output).length-1)+"</h2>";
      
      container.html(this_title+ output+"<br><div class='graphArea'></div><br><div class='histArea'></div><br>"+
      "<button type='button' id='one_sample_ttest"+one_sample_ttest_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
      "<button type='button' onclick='remove_from_output(\"one_sample_ttest"+one_sample_ttest_no+"\")'>Remove from output</button>"+
      "<hr style='background-color:black'></hr>");     
      
      container.appendTo("#output_area");
      
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
      
      var plot_names      = ["hist1"];
      var histrogram_divs = [".histArea"];
      var sum_arrays      = [sum_array];
      
      create_histogram(sum_arrays,histrogram_divs,container,plot_names,these_results);
      
      store_results(these_results);
    
    
  }  
