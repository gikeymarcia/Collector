<?php
    adminOnly();
    ob_end_clean();

    $requiredInputs = array('u', 'format', 'files');
    foreach ($requiredInputs as $req) {
        if (!isset($_POST[$req])) trigger_error('Missing input', E_USER_ERROR);
    }

    if (!isset($_POST['trialTypes'])) $_POST['trialTypes'] = array();


    $skipTrialTypes = getAllTrialTypeFiles();
    foreach ($_POST['trialTypes'] as $type) {
        unset($skipTrialTypes[$type]);
    }

    $dataFiles = array_flip($_POST['files']);


    $dataFolders = array();

    foreach ($_POST['u'] as $userInfo) {
        $info = explode('/', $userInfo);
        $exp       = $info[0];
        $debugMode = $info[1];
        $username  = $info[2];
        $id        = $info[3];
        $file      = $info[4];

        $dataFolders[$exp][$debugMode][$id] = array($username, $file);
    }

    // get the columns requested from each category
    $columnCategories = array();
    
    foreach ($filePrefixes as $file => $prefix) {
        $category = $file . '_cols';
        
        if (!isset($_POST[$category])) {
            $columnCategories[$file] = array();
        } else {
            $columnCategories[$file] = $_POST[$category];
        }
    }

    if ($_POST['format'] === 'summary') {
        foreach ($filePrefixes as $file => $prefix) {
            if ($file !== 'Side') {
                unset($columnCategories[$file]);
            }
        }
    }
    
    // merge columns into one array
    $columns = array();
    
    foreach ($columnCategories as $colsInCategory) {
        $columns = array_merge($columns, $colsInCategory);
    }

    #### HTML Preview
    if ($_POST['format'] === 'html') {
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="<?= $admin['tool'] . '/GetDataStyle.css' ?>" rel="stylesheet" type="text/css" />
	<title>Get Data</title>
</head>
<body>
	<table id="GetDataTable">
        <thead> <tr> <th><?= implode('</th><th>', $columns) ?></th> </tr> </thead>
        <tbody>
<?php
    #### Data Summary
    } elseif ($_POST['format'] === 'summary') {
        require $_PATH->get('Header');
?>
    <script>
      if (typeof jQuery === "undefined") {
        document.write("<script src='<?= $_PATH->get('Jquery', 'url') ?>'><\/script>");
      }
    </script>
    <script src="GetData/summaryFunctions.js"></script>
    <script>
    var data = [
<?php
        echo json_encode(array_values($columns)), "\r\n";
    #### File Output
    } else {
        ini_set('html_errors', false);

        if ($_POST['format'] === 'csv') {
            $d = ',';
        } elseif ($_POST['format'] === 'txt') {
            $d = "\t";
        }

        $filename = 'Collector_GetData_' . implode('_', array_keys($dataFolders)) . '_' . date('y.m.d') . '.' . $_POST['format'];
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=".$filename);
        header("Content-Type: text/csv");
        header("Content-Transfer-Encoding: binary");
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, $columns, $d);
    }

    $debugModeDir = array(
        'Normal' => '',
        'Debug'  => '/Debug'
    );

    foreach ($dataFolders as $exp => $debugModes) {
        $_PATH->setDefault('Current Experiment', $exp);
        foreach ($debugModes as $debugMode => $ids) {
            $_PATH->setDefault('Data Sub Dir', $debugModeDir[$debugMode]);

            $dataByID = array();
            foreach ($ids as $id => $idData) {
                $dataByID[$id] = array();
            }


            if (isset($dataFiles['beg'])) {
                $staBegData = getdataReadCsvByIndex($_PATH->get('Status Begin Data'), 'ID');
                foreach ($staBegData as $id => $row) {
                    if (isset($dataByID[$id])) {
                        foreach ($row as $col => $val) {
                            $dataByID[$id][$filePrefixes['Status_Begin'].$col] = $val;
                        }
                    }
                }
            }

            if (isset($dataFiles['end'])) {
                $staEndData = getdataReadCsvByIndex($_PATH->get('Status End Data'), 'ID');
                foreach ($staEndData as $id => $row) {
                    if (isset($dataByID[$id])) {
                        foreach ($row as $col => $val) {
                            $dataByID[$id][$filePrefixes['Status_End'].$col] = $val;
                        }
                    }
                }
            }

            if (isset($dataFiles['side'])) {
                $sideData = array();
                $sideDataFile = $_PATH->get('SideData Data');
                if (is_file($sideDataFile)) {
                    $fileRes = fopen($sideDataFile, 'r');
                    $headers = fgetcsv($fileRes);
                    $headersCount = count($headers);
                    while ($line = fgetcsv($fileRes)) {
                        if (count($line) === count($headers)) {
                            $row = array_combine($headers, $line);
                        } else {
                            $row = array();
                            foreach ($headers as $i => $header) {
                                if (isset($line[$i])) {
                                    $row[$header] = $line[$i];
                                } else {
                                    $row[$header] = '';
                                }
                            }
                        }

                        $user = $row['Username'];
                        $id   = $row['ID'];

                        if (!isset($dataByID[$id])) continue; // not interested in this id's data

                        // unset($row['Username'], $row['ID']);

                        $sideData[$user][$id] = $row;
                    }
                }

                foreach ($sideData as $user => $sideIDs) {
                    $finalSideData = array();
                    foreach (end($sideIDs) as $col => $val) {
                        $finalSideData[$filePrefixes['Side'].$col] = $val;
                    }

                    foreach ($sideIDs as $id => $sideDataRow) {
                        foreach ($finalSideData as $col => $val) {
                            $dataByID[$id][$col] = $val;
                        }
                    }
                }
            }

            if ($_POST['format'] === 'summary') {
                // just output one row from each user
                foreach ($sideData as $user => $idDataRows) {
                    $firstRow = array();
                    foreach (reset($idDataRows) as $col => $val) {
                        $firstRow[$filePrefixes['Side'].$col] = $val;
                    }
                    $sortedRow = array();
                    foreach ($columns as $col) {
                        if (isset($firstRow[$col])) {
                            $sortedRow[$col] = $firstRow[$col];
                        } else {
                            $sortedRow[$col] = '';
                        }
                    }
                    echo ",\r\n", json_encode(array_values($sortedRow));
                }
            } elseif (!isset($dataFiles['exp'])) {
                foreach ($dataByID as $row) {
                    $sortedRow = array();
                    foreach ($columns as $col) {
                        if (isset($row[$col])) {
                            $sortedRow[$col] = $row[$col];
                        } else {
                            $sortedRow[$col] = '';
                        }
                    }
                    if ($_POST['format'] === 'html') {
                        echo '<tr><td>' . implode('</td><td>', $sortedRow) . '</td></tr>';
                    } else {
                        fputcsv($outstream, $sortedRow, $d);
                    }
                }
            } else {
                foreach ($ids as $id => $idData) {
                    $name = $idData[0];
                    $file = $idData[1];
                    $filePath = $_PATH->get('Experiment Output', 'relative', array('Output' => $file));
                    $expData = getdataReadCsv($filePath);

                    foreach ($expData as $row) {
                        $trialType = strtolower($row['main * trial type']);
                        if (isset($skipTrialTypes[$trialType])) continue;

                        $rowData = $dataByID[$id];
                        foreach ($row as $col => $val) {
                            $rowData[$filePrefixes['Output'].$col] = $val;
                        }

                        $sortedRow = array();
                        foreach ($columns as $col) {
                            if (isset($rowData[$col])) {
                                $sortedRow[$col] = $rowData[$col];
                            } else {
                                $sortedRow[$col] = '';
                            }
                        }

                        if ($_POST['format'] === 'html') {
                            echo '<tr><td>' . implode('</td><td>', $sortedRow) . '</td></tr>';
                        } else {
                            fputcsv($outstream, $sortedRow, $d);
                        }
                    }
                }
            }
        }
    }

    if ($_POST['format'] === 'html') {
        echo '</tbody></table></body></html>';
    } elseif ($_POST['format'] === 'summary') {
?>
    ];
    
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
      var statsSet, col, values, value;
      
      for (setName in flatData) {
          outputStats[setName] = {};
          
          for (i=0, len=dependents.length; i<len; ++i) {
              col      = dependents[i];
              statsSet = {};
              values   = [];
              
              for (j=0, len2=flatData[setName].length; j<len2; ++j) {
                  value = flatData[setName][j][col];
                  
                  if (value === '' || isNaN(value)) continue; // skip missing data (as determined by an empty string in this column)
                      
                  value = parseFloat(value);
                  
                  values.push(value);
              }
              
              statsSet.values = values;
              statsSet.count  = values.length;
              
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
                  
                  statsSet.ave = statsSet.sum / statsSet.count;
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
      if (tableRows[i]==="") continue;
      
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
    
    // can a string version of crosstabs be done here!?
    
    
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
          url: "GetData/getdataImgGenerate.php", 
          data: 'data='+jsonBarChart+"&yAxis=" + yAxisLabel,
          success: function(result){
                     $("#barChart").html("<img src='GetData/" + result + "'>");
                   },
          dataType: "text"
        });
    }
  });
</script>
</body>
</html>
<?php
    } else {
        fclose($outstream);
    }
