<script>
var getdataHeaders = data[0].slice(0);
var getdataData    = associateArray(data);
</script>




<style>
  table, th, td {
   border: 1px solid black;
  }
  
  table { margin-left: auto; margin-right: auto; }
  
  body { display: block; text-align: center; }
  
  #varSelection td { padding: 7px; }
  
  #summaryTable { margin: 20px auto; }
  #summaryTable td { padding: 4px; }
  #CategoryHeader { vertical-align: middle; }
  .tableHeader { font-weight: bold; text-align: center; }
  .summaryDataCell { text-align: right; }
  
  #barChart { margin: 20px auto; }
</style>



<h1> Cross-Tabulation</h1>

<table id="varSelection">
  <tr>
    <td>Categorical/Predictor variable(s)</td>
    <td>Dependent variable(s)</td>
  </tr>
  <tr>
    <td>
      <div id="predVariables">
        <select id="predVariable0" onchange="updateTableInfo()">
          <option> [select a variable] </option>
        </select>
        <br>
      </div>
      <input id="addPredVarButton" type="button" value="add variable" style="display:none" onclick="addPredVar(noPredVars)"> 
      <input id="remPredVarButton" type="button" value="remove variable" style="display:none" onclick="remPredVar(noPredVars)">        
    </td>
    <td> 
      <div id="depVariables">
        <select id="depVariable0" onchange="updateTableInfo()">
          <option> [select a variable] </option>
        </select>
        <br>
      </div>
      <input id="addDepVarButton" type="button" value="add variable" style="display:none" onclick="addDepVar(noDepVars)"> 
      <input id="remDepVarButton" type="button" value="remove variable" style="display:none" onclick="remDepVar(noDepVars)">
    </td>
  </tr>
</table>

<br><br>

<button id="createTableButton" class="collectorButton">create table!</button>

<div id="crosstabTableJSON" style="display: none;">
</div>
<div id="crosstabTable">
</div>
<div id="barChart">
</div>



