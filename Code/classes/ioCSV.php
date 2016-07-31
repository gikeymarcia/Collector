<?php
/**
 * This is the implementation of the CSV reader as promised
 * in Code/classes/ioAbstractDataType.php
 */
class ioCSV extends ioAbstractDataType {
    /**
     * Things I'm trying to handle (e.g. should write tests for)
     *
     * 1. trim all cells
     * 2. skip empty rows and columns
     * 3. skip contents in columns without a header
     *    (i.e. dont give an error for different length rows)
     */
    public static function read($path) {
        if (!is_file($path)) return array();

        $fileStream = fopen($path, 'r');
        $data = array();

        while ($lineRaw = fgetcsv($fileStream)) {
            $isEmpty = false;
            $line = array();

            foreach ($lineRaw as $i => $cell) {
                $cleanCell = trim($cell);

                if ($cleanCell !== '') $isEmpty = false;

                $line[$i] = $cleanCell;
            }

            if ($isEmpty) continue;

            if (!isset($headers)) {
                foreach ($line as $i => $cell) {
                    if ($cell === '') unset($line[$i]); // remove empty columns
                }

                $headers = $line;
                $headersCount = count($line);
                continue;
            }

            if (count($line) == $headersCount) {
                $data[] = array_combine($headers, $line);
            } else {
                $row = array();

                foreach ($headers as $i => $header) {
                    if (isset($line[$i])) {
                        $row[$header] = $line[$i];
                    } else {
                        $row[$header] = '';
                    }
                }

                $data[] = $row;
            }
        }

        fclose($fileStream);
        return $data;
    }

    /**
     * Things I'm trying to handle (e.g. should write tests for)
     *
     * 1. if file doesnt exist, create row and write headers
     * 2. if new columns are being written, add columns to first row of file
     * 3. sort row in order of the headers of this file
     * 4. make sure I'm not allowing characters that break formatting when file is opened in Excel
     */
    public static function write($path, $data, $index = null) {
        if ($index !== null && (!is_numeric($index) || $index < 0)) {
            throw new Exception('Csv append index must be null or non-negative number');
        }

        if (is_numeric($index)) {
            $index = (int) $index;

            $existingData = self::read($path);

            if (!isset($existingData[$index])) {
                $existingData = array_pad($existingData, $index-1, array());
            }

            $existingData[$index] = $data;
            return self::write($path, $existingData);
        }

        if (!is_file($path)) {
            return self::write($path, array($data));
        }

        $fileStream = fopen($path, 'r+');
        $oldHeaders = array_flip(fgetcsv($fileStream));
        $newHeaders = array();

        foreach ($data as $key => $val) {
            if (!isset($oldHeaders[$key])) {
                $newHeaders[] = $key;
            }
        }

        if ($newHeaders !== array()) {
            $finalHeaders = array_merge(array_keys($oldHeaders), $newHeaders);
            $oldData = stream_get_contents($fileStream);
            rewind($fileStream);

            fputcsv($fileStream, $finalHeaders);
            fwrite($fileStream, $oldData);

        } else {
            fseek($fileStream, 0, SEEK_END);
            $finalHeaders = array_keys($oldHeaders);
        }

        $sortedData = array();

        foreach ($finalHeaders as $header) {
            if (isset($data[$header])) {
                $sortedData[$header] = $data[$header];
            } else {
                $sortedData[$header] = '';
            }
        }

        fputcsv($fileStream, $sortedData);

        return fclose($fileStream);
    }

    public static function writeMany($path, $data) {
        foreach ($data as $row) {
            $lastWrite = self::append($path, $row, null);
        }

        return $lastWrite;
    }

    public static function overwrite($path, $data) {
        $dir = dirname($path);

        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $fileStream = fopen($path, 'w');

        $headers = array();

        foreach ($data as $row) {
            foreach ($row as $header => $val) {
                $headers[$header] = true;
            }
        }

        $headers = array_keys($headers);

        fputcsv($fileStream, $headers);

        foreach ($data as $row) {
            $sortedRow = array();

            foreach ($headers as $header) {
                if (isset($row[$header])) {
                    $sortedRow[$header] = $row[$header];
                } else {
                    $sortedRow[$header] = '';
                }
            }

            fputcsv($fileStream, $sortedRow);
        }

        return fclose($fileStream);
    }
}
