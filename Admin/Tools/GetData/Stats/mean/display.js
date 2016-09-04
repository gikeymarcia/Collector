// add options //
  
  var x = document.getElementById("loneVariable");
  for(i=0; i<anthonyColumns.length; i++){
    var option = document.createElement("option");
    option.text = anthonyColumns[i];
    x.add(option);    
  }
  
  function report_mean(input_variable){
    var sum_array = anthony_object[input_variable];    
    outputArea.innerHTML += "<br> mean("+ input_variable +") <br> "+  calculate_mean(sum_array);
  }