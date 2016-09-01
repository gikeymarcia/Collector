
  /* * * * * * * * * * * * 
   * Function Definitions
   */
   
  // similar to getData from Collector PHP
  // uses the first row as headers, returns array of objects
  function associateArray(rawArray) {
      var assocArray = [];
      var headers = rawArray[0]; // takes top row of array and uses it for associative array for rest of data
      
      var i, len, row, j, len2;
      
      for (i=1, len=rawArray.length; i<len; ++i) {  
          row = {}; // creating row as an object
          
          for (j=0, len2=headers.length; j<len2; ++j) {
              row[headers[j]] = rawArray[i][j];
          }
          
          assocArray.push(row);
      }
      
      return assocArray;
  }
  
  // gets array with list of unique values from bigger array
  function arrayUnique(arr) {
      var i, len, uniques = [], uniqueObj = {}, val;
      
      for (i=0, len=arr.length; i<len; ++i) {
          val = arr[i];
          
          uniqueObj[val] = true;
      }
      
      for (val in uniqueObj) {
          uniques.push(val);
      }
      
      return uniques;
  }
  
  // calculates standard deviation from array of values
    function standardDeviationSample(values){
      var avg = average(values);
      
      var squareDiffs = values.map(function(value){
        var diff = value - avg;
        var sqrDiff = diff * diff;
        return sqrDiff;
      });
      
      var SS = squareDiffs.reduce(function(sum, value){
        return sum + value;
      }, 0);
      
      var variance = SS / (values.length - 1);

      var stdDev = Math.sqrt(variance);
      return stdDev;
    }
  
  // calculates standard deviation from array of values
    function standardDeviation(values){
      var avg = average(values);
      
      var squareDiffs = values.map(function(value){
        var diff = value - avg;
        var sqrDiff = diff * diff;
        return sqrDiff;
      });
      
      var avgSquareDiff = average(squareDiffs);

      var stdDev = Math.sqrt(avgSquareDiff);
      return stdDev;
    }

    // calculates average of array of values
    function average(data){
      var sum = data.reduce(function(sum, value){
        return sum + value;
      }, 0);

      var avg = sum / data.length;
      return avg;
    }
    
  
  function addPredVar(x){
    //alert ("addPredVar");
    noPredVars++;
    predVariables.innerHTML=predVariables.innerHTML+
                            "<div id='predDiv"+noPredVars+"'>"+
                              "<select id='predVariable"+noPredVars+"' onchange='updateTableInfo()'>"+
                                  "<option> [select a variable] </option>"+
                              "</select><br>"
                            "</div>";
    for(i=0;i<noPredVars;i++){
      document.getElementById("predVariable"+i).value=tableObject.pred[i];
    }
    for(y in data[0]){ // predictor variable 1
      //elementList.push(trialTypeElements['elements'][x].elementName); //may become redundant
      var option = document.createElement("option");
      option.text = data[0][y];
      option.value=data[0][y]; //;
      document.getElementById("predVariable"+noPredVars).add(option);

    }
    
    $("#remPredVarButton").show(1000);
  }
  
  function remPredVar(x){
    //alert ("remPredVar");
    $("#predDiv"+noPredVars).remove();
    tableObject.pred.splice(noPredVars,1); 
    noPredVars--;
    if(noPredVars==0){
      $("#remPredVarButton").hide(1000);
    }
    updateTableInfo();
  }

  function addDepVar(x){
    //alert ("addDepVar");
    noDepVars++;
    //code for adding a variable
    depVariables.innerHTML=depVariables.innerHTML+
                            "<div id='depDiv"+noDepVars+"'>"+
                              "<select id='depVariable"+noDepVars+"' onchange='updateTableInfo()'>"+
                                "<option> [select a variable] </option>"+
                              "</select><br>"; //+
                            "</div>";
    for(i=0;i<noDepVars;i++){
      document.getElementById("depVariable"+i).value=tableObject.dep[i];
    }
    for(y in data[0]){ // dependent variable 1
      //elementList.push(trialTypeElements['elements'][x].elementName); //may become redundant
      var option = document.createElement("option");
      option.text = data[0][y];
      option.value=data[0][y]; //;
      document.getElementById("depVariable"+noDepVars).add(option);
    }

    $("#remDepVarButton").show(1000);
 
    
  }
  
  function remDepVar(x){
    $("#depDiv"+noDepVars).remove();
    tableObject.dep.splice(noDepVars,1); 
    noDepVars--;
    if(noDepVars==0){
      $("#remDepVarButton").hide(1000);
    }
    updateTableInfo();
  }