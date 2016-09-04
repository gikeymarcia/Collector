// add options //
  var x = document.getElementById("loneVariable");
  for(i=0; i<anthonyColumns.length; i++){
    var option = document.createElement("option");
    option.text = anthonyColumns[i];
    x.add(option);    
  }