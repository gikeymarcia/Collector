
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
    
    var t_score = (calculate_mean(input_array1)-calculate_mean(input_array2))/(Math.sqrt(calculate_se(input_array1)+calculate_se(input_array2)));
    var df      = input_array1.length + input_array2.length -2;
    var p_value = jStat.ttest(t_score,df+2,1); // assuming its a one-sided test
    
    return [t_score,df,p_value];
    
  }

  // this should be part of a staple of JS functions//
  
  function onlyUnique(value, index, self) { //on http://stackoverflow.com/questions/1960473/unique-values-in-an-array by TLindig and nus
    return self.indexOf(value) === index;
  }

  // usage example:
  var a = ['a', 1, 'a', 2, '1'];
  var unique = a.filter( onlyUnique ); // returns ['a', 1, 2, '1']
  
  
  function report_t_test_independent(grouping_variable,dependent_variable){
    
    var dependent_array = data_by_cols[dependent_variable];
    var grouping_array  = data_by_cols[grouping_variable];
    
    grouping_array_short  = grouping_array.filter(onlyUnique);
    
    if (grouping_array_short.length >2){
      alert("more than 2 variables included, use ANOVA!")
    } else {
      
      input_array1=[];
      input_array2=[];
      
      for(i=0;i<dependent_array.length;i++){
        if(grouping_array[i]==grouping_array_short[0]){
          input_array1[input_array1.length]=dependent_array[i];
        } else {
          input_array2[input_array2.length]=dependent_array[i];
        }
      }
      
      outputArea.innerHTML += "<br> independent samples t-test("+ grouping_array_short[0]+","+ grouping_array_short[1] +") <br> "+  calculate_t_test_independent(input_array1,input_array2);
    }
  } 
  
