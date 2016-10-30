
  /* dependencies
  
  - mean
  
  */
  var scrpt = document.createElement('script');
  scrpt.src='Stats/mean/stats.js';
  document.head.appendChild(scrpt);
  
  

  function calculate_sd(input_array){
    var sd = 0;
    array_length = input_array.filter(Number).length;
        
    for( var i = 0; i < input_array.length; i++ ){
      if(!isNaN(parseInt(input_array[i]))){
        sd += (Math.abs(parseInt( input_array[i], 10 ) - calculate_mean(input_array)))^2; //don't forget to add the base
      }
    }
    return Math.sqrt(sd/(array_length-1));
    
  }

  