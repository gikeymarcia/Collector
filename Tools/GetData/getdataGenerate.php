<?php
    adminOnly();
    ob_end_clean();
    
    $requiredInputs = array('u', 'c', 'format', 'files');
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
    
    $columns = $_POST['c'];
    
    
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
                        
                        unset($row['Username'], $row['ID']);
                        
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
            
            if (!isset($dataFiles['exp'])) {
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
    } else {
        fclose($outstream);
    }
