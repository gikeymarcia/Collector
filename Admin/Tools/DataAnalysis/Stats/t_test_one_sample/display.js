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

    store_results(these_results);
    
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
            },
            'text'
        );
        
        
        console.dir(sum_array);
        this_sum_array= sum_array;
        hist_min      = jStat.min(this_sum_array);
        hist_max      = jStat.max(this_sum_array);
        hist_range    = hist_max - hist_min;
        hist_bin_width= hist_range/10;
        
        col_data={};
        for(i=0;i<10;i++){
          var this_bin_min = i*hist_bin_width+hist_min;
          var this_bin_max = (i+1)*hist_bin_width+hist_min;
          var valid_rows   = 0;
          for(j=0;j<this_sum_array.length;j++){
            if(this_sum_array[j] <= this_bin_max &
               this_sum_array[j] >= this_bin_min){
               valid_rows++;
               console.dir(this_sum_array[j]+"-"+this_bin_min+"-"+this_bin_max);
             }
          }
          col_data[this_bin_min+"-"+this_bin_max]={height:valid_rows};
        }
        col_data=JSON.stringify(col_data);
        
        y_axis = "Frequency";
        
        $.post(
            'histGenerate.php',
            {
                data: col_data,
                yAxis: y_axis
            },
            function(img_url) {
                if (img_url.substring(0, 5) === 'Error') {
                  container.find(".histArea").html(img_url);
                    
                } else {
                  container.find(".histArea").html('<img src="' + img_url + '">');

                }
            },
            'text'
        );
        
        
     // container.appendTo("#output_area");
    
  }  
