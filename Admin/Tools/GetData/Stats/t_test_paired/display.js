  var jsInputs = ['variable_1','variable_2'];


  for (j=0;j<jsInputs.length;j++){
    var x = document.getElementById(jsInputs[j]);
    for(i=0; i<anthonyColumns.length; i++){
      var option = document.createElement("option");
      option.text = anthonyColumns[i];
      x.add(option);    
    }    
  }
