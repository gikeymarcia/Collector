  /* dependencies
  
  - se
  - which calls:
    -sd 
    -mean
  
  */
  var scrpt = document.createElement('script');
  scrpt.src='Stats/se/stats.js';
  document.head.appendChild(scrpt);
  
  
  function get_ranks(arr){ // solution based on Denys Séguret answer at http://stackoverflow.com/questions/14834571/ranking-array-elements
    // rank each array
    // var arr = [79, 5, 18, 5, 32, 1, 16, 1, 82, 13];
    var sorted = arr.slice().sort(function(a,b){return b-a})
    var ranks = arr.slice().map(function(v){ return sorted.indexOf(v)+1 });
    
    return ranks;
  }  
  
  function calculate_Spearman_R(input_array1,input_array2){
    
    for(var i=0;i<input_array1.length;i++){
      input_array1[i]  = parseFloat(input_array1[i]);
    }
    
    for(var i=0;i<input_array2.length;i++){
      input_array2[i]  = parseFloat(input_array2[i]);
    }
    
    input_array1=get_ranks(input_array1);
    input_array2=get_ranks(input_array2);
    
    
    var pearson_r = jStat.corrcoeff(input_array1,input_array2);
    
    var df = input_array1.length-2;
    
    var t_score = pearson_r/Math.sqrt((1-Math.pow(pearson_r,2))/df)
    var p_value = jStat.ttest(t_score,df+1,2); // assuming its a two-sided test
        
    return [pearson_r,df,p_value]; 
    
  }
  
  
