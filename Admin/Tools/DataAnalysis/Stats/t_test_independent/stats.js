
  /* dependencies
  
  - se
  - which calls:
    -sd 
    -mean
  
  */
  var scrpt = document.createElement('script');
  scrpt.src='Stats/se/stats.js';
  document.head.appendChild(scrpt);
  
  

  function calculate_t_test_independent(input_array1,input_array2){
    
    for(var i=0;i<input_array1.length;i++){
      input_array1[i]  = parseFloat(input_array1[i]);
    }
    
    for(var i=0;i<input_array2.length;i++){
      input_array2[i]  = parseFloat(input_array2[i]);
    }
    
    // clear NaN
    input_array1 = input_array1.filter(Boolean);
    input_array2 = input_array2.filter(Boolean);
        
    var this_N1 =  input_array1.length;
    var df1 = this_N1 - 1;
    var mean1 = jStat.mean(input_array1);
    
    var SS1 = 0;
    for(var i=0;i<input_array1.length;i++){
      SS1 += Math.pow(input_array1[i]-jStat.mean(input_array1),2);
    }
    
    var s21 = SS1/(this_N1 - 1);

// resume here!!
    
    //treament 2
    
    var this_N2 =  input_array2.length;
    var df2 = this_N2 - 1;
    var mean2 = jStat.mean(input_array2);
    
    var SS2 = 0;
    for(var i=0;i<input_array2.length;i++){
      SS2 += Math.pow(input_array2[i]-jStat.mean(input_array2),2);
    }
    
    var s22 = SS2/(this_N2 - 1);
    
    
    var s2p = ((df1/(df1 + df2)) * s21) + ((df2/(df1 + df2)) * s22)
    
    var s2m1 = s2p/this_N1;
    var s2m2 = s2p/this_N2;
    t_score = (mean1 - mean2)/Math.sqrt(s2m1 + s2m2);

    
    var df      = input_array1.length + input_array2.length -2;
    
    console.dir(t_score);
    console.dir(df);
    
    var p_value = jStat.ttest(t_score,df+1,2); // assuming its a two-sided test
        
    var sd1 = jStat.stdev(input_array1);
    var sd2 = jStat.stdev(input_array2);
    
    
    return [t_score,df,p_value,mean1,sd1,mean2,sd2];
    
  }

  // this should be part of a staple of JS functions//
  
 
   
  
