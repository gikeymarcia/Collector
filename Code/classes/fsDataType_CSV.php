<?php

abstract class fsDataType_CSV extends fsAbstractDataType
{
    /**
     * Things I'm trying to handle (e.g. should write tests for)
     *
     * 1. trim all cells
     * 2. skip empty rows
     * 3. dont give an error for different length rows
     */
    public static function read($path) {
        if (!is_file($path)) return array();

        $fileStream = fopen($path, 'r');
        $data = array();

        $headers = self::readRow($fileStream);

        while ($row = self::readRow($fileStream, $headers)) {
            $data[] = $row;
        }

        fclose($fileStream);
        return $data;
    }

    public static function overwrite($path, $data) {
        $dir = dirname($path);

        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $data = self::trimHeaders($data);
        $fileStream = fopen($path, 'w');
        $headers = array();

        foreach ($data as $row) {
            foreach ($row as $header => $val) {
                $headers[$header] = true;
            }
        }

        $headers = array_keys($headers);
        $written = fputcsv($fileStream, $headers);
        $written += self::writeRows($fileStream, $headers, $data);
        fclose($fileStream);
        return $written;
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
            return self::rewriteLine($path, $data, $index);
        } else {
            return self::writeMany($path, array($data));
        }
    }

    public static function writeMany($path, $data) {
        if (!is_file($path)) {
            return self::overwrite($path, $data);
        }

        $data           = self::trimHeaders($data);
        $fileStream     = fopen($path, 'r+');
        $oldHeaders     = fgetcsv($fileStream);
        $oldHeadersFlip = array_flip($oldHeaders);
        $newHeadersFlip = array();

        foreach ($data as $row) {
            foreach ($row as $col => $cell) {
                if (!isset($oldHeadersFlip[$col])) {
                    $newHeadersFlip[$col] = true;
                }
            }
        }

        if ($newHeadersFlip !== array()) {
            $finalHeaders = array_merge($oldHeaders, array_keys($newHeadersFlip));
            $oldData = stream_get_contents($fileStream);
            rewind($fileStream);

            fputcsv($fileStream, $finalHeaders);
            fwrite($fileStream, $oldData);

        } else {
            fseek($fileStream, 0, SEEK_END);
            $finalHeaders = $oldHeaders;
        }

        $written = self::writeRows($fileStream, $finalHeaders, $data);
        fclose($fileStream);
        return $written;
    }

    private static function writeRows($fileStream, $headers, $rows) {
        $written = 0;

        foreach ($rows as $row) {
            $sortedRow = array();

            foreach ($headers as $header) {
                $sortedRow[$header] = isset($row[$header]) ? $row[$header] : '';
            }

            $written += fputcsv($fileStream, $sortedRow);
        }

        return $written;
    }

    private static function readRow($fileStream, $headers = null) {
        $rowIsEmpty = true;

        while ($rowIsEmpty) {
            $row = fgetcsv($fileStream);

            if ($row === false || $row === null) return $row;

            $cleanRow = array();

            foreach ($row as $cell) {
                if (($cleanRow[] = trim($cell)) !== '') $rowIsEmpty = false;
            }
        }

        if ($headers === null) {
            return $row;
        } else {
            $sortedRow = array();

            foreach ($headers as $i => $header) {
                $sortedRow[$header] = isset($row[$i]) ? $row[$i] : '';
            }

            return $sortedRow;
        }
    }

    private static function trimHeaders($data) {
        $trimmedData = array();

        foreach ($data as $i => $row) {
            foreach ($row as $header => $cell) {
                $trimmedData[$i][trim($header)] = $cell;
            }
        }

        return $trimmedData;
    }

    private static function rewriteLine($path, $data, $index) {
        $index = (int) $index;
        $fileStream = fopen($path, 'r+');
        $headers = self::readRow($fileStream);
        $currentIndex = 0;
        $newData = self::trimHeaders(array($data));

        do {
            if ($currentIndex === $index) {
                $rewriteOffset = ftell($fileStream);
                self::readRow($fileStream); // skip this row, we are about to overwrite
                $remainingData = stream_get_contents($fileStream);
                fseek($fileStream, $rewriteOffset, SEEK_SET); // go back to the beginning of the row we are replacing
                $written = self::writeRows($fileStream, $headers, $newData);
                fwrite($fileStream, $remainingData);
                fclose($fileStream);
                return $written;
            }

            ++$currentIndex;
        } while (self::readRow($fileStream));

        // turns out, the row we are replacing doesnt exist
        // so, create some padding rows
        $paddingContent = array_pad(array(), count($headers), '_');
        $paddingContent = array_combine($headers, $paddingContent);
        $paddingRowCount = $index - $currentIndex + 1;
        $allNewData = array_pad(array(), $paddingRowCount, $paddingContent); // padding rows
        $allNewData[] = $newData[0]; // add new row to the end of padding
        $written = self::writeRows($fileStream, $headers, $allNewData);
        fclose($fileStream);
        return $written;
    }
}
