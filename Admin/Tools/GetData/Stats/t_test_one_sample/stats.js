


  

  /* dependencies
  
  - se
  - which calls:
    -sd 
    -mean
  
  */
  var scrpt = document.createElement('script');
  scrpt.src='Stats/se/stats.js';
  document.head.appendChild(scrpt);
  
  
  function calculate_t_test_one_sample(input_array,baseline){
    input_array = input_array.filter(Number);
    
    for(var i=0;i<input_array.length;i++){
      input_array[i]  = parseInt(input_array[i]);
    }
    
    
    sample_mean = calculate_mean(input_array);
    sample_se   = calculate_se(input_array);
    adjusted_sample_mean = sample_mean-baseline;
    var df      = input_array.filter(Number).length - 1;
    t_score     = adjusted_sample_mean/sample_se;
    p_value     = jStat.ttest(t_score,input_array.length,1); // assuming its a one-sided test
    
    return [t_score,df,p_value];
    
    // above code might be simplified by calling jStats//
    
  }
  
  
  /*

  function calculate_se(input_array){
    var se = 0;
    array_length = input_array.filter(Number).length;
    var sd = calculate_sd(input_array);
    return sd/Math.sqrt(array_length);
    
  }

  function report_se(input_variable){
    var se_array = anthony_object[input_variable];    
    outputArea.innerHTML += "<br> se("+ input_variable +") <br> "+  calculate_se(se_array);
  } 
*/  
