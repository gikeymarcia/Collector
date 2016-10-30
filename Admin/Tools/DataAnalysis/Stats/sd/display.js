

// add options //
  var x = document.getElementById("loneVariable");
  for(i=0; i<getdata_columns.length; i++){
    var option = document.createElement("option");
    option.text = getdata_columns[i];
    x.add(option);    
  }

  
  function report_sd(input_variable){
    var sd_array = data_by_cols[input_variable];
    outputArea.innerHTML += "<br> sd("+ input_variable +") <br> "+  calculate_sd(sd_array);
  }  
