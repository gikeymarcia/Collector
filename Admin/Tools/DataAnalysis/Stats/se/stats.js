


  /* dependencies
  
  - sd
  
  */
  var scrpt = document.createElement('script');
  scrpt.src='Stats/sd/stats.js';
  document.head.appendChild(scrpt);
  
  

  function calculate_se(input_array){
    var se = 0;
    array_length = input_array.filter(Number).length;
    var sd = calculate_sd(input_array);
    return sd/Math.sqrt(array_length);
    
  }

  function report_se(input_variable){
    var se_array = data_by_cols[input_variable];    
    outputArea.innerHTML += "<br> se("+ input_variable +") <br> "+  calculate_se(se_array);
  }  
