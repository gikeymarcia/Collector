<?php
adminOnly();

function getDataScan(Pathfinder $_path) {
    // Plan:
    // scan Data/ for all data folders
    // scan those folders for output and Debug/output folders
    // scan each of those folders for status, sidedata, and exp output
    // read first line of each file for headers
    // read all status data to get condition and user info

    // at the end, return $getDataScan with all needed data
    $getDataScan = array();
    $getDataScan['Experiments'] = array();
    $getDataScan['Columns']     = array();
    $getDataScan['Columns']['Output']       = array();
    $getDataScan['Columns']['Side']         = array();
    $getDataScan['Columns']['Status_Begin'] = array();
    $getDataScan['Columns']['Status_End']   = array();

    // find experiments with data folders
    $dataDirPath = $_path->get('Data');
    $dataDirScan = scandir($dataDirPath);
    foreach ($dataDirScan as $i => $entry) {
        if ($entry === '.'
            || $entry === '..'
            || !is_dir("$dataDirPath/$entry")
            || substr($entry, -5) !== '-Data'
        ) {
            unset($dataDirScan[$i]);
        } else {
            $dataDirScan[$i] = substr($entry, 0, -5); // leave out the "-Data" suffix
        }
    }

    // list data subdirectories to search inside
    $dataSubDirs = array(
        'Normal' => '',
        'Debug'  => '/Debug'
    );

    // start scanning every experiment data folder
    foreach ($dataDirScan as $exp) {
        $_path->setDefault('Current Experiment', $exp);

        // look for both normal and debug data
        foreach ($dataSubDirs as $debugType => $dataSubDir) {
            $_path->setDefault('Data Sub Dir', $dataSubDir);

            if (!is_dir($_path->get('Current Data Dir'))) continue;

            // find data locations
            $status = array (
                'Begin' => $_path->get('Status Begin Data'),
                'End'   => $_path->get('Status End Data')
            );

            foreach (getHeadersInDir($_path->get('Output Dir')) as $header) {
                $getDataScan['Columns']['Output'][$header] = true;
            }

            if (is_file($_path->get('SideData Data'))) {
                $sideDataFileResource = fopen($_path->get('SideData Data'), 'r');
                $headers = fgetcsv($sideDataFileResource);

                foreach ($headers as $header) {
                    if ($header === 'Username' OR $header === 'ID') continue;

                    $getDataScan['Columns']['Side'][$header] = true;
                }

            } else {
                $getDataScan['Columns']['Side'] = array();
            }

            // get user info from status begin and end
            $userInfo = array();

            foreach ($status as $type => $file) {
                if (is_file($file)) {
                    $data = getdataReadCsv($file);
                } else {
                    $data = array();
                }

                // while we are here, get the columns of status files
                $firstRow = reset($data); // reset() will return the first row of data if it exists, false otherwise
                if ($firstRow !== false) {
                    foreach ($firstRow as $header => $value) {
                        $getDataScan['Columns']['Status_'.$type][$header] = true;
                    }
                }

                foreach ($data as $row) {
                    $cond = $row['Cond_Description'];
                    $name = $row['Username'];
                    $id   = $row['ID'];
                    unset($row['Username'], $row['ID']);
                    foreach ($row as $header => $value) {
                        if (substr($header, 0, 5) === 'Cond_') unset($row[$header]);
                    }
                    $userInfo[$cond][$name][$id][$type] = $row;
                }
            }

            // set Complete Data, so that it appears first in the array
            $userInfoFlagged = array('Complete Data' => array());

            foreach ($userInfo as $cond => $conds) {
                foreach ($conds as $username => $ids) {
                    $hasFinished = false;
                    foreach ($ids as $id => $statuses) {
                        if (isset($statuses['End']['State']) AND $statuses['End']['State'] === 'done') {
                            $hasFinished = true;
                            break;
                        }
                    }
                    if ($hasFinished) {
                        $userFlag = 'Complete Data';
                    } else {
                        $userFlag = 'Incomplete Data';
                    }
                    $userInfoFlagged[$userFlag][$cond][$username] = $ids;
                }
            }

            // if there are no complete data, remove the empty array
            if (count($userInfoFlagged['Complete Data']) < 1) unset($userInfoFlagged['Complete Data']);

            $getDataScan['Experiments'][$exp][$debugType] = $userInfoFlagged;
        }
    }

    foreach ($getDataScan['Columns'] as $category => $columns) {
        $getDataScan['Columns'][$category] = array_keys($columns);
    }

    return $getDataScan;
}
