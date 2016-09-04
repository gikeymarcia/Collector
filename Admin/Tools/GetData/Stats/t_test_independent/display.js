  var jsInputs = ['grouping_variable','dependent_variable'];


  for (j=0;j<jsInputs.length;j++){
    var x = document.getElementById(jsInputs[j]);
    for(i=0; i<anthonyColumns.length; i++){
      var option = document.createElement("option");
      option.text = anthonyColumns[i];
      x.add(option);    
    }    
  }
