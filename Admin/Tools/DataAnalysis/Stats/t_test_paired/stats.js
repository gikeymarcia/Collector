
  /* dependencies
  
  - t_test_one_sample
  - which calls:
    -se and mean
  
  */
  var scrpt = document.createElement('script');
  scrpt.src='Stats/t_test_one_sample/stats.js';
  document.head.appendChild(scrpt);
  
  function calculate_t_test_paired(input_array1,input_array2){
    
    //cleaning
    
    for(var i=0;i<input_array1.length;i++){
      input_array1[i]  = parseFloat(input_array1[i]);
    }
    
    for(var i=0;i<input_array2.length;i++){
      input_array2[i]  = parseFloat(input_array2[i]);
    }
    
    // clear NaN
    input_array1 = input_array1.filter(Boolean);
    input_array2 = input_array2.filter(Boolean);
    
    paired_array = [];
    
    // create data that can be pushed into one-sample ttest
    for(var i=0;i<input_array1.length;i++){ // arbitrary which length we choose
      paired_array[i] = input_array1[i] - input_array2[i];
    }
    console.dir(paired_array);
    
    mean1 = jStat.mean(input_array1);
    mean2 = jStat.mean(input_array2);
    se1   = jStat.stdev(input_array1)/Math.sqrt(input_array1.length-1);
    se2   = jStat.stdev(input_array2)/Math.sqrt(input_array2.length-1);
    
    var one_sample_values = calculate_t_test_one_sample(paired_array,0);
    
    return [one_sample_values[0],
            one_sample_values[1],
            one_sample_values[2],
            mean1,se1,mean2,se2];
    

  }

  
  
