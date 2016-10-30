  
  function calculate_mean(input_array){
    var sum = 0;
    array_length = input_array.filter(Number).length;
    
    for( var i = 0; i < input_array.length; i++ ){
      if(!isNaN(parseInt(input_array[i]))){
        sum += parseInt( input_array[i], 10 ); //don't forget to add the base
      }
    }
    return sum/array_length;
  }

  
  
