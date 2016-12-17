  var jsInputs = ['grouping_variable','dependent_variable'];

  Columns=Object.keys(data_by_columns);
  
  for (j=0;j<jsInputs.length;j++){
    var x = document.getElementById(jsInputs[j]);
    for(i=0; i<Columns.length; i++){
      var option = document.createElement("option");
      option.text = Columns[i];
      x.add(option);    
    }    
  }

   function onlyUnique(value, index, self) { //on http://stackoverflow.com/questions/1960473/unique-values-in-an-array by TLindig and nus
    return self.indexOf(value) === index;
  }

  
  function report_t_test_independent(grouping_variable,dependent_variable,DV_units){
    
    var dependent_array = data_by_columns[dependent_variable];
    var grouping_array  = data_by_columns[grouping_variable];
    
    grouping_array_short  = grouping_array.filter(onlyUnique);
    
    if (grouping_array_short.length >2){
      alert("more than 2 variables included, use ANOVA!")
    } else {
      
      input_array1=[];
      input_array2=[];
      
      for(i=0;i<dependent_array.length;i++){
        if(grouping_array[i]==grouping_array_short[0]){
          input_array1[input_array1.length]=dependent_array[i];
        } else {
          input_array2[input_array2.length]=dependent_array[i];
        }
      }
      console.dir(input_array1);
      console.dir(input_array2);
      
      t_test_results = calculate_t_test_independent(input_array1,input_array2);
      
      bold_p_value(t_test_results[2]);
    
      var script = "report_t_test_independent('"+grouping_variable+"','"+dependent_variable+"','"+DV_units+"')";
      script_array[script_array.length]=script;
      
      var testName = "theNewName";
      
      var output = "<br>" +script + 
                    "<br> t("+ t_test_results[1] +") = " + t_test_results[0] +
                    ", p = "+bold_sig_on+t_test_results[2]+bold_sig_off+" (2-tailed)"+
                    // descriptives
                    "<br> group 1 mean = "+t_test_results[3]+
                         "; sd = "+t_test_results[4]+
                    "<br> group 2 mean = "+t_test_results[5]+
                         "; sd = "+t_test_results[6];
     
    var these_results = {
      p_value : t_test_results[2],
      t_value : t_test_results[0],
      df      : t_test_results[1]
    }

    store_results(these_results);
     
     
      anObjectName_1 = grouping_array_short[0];
      this[anObjectName_1] = {"height":t_test_results[3],
                            "error" :t_test_results[7]}

      anObjectName_2 = grouping_array_short[1];
      this[anObjectName_2] = {"height":t_test_results[5],
                            "error" :t_test_results[8]}

      
      col_data = {};
      col_data[grouping_array_short[0]]=this[anObjectName_1];
      col_data[grouping_array_short[1]]=this[anObjectName_2];
      col_data=JSON.stringify(col_data);
                         
      var y_axis = DV_units;
          

      if(typeof independent_sample_ttest_no == "undefined"){
        independent_sample_ttest_no=0;
      } else {
        independent_sample_ttest_no++;
      }
      
      var container = $("<div>");
      var id = 'independent_sample_ttest' + independent_sample_ttest_no;
      
      container.attr('id', id);
            
      container.html( output+"<br><div class='graphArea'></div><br><button type='button' id='independent_sample_ttest"+independent_sample_ttest_no+"' onclick='add_to_script("+(script_array.length-1)+")'>Add to script</button>"+
      "<button type='button' onclick='remove_from_output(\"independent_sample_ttest"+independent_sample_ttest_no+"_div\")'>Remove from output</button>"+
      "<hr style='background-color:black'></hr>");     
    
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
        
      container.appendTo("#output_area");
      
    }
  }