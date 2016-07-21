<?php
adminOnly();

function getHeadersInDir($dir) {
    if (!is_dir($dir)) return array();

    $allHeaders = array();

    $scan = scandir($dir);
    foreach ($scan as $entry) {
        if (substr($entry, -4) !== '.csv') continue;

        $fileResource = fopen("$dir/$entry", 'r');
        $headers = fgetcsv($fileResource);
        fclose($fileResource);

        if ($headers !== false AND $headers !== null) {
            foreach ($headers as $header) {
                $allHeaders[$header] = true;
            }
        }
    }

    return array_keys($allHeaders);
}

function getdataReadCsv($filename) {
    $output = array();

    $fileRes = fopen($filename, 'r');
    $headers = fgetcsv($fileRes);
    if ($headers === false || $headers === null) {
        fclose($fileRes);
        return $output;
    }
    $headersCount = count($headers);

    while ($line = fgetcsv($fileRes)) {
        if (count($line) === $headersCount) {
            $output[] = array_combine($headers, $line);
        } else {
            $row = array();
            foreach ($headers as $i => $header) {
                if (isset($line[$i])) {
                    $row[$header] = $line[$i];
                } else {
                    $row[$header] = '';
                }
            }
            $output[] = $row;
        }
    }

    fclose($fileRes);

    return $output;
}

function getdataReadCsvByIndex($filename, $index) {
    $output = array();

    $fileRes = fopen($filename, 'r');
    $headers = fgetcsv($fileRes);
    if ($headers === false || $headers === null) {
        fclose($fileRes);
        return $output;
    }
    $headersCount = count($headers);

    while ($line = fgetcsv($fileRes)) {
        if (count($line) === $headersCount) {
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
        if (isset($row[$index])) $output[$row[$index]] = $row;
    }

    fclose($fileRes);

    return $output;
}