<script>
    
  function updateTableInfo(){
    for(i=0;i<=noPredVars;i++){ //add to object
      tableObject.pred[noPredVars]=document.getElementById("predVariable"+i).value;
    }
    for(i=0;i<=noDepVars;i++){
      tableObject.dep[noDepVars]=document.getElementById("depVariable"+i).value;
    }
    
    crosstabTableJSON.innerHTML=JSON.stringify(tableObject);
    
    // could be somewhere better perhaps
    if(predVariable0.value!="[select a variable]"){
      $("#addPredVarButton").show();
    }
    if(depVariable0.value!="[select a variable]"){
      $("#addDepVarButton").show();
    }

  }

  var tableObject= {
    pred:[],
    dep:[]
  }

  
  var noPredVars = 0;
  var noDepVars = 0;
  for(var x in getdataHeaders){ // predictor variable 1
    //elementList.push(trialTypeElements['elements'][x].elementName); //may become redundant
    var option   = document.createElement("option");
    option.text  = getdataHeaders[x];
    option.value = getdataHeaders[x]; //;
    document.getElementById("predVariable0").add(option);

  }
  for(x in getdataHeaders){ // dependent variable 1
    //elementList.push(trialTypeElements['elements'][x].elementName); //may become redundant
    var option   = document.createElement("option");
    option.text  = getdataHeaders[x];
    option.value = getdataHeaders[x]; //;
    document.getElementById("depVariable0").add(option);
  }
    
        
  function onlyUnique(value, index, self) { //solution by nus at http://stackoverflow.com/questions/1960473/unique-values-in-an-array
    return self.indexOf(value) === index;
  }
  
  
  
  $("#createTableButton").click(function(){
    // preparing variables for "createTableButton"
    var outputStats = {}; 
  
    var categories = tableObject.pred; // dummy array: ['Gender', 'Eye color'];
    var dependents = tableObject.dep// ['Age', 'Score']; 
  
  
    /* * * * * * * * * * * * * * * * * * * * *
     * Procedure
     *
     * This part should probably be moved inside whatever
     * click event triggers the calculates to happen
     */
      
      var categoryValues = {};
      
      // init some needed variables for the recursion below
      var category, set, i, j, len1, len2, l, len3;
      
      // find all the categories
      for (i=0, len=getdataData.length; i<len; ++i) {
          for (j=0, len2=categories.length; j<len2; ++j) {
              category = categories[j];
              
              if (typeof categoryValues[category] === "undefined") {
                  categoryValues[category] = [];
              }
              
              categoryValues[category].push(getdataData[i][category]);
          }
      }
      
      // make the categories only contain unique values
      for (j=0, len2=categories.length; j<len2; ++j) {
          category = categories[j];
          
          categoryValues[category] = arrayUnique(categoryValues[category]);
      }
      
      var dependent;
      var dependentValues = {};
      
      // find all the dependents
      for (i=0, len=getdataData.length; i<len; ++i) {
          for (j=0, len2=dependents.length; j<len2; ++j) {
              dependent = dependents[j];
              
              if (typeof dependentValues[dependent] === "undefined") {
                  dependentValues[dependent] = [];
              }
              
              dependentValues[dependent].push(getdataData[i][dependent]);
          }
      }
      
      // make the dependents only contain unique values
      for (j=0, len2=dependents.length; j<len2; ++j) {
          dependent = dependents[j];
          
          dependentValues[dependent] = arrayUnique(dependentValues[dependent]);
      }
      
      // initialize the categorized object structure
      var dataCategorized = {};
      
      var set = [dataCategorized], nextSet = [], categoryName;
      var flatData = {}, setName;
      
      for (categoryName in categoryValues) {
          category = categoryValues[categoryName];
          
          for (j=0, len2=category.length; j<len2; ++j) {
              for (l=0, len3=set.length; l<len3; ++l) {
                  set[l][category[j]] = {};
                  nextSet.push(set[l][category[j]]);
              }
          }
          
          set     = nextSet;
          nextSet = [];
      }
      
      var sets = [''], setsCopy;
      
      for (categoryName in categoryValues) {
          setsCopy = sets.slice();
          sets = [];
          
          for (i=0, len=categoryValues[categoryName].length; i<len; ++i) {
              for (j=0, len2=setsCopy.length; j<len2; ++j) {
                  sets.push(setsCopy[j]+"-"+categoryValues[categoryName][i]);
              }
          }
      }
      
      for (i=0, len=sets.length; i<len; ++i) {
          flatData[sets[i].substr(1)] = [];
      }
      
      for (i=0, len=getdataData.length; i<len; ++i) {
          set     = dataCategorized;
          setName = [];
          
          for (j=0, len2=categories.length; j<len2; ++j) {
              category = getdataData[i][categories[j]];
              set      = set[category];
              setName.push(category);
          }
          
          setName = setName.join("-");
          
          if (typeof flatData[setName] === "undefined") {
              flatData[setName] = [];
          }
          
          flatData[setName].push(getdataData[i]);
      }
      
      /* * * * * * * * * * * * * *
       * Run Stats on each group
       */
      var statsSet, col, values, value, rowCount, missingCount, nonNumericValCount, valCountName, nonNumericDependentVal;
      
      for (setName in flatData) {
          if (setName === '') continue;
          
          outputStats[setName] = {};
          
          for (i=0, len=dependents.length; i<len; ++i) {
              col      = dependents[i];
              statsSet = {};
              values   = [];
              rowCount = 0;
              nonNumericValCount = {};
              
              for (j=0, len2=flatData[setName].length; j<len2; ++j) {
                  ++rowCount;
                  
                  value = flatData[setName][j][col];
                  
                  if (value === '') continue;
                  
                  if (!isNaN(value)) {
                      value = parseFloat(value);
                      values.push(value);
                  } else {
                      if (typeof nonNumericValCount[value] === "undefined") {
                          nonNumericValCount[value] = 0;
                      }
                      
                      ++nonNumericValCount[value];
                  }
              }
              
              statsSet['Values']         = values;
              statsSet['Total Count']    = rowCount;
              statsSet['filtered Count'] = values.length;
              statsSet['Missing Count']  = rowCount - values.length;
              
              for (valCountName in nonNumericValCount) {
                  statsSet["Count " + valCountName] = nonNumericValCount[valCountName];
              }
              
              for (j=0; j<dependentValues[col].length; ++j) {
                  nonNumericDependentVal = dependentValues[col][j];
                  if (nonNumericDependentVal === '')       continue;
                  if ($.isNumeric(nonNumericDependentVal)) continue;
                  
                  if (typeof statsSet["Count " + nonNumericDependentVal] === "undefined") {
                      statsSet["Count " + nonNumericDependentVal] = 0;
                  }
              }
              
              if (values.length === 0) {
                  statsSet.sum   = "null";
                  statsSet.ave   = "null";
                  statsSet.stDev = "null";
                  
              } else {
                  /* * * * * * * * * * * *
                   * Stats calculations
                   *
                   * You can add more calculations here, if you like. Just
                   * make sure to provide a default value above if there
                   * are no values for this category
                   */
                  statsSet.sum = values.reduce(function(total, currentVal) {
                      return total + currentVal;
                  }, 0);
                  
                  statsSet.ave = statsSet.sum / values.length;
                  statsSet.stDev = standardDeviationSample(values);
              }
              
              outputStats[setName][col] = statsSet;
          }
      }
      
      /* * * * * * * *
       * End Result:
       *
       * outputStats should contain all the groups, with each group
       * containing all the stats for each dependent column.
       */
      
      /* * * * * * * * *
       * End Procedure
       */
      
    /* * * * * * * * 
    * create table
    */
    
 
    // create an index with which to work through object
    var tableRows = Object.keys(outputStats);
    var tableColumns = Object.keys(outputStats[tableRows[1]]);
    var subColumns = Object.keys(outputStats[tableRows[0]][tableColumns[0]]);
    
    // create columns of data using this structure
    var tableString="<table id='summaryTable'>"+
                      "<tr>"+
                        "<td rowspan='2' class='tableHeader' id='CategoryHeader'>Categories</td>";
    for (i=0;i<tableColumns.length;i++){
      tableString+="<td colspan='"+(subColumns.length - 1)+"' class='tableHeader'>"+tableColumns[i]+"</td>";
    }
    tableString+="</tr>"+
          "<tr>";
    for (i=0;i<tableColumns.length;i++){
      for(j=1;j<subColumns.length;j++){
        tableString+="<td class='tableHeader'>"+ subColumns[j] +"</td>";
      }
    }
    tableString+="</tr>";
                        
                        // add column headers, using column width
    
    for(i=0;i<tableRows.length;i++){
      if (tableRows[i] === '') continue;
      
      tableString+="<tr>"+
                    "<td>"+tableRows[i]+"</td>";
      for(j=0;j<tableColumns.length;j++){
        for (k=1;k<subColumns.length;k++){
          var cellValue = outputStats[tableRows[i]][tableColumns[j]][subColumns[k]];
          cellValue = Math.round(cellValue*1000)/1000; 
          tableString+="<td class='summaryDataCell'>"+cellValue+"</td>";
        }  
      }
      tableString=tableString+"</tr>";
    }
    tableString = tableString+"</table>"; 
    
    crosstabTable.innerHTML=tableString;
    
    
    /* * * * * * * * *
     * AJAX for plot
     */
    
    var dataToGraph = {};
    var dependentVar = tableObject['dep'][0];
    var height, error;
    var colInfo;
    
    for (var groupName in outputStats) {
        colInfo = outputStats[groupName][dependentVar];
        
        height = parseFloat(colInfo["ave"]);
        error  = parseFloat(colInfo["stDev"]);
        
        if (!$.isNumeric(height)) continue;
        if (!$.isNumeric(error))  error = 0;
        
        dataToGraph[groupName] = {
            height: height,
            error:  error
        };
    }
    
    if (Object.keys(dataToGraph).length === 0) {
        $("#barChart").html("");
    } else {
      // the AJAXing itself
      
        var jsonBarChart=JSON.stringify(dataToGraph);
        var yAxisLabel = dependentVar;
        
      // var dataAjax = 'data='+jsonBarChart;   // Ajax based on 
        
        $.ajax({
          type:"POST",
          url: "getdataImgGenerate.php", 
          data: 'data='+jsonBarChart+"&yAxis=" + yAxisLabel,
          success: function(result){
                     $("#barChart").html("<img src='" + result + "'>");
                   },
          dataType: "text"
        });
    }
  });
</script>
</body>
</html>
