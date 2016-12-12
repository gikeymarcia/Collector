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
    
    for(var i=0;i<input_array.length;i++){
      if(input_array[i]==0){
        input_array[i]=0;
      } else {
        input_array[i]  = parseFloat(input_array[i]);
      }
    }
    
    var t_score  = jStat.tscore(baseline,data_by_columns['Resp_Duration']);
    var p_value = jStat.ttest(t_score,input_array.length,2); // 2-tailed
    var df      = input_array.length - 1;
    
    sample_mean = calculate_mean(input_array);
    descriptives  = "mean="+sample_mean+"; SD="+calculate_sd(input_array);
    
    return [t_score,df,p_value,descriptives];
    
  }
  
  
