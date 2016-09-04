// add options //
  var x = document.getElementById("loneVariable");
  for(i=0; i<getdata_columns.length; i++){
    var option = document.createElement("option");
    option.text = getdata_columns[i];
    x.add(option);    
  }