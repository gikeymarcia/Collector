  /* dependencies
  
  - se
  - which calls:
    -sd 
    -mean
  
  */
  var scrpt = document.createElement('script');
  scrpt.src='Stats/se/stats.js';
  document.head.appendChild(scrpt);
  
  
  function calculate_Pearson_R(input_array1,input_array2){
    
    for(var i=0;i<input_array1.length;i++){
      input_array1[i]  = parseFloat(input_array1[i]);
    }
    
    for(var i=0;i<input_array2.length;i++){
      input_array2[i]  = parseFloat(input_array2[i]);
    }
    
    var pearson_r = jStat.corrcoeff(input_array1,input_array2);
    
    var df = input_array1.length-2;
    
    var t_score = pearson_r/Math.sqrt((1-Math.pow(pearson_r,2))/df)
    var p_value = jStat.ttest(t_score,df+1,2); // assuming its a two-sided test
        
    return [pearson_r,df,p_value]; 
    
  }
  
  
