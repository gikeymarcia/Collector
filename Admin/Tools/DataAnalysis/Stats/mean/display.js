// add options //
  
  var x = document.getElementById("loneVariable");
  for(i=0; i<getdata_columns.length; i++){
    var option = document.createElement("option");
    option.text = getdata_columns[i];
    x.add(option);    
  }
  
  function report_mean(input_variable){
    var sum_array = data_by_cols[input_variable];    
    outputArea.innerHTML += "<br> mean("+ input_variable +") <br> "+  calculate_mean(sum_array);
  }