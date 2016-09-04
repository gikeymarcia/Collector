<?php
    if(!isset($_SESSION)) { exit; }
    ob_end_clean();
    // ini_set('html_errors', false);
    
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
	<link href="GetDataStyle.css" rel="stylesheet" type="text/css" />
	<title>Get Data</title>
</head>
<body>
	<table id="GetDataTable">
      <thead> <tr> <th><?= implode('</th><th>', $columns) ?></th> </tr> </thead>
        <tbody>
<?php
    } elseif ($_POST['format'] === 'summary' || $_POST['format'] === 'stats') {
        require $_PATH->get('Header');
?>
    <script>
      if (typeof jQuery === "undefined") {
        document.write("<script src='<?= $_PATH->get('Jquery', 'url') ?>'><\/script>");
      }
    </script>
    
    <script src="summaryFunctions.js"></script>
    
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
                    } elseif ($_POST['format'] === 'stats') {
                        echo ",\r\n", json_encode(array_values($sortedRow));
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
                        } elseif ($_POST['format'] === 'stats') {
                            echo ",\r\n", json_encode(array_values($sortedRow));
                        } else {
                            fputcsv($outstream, $sortedRow, $d);
                        }
                        
                        /* 
                        $json_sorted_row=json_encode($sortedRow);
                        
                        // Anthony injection //
                        ?>
                        <script>
                        if(typeof(row_array)=="undefined"){
                          row_array=[<?= $json_sorted_row ?>];
                        } else {
                          row_array[row_array.length]=<?= $json_sorted_row ?>;
                        }
                        </script>
                        <?php
                         */
                        // End of Anthony injection //
                    }
                }
            }
        }
    }
    
    if ($_POST['format'] === 'html') {
        echo '</tbody></table></body></html>';
    } elseif ($_POST['format'] === 'stats') {
?>
    ];    
<?php
        echo '</script>';
        require 'statsPage.php';
    } elseif ($_POST['format'] === 'summary') {
?>
    ];
<?php
        echo '</script>';
        require 'summaryPage.php';
    } else {
        fclose($outstream);
    }
