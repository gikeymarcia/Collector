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
    
    sample_mean           = calculate_mean(input_array);
    sample_se             = calculate_se(input_array);
    adjusted_sample_mean  = sample_mean-baseline;
    var df                = input_array.length - 1;
    t_score               = adjusted_sample_mean/sample_se;
    p_value               = jStat.ttest(t_score,input_array.length,1); // assuming its a one-sided test
    
    /* - can I make use of the jstat;
      t_score = jStat.tscore(input_array);
      p_value = jstat.ttest(t_score,1);
    */
    
    return [t_score,df,p_value];
    
    // above code might be simplified by calling jStats//
    
  }
  
  
